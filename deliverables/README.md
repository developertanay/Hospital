# CareRoute Android — 1.7.0

[**Download Hospital-Test.apk**](Hospital-Test.apk) · [Test accounts](../docs/TEST-ACCOUNTS.md) · [Automatic SOS guide](../docs/SOS-PHOTO.md)

| Item | Value |
| --- | --- |
| Version | 1.7.0 (13) |
| Package | `com.careroute.careroute` |
| Android | 7.0+ (minimum API 24; target API 36) |
| Architectures | ARM32, ARM64, x86_64 |
| Signing | Debug test build; APK v2 signature verified |
| Size | 162,078,155 bytes (about 155 MiB) |
| Server | `http://10.9.6.195/Hospital/public/api/mobile` |

## This update

Tap SOS to automatically capture a front-camera photo and record 10 seconds of audio. No shutter, gallery or media confirmation is needed. Android asks for camera/microphone permission on first use; capture then runs automatically while the app stays in the foreground.

The photo and recording are privately stored with the SOS. Eligible/assigned drivers can view the photo and play audio in the app or web dispatch. Denied permission or interrupted/incomplete capture sends no new SOS.

Login, ordinary ambulance/bed booking, dispatch rules, recipient forms and the existing minimal interface remain unchanged. No new app dependency, migration, seeder or password reset is required. Use this APK with the updated backend; older APKs cannot send a new SOS without both attachments.

## Install and connect

Install over the previous test version. Keep Apache/MySQL running and the phone on the PC's Wi-Fi. Choose **Server connection → Current test server → Test & save** if the app remembers an older address.

The dedicated test accounts still use `TestCare@2026`; see [the account list](../docs/TEST-ACCOUNTS.md).

## Verified

52 Flutter tests and clean analysis; 64 automatic voice checks, 60 photo checks, 30 SOS API checks, 50 shared SQL checks and 44 insurance/family checks passed. All five real API logins and the web voice/history display check passed. Database writes roll back and media fixtures use disposable private folders. Android compilation and APK package/version/v2 signature were verified.

Physical phone camera/microphone capture, GPS and installation remain device checks. Follow [the two-phone test](../docs/SOS-PHOTO.md).

SHA-256: `AB897C9E32BB82C513161403C3A4E357E0C59917440B9831CCC6D198F520D2D4`.
