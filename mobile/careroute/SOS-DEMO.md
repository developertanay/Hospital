# SOS ambulance demo — version 1.2.0

## Setup

Start Apache and MySQL in XAMPP. This app uses the updated SQL users, role permissions, hospitals, care types and bed inventory. Dispatch trips/emergency profiles remain a locked JSON demo store (`storage/app/ambulance-demo.json`); the SQL ambulance master is not an external fleet integration. Do not reset either store to test this flow.

On the phone open Server connection and set `http://PC-WIFI-IP/Hospital/public/api/mobile`. Currently this PC is `10.9.3.66`. Phone and PC must share Wi-Fi. Test the `/health` address in the phone browser first. The Android emulator uses `10.0.2.2`; the Flutter browser demo uses `http://localhost/Hospital/public/api/mobile`.

Existing demo accounts on this PC were verified enabled with password `CareRoute@2026`:

| Login | Purpose |
|---|---|
| patient@careroute.test | Patient / SOS |
| driver.1@careroute.test | Aman / ambulance D1 |
| driver.2@careroute.test | Riya / ambulance D2 |
| driver.3@careroute.test | Kabir / ambulance D3 |

A new installation may explicitly run `php artisan db:seed --class=AmbulanceDemoSeeder` to create missing drivers; it does not overwrite existing passwords or enabled flags. SQL migrations from the root README must already be applied. No new migration is needed for this SOS update. New patients can register in the app. Admin assigns hospital operator accounts to actual SQL hospitals and operators add beds; old A–E JSON hospital fixtures are not used.

## Two-minute demo

1. Open the patient APK on a phone. Open the Flutter app/browser preview on two additional devices or separate browser profiles for driver 1 and driver 2. All sessions must use the same backend. Do not sign out a driver to sign in a patient on the same device: signing out marks that driver off duty.
2. Log in as each driver. Select **Online for demo** in the setup prompt. This explicitly enables simulated ambulance positions near each pickup, including real GPS pickups. Keep each driver app visible/open. An existing on-duty demo driver can select **Enable demo pickups near patient GPS**. Driver 1 is closest, followed by driver 2 and driver 3.
3. For actual call demonstrations select **Set driver phone for call demo** and enter a test number you control. Seed phone numbers are dummy numbers. Calls open the OS dialer and require the user to place the call.
4. Log in as the patient. If basic details are missing, the app prompts for full name, mobile, age, emergency contact name and number. Save once. If you dismiss setup, tapping SOS opens setup again; it does not send an incomplete SOS.
5. Use real GPS or explicitly switch on **Use demo pickup location**. Tap **SOS · Request ambulance**. No emergency request form is required. Patient sees **Finding your driver…**, the number of alerted nearby drivers, and honest searching feedback when no drivers are online.
6. Within the normal 5-second refresh, both online demo drivers see the SAME pickup with an alert, distance, estimated travel time, **Accept pickup** and **Reject pickup**. Unassigned drivers see pickup/age but not contact details.
7. Driver 1 rejects. Driver 2 still has the request and accepts it. Alternatively accept on both simultaneously: exactly one wins, and the other is told that the offer is no longer available. Accepting removes this pickup from the other drivers and removes the winning driver from other open pickups.
8. Patient sees **Driver is on the way**, driver's name, ambulance, phone, distance, ETA and progress diagram. Demo driver position moves toward the pickup over time. **View driver in Maps** opens the current simulated coordinates. All simulated movement is visibly labelled. Real GPS drivers use fresh foreground GPS updates instead.
9. Patient selects **Call assigned driver**. Assigned driver selects **Call patient** or **Call emergency contact**. The native phone dialer opens with the saved number. Browser previews require a phone handler, so use an actual phone to demo calls.
10. Driver selects **Reached pickup location → Patient picked up → Record first aid provided**. Either complete without hospital transfer, or select **Hospital needed · request a bed**, choose a SQL care type and an online hospital with an available bed.
11. For hospital transfer, the hospital operator logs into the website, verifies severity, accepts the linked bed request and confirms arrival. Driver then selects **Complete hospital handoff**. The patient owns the linked SQL request and can open it in the app. No automatic hospital acceptance or self-declared Critical priority is granted.

## Behavior and limits

- Nearby eligible drivers within 25 km receive a broadcast, ranked by distance/estimated pickup time; each can accept or reject. First acceptance wins under one exclusive lock. It is not automatically assigned solely because a driver is nearest.
- Declining suppresses this pickup for that driver for the rest of the request. Other drivers remain eligible. Late online drivers are added while searching. An accepted busy driver cannot accept a second pickup.
- Duplicate SOS taps return the existing active trip. Three new SOS requests per ten minutes are allowed. Unconfirmed pickups expire after ten minutes. Driver presence expires after 90 seconds without a heartbeat; real GPS must be updated within 120 seconds. Foreground GPS updates every 30 seconds and state refreshes every five seconds.
- Cancellation is allowed before pickup and is shown to the assigned driver. After pickup only the assigned driver can advance the trip. Today's assigned trips and previous trip activity appear in dispatch.
- ETA is an estimate using straight-line distance, a 1.3 route factor and assumed 25 km/h speed. It is not live road routing or traffic data. The progress diagram is not a street map. Demo movement does not automatically record driver arrival/pickup.
- Alerts are sound/haptic/in-app while the app is open; closed/background apps do not receive push notifications. This is a working local demo, not a connected emergency ambulance provider. Real emergency service dispatch/push must be added before real-world use.

## Verification

From project root: `php tests/sos-smoke.php`, `php tests/sos-api-smoke.php`, `php tests/sos-race.php`, `php tests/mobile-merge-smoke.php`. These use isolated stores/databases and do not replace actual demo trips. From `mobile/careroute`: `flutter analyze`, `flutter test`, `flutter build apk --debug --dart-define=API_BASE_URL=http://PC-WIFI-IP/Hospital/public/api/mobile`. iOS uses the same Flutter source; building/signing on iOS requires a Mac/Xcode and was not validated on this Windows PC.
# Current database-backed test

Use [the web/app database setup](../../docs/WEB-APP-DATABASE.md) and `MobileTestAccountsSeeder`. Current test logins are `patient.test@careroute.test`, `hospital.test@careroute.test`, `driver1.test@careroute.test` and `driver2.test@careroute.test`. They use SQL profiles, assigned vehicles and shared dispatch transactions. The old fixed-driver prototype instructions below are superseded; demo GPS remains an explicit optional testing mode.
