# Hospital Management & CareRoute

Laravel 8 web application and Flutter Android client for hospital masters, hospital inventory, patient emergency requests and ambulance dispatch. Both clients use the same MySQL database through Laravel. Mobile users authenticate through Sanctum; database credentials stay on the server.

## Final features and responsibilities

| Role | Responsibilities |
| --- | --- |
| Admin | Hospitals, departments, hospital-to-department mappings, bed and blood types, ambulance categories/details, accounts, roles and module assignments |
| Hospital | Its own beds, blood stock, accepting status, request approvals, severity verification, reservations, patient arrival, discharge and history |
| Patient | Own emergency profile, request CRUD, hospital recommendations, reservation status and ambulance requests |
| Driver | Assigned ambulance, availability, foreground location, trip acceptance, pickup, transport and handoff |

Menus come from active `modules` and the admin's saved `module_assigning` records. Role and ownership checks also protect pages, API responses and writes. Admins do not approve clinical requests or manage patient reservations. Hospital users cannot change another hospital's inventory.

The dashboard shows role-specific details with cards linking to the relevant assigned pages. Full data lives on separate module pages. Login, favicon and CRUD views follow the application's blue theme and Hospital Master layout.

Master data uses MVC models and resource controllers. Ambulance records include vehicle number, category, doctor/driver names and contact details, and optional vehicle/doctor/driver photos (JPG, PNG or WebP, up to 2 MB each). Vehicles may belong to a hospital or operate privately. Driver accounts are linked to an active ambulance through account management.

Hospital bed inventory uses individual beds with database-defined care types. Any active bed type can be added; inactive types are rejected. Only available beds can be edited or deleted. Hospital recommendations use stored coordinates, compatible care and availability. Allocation and dispatch use SQL transactions and coordination locks.

Request Care captures the Internal/External problem type, description, required service/department and foreground GPS. After submission, Internal requests show ambulance booking first and hospital beds as another option; External requests show hospital beds first and ambulance pickup as another option. Both can be booked on the same care request.

The ambulance list shows only on-duty drivers with fresh real GPS, within 25 km, without an accepted trip or selected pending booking. It is ordered by distance and shows vehicle, category, available doctor information and estimated arrival. Selecting **Book this ambulance** reserves that driver atomically and notifies only that driver; driver confirmation is still required. Patient and driver share booking status and pickup GPS, and the patient sees driver contact, distance and ETA. Declining, cancelling, completing or expiring a booking releases the ambulance. Test/demo GPS drivers do not appear in this real-GPS list.

Hospital options require an active service mapping, accepting hospital and compatible available bed. Incompatible hospitals are hidden in the app and rejected by the backend even if a crafted request names them. Booking reaches only the selected hospital, which verifies severity and accepts the reservation. A driver transporting a patient reuses the patient's existing care/bed request. Saving an unbooked form does not broadcast patient details to hospitals. Browser GPS requires localhost or HTTPS and location permission.

## Database structure

The installed database is `colleges` at `localhost:3306`. Existing hospital and account records are reused.

| Tables | Purpose |
| --- | --- |
| `hospital_mast`, `users`, `role_mast` | Hospitals, shared login accounts, roles and hospital assignments |
| `modules`, `module_assigning` | Admin-managed menus and permissions |
| `bedding_type`, `blood_type`, `department_mast` | Care types, blood groups and departments |
| `blodd_inventory_management` | Hospital blood stock; original table spelling retained |
| `bed_inventory_management` | Preserved legacy aggregate inventory; individual allocations use `care_beds` |
| `care_beds`, `care_requests`, `care_events`, `care_workflow_lock` | Bed inventory, patient requests, workflow history and allocation coordination |
| `hospital_department_mapping`, `ambulance_category_mast`, `ambulance_details` | Hospital departments and ambulance masters |
| `ambulance_drivers`, `patient_emergency_profiles`, `ambulance_trips`, `ambulance_dispatch_lock` | SQL driver assignments, patient profiles, dispatch and trip history |
| `personal_access_tokens` | Scoped mobile authentication |

Live dispatch uses SQL rather than static driver email mappings or JSON files. Migration 000009 imports eligible legacy JSON profiles/trips and leaves the original file as a backup. Existing unassigned driver accounts need an ambulance assignment. A file adapter remains only for explicitly configured isolated tests.

