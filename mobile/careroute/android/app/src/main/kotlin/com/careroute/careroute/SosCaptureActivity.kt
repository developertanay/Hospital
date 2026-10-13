package com.careroute.careroute

import android.Manifest
import android.app.Activity
import android.content.Intent
import android.content.pm.PackageManager
import android.graphics.Bitmap
import android.graphics.BitmapFactory
import android.graphics.Color
import android.graphics.ImageFormat
import android.graphics.Matrix
import android.graphics.SurfaceTexture
import android.hardware.camera2.CameraCaptureSession
import android.hardware.camera2.CameraCharacteristics
import android.hardware.camera2.CameraDevice
import android.hardware.camera2.CameraManager
import android.hardware.camera2.CaptureRequest
import android.media.ExifInterface
import android.media.ImageReader
import android.media.MediaMetadataRetriever
import android.media.MediaRecorder
import android.os.Bundle
import android.os.Handler
import android.os.HandlerThread
import android.os.Looper
import android.os.SystemClock
import android.util.Size
import android.view.Gravity
import android.view.Surface
import android.view.TextureView
import android.view.WindowManager
import android.widget.LinearLayout
import android.widget.TextView
import java.io.ByteArrayInputStream
import java.io.ByteArrayOutputStream
import java.io.File
import kotlin.math.max
import kotlin.math.roundToInt

