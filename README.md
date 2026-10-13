<img src="public/assets/images/hospital-logo.svg" width="64" alt="CareRoute hospital logo">

# Hospital & CareRoute

A shared care coordination platform for patients, hospitals and ambulance drivers. The Laravel web portal manages resources and approvals; the Flutter Android app handles care requests, SOS and pickup tracking. Both use the same MySQL database.

[**System flow & jury pitch**](docs/SYSTEM-FLOW-AND-PITCH.md) · [Download APK](deliverables/Hospital-Test.apk) · [Setup](docs/SETUP.md) · [Test logins](docs/TEST-ACCOUNTS.md)

## Current release: 1.7.0 (13)

SOS automatically captures a front-camera photo and records **10 seconds of audio** inside the Android app, then submits them with the pickup location. There is no shutter or media confirmation. Android camera/microphone permission is required; capture runs in the foreground. Drivers can view the photo and play the recording, and both remain privately stored with the SOS.

Other features include database-backed master CRUD, department-based hospital matching, bed/blood inventory, driver availability, family profiles, insurance and hospital panel mappings. Role menus follow admin-assigned modules.

## Roles and care flow

| Role | Main responsibilities |
| --- | --- |
| Admin | Hospitals, departments, ambulance categories/vehicles, accounts, masters and module permissions |
| Hospital | Own beds, blood stock, accepting status, request verification, approval, arrival and discharge |
| Patient | Own/family/other-person requests, hospital or ambulance selection, tracking and coverage records |
| Driver | On-duty location, offered/assigned pickups, trip updates, transport and hospital handoff |

| Request type | First option | Alternative |
| --- | --- | --- |
| Internal problem | Nearby idle ambulance with estimated arrival | Hospital bed booking |
| External injury | Nearby compatible hospital and bed | Ambulance pickup |
| Direct SOS | Automatic photo/audio capture, then offers to eligible nearby drivers | Hospital transfer can follow pickup |

Hospitals are filtered by service, accepting status and compatible bed availability. Selected ambulance bookings go to the chosen driver; SOS broadcasts to eligible drivers and the first confirmed acceptance wins. Bed requests need hospital verification and acceptance. Insurance verification and cashless decisions are separate from bed approval.

## Run the existing installation

1. Start **Apache** and **MySQL** in XAMPP.
2. Open [web login](http://localhost/Hospital/public/login) and use an existing account or [test login](docs/TEST-ACCOUNTS.md).
3. Install [CareRoute 1.7.0](deliverables/Hospital-Test.apk) on an Android 7.0+ phone connected to the PC's Wi-Fi.
4. In **Server connection**, choose **Current test server → Test & save** if the app remembers an older address. Web and app use the same accounts.

| Client | Address |
| --- | --- |
| PC web portal | `http://localhost/Hospital/public/login` |
| Phone API bundled with this APK | `http://10.9.6.195/Hospital/public/api/mobile` |
| Android emulator API | `http://10.0.2.2/Hospital/public/api/mobile` |

If the PC's Wi-Fi IP changes, update the app's server address. The populated database is `colleges`; keep the existing database and application key. A new installation needs the legacy schema plus the additive migrations in [SETUP.md](docs/SETUP.md). The SOS media update itself needs no new migration or password reset.

## Interface preview

Teal/white screens adapt to small phones, landscape and tablets. History previews show two recent records with **View all / View less**; active requests stay visible. These widget-rendered previews use illustrative test data.

| Phone login | Tablet navigation |
| --- | --- |
| <img src="docs/images/app-login-phone.png" width="180" alt="Phone login preview"> | <img src="docs/images/app-home-tablet.png" width="320" alt="Tablet navigation preview"> |

## Verification and troubleshooting

The 1.7.0 build passed **52 Flutter tests**, 64 voice checks, 60 photo checks, 30 SOS API checks, 50 shared SQL checks, 44 insurance/family checks and all five real API logins. APK version/signature and web voice/history checks also passed. Layout tests cover 320–1440px widths, landscape and enlarged text.

From the project root:

```powershell
C:\xampp\php\php.exe tests/login-http-smoke.php
C:\xampp\php\php.exe tests/shared-database-api-smoke.php
C:\xampp\php\php.exe tests/sos-photo-smoke.php
C:\xampp\php\php.exe tests/sos-voice-smoke.php
```

From `mobile/careroute`, run `flutter analyze` and `flutter test`. SQL workflow checks roll back their writes; media tests use disposable private folders. Physical phone camera/microphone, GPS and installation checks remain pending.

| Issue | Check |
| --- | --- |
| App cannot connect | PC Wi-Fi IP, same network, Apache/MySQL and the API health endpoint |
| Login/module access fails | Enabled account, admin-assigned modules and hospital/ambulance assignment |
| SOS capture cannot finish | Camera/microphone permissions and foreground capture; both attachments are required |
| No ambulance listed | Driver must be on duty, idle and sharing fresh real GPS |
| No hospital listed | Required department/service, accepting status and a compatible available bed |
| Web form returns 419 | Refresh and sign in again |

## Documentation

| Guide | Purpose |
| --- | --- |
| [System flow & jury pitch](docs/SYSTEM-FLOW-AND-PITCH.md) | Role journey, spoken pitch, demonstration and jury questions |
| [Setup](docs/SETUP.md) | XAMPP, migrations, networking and sample data |
| [Test accounts](docs/TEST-ACCOUNTS.md) | Current local web/app logins |
| [Database & architecture](docs/WEB-APP-DATABASE.md) | MVC structure, shared tables, ownership and transactions |
| [Automatic SOS media](docs/SOS-PHOTO.md) | Capture, private storage, playback and device checks |
| [Insurance & family](docs/INSURANCE-FAMILY-SOS.md) | Coverage/panel CRUD and hospital review |
| [Android development](mobile/careroute/README.md) | Run, test and build commands |
| [APK details](deliverables/README.md) | Version, compatibility and checksum |

The current APK is a debug-signed local test build. GPS/status updates require the app to stay open. Background push, road-routing ETAs and live insurer/TPA integrations are not implemented. Automatic SOS capture is Android-only in this release.