## Web setup on XAMPP

1. Start Apache and MySQL. Use the existing populated database. For a new installation, import the project's legacy database schema/data first: the original Laravel migrations do not recreate every legacy table.
2. Install PHP dependencies with `composer install`. Confirm `public/index.php` and `vendor/autoload.php` exist. Old `public.zip`/`vendor.zip` snapshots are recovery material and should not overwrite current application files blindly.
3. Configure `.env` with your database credentials. Keep the existing `APP_KEY`; generate a key only for a new installation without one.

```dotenv
APP_URL=http://localhost
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=colleges
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

4. Back up the database, then apply the additive migrations below. Already applied migrations are skipped. Do not use `migrate:fresh` or run all legacy migrations against the populated database.

Run from the project directory in PowerShell:

```powershell
C:\xampp\php\php.exe artisan migrate --path=database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php
C:\xampp\php\php.exe artisan migrate --path=database/migrations/2026_10_12_000001_create_dynamic_hospital_workflow.php
C:\xampp\php\php.exe artisan migrate --path=database/migrations/2026_10_12_000002_add_dynamic_account_management.php
C:\xampp\php\php.exe artisan migrate --path=database/migrations/2026_10_12_000003_update_dynamic_module_labels.php
C:\xampp\php\php.exe artisan migrate --path=database/migrations/2026_10_12_000004_add_workflow_relationships.php
C:\xampp\php\php.exe artisan migrate --path=database/migrations/2026_10_12_000005_create_hospital_masters.php
C:\xampp\php\php.exe artisan migrate --path=database/migrations/2026_10_12_000006_enable_hospital_master_crud.php
C:\xampp\php\php.exe artisan migrate --path=database/migrations/2026_10_12_000007_create_ambulance_details.php
C:\xampp\php\php.exe artisan migrate --path=database/migrations/2026_10_12_000008_preserve_mobile_driver_accounts.php
C:\xampp\php\php.exe artisan migrate --path=database/migrations/2026_10_12_000009_create_sql_ambulance_dispatch.php
C:\xampp\php\php.exe artisan migrate --path=database/migrations/2026_10_12_000010_add_request_care_details.php
C:\xampp\php\php.exe artisan migrate --path=database/migrations/2026_10_12_000011_add_care_request_department.php
C:\xampp\php\php.exe artisan optimize:clear
C:\xampp\php\php.exe artisan storage:link
```

5. Keep `storage` and `bootstrap/cache` writable. Hospital logos use the public storage link; ambulance photos are stored privately and served through authenticated routes.
6. Open [web login](http://localhost/Hospital/public/login). Hospital bed creation is available at [Add bed](http://localhost/Hospital/public/emergency/inventory/create) after hospital login and module assignment.

## Optional sample masters and test accounts

Run these seeders explicitly when sample data is wanted. They preserve existing operational records and existing account passwords.

```powershell
C:\xampp\php\php.exe artisan db:seed --class=HospitalMasterSampleSeeder
C:\xampp\php\php.exe artisan db:seed --class=MobileTestAccountsSeeder
```

The master seeder adds standard departments (including ENT, Ophthalmology and Dentistry), ambulance categories, bed types, blood groups and labelled sample hospitals/mappings. Sample master hospitals have request acceptance disabled.

The test account seeder adds a separate App Test Hospital with three beds, two ambulances (hospital-associated and private), and the following accounts. All five use **`TestCare@2026`** on web and Android.

| Login role | User ID / email |
| --- | --- |
| Patient | `patient.test@careroute.test` |
| Hospital | `hospital.test@careroute.test` |
| Driver 1 | `driver1.test@careroute.test` |
| Driver 2 | `driver2.test@careroute.test` |
| Admin | `admin.test@careroute.test` |

Dedicated test roles have the needed clinical modules without changing existing role permissions. Save the patient's emergency profile before testing SOS. Test phone numbers are placeholders; replace them with your own test numbers before using call buttons. These credentials are for local testing only.

## Android APK and connection

Install [Hospital-Test.apk](deliverables/Hospital-Test.apk). App name: **CareRoute**, version **1.4.1 (7)**. The debug-signed APK supports Android 7.0+ (API 24), ARM32, ARM64 and x86_64; size is approximately 154 MiB.

Keep Apache/MySQL running and connect phone and PC to the same Wi-Fi. In the app's **Server connection**, set the Laravel API address:

| Device | API address |
| --- | --- |
| Physical phone | `http://10.9.4.134/Hospital/public/api/mobile` |
| Android emulator | `http://10.0.2.2/Hospital/public/api/mobile` |

