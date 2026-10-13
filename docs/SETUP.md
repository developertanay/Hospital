# Installation and sample data

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
C:\xampp\php\php.exe artisan migrate --path=database/migrations/2026_10_12_000012_add_insurance_and_dependents.php
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
C:\xampp\php\php.exe artisan db:seed --class=CoverageSampleSeeder
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

