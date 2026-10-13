package com.careroute.careroute

import android.app.Activity
import android.content.Intent
import android.media.MediaPlayer
import android.util.Base64
import io.flutter.embedding.android.FlutterActivity
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.plugin.common.MethodChannel
import java.io.File
import java.util.concurrent.Executors

class MainActivity : FlutterActivity() {
    private var captureResult: MethodChannel.Result? = null
    private var voiceResult: MethodChannel.Result? = null
    private var voicePlayer: MediaPlayer? = null
    private var voiceFile: File? = null
    private val mediaWorker = Executors.newSingleThreadExecutor()

    override fun configureFlutterEngine(flutterEngine: FlutterEngine) {
        super.configureFlutterEngine(flutterEngine)
        MethodChannel(flutterEngine.dartExecutor.binaryMessenger, "careroute/sos_capture")
            .setMethodCallHandler { call, result ->
                when (call.method) {
                    "capture" -> {
                        if (captureResult != null) {
                            result.error("sos_capture_busy", "SOS capture is already running.", null)
                        } else {
                            stopVoice()
                            captureResult = result
                            try {
                                startActivityForResult(Intent(this, SosCaptureActivity::class.java), CAPTURE_REQUEST)
                            } catch (error: Exception) {
                                captureResult = null
                                result.error("sos_capture_failed", "The camera could not open. Please try again.", null)
                            }
                        }
                    }
                    "playVoice" -> playVoice(call.argument<String>("voice_base64"), result)
                    "stopVoice" -> {
                        stopVoice()
                        result.success(null)
                    }
                    else -> result.notImplemented()
                }
            }
    }

    @Deprecated("The result belongs to this foreground capture activity.")
    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        super.onActivityResult(requestCode, resultCode, data)
        if (requestCode != CAPTURE_REQUEST) return
        val pending = captureResult ?: return
        if (resultCode != Activity.RESULT_OK) {
            captureResult = null
            pending.error(
                data?.getStringExtra("error_code") ?: "sos_capture_cancelled",
                data?.getStringExtra("error") ?: "SOS capture was cancelled. No SOS was sent.",
                null
            )
            return
        }
        val photoPath = data?.getStringExtra("photo_path")
        val voicePath = data?.getStringExtra("voice_path")
        val duration = data?.getLongExtra("voice_duration_ms", 0L) ?: 0L
        mediaWorker.execute {
            var photo: File? = null
            var voice: File? = null
            try {
                val mediaDirectory = File(cacheDir, "sos_capture").canonicalFile
                photo = safeCaptureFile(photoPath, mediaDirectory)
                voice = safeCaptureFile(voicePath, mediaDirectory)
                require(duration in 9000L..11000L) { "The complete 10-second voice recording was not captured." }
                val payload = mapOf(
                    "photo_base64" to Base64.encodeToString(photo.readBytes(), Base64.NO_WRAP),
                    "voice_base64" to Base64.encodeToString(voice.readBytes(), Base64.NO_WRAP),
                    "voice_duration_ms" to duration
                )
                runOnUiThread {
                    if (captureResult === pending) {
                        captureResult = null
                        pending.success(payload)
                    }
                }
            } catch (error: Exception) {
                runOnUiThread {
                    if (captureResult === pending) {
                        captureResult = null
                        pending.error("sos_capture_failed", error.message ?: "SOS media could not be captured.", null)
                    }
                }
            } finally {
                photo?.delete()
                voice?.delete()
            }
        }
    }

    private fun safeCaptureFile(path: String?, directory: File): File {
        require(!path.isNullOrBlank()) { "SOS media is missing." }
        val file = File(path).canonicalFile
        require(file.parentFile == directory && file.isFile && file.length() in 1L..MAX_MEDIA_BYTES) {
            "SOS media is missing or too large. Please try again."
        }
        return file
    }

    private fun playVoice(encoded: String?, result: MethodChannel.Result) {
        stopVoice()
        try {
            require(encoded != null && encoded.length <= 2_800_000) { "The voice recording could not be played." }
            val bytes = Base64.decode(encoded, Base64.DEFAULT)
            require(bytes.isNotEmpty() && bytes.size <= MAX_MEDIA_BYTES) { "The voice recording could not be played." }
            val file = File.createTempFile("sos_voice_", ".m4a", cacheDir)
            voiceFile = file
            file.writeBytes(bytes)
            val player = MediaPlayer()
            voicePlayer = player
            voiceResult = result
            player.setOnCompletionListener { stopVoice() }
            player.setOnErrorListener { _, _, _ ->
                stopVoice("The voice recording could not be played.")
                true
            }
            player.setOnPreparedListener { prepared ->
                if (voicePlayer === prepared) prepared.start()
            }
            player.setDataSource(file.absolutePath)
            player.prepareAsync()
        } catch (error: Exception) {
            if (voiceResult === result) {
                stopVoice("The voice recording could not be played.")
            } else {
                stopVoice()
                result.error("sos_voice_failed", "The voice recording could not be played.", null)
            }
        }
    }

    private fun stopVoice(error: String? = null) {
        val result = voiceResult
        voiceResult = null
        voicePlayer?.setOnCompletionListener(null)
        voicePlayer?.setOnErrorListener(null)
        voicePlayer?.setOnPreparedListener(null)
        try { voicePlayer?.release() } catch (_: Exception) { }
        voicePlayer = null
        voiceFile?.delete()
        voiceFile = null
        if (error == null) result?.success(null)
        else result?.error("sos_voice_failed", error, null)
    }

    override fun onPause() {
        stopVoice()
        super.onPause()
    }

    override fun onDestroy() {
        stopVoice()
        captureResult?.error("sos_capture_cancelled", "SOS capture was interrupted. No SOS was sent.", null)
        captureResult = null
        mediaWorker.shutdown()
        super.onDestroy()
    }

    companion object {
        private const val CAPTURE_REQUEST = 1705
        private const val MAX_MEDIA_BYTES = 2_097_152L
    }
}
