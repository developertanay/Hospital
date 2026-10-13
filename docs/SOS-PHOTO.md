# Automatic SOS photo and voice — CareRoute 1.7.0

Every new direct SOS automatically captures a front-camera photo of the **person sending it** and records **10 seconds of microphone audio**. This applies to Self, Family and Other recipients. The attachments identify the reporter and capture nearby audio; the photo is not labelled as the patient's photo.

## Android flow

1. With an existing emergency profile, tap **SOS · Request ambulance**. For a family/other-person request, complete the existing recipient details first.
2. An in-app screen selects the front camera, takes the photo automatically, and records audio for 10 seconds. There is no shutter, gallery, review or media confirmation button.
3. Recording stops automatically. The app refreshes pickup GPS and submits both attachments with the SOS. A remote patient's manual pickup pin stays unchanged.
4. Eligible ambulance drivers see **SOS sender photo** and **Play audio · 10s** on the offer/trip. Web driver dispatch also has a photo and audio controls.

Android's first-use camera and microphone permission prompts cannot be skipped. Once granted, media capture requires no further tap. Denied permission, interruption, a missing front camera/microphone, failed capture or failed upload sends no new SOS. Returning to the background stops capture; partial attachments are discarded. Ordinary ambulance selection, bed booking, login and recipient/profile rules remain unchanged.

Automatic capture/playback is implemented for the Android test app. iOS automatic capture is not implemented in this release.

## Server storage and access

The backend requires both attachments before creating an SOS. Photos are checked and stored as JPEG without embedded metadata. Voice recordings are AAC in an M4A container, up to 2 MiB. The server checks audio-only AAC structure and actual sample timing around 10 seconds (9–11 seconds allows encoder padding). It does not trust only the duration supplied by the app.

Photos stay under `storage/app/sos-reporter-photos`; recordings stay under `storage/app/sos-reporter-voices`. Both are private, outside public storage. The existing `ambulance_trips.payload` stores their associations; no new table, migration or password reset is needed. Include both folders with database backups. Attachments remain after cancellation or completion.

Only the SOS owner, an admin, or its currently offered/assigned driver can retrieve the attachments through authenticated endpoints. Other patients and hospitals cannot access them. Drivers whose offer disappears lose access; the assigned driver keeps its trip history. API state exposes authenticated URLs rather than private paths or attachment bytes. Audio responses support authenticated byte ranges for browser playback.

Retrying an already active SOS preserves its original attachments and avoids duplicate files. Failure while saving removes newly written attachments. Older trips without voice recordings continue to display normally. Use this APK with the updated backend: older apps cannot send a new SOS without the required photo and voice recording.

## Check on two phones

1. Keep XAMPP running. Install [the updated APK](../deliverables/Hospital-Test.apk), connect the phones to the PC's Wi-Fi and select the current server.
2. Sign in as a [test driver](TEST-ACCOUNTS.md), go on duty with GPS and keep the app open.
3. On the patient phone, tap SOS and allow camera/microphone access when Android requests it. The app should automatically take the front-camera photo and count down the 10-second recording, then send SOS without another tap.
4. The driver should receive the request, show the matching photo and play the full recording. Accept the pickup and check both remain with the trip.
5. Repeat for Family/Other after closing the first trip. Test denying permission or leaving capture: no incomplete SOS should be submitted.

## Automated checks

Verified: **52 Flutter tests**, 64 voice checks, 60 photo checks, 30 SOS API checks, 50 shared SQL checks and 44 insurance/family checks pass. All five dedicated accounts pass real API login.

From the project folder:

```powershell
C:\xampp\php\php.exe tests/sos-voice-smoke.php
C:\xampp\php\php.exe tests/sos-photo-smoke.php
C:\xampp\php\php.exe tests/login-http-smoke.php
```

From `mobile/careroute`, run `flutter analyze` and `flutter test`. Android compilation, app tests and server checks do not replace physical phone camera/microphone testing.
