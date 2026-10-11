# SOS + ambulance dispatch demo

**SQL merge update:** hospital transfer now uses active SQL hospital records, numeric IDs, real inventory, configured care types and saved coordinates. Admin must assign hospital operators and operators must add available beds first. Choose whichever compatible hospital exists; A/C/E sample comparisons below are historical examples. The former `CareRouteDemoSeeder` no longer creates business accounts. Existing passwords are preserved; `AmbulanceDemoSeeder` explicitly provisions only the three dispatch demo drivers on a new installation. Run the eight additive migrations listed in the root README. Driver accounts migrated from the old app keep their passwords and enabled/disabled status.

Install the updated CareRoute **1.1.0 (build 2)** Android APK over the previous app. Existing patient login and server settings remain. Start Apache + MySQL and use the same local API address described in [LOCAL-SETUP.md](LOCAL-SETUP.md).

If preparing another checkout, run `php artisan config:clear`, `php artisan route:clear`, and `php artisan db:seed --class=CareRouteDemoSeeder`. No new database migration is needed for SOS. Do not reset existing emergency data.

## Accounts

Every seeded account uses `CareRoute@2026`, unless its password was subsequently changed.

| Login | Role |
| --- | --- |
| patient@careroute.test | Patient: saved emergency profile, SOS, trip status, normal hospital requests |
| driver.1@careroute.test | Aman, ambulance D1, closest to the sample pickup |
| driver.2@careroute.test | Riya, ambulance D2, next closest |
| driver.3@careroute.test | Kabir, ambulance D3 |
| hospital.c@careroute.test | Hospital C: ICU verification, acceptance and arrival |
| hospital.b@careroute.test | Hospital B: alternative ICU allocation |
| admin@careroute.test | All SOS trips and existing hospital administration |

Driver accounts are seeded by the server. Patient signup cannot create a driver/admin role. Seeded driver phone numbers and vehicles are mock values. The patient/contact numbers entered in the emergency profile are the numbers the driver's call buttons open in the phone dialer; calls are not automatically placed. Maps opens the pickup coordinates in an external maps application/browser.

## Fast demo on two devices

1. Driver device: sign in as **driver.1@careroute.test** → **Dispatch** → enable **Use demo driver coordinates** → **Go on duty**. Keep this app open.
2. Optionally put driver 2 on duty on another device with demo coordinates. A separate browser origin (e.g. `localhost:8088` versus `127.0.0.1:8088`) has separate stored sessions; two tabs at the same origin share login storage.
3. Patient device: sign in → **SOS** → **Add emergency details**. Save name, mobile number, age, emergency contact name and number. These are saved server-side once and reused for future SOS requests.
4. Enable **Use demo pickup location**. The sample point is Delhi, 28.6139 / 77.2090. Both driver and patient must use demo mode for this scenario. The app clearly marks this as simulated coordinates.
5. Tap the large **SOS · Request ambulance** button. No emergency request form is needed. The patient sees **Searching**, not a claimed confirmed dispatch.
6. Aman gets a foreground alert and a **30-second offer** with pickup coordinates, age and straight-line distance. He cannot see patient phone/contact details before accepting. Other drivers do not see this offer.
7. Driver taps **Accept pickup**. Patient sees **Accepted**, assigned driver and vehicle. Driver can now call the patient or emergency contact and open the pickup in Maps.
8. Driver taps **Reached pickup location** → **Patient picked up** → **Record first aid provided**. Status updates appear on the patient's screen every five seconds while open. The first-aid button records an operator action; it does not generate treatment advice or certify the driver's clinical training.
9. If no hospital transfer is needed: **Complete · no hospital transfer** ends the trip and frees the driver.
10. If hospital care is needed: **Hospital needed · request a bed** → choose **ICU** → request an available compatible hospital, e.g. **Hospital C**. Hospital A has no ICU bed; Hospital E remains Offline. Hospital distances are existing seeded demo values, not live route measurements. If C is full, choose B or D.
11. Hospital operator uses the website or mobile app → **Requests** → the request named `Patient [SOS id]` → **Verify Critical** → **Accept bed request**. The patient can also open it through **Open linked hospital request** on the SOS card. Driver cannot grant verified Critical priority or hospital approval.
12. At hospital arrival: hospital operator → **Mark patient arrived**. Bed becomes **Occupied**. Driver → **Complete hospital handoff**. This is blocked until the hospital confirms arrival.

