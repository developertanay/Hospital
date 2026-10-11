# Hospital Management & Emergency Bed Coordination

A Laravel 8 MVC application with database-backed hospitals, accounts, bed inventory, blood stock, emergency requests, hospital approvals and reservation history.

Hospital names, accounts, care types, blood types, stock, beds and requests come from the database. The application no longer generates A–E hospitals, fixed bed capacities, sample emergencies, published demo passwords or fixed distances. It does not read the old emergency JSON file.

## Responsibilities

| Role | Pages and actions |
| --- | --- |
| Admin | Hospital master, bed/blood type masters, accounts, hospital assignments, modules and permissions |
| Hospital operator | Own hospital's bed/blood inventory, settings, approvals, arrival, discharge and relevant history |
| Patient | Own emergency request CRUD, recommendations, reservations and history |

Admins do not approve emergency requests or manage patient reservations. Page access, API responses and writes enforce the user's role and hospital/patient ownership.

## Database

Existing tables reused:

| Table | Use |
| --- | --- |
| `hospital_mast` | Real hospital records, contact/address, coordinates and accepting status |
| `users` | Login accounts, role, enabled status and assigned hospital |
| `role_mast` | Existing role records |
| `modules`, `module_assigning` | Stored menus and role/module assignments |
| `bedding_type` | Care type options for beds and requests |
| `blood_type` | Blood group options |
| `blodd_inventory_management` | Blood stock, now with nullable hospital ownership; original spelling retained |

New tables:

| Table | Use |
| --- | --- |
| `care_beds` | Individual beds, care type, hospital and allocation state |
| `care_requests` | Patient-owned requests, verification, allocation and expiry |
| `care_events` | Persistent decision history |
| `care_workflow_lock` | One internal coordination row for transaction locking |

The old `bed_inventory_management` table stores aggregate counts without hospital ownership or individual allocation state. Its existing records are preserved; protected emergency allocation uses `care_beds`. Old blood records without a hospital assignment are preserved as unassigned stock. Admin can associate them with a real hospital through Blood Inventory CRUD.

Foreign keys link new workflow records to hospitals, users and care types. No mock records are imported from the old JSON store. Existing users/passwords and legacy records are preserved.

## First-time workflow

