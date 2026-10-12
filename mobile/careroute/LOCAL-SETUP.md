# CareRoute mobile: local setup and complete demo

**Current build:** see [the shared database setup](../../docs/WEB-APP-DATABASE.md). Ambulance dispatch now uses MySQL and SQL vehicle/account assignments. Provision `MobileTestAccountsSeeder` for verified test logins. The current PC Wi-Fi API address is `http://10.9.4.210/Hospital/public/api/mobile`; older IP addresses and fixed driver/A–E accounts below are historical prototype examples.

**After merging the SQL workflow:** hospitals, care types, inventory, accounts and permissions now come from MySQL. Follow the root README's eight additive migrations. Existing passwords remain, but operators need a real hospital assignment through Accounts & Hospital Access. `CareRouteDemoSeeder` is now a no-op; it no longer provisions patient/admin/hospital accounts. Patient signup still works in the app. Existing demo driver accounts are retained by migration 000008; new installations can explicitly run `AmbulanceDemoSeeder` for dispatch-only demo drivers. The older A–E examples below describe the previous prototype and are superseded by the SQL workflow.

For the new SOS + ambulance flow, saved emergency profile and driver accounts, read [SOS-DEMO.md](SOS-DEMO.md).

The Flutter app targets Android and iOS. It connects to the existing Laravel application; it does not contain a separate database or reset emergency data when installed. Patient requests, hospital acceptance, expiry and bed status are shared with the website.

## 1. Prepare the Laravel server

Start Apache and MySQL in XAMPP. Keep the existing working `.env`, database and app key. From `C:\xampp\htdocs\Hospital` run:

```powershell
php artisan db:seed --class=CareRouteDemoSeeder
php artisan migrate --path=database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php --force
php artisan config:clear
php artisan route:clear
```

Only the token migration is needed for mobile login; do not run every legacy migration against the populated database. The token table has already been created on this computer.

Test `http://localhost/Hospital/public/api/mobile/health` in the PC browser. It must return `{"service":"CareRoute","mobile_api":1,"mock_data":true}`. Phone login uses MySQL accounts, so MySQL must run as well as Apache.

## 2. Connect a phone to this local server

Your phone's `localhost` means the phone, not your PC. Both devices must be on the same Wi-Fi (a home router or phone hotspot is usually easiest; campus/guest Wi-Fi may isolate devices).

Run `ipconfig` on the PC and copy **Wi-Fi → IPv4 Address**. When this app was built it was `10.9.3.66`; it can change. Ignore VirtualBox/WSL/Hyper-V addresses.

| Device | Mobile API address |
| --- | --- |
| Android or iPhone on the PC's Wi-Fi | `http://10.9.3.66/Hospital/public/api/mobile` (replace IP) |
| Android Studio emulator on this PC | `http://10.0.2.2/Hospital/public/api/mobile` |
| Flutter browser preview on this PC | `http://localhost/Hospital/public/api/mobile` |
| iOS simulator on a Mac while Laravel runs on this Windows PC | Windows PC's Wi-Fi IP, not simulator localhost |
| iOS simulator with Laravel running on that same Mac | `http://localhost/Hospital/public/api/mobile` |

First open `http://PC-IP/Hospital/public/api/mobile/health` in the phone browser. Once that works, open the app → **Server connection** → paste the API address → **Test & save** → sign in. Changing servers signs out the previous account.

If the phone cannot open health: verify Apache is listening on port 80, both devices share a network, and the Windows firewall permits Apache on the **Private** network. Do not disable the firewall. An alternative backend is `php artisan serve --host=0.0.0.0 --port=8000`, with API address `http://PC-IP:8000/api/mobile`; allow only that server on a trusted Private network. No router port forwarding, public hosting or tunnel is required.

## 3. Android: run or install

Install Flutter stable and Android Studio with SDK Platform 36, Build Tools, Android SDK Command-line Tools (latest), and the SDK/NDK versions requested by Flutter. This PC has a local Flutter SDK at `C:\xampp\htdocs\Hospital\.tools\flutter`, which is ignored by Git. Team members install their own SDK.

```powershell
# From project root, for this PC's local SDK:
$env:Path = "C:\xampp\htdocs\Hospital\.tools\flutter\bin;" + $env:Path
cd mobile/careroute
flutter doctor
flutter pub get
flutter devices
flutter run --dart-define=API_BASE_URL=http://10.9.3.66/Hospital/public/api/mobile
```

For a USB-connected Android phone enable Developer options / USB debugging. For an emulator use the emulator address above, or edit it inside the app.

```powershell
flutter build apk --debug --dart-define=API_BASE_URL=http://10.9.3.66/Hospital/public/api/mobile
```

Install `build/app/outputs/flutter-apk/app-debug.apk` on Android. A debug build allows HTTP for the local prototype. Android release builds require an HTTPS backend and a production signing key; debug signing is not a Play Store release configuration. Internet access is declared, and Android auto-backup is disabled to protect secure storage keys.

Windows shortcut from the Flutter folder: `./run-android.ps1 -ServerUrl http://PC-IP/Hospital/public/api/mobile`. Add `-BuildApk` to build, or `-DeviceId emulator-5554` to choose a connected device. It checks the backend before starting Flutter.

## 4. iOS: use a Mac with Xcode

Windows cannot build or sign an iOS application. Copy/clone this repository onto a Mac, install Flutter and Xcode, then:

