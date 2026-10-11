# Web and Android use one database

The running backend is Laravel 8/PHP in XAMPP, using the existing MySQL `colleges` database at `localhost:3306`. Its configuration stays in `.env`; database credentials are never built into Android. The Flutter app calls Laravel's `/api/mobile` API. Web sessions and mobile Sanctum tokens authenticate against the same `users` rows and hashed passwords.

## Data and responsibilities

| Area | Shared SQL tables | Access |
|---|---|---|
| Accounts and menus | users, role_mast, modules, module_assigning | Admin manages accounts and role assignments |
| Hospital masters | hospital_mast, department_mast, hospital_department_mapping, bedding_type, blood_type | Assigned master modules |
| Hospital workflow | care_beds, care_requests, care_events; existing blodd_inventory_management | Patient owns requests; hospital manages its assigned hospital |
| Ambulance details | ambulance_category_mast, ambulance_details | Admin maintains vehicles, staff and ownership |
| Drivers | ambulance_drivers linked to users and ambulance_details | One assigned ambulance per driver account |
| Emergency profiles and trips | patient_emergency_profiles, ambulance_trips | Patient owns profile/trips; driver sees offered or assigned trips |
| API authentication | personal_access_tokens | Scoped mobile tokens, disabled-account checks, seven-day expiry |

Dispatch used to read a JSON file and fixed driver email mappings. Its default store now uses SQL transactions and a shared dispatch row lock. First acceptance wins. The JSON file adapter remains only for explicitly supplied isolated test paths; live dispatch does not use it. Existing JSON profiles/trips with valid owners and drivers are imported by migration 000009; the original file remains a backup. Existing unassigned driver accounts must be linked to a vehicle through Accounts & Hospital Access.

## Setup and test build

Keep Apache and MySQL running. Apply only the additive migrations needed by this populated database:

```powershell
C:\xampp\php\php.exe artisan migrate --path=database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php --force
C:\xampp\php\php.exe artisan migrate --path=database/migrations/2026_10_12_000009_create_sql_ambulance_dispatch.php --force
C:\xampp\php\php.exe artisan db:seed --class=MobileTestAccountsSeeder --force
```

The test seeder creates separate patient, hospital, two driver and admin accounts, a labelled test hospital with three beds, and two test ambulances. It preserves existing accounts, passwords and operational records. Test vehicles include one hospital-associated and one private ambulance. Clinical modules are assigned to dedicated test roles; existing role permissions are preserved.

On the phone, use **Server connection** to enter `http://10.9.4.134/Hospital/public/api/mobile`. This is the PC's Wi-Fi address at build time; update it if the address changes. Phone and PC must share the network. Open the API `/health` address in the phone browser first. Android emulator uses `http://10.0.2.2/Hospital/public/api/mobile`. The web login is `http://localhost/Hospital/public/login` on the PC.

The APK is a debug-signed local test build with HTTP allowed. Its release configuration still requires HTTPS. GPS is foreground-only; background dispatch/push notifications are not implemented. No real phone was attached during automated checks.

## Verification

`tests/shared-database-api-smoke.php` checks installed MySQL logins and a complete patient SOS → driver acceptance → pickup → hospital verification/acceptance/arrival → handoff flow inside a rolled-back transaction. `tests/shared-logins-http.ps1` checks actual Apache web/API logins for every test role, then revokes issued test tokens. Flutter analysis and widget tests cover the Android client. Older unrelated legacy school routes remain in the repository and are not used by the new app.