## Actual GPS mode on phones

Disable both demo switches. Put the driver on duty near the patient using current GPS. Allow **precise location / while using the app** on both devices. The first SOS tap obtains a fresh location fix and sends it with the saved profile. No background-location permission is requested.

While apps are open, driver GPS and pre-pickup patient GPS refresh every 30 seconds. After pickup the pickup point stops changing; driver position can still update. Denied permission, disabled GPS, an old fix or accuracy worse than 200 m produces an error instead of silently using demo coordinates. The pickup coordinate mode is included in every request; demo drivers cannot be matched to a real GPS SOS.

Use the same Wi-Fi and PC API URL for this local prototype. The APK's GPS works on the phone; Laravel still runs on the PC. Browser GPS requires a secure context (localhost/HTTPS); the explicit demo mode works for browser testing.

## Dispatch rules and failure scenarios

- Available means **on duty**, foreground heartbeat within 90 seconds, not already assigned/offered another active pickup, within a 25 km straight-line radius. GPS drivers also need a location update within 120 seconds.
- Rank by Haversine distance, then driver ID for ties. Only one eligible driver gets an offer at a time.
- Decline / 30-second timeout / stale or off-duty driver moves the offer to the next available driver. Skipped drivers become eligible for another attempt after 60 seconds.
- Acceptance and driver allocation are protected by the same exclusive file lock. Duplicate accept cannot allocate two drivers. A driver stays busy until trip completion/cancellation; an accepted trip is never silently reassigned after connection loss.
- No drivers: show an honest Searching state. Search ends as **Expired** after ten minutes; the patient may then send another SOS. Accepted trips do not expire automatically.
- Duplicate taps return the existing active SOS. At most three new SOS requests per patient per ten minutes; existing API request throttling also applies.
- Patient can cancel before pickup, with confirmation. Another patient or unassigned driver cannot modify the trip. After pickup, driver follows the ordered trip workflow.
- Patient sees only own SOS trips. Driver sees only current offers/assigned trip history. Admin sees all trips. Hospital users receive only normal linked hospital requests, not dispatch data.
- Temporary/confirmed hospital bed expiry still applies. A failed bed allocation keeps its linked request for retry rather than creating duplicate patient requests. Hospital verification and confirmation remain mandatory; occupied beds stay protected.

## Implementation

`lib/sos.dart`: patient profile + one-tap SOS, foreground driver alert, driver duty/trip actions, maps/call links and hospital handoff.

`lib/location.dart`: permission checks, fresh GPS acquisition and explicitly separate demo coordinates.

`lib/session.dart`: shared login, five-second role-scoped refresh and dispatch state; logout takes drivers off duty when the server is reachable.

`/api/mobile/sos/state` and `/api/mobile/sos/action`: scoped Sanctum tokens and server-enforced role/ownership checks.

`AmbulanceService`: JSON mock dispatch, serialized acceptance, Haversine ranking, timeouts, availability and linked `EmergencyBedService` requests. `storage/app/ambulance-demo.json` is runtime data and excluded from Git, like the existing emergency mock store.

This is a working demo dispatch system with seeded drivers. It does not call an actual ambulance provider, send SMS, or deliver push notifications when the driver app is closed. A deployed backend, provider onboarding and a push service would be needed for that extension. iOS source includes foreground location permission text; iOS build/device verification still requires Mac/Xcode.

## Validation

```powershell
# Repository root; isolated JSON stores, preserve real demo records:
php tests/sos-smoke.php
php tests/sos-api-smoke.php
php tests/sos-race.php
php tests/emergency-smoke.php
# Flutter folder:
flutter analyze
flutter test
flutter build apk --debug --dart-define=API_BASE_URL=http://PC-IP/Hospital/public/api/mobile
```
