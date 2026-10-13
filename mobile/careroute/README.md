# CareRoute Android app

**1.7.0 (13)** is the Flutter client for the shared Laravel/MySQL system. It includes care requests, hospital/ambulance selection, foreground tracking, family/coverage records and automatic SOS photo plus 10-second audio capture.

[Download APK](../../deliverables/Hospital-Test.apk) · [Test accounts](../../docs/TEST-ACCOUNTS.md) · [System flow & pitch](../../docs/SYSTEM-FLOW-AND-PITCH.md) · [Setup](../../docs/SETUP.md)

## Connect

Keep Apache/MySQL running and the phone on the backend PC's Wi-Fi. Use **Server connection → Current test server → Test & save**, or enter the PC's current API address. Phone `localhost` is the phone itself; the Android emulator uses `http://10.0.2.2/Hospital/public/api/mobile`.

Screens and menus follow the account's role and assigned modules. Internal care prefers ambulance selection; External care prefers a compatible hospital bed. Both alternatives remain available. SOS media capture is automatic after the existing profile/recipient details and Android camera/microphone permission. Read [the SOS guide](../../docs/SOS-PHOTO.md) for storage and interruption behavior.

## Develop and test

From this folder, with Flutter and Android SDK configured:

```sh
flutter pub get
flutter analyze
flutter test
flutter run --dart-define=API_BASE_URL=http://10.0.2.2/Hospital/public/api/mobile
```

The repository-local SDK, when installed, is `../../.tools/flutter`. Use `run-android.ps1 -ServerUrl http://PC-IP/Hospital/public/api/mobile` to check the backend and run. Add `-DeviceId <device-id>` to choose a device or `-BuildApk` to package a debug APK.

## Build

On this configured Windows workspace, run:

```powershell
.\build-test-apk.ps1
```

This builds the Android test APK with the configured API address and copies it to `../../deliverables/Hospital-Test.apk`. Pass `-ApiBaseUrl http://PC-IP/Hospital/public/api/mobile` for another server. General Flutter builds can use `flutter build apk --debug --dart-define=API_BASE_URL=http://PC-IP/Hospital/public/api/mobile`.

Release builds require HTTPS and production signing configuration. iOS builds require a Mac/Xcode; automatic SOS capture and playback are implemented only on Android. The 1.7.0 release passed 52 Flutter tests and analysis; physical camera/microphone/GPS behavior remains a device check.

[Database/API architecture](../../docs/WEB-APP-DATABASE.md) · [Insurance & family](../../docs/INSURANCE-FAMILY-SOS.md) · [APK checksum](../../deliverables/README.md)