1. Start Apache and MySQL in XAMPP. Open [Login](http://localhost/Hospital/public/login) and use an existing administrator account.
2. In **Hospital Master**, create/edit actual hospital records. Coordinates are optional.
3. In **Bed Type Master** and **Blood Type Master**, maintain the types available in dropdowns.
4. In **Accounts & Hospital Access**, add/edit accounts, select the role, and assign each hospital operator to its actual hospital.
5. Hospital operators create bed and blood inventory through their own Add/Edit/Delete pages, then configure contact/address and Open/Offline status.
6. Patients add an emergency request, select a recommended hospital, and request a bed.
7. The assigned hospital verifies severity, accepts/rejects, records arrival and later discharges the patient.

Previously configured prototype hospital accounts deliberately have no automatic A–E mapping. Until an admin assigns a real hospital, those accounts show an assignment notice and cannot access hospital records. New accounts require an explicitly entered password; there is no default shared password.

## CRUD and MVC

| Controller | Responsibility |
| --- | --- |
| `Hospitalcontroller` | Existing hospital master resource CRUD |
| `AccountController` | Account resource CRUD and hospital assignments |
| `HospitalInventoryController` | Hospital-owned bed and blood resource CRUD |
| `HospitalSettingsController` | Read/edit/update the operator's assigned hospital |
| `EmergencyRequestController` | Patient-owned request resource CRUD |
| `EmergencyController` | Dashboard/state responses and clinical workflow actions |

Models live in `app/Models/`, controllers in `app/Http/Controllers/`, and Blade forms/lists in `resources/views/`. `RoleAccess` reads accounts and menu assignments from SQL. `EmergencyBedService` contains matching and allocation rules, uses the models, and saves changed records inside database transactions. It has no file persistence or production data seeding.

`config/workflow.php` contains initial module definitions for installation. Runtime menus use `modules` and `module_assigning`; labels, grouping and workspace roles can be edited with Module Master and Role Assignments.

## Allocation rules

- Self-reported severity starts at standard priority. Hospital verification enables Critical/Urgent priority.
- Compatible, open hospitals with available beds are recommended.
- Distance is calculated from saved hospital and submitted patient coordinates. Missing coordinates show **Distance not provided**; distance is a straight-line estimate, not a driving route.
- Pending holds and confirmed reservations expire after the configured interval; occupied beds do not expire.
- Verified Critical requests may reclaim lower-priority pending holds, but never confirmed reservations or occupied beds.
- Only Available beds can be edited/deleted. Discharge makes an Occupied bed Available and marks its request Discharged.
- Allocated/completed patient requests cannot be edited or cancelled. Request deletion cancels an unallocated request while preserving its history.
- Account deletion disables access and preserves historical records.

## Local setup

PHP 8.2 has been verified locally. The backend uses Laravel 8, MySQL/MariaDB, Blade and Eloquent. Composer dependencies include DomPDF, Intervention Image, Guzzle, Laravel UI and Sanctum. Laravel Mix/Sass/React scaffold is present for asset builds.

Restore dependencies and public assets when absent:

```sh
composer install
```

For a fresh checkout using the supplied snapshot archives:

```powershell
Expand-Archive -Path .\public.zip -DestinationPath .
Expand-Archive -Path .\vendor.zip -DestinationPath .
```

Confirm `public/index.php` and `vendor/autoload.php` exist without extra nesting. Avoid overwriting installed local assets. The existing Git configuration ignores `public/`, `vendor/` and `composer.lock`.

Keep the installed `.env`, or configure your actual database, MySQL port and application key for a new environment. Start MySQL and run `php artisan config:clear` after environment changes. Generate a key only for a new environment. Keep `storage/` and `bootstrap/cache/` writable.

The new workflow requires the existing legacy schema. The repository's original Laravel migrations do not recreate all legacy tables. For an installed legacy database, apply the additive workflow migrations in order:

```sh
php artisan migrate --path=database/migrations/2026_10_12_000001_create_dynamic_hospital_workflow.php
php artisan migrate --path=database/migrations/2026_10_12_000002_add_dynamic_account_management.php
php artisan migrate --path=database/migrations/2026_10_12_000003_update_dynamic_module_labels.php
php artisan migrate --path=database/migrations/2026_10_12_000004_add_workflow_relationships.php
php artisan storage:link
```

These migrations have already been applied to the current local installation. Explicit paths avoid rerunning original base migrations against a legacy database that lacks their historical migration ledger. The compatibility demo seeder no longer creates users or hospital data.

Use XAMPP Apache at `http://localhost/Hospital/public/login`, or run `php artisan serve` and use its printed URL.

Optional asset build:

```sh
npm install
npm run dev
```

## Verification

```sh
php tests/emergency-smoke.php
php tests/database-smoke.php
```

The first test uses disposable SQLite memory fixtures. The second checks the installed transactional MySQL schema, role ownership, resource forms and persistence inside an outer transaction; test records are rolled back.

For read-only HTTP permissions, provide test account credentials through `CARE_ADMIN_EMAIL/PASSWORD`, `CARE_PATIENT_EMAIL/PASSWORD` and `CARE_HOSPITAL_EMAIL/PASSWORD`, then run with PowerShell 7:

```powershell
pwsh -File .\tests\demo-page-access.ps1 -BaseUrl http://localhost/Hospital/public
```

An unassigned hospital account is expected to have only dashboard access. `php artisan test` also exists, but its original PHPUnit examples are not comprehensive coverage.

## Notes

- Unrecorded inventory stays empty; the application does not invent availability.
- The dashboard polls every five seconds; external hospital/GPS feeds and WebSockets are not implemented.
- Legacy ERP routes remain and may depend on missing controllers or data. The legacy external-login/password endpoints require a separate security review before deployment.
- The original project license is in [LICENSE](LICENSE) (Apache License 2.0).