/** Foreground-only SOS evidence capture; it has no shutter, review, or submit controls. */
class SosCaptureActivity : Activity(), TextureView.SurfaceTextureListener {
    private val ui = Handler(Looper.getMainLooper())
    private lateinit var preview: TextureView
    private lateinit var status: TextView
    private var cameraThread: HandlerThread? = null
    private var cameraHandler: Handler? = null
    private var camera: CameraDevice? = null
    private var cameraSession: CameraCaptureSession? = null
    private var imageReader: ImageReader? = null
    private var previewSurface: Surface? = null
    private var recorder: MediaRecorder? = null
    private var photoFile: File? = null
    private var voiceFile: File? = null
    private var previewSize = Size(640, 480)
    private var jpegRotation = 0
    private var began = false
    private var recording = false
    private var photoReady = false
    private var voiceReady = false
    private var recordingStartedAt = 0L
    private var voiceDurationMs = 0L
    @Volatile private var finished = false
    private var foreground = false

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        window.addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON or WindowManager.LayoutParams.FLAG_SECURE)
        window.statusBarColor = Color.rgb(15, 118, 110)
        window.navigationBarColor = Color.rgb(15, 118, 110)
        val layout = LinearLayout(this).apply {
            orientation = LinearLayout.VERTICAL
            gravity = Gravity.CENTER_HORIZONTAL
            setBackgroundColor(Color.rgb(244, 248, 247))
            val inset = (20 * resources.displayMetrics.density).roundToInt()
            setPadding(inset, inset, inset, inset)
            setOnApplyWindowInsetsListener { view, insets ->
                view.setPadding(inset, inset + insets.systemWindowInsetTop, inset, inset + insets.systemWindowInsetBottom)
                insets
            }
        }
        layout.addView(TextView(this).apply {
            text = "Preparing SOS"
            textSize = 24f
            setTextColor(Color.rgb(23, 53, 47))
            gravity = Gravity.CENTER
        }, LinearLayout.LayoutParams(-1, -2))
        status = TextView(this).apply {
            text = "Starting front camera and microphone…"
            textSize = 16f
            setTextColor(Color.rgb(15, 118, 110))
            gravity = Gravity.CENTER
            setPadding(0, 16, 0, 16)
        }
        layout.addView(status, LinearLayout.LayoutParams(-1, -2))
        preview = TextureView(this).apply { surfaceTextureListener = this@SosCaptureActivity }
        layout.addView(preview, LinearLayout.LayoutParams(-1, 0, 1f))
        setContentView(layout)
        // Process recreation must never send stale or partly recorded SOS media.
        if (savedInstanceState != null) {
            fail("SOS capture was interrupted. Please try again.", "sos_capture_cancelled")
            return
        }
        if (!hasPermissions()) {
            requestPermissions(arrayOf(Manifest.permission.CAMERA, Manifest.permission.RECORD_AUDIO), PERMISSION_REQUEST)
        }
    }

    override fun onResume() {
        super.onResume()
        foreground = true
        tryStart()
    }

    private fun hasPermissions(): Boolean =
        checkSelfPermission(Manifest.permission.CAMERA) == PackageManager.PERMISSION_GRANTED &&
            checkSelfPermission(Manifest.permission.RECORD_AUDIO) == PackageManager.PERMISSION_GRANTED

    override fun onRequestPermissionsResult(requestCode: Int, permissions: Array<out String>, grantResults: IntArray) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults)
        if (requestCode != PERMISSION_REQUEST || finished) return
        if (hasPermissions()) tryStart()
        else fail("Camera and microphone access are needed for SOS capture.", "sos_capture_permission_denied")
    }

    private fun tryStart() {
        if (finished || began || !foreground || !preview.isAvailable || !hasPermissions()) return
        began = true
        try {
            val directory = File(cacheDir, "sos_capture").apply { mkdirs() }
            // Remove only abandoned evidence files belonging to this capture feature.
            directory.listFiles()?.filter { System.currentTimeMillis() - it.lastModified() > 3_600_000L }?.forEach { it.delete() }
            photoFile = File.createTempFile("photo_", ".jpg", directory)
            voiceFile = File.createTempFile("voice_", ".m4a", directory)
            cameraThread = HandlerThread("SosFrontCamera").apply { start() }
            cameraHandler = Handler(cameraThread!!.looper)
            val manager = getSystemService(CAMERA_SERVICE) as CameraManager
            val id = manager.cameraIdList.firstOrNull {
                manager.getCameraCharacteristics(it).get(CameraCharacteristics.LENS_FACING) == CameraCharacteristics.LENS_FACING_FRONT
            } ?: throw IllegalStateException("A front camera is required for SOS capture.")
            val characteristics = manager.getCameraCharacteristics(id)
            val map = characteristics.get(CameraCharacteristics.SCALER_STREAM_CONFIGURATION_MAP)
                ?: throw IllegalStateException("The front camera is unavailable.")
            val jpegSize = chooseSize(map.getOutputSizes(ImageFormat.JPEG))
            previewSize = chooseSize(map.getOutputSizes(SurfaceTexture::class.java))
            val sensor = characteristics.get(CameraCharacteristics.SENSOR_ORIENTATION) ?: 0
            val displayDegrees = when (windowManager.defaultDisplay.rotation) {
                Surface.ROTATION_90 -> 90
                Surface.ROTATION_180 -> 180
                Surface.ROTATION_270 -> 270
                else -> 0
            }
            jpegRotation = (sensor + displayDegrees) % 360
            imageReader = ImageReader.newInstance(jpegSize.width, jpegSize.height, ImageFormat.JPEG, 2).apply {
                setOnImageAvailableListener({ reader ->
                    val captured = try { reader.acquireLatestImage() } catch (_: Exception) { null }
                    if (captured != null) {
                        try {
                            if (!finished) {
                                val buffer = captured.planes[0].buffer
                                val bytes = ByteArray(buffer.remaining())
                                buffer.get(bytes)
                                writePhoto(bytes)
                                runOnUiThread {
                                    if (!finished) {
                                        photoReady = true
                                        completeIfReady()
                                    }
                                }
                            }
                        } catch (_: Exception) {
                            fail("The SOS photo could not be captured. Please try again.")
                        } finally {
                            captured.close()
                            if (finished && !photoReady) photoFile?.delete()
                        }
                    }
                }, cameraHandler)
            }
            manager.openCamera(id, object : CameraDevice.StateCallback() {
                override fun onOpened(device: CameraDevice) {
                    runOnUiThread {
                        if (finished) device.close()
                        else {
                            camera = device
                            createPreview(device)
                        }
                    }
                }
                override fun onDisconnected(device: CameraDevice) {
                    device.close()
                    fail("The front camera was disconnected. Please try again.")
                }
                override fun onError(device: CameraDevice, error: Int) {
                    device.close()
                    fail("The front camera is unavailable. Close other camera apps and try again.")
                }
            }, cameraHandler)
            ui.postDelayed({ if (!finished) fail("SOS capture timed out. Please try again.") }, 20_000L)
        } catch (error: Exception) {
            fail(error.message ?: "SOS capture could not start. Please try again.")
        }
    }

    private fun chooseSize(sizes: Array<Size>?): Size {
        val available = sizes?.toList().orEmpty()
        require(available.isNotEmpty()) { "The front camera is unavailable." }
        val bounded = available.filter { max(it.width, it.height) <= 1280 }
        return bounded.maxByOrNull { it.width.toLong() * it.height }
            ?: available.minBy { it.width.toLong() * it.height }
    }

    private fun createPreview(device: CameraDevice) {
        try {
            val texture = preview.surfaceTexture ?: throw IllegalStateException("The camera preview is unavailable.")
            texture.setDefaultBufferSize(previewSize.width, previewSize.height)
            val surface = Surface(texture)
            previewSurface = surface
            val photoSurface = imageReader!!.surface
            device.createCaptureSession(listOf(surface, photoSurface), object : CameraCaptureSession.StateCallback() {
                override fun onConfigured(session: CameraCaptureSession) {
                    runOnUiThread {
                        if (finished) { session.close(); return@runOnUiThread }
                        cameraSession = session
                        try {
                            val request = device.createCaptureRequest(CameraDevice.TEMPLATE_PREVIEW).apply {
                                addTarget(surface)
                                set(CaptureRequest.CONTROL_MODE, CaptureRequest.CONTROL_MODE_AUTO)
                            }
                            session.setRepeatingRequest(request.build(), null, cameraHandler)
                            startRecording()
                        } catch (_: Exception) {
                            fail("The front camera could not start. Please try again.")
                        }
                    }
                }
                override fun onConfigureFailed(session: CameraCaptureSession) {
                    session.close()
                    fail("The front camera could not start. Please try again.")
                }
            }, cameraHandler)
        } catch (_: Exception) {
            fail("The front camera could not start. Please try again.")
        }
    }

    @Suppress("DEPRECATION")
    private fun startRecording() {
        if (finished || !foreground) return
        try {
            val audioRecorder = MediaRecorder()
            recorder = audioRecorder
            audioRecorder.apply {
                setAudioSource(MediaRecorder.AudioSource.MIC)
                setOutputFormat(MediaRecorder.OutputFormat.MPEG_4)
                setAudioEncoder(MediaRecorder.AudioEncoder.AAC)
                setAudioEncodingBitRate(64_000)
                setAudioSamplingRate(44_100)
                setAudioChannels(1)
                setOutputFile(voiceFile!!.absolutePath)
                setMaxDuration(10_000)
                setOnInfoListener { _, what, _ ->
                    if (what == MediaRecorder.MEDIA_RECORDER_INFO_MAX_DURATION_REACHED) finishRecording()
                }
                setOnErrorListener { _, _, _ -> fail("The microphone could not record. Please try again.") }
                prepare()
                start()
            }
            recordingStartedAt = SystemClock.elapsedRealtime()
            recording = true
            showCountdown()
            ui.postDelayed({ if (!finished) takePhoto() }, 900L)
            ui.postDelayed({ if (!finished && recording) finishRecording() }, 10_000L)
        } catch (_: Exception) {
            fail("The microphone could not record. Check microphone access and try again.")
        }
    }

    private fun showCountdown() {
        if (finished || !recording) return
        val remaining = ((10_000L - (SystemClock.elapsedRealtime() - recordingStartedAt)).coerceAtLeast(0L) + 999L) / 1000L
        status.text = "Capturing photo and voice · ${remaining}s"
        ui.postDelayed({ showCountdown() }, 250L)
    }

    private fun takePhoto() {
        try {
            val device = camera ?: throw IllegalStateException("The front camera is unavailable.")
            val request = device.createCaptureRequest(CameraDevice.TEMPLATE_STILL_CAPTURE).apply {
                addTarget(imageReader!!.surface)
                set(CaptureRequest.CONTROL_MODE, CaptureRequest.CONTROL_MODE_AUTO)
                set(CaptureRequest.JPEG_ORIENTATION, jpegRotation)
                set(CaptureRequest.JPEG_QUALITY, 85.toByte())
            }
            cameraSession!!.capture(request.build(), null, cameraHandler)
        } catch (_: Exception) {
            fail("The SOS photo could not be captured. Please try again.")
        }
    }

    private fun writePhoto(bytes: ByteArray) {
        val original = BitmapFactory.decodeByteArray(bytes, 0, bytes.size)
            ?: throw IllegalStateException("The SOS photo could not be captured.")
        val orientation = ExifInterface(ByteArrayInputStream(bytes)).getAttributeInt(
            ExifInterface.TAG_ORIENTATION, ExifInterface.ORIENTATION_NORMAL
        )
        val matrix = Matrix().apply {
            when (orientation) {
                ExifInterface.ORIENTATION_ROTATE_90 -> postRotate(90f)
                ExifInterface.ORIENTATION_ROTATE_180 -> postRotate(180f)
                ExifInterface.ORIENTATION_ROTATE_270 -> postRotate(270f)
                ExifInterface.ORIENTATION_FLIP_HORIZONTAL -> postScale(-1f, 1f)
                ExifInterface.ORIENTATION_FLIP_VERTICAL -> postScale(1f, -1f)
                ExifInterface.ORIENTATION_TRANSPOSE -> { postRotate(90f); postScale(-1f, 1f) }
                ExifInterface.ORIENTATION_TRANSVERSE -> { postRotate(270f); postScale(-1f, 1f) }
            }
        }
        var bitmap = if (matrix.isIdentity) original
        else Bitmap.createBitmap(original, 0, 0, original.width, original.height, matrix, true)
        if (bitmap !== original) original.recycle()
        try {
            val longest = max(bitmap.width, bitmap.height)
            if (longest > 1280) {
                val scale = 1280f / longest
                val resized = Bitmap.createScaledBitmap(bitmap, (bitmap.width * scale).roundToInt().coerceAtLeast(1),
                    (bitmap.height * scale).roundToInt().coerceAtLeast(1), true)
                if (resized !== bitmap) bitmap.recycle()
                bitmap = resized
            }
            val encoded = ByteArrayOutputStream()
            check(bitmap.compress(Bitmap.CompressFormat.JPEG, 85, encoded))
            val photo = encoded.toByteArray()
            require(photo.isNotEmpty() && photo.size <= 2_097_152) { "The SOS photo is too large." }
            if (!finished) photoFile!!.writeBytes(photo)
        } finally {
            bitmap.recycle()
        }
    }

    private fun finishRecording() {
        if (finished || !recording) return
        recording = false
        try {
            recorder?.setOnInfoListener(null)
            recorder?.setOnErrorListener(null)
            recorder?.stop()
            recorder?.release()
            recorder = null
            val metadata = MediaMetadataRetriever()
            try {
                metadata.setDataSource(voiceFile!!.absolutePath)
                voiceDurationMs = metadata.extractMetadata(MediaMetadataRetriever.METADATA_KEY_DURATION)?.toLongOrNull() ?: 0L
            } finally {
                metadata.release()
            }
            require(voiceDurationMs in 9000L..11000L && voiceFile!!.length() in 1L..2_097_152L)
            voiceReady = true
            status.text = "Preparing SOS…"
            completeIfReady()
        } catch (_: Exception) {
            fail("The complete 10-second voice recording could not be captured. Please try again.")
        }
    }

    private fun completeIfReady() {
        if (finished || !photoReady || !voiceReady) return
        finished = true
        closeResources()
        setResult(RESULT_OK, Intent().apply {
            putExtra("photo_path", photoFile!!.absolutePath)
            putExtra("voice_path", voiceFile!!.absolutePath)
            putExtra("voice_duration_ms", voiceDurationMs)
        })
        finish()
    }

    private fun fail(message: String, code: String = "sos_capture_failed") {
        if (Looper.myLooper() != Looper.getMainLooper()) {
            runOnUiThread { fail(message, code) }
            return
        }
        if (finished) return
        finished = true
        closeResources()
        photoFile?.delete()
        voiceFile?.delete()
        setResult(RESULT_CANCELED, Intent().putExtra("error", message).putExtra("error_code", code))
        finish()
    }

    private fun closeResources() {
        ui.removeCallbacksAndMessages(null)
        recording = false
        recorder?.setOnInfoListener(null)
        recorder?.setOnErrorListener(null)
        try { recorder?.stop() } catch (_: Exception) { }
        try { recorder?.release() } catch (_: Exception) { }
        recorder = null
        try { cameraSession?.close() } catch (_: Exception) { }
        cameraSession = null
        try { camera?.close() } catch (_: Exception) { }
        camera = null
        try { imageReader?.close() } catch (_: Exception) { }
        imageReader = null
        previewSurface?.release()
        previewSurface = null
        cameraThread?.quitSafely()
        cameraThread = null
        cameraHandler = null
    }

    override fun onPause() {
        foreground = false
        if (began && !finished) fail("SOS capture was interrupted. No SOS was sent.", "sos_capture_cancelled")
        super.onPause()
    }

    override fun onDestroy() {
        if (!finished) fail("SOS capture was interrupted. No SOS was sent.", "sos_capture_cancelled")
        closeResources()
        super.onDestroy()
    }

    @Deprecated("Back cancels the foreground capture.")
    override fun onBackPressed() {
        fail("SOS capture was cancelled. No SOS was sent.", "sos_capture_cancelled")
    }

    override fun onSurfaceTextureAvailable(surface: SurfaceTexture, width: Int, height: Int) = tryStart()
    override fun onSurfaceTextureSizeChanged(surface: SurfaceTexture, width: Int, height: Int) { }
    override fun onSurfaceTextureUpdated(surface: SurfaceTexture) { }
    override fun onSurfaceTextureDestroyed(surface: SurfaceTexture): Boolean {
        if (began && !finished) fail("SOS capture was interrupted. No SOS was sent.", "sos_capture_cancelled")
        return true
    }

    companion object {
        private const val PERMISSION_REQUEST = 1706
    }
}
