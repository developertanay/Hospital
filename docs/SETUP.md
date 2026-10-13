# Installation and sample data

## Web setup on XAMPP

1. Start Apache and MySQL. Use the existing populated database. For a new installation, restore a valid legacy database backup first: this checkout does not include a complete SQL dump, and the original Laravel migrations do not recreate every legacy table.
2. Install PHP dependencies with `composer install`. Confirm `public/index.php` and `vendor/autoload.php` exist. Old `public.zip`/`vendor.zip` snapshots are recovery material and should not overwrite current application files blindly.
3. Configure `.env` with your database credentials. Keep the existing `APP_KEY`; generate a key only for a new installation without one.

```dotenv
APP_URL=http://localhost/Hospital/public
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
C:\xampp\php\php.exe artisan migrate --path=database/migrations/2026_10_12_000012_add_insurance_and_dependents.php
C:\xampp\php\php.exe artisan optimize:clear
C:\xampp\php\php.exe artisan storage:link
```

5. Keep `storage` and `bootstrap/cache` writable. Hospital logos use the public storage link; ambulance photos, SOS photo/voice attachments and insurance cards are stored privately and served through authenticated routes. Include private storage folders in backups.
6. Open [web login](http://localhost/Hospital/public/login). Hospital bed creation is available at [Add bed](http://localhost/Hospital/public/emergency/inventory/create) after hospital login and module assignment.

## Optional sample masters and test accounts

Run these seeders explicitly when sample data is wanted. They preserve existing operational records and existing account passwords.

```powershell
C:\xampp\php\php.exe artisan db:seed --class=HospitalMasterSampleSeeder
C:\xampp\php\php.exe artisan db:seed --class=MobileTestAccountsSeeder
C:\xampp\php\php.exe artisan db:seed --class=CoverageSampleSeeder
```

The master seeder adds standard departments (including ENT, Ophthalmology and Dentistry), ambulance categories, bed types, blood groups and labelled sample hospitals/mappings. Sample master hospitals have request acceptance disabled.

The test account seeder adds a separate App Test Hospital with three beds and two ambulances (hospital-associated and private). Use the five dedicated accounts in [TEST-ACCOUNTS.md](TEST-ACCOUNTS.md) on web and Android.

Dedicated test roles have the needed clinical modules without changing existing role permissions. Save the patient's emergency profile before testing SOS. Test phone numbers are placeholders; replace them with your own test numbers before using call buttons. These credentials are for local testing only.


## Connect Android

Install [the APK](../deliverables/Hospital-Test.apk) and keep the phone/PC on the same Wi-Fi. In Server connection, choose **Current test server → Test & save** or enter `http://PC-IP/Hospital/public/api/mobile`. Phone `localhost` refers to the phone; Android emulator uses `http://10.0.2.2/Hospital/public/api/mobile`.

Run `ipconfig` on Windows and use the Wi-Fi IPv4 address rather than a virtual-adapter address. Open the API `/health` endpoint in the phone browser first. It must report `storage: mysql` and `mock_data: false`. If unreachable, check Apache, the network and Apache's firewall access on the trusted Private network.

The first Android SOS use requires camera/microphone permission; it then automatically captures a front photo and 10 seconds of audio. Keep the app open. Save the patient's emergency profile first and follow [the two-phone SOS check](SOS-PHOTO.md).

The commands above clear installation caches. If the running installation uses cached configuration and you deliberately change `.env`, refresh it with `C:\xampp\php\php.exe artisan config:cache`; keep the existing application key. No additional migration is required specifically for the 1.7.0 SOS media change.

See [Android development](../mobile/careroute/README.md), [current test logins](TEST-ACCOUNTS.md) and [system flow & jury demonstration](SYSTEM-FLOW-AND-PITCH.md).