```bash
cd mobile/careroute
flutter pub get
flutter run --dart-define=API_BASE_URL=http://WINDOWS-PC-IP/Hospital/public/api/mobile
# For an archive, select your development team and unique bundle ID in Xcode first:
open ios/Runner.xcworkspace
flutter build ipa --dart-define=API_BASE_URL=https://YOUR-HOST/api/mobile
```

The iOS project includes local-network permission text, a local-network ATS exception and Keychain entitlements for token storage. Allow the local network prompt on the iPhone. iOS builds and physical iPhone behavior must be verified on the Mac; they cannot be verified on this Windows computer. Local networking uses a private IP or local hostname. Production traffic should use HTTPS.

## 5. Demo logins

All seeded accounts below use **`CareRoute@2026`** (unless someone has changed their password in the existing database).

| Login | Role / access |
| --- | --- |
| `patient@careroute.test` | Submit emergency requests, choose compatible hospitals, view own requests and bed status |
| `hospital.c@careroute.test` | Hospital C: verify severity, accept/reject assigned requests, record arrival, view own beds |
| `hospital.b@careroute.test` | Hospital B: same operations for B |
| `hospital.a@careroute.test` | Hospital A: manage A requests, useful for General Bed requests |
| `hospital.d@careroute.test` | Hospital D: manage D requests and beds |
| `hospital.e@careroute.test` | Hospital E: own bed dashboard; new requests remain rejected while E is Offline |
| `admin@careroute.test` | All emergency requests and beds, hospital actions, create requests, reset demo |

**New patient? Create an account** supports name, email, password and confirmation. New accounts receive only the Patient Requester role and can also sign in to the website. Passwords are hashed server-side. API tokens are stored with Flutter Secure Storage and expire after seven days. Logout revokes the current token. No password is embedded in the app.

## 6. Full phone-to-hospital flow

1. On the phone sign in as Patient or create a patient account.
2. Open **Request care**. Enter a unique patient name/ID, location **Accident Site**, severity **Critical**, care **ICU**. Tap **Submit emergency request**.
3. Request details show severity awaiting verification (priority 1). Hospital A is rejected because ICU availability is zero. Hospital E is rejected because it is Offline even if its inventory lists free beds. C and B are compatible when beds are free; C is closer.
4. Tap **Request ICU bed** on Hospital C. Status becomes **Pending** with a five-minute temporary hold.
5. Use another device, or keep the patient's phone open and use the website at `/login`, to sign in as `hospital.c@careroute.test`. The same hospital login also works in the Flutter app.
6. Hospital opens **Requests** → patient's request → **Verify Critical** → **Accept bed request**. The ICU bed becomes **Reserved**. Hospital B cannot accept C's request.
7. Patient opens **My requests** → request details. It refreshes every five seconds while the app is active and shows **Reserved**, hospital and bed ID, expiry, and request activity. Pull down or tap Refresh for an immediate update.
8. At arrival the hospital opens the same request → **Mark patient arrived**. Bed becomes **Occupied**, has no hold expiry and cannot be reclaimed.
9. Submit a second Critical ICU request with another unique patient name. C's confirmed/occupied bed stays protected; another compatible hospital is offered. For a priority-reassignment demo use a lower-priority **Pending** hold first, then have a hospital verify a new Critical request. Only the lower-priority unconfirmed hold is eligible for reassignment.
10. **Beds** shows Available, Temporary, Reserved and Occupied. **Account** explains the flow and holds server settings/sign out. Admin can reset all emergency mock data after confirming the reset; do not reset while another teammate is demonstrating.

The Flutter app covers the emergency workflow for patient, hospital and admin. Admin → **Account → Open admin portal** opens existing Hospital/Bed Type/Blood Type/inventory/user/module administration in the browser. Sign in there with the same admin credentials; browser sessions and mobile tokens are separate. The web admin menus are described in the root `DEMO-LOGIN-FLOW.md`; those legacy master forms are not rebuilt as native Flutter screens. Existing master-table inventory and the emergency mock store remain separate, as in the web prototype.

## 7. Architecture and checks

`lib/api.dart` → JSON HTTP client / server URL validation / structured errors.
`lib/session.dart` → secure token persistence, role state, foreground-only polling, connectivity and logout.
`lib/app.dart` → login/signup, request form/details/recommendations, queue, bed dashboard and account.
`lib/main.dart` → app entry point.

Laravel `/api/mobile` → Sanctum bearer authentication + seven-day scoped token middleware → existing `EmergencyController` → locked JSON `EmergencyBedService`. Registration uses existing MySQL `users`/`role_mast`. Web and mobile share matching, ownership, priority, expiry, duplication and rate-limit rules.

```powershell
# Flutter project:
flutter analyze
flutter test
flutter build web --dart-define=API_BASE_URL=http://localhost/Hospital/public/api/mobile
# Repository root, PowerShell 7, XAMPP running:
./tests/mobile-api-smoke.ps1
php tests/mobile-token-smoke.php
php tests/emergency-smoke.php
```

The HTTP smoke test creates only uniquely marked test records, removes those records under the JSON store lock and revokes its tokens; it does not reset the user's data. It checks login, signup, ownership, role restrictions, verification, request, acceptance, arrival and token revocation. Flutter tests cover form validation, payloads, API errors and disabled offline submission.

Requests require the local backend connection; no offline request queue is simulated. Last fetched state is clearly marked when disconnected. This remains a mock hospital prototype with manually entered location and seeded distances, not a live emergency service.