`10.9.4.134` was the PC's Wi-Fi address when the APK was built; update it if the address changes. Check `/api/mobile/health` in the phone browser before logging in. `localhost` on a phone refers to the phone itself.

Version 1.4.1 includes nearest idle ambulance booking, driver details/ETA, pickup tracking and hospital options filtered by the required service. Install this updated APK and follow [the booking demo guide](docs/CARE-BOOKING-DEMO.md). APK signature and checksum details are in [deliverables/README.md](deliverables/README.md).

### Rebuild the test APK

The tested toolchain uses Flutter 3.47.7 / Dart 3.13.5, JDK 17 and Android SDK 36. The helper expects Flutter in `.tools/flutter`, Android SDK in `%LOCALAPPDATA%/Android/Sdk`, and the JDK path recorded in the script; adjust these for another machine.

```powershell
.\mobile\careroute\build-test-apk.ps1 -ApiBaseUrl 'http://YOUR-PC-IP/Hospital/public/api/mobile'
```

The output is copied to `deliverables/Hospital-Test.apk`. See [mobile setup](mobile/careroute/README.md) for client development. The local test build allows HTTP; release configuration requires HTTPS. iOS source is present but no iOS build was produced on Windows.

## Verification and test flow

Verified during implementation: all five actual Apache web/API logins, 50 shared-database API checks, 24 installed-database workflow checks, 40 isolated bed workflow checks, six dynamic bed CRUD checks, 13 Request Care detail checks, 30 care booking checks, Flutter analysis and 16 Flutter UI tests. Installed-database smoke checks roll back their workflow changes; the HTTP login check revokes its issued test tokens.

Useful checks from the project root:

```powershell
C:\xampp\php\php.exe tests/dynamic-bed-crud-smoke.php
C:\xampp\php\php.exe tests/request-care-details-smoke.php
C:\xampp\php\php.exe tests/care-booking-flow-smoke.php
C:\xampp\php\php.exe tests/database-smoke.php
C:\xampp\php\php.exe tests/shared-database-api-smoke.php
.\tests\shared-logins-http.ps1
```

For manual testing: add an active bed as the hospital, submit a patient request, verify/approve it as the hospital, then confirm reservation, arrival and discharge. For SOS, make a driver available, submit a patient emergency, accept and pick up as the driver, complete hospital acceptance/arrival and handoff. Check both clients for the same updated records.

A physical phone installation has not been tested. GPS runs in the foreground and notifications are in-app; background push and real emergency-provider integration are not implemented. Distance/ETA is an estimate from coordinates, not a road-routing service. Older unrelated school routes remain; a full `artisan route:list` may fail on the missing legacy `FeedbackController`.

## Troubleshooting

| Problem | Check |
| --- | --- |
| `server.php` cannot find `public/index.php` | Restore the project's public entry point and confirm you are running the correct project directory. |
| MySQL connection refused / error 2002 | Start XAMPP MySQL; match `.env` host, port and database credentials to the running server, then clear configuration cache. |
| Bed cannot be added | Use a hospital account linked to an active hospital, grant the inventory module, select an active bed type and review validation messages. |
| 403 or missing menu | Check the account's role, enabled status, hospital/ambulance assignment and admin-saved module permissions. |
| 419 on form submission | Refresh the login/form session and check session storage permissions; forms require Laravel's CSRF token. |
| Phone cannot reach API | Use the PC's current network IP, same Wi-Fi, running Apache and allowed inbound access. Verify the API health URL first. |
| Driver cannot accept trips | Assign an active ambulance and enable driver availability/location; only eligible or assigned trips are exposed. |

## Further documentation

- [Shared web/app database and architecture](docs/WEB-APP-DATABASE.md)
- [Test accounts and login flow](docs/TEST-ACCOUNTS.md)
- [Internal/External booking demonstration](docs/CARE-BOOKING-DEMO.md)
- [Flutter client](mobile/careroute/README.md)
- [APK metadata and checksum](deliverables/README.md)
