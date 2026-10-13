# CareRoute Android app

**Version 1.7.0** connects patients, hospitals and ambulance drivers to the existing Laravel web backend. SOS automatically captures a front-camera photo and records 10 seconds of audio inside the Android app. No shutter, gallery or media confirmation is needed. The first use requires Android camera/microphone permission. Drivers see the photo and can play the voice recording; both are privately stored with the SOS. Login and ordinary care booking behavior are preserved. The minimal responsive interface remains.

[Download the APK](../../deliverables/Hospital-Test.apk) · [Test accounts](../../docs/TEST-ACCOUNTS.md) · [Shared database setup](../../docs/WEB-APP-DATABASE.md)

## Connect and sign in

Keep the phone and backend PC on the same Wi-Fi, with Apache/MySQL running. Use **Server connection → Current test server → Test & save** to select the address bundled with this APK. Enter another address if the PC's IP changes. The web and app use the same accounts.

## Main screens

| Screen | Purpose |
| --- | --- |
| SOS / Dispatch | Request emergency assistance or manage driver pickups |
| Request care | Describe the problem and select hospital or ambulance assistance |
| Requests | Review booking status, approvals and patient arrival |
| Beds | See current hospital bed availability |
| Account | Manage family/insurance records and connection settings |

Screens are shown according to your role and assigned permissions. Internal cases prefer ambulance assistance; External cases prefer suitable hospital beds. Both booking options remain available.

Read [SOS photo flow and device checks](../../docs/SOS-PHOTO.md). Use this APK with the updated backend; older apps cannot send a new SOS without both required attachments.

## Development

```sh
flutter pub get
flutter analyze
flutter test
flutter run --dart-define=API_BASE_URL=http://10.0.2.2/Hospital/public/api/mobile
```

To package the local Android test build, run `build-test-apk.ps1`. It keeps the existing server configuration and copies the APK to `../../deliverables/Hospital-Test.apk`.

[UI verification](../../docs/UI-REFRESH.md) · [Booking demo](../../docs/CARE-BOOKING-DEMO.md) · [Insurance and family SOS](../../docs/INSURANCE-FAMILY-SOS.md)
