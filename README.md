# CareRoute — Emergency Bed Management MVP

Working hackathon prototype built on the existing Laravel 8 project. No new packages required. The new landing page is the emergency coordination dashboard; legacy hospital CRUD stays available at its existing routes.

## Run locally (XAMPP)

1. Start **Apache and MySQL** in XAMPP. Accounts, modules and masters use the existing `hospitals` database on port `3306`; emergency allocation uses its own JSON mock store.
2. Restore PHP dependencies: extract the bundled `vendor.zip` in the project root (it contains `vendor/`). Alternatively run `composer install` with a compatible PHP environment; this repository does not include a dependency lockfile.
3. If `.env` is missing, copy `env (1)` to `.env`. Preserve the existing application key. For a fresh environment without a key, run `php artisan key:generate`.
4. Ensure `storage/` and `bootstrap/cache/` are writable. Run `php artisan config:clear` and `php artisan view:clear` after setup.
5. Open **http://localhost/Hospital/public/emergency** (also the project root landing page). An alternative is `php artisan serve`, then http://127.0.0.1:8000/emergency.
6. Run `php artisan db:seed --class=CareRouteDemoSeeder` to create missing demo users and assignments. Sign in at `/login`; new demo accounts use password **CareRoute@2026**. See [DEMO-LOGIN-FLOW.md](DEMO-LOGIN-FLOW.md) for all accounts and exact instructions.

## Demo and checks

Patient login: submit the accident scenario and request C. Hospital C login: verify Critical and accept. Patient login: check Reserved and submit a second Critical patient. Hospital A is rejected (0 available ICU); C is recommended (8 km, 1 ICU), followed by B (10 km, 2 ICU). A second Critical request goes to B when C is confirmed. See [HACKATHON-DEMO.md](HACKATHON-DEMO.md) for the full 1–2 minute script and optional reclaim scenario.

```powershell
php tests/emergency-smoke.php
```

Checks cover matching, confirmation, verified priority, temporary hold reclaim, protected confirmed/occupied beds, expiry, duplicate patients and rate limiting. Tests use their own temporary store and do not modify demo data.

## Seed and architecture

| Hospital | Distance | General free | ICU free | Ventilator free | Status |
| --- | --- | --- | --- | --- | --- |
| A | 5 km | 4 | 0 | 0 | Open |
| B | 10 km | 5 | 2 | 1 | Open |
| C | 8 km | 3 | 1 | 1 | Open |
| D | 14 km | 6 | 3 | 2 | Open |
| E | 3 km | 2 | 1 | 1 | Offline |

Each care category also includes one seeded occupied bed to demonstrate protection. Ventilator beds are a separate care category with a ventilator attached.

`EmergencyController` validates input and gates hospital actions using authenticated Admin/Hospital/Patient roles. `EmergencyBedService` seeds hospitals, ranks compatible hospitals by ascending distance (available capacity breaks ties), and serializes all storage operations through a separate file lock. State persists in `storage/app/emergency-demo.json`, created on first access. `emergency.blade.php` is responsive HTML/CSS with plain JavaScript fetch and 5-second polling.

Queue priority: operator-verified Critical > verified Urgent > Stable/unverified, FIFO within equal priority. Self-declared Critical does not grant elevated priority. Critical requests can reclaim only lower-priority **unconfirmed temporary holds**. Confirmed reservations and occupied beds are protected from priority displacement. Operator acceptance changes a temporary hold into Reserved; arrival changes it to Occupied. Pending and Reserved expire after five minutes; Occupied never expires. Expiry executes on the next read/action, with no worker required. Request Bed is revalidated under the same lock as allocation, preventing stale recommendations from double-allocating beds.

Five submissions per IP per minute, duplicate active patient prevention, account ownership for patient bed requests, failed-login throttling, CSRF protection, and hospital verification demonstrate basic abuse controls. The old operator PIN is disabled. Hospital C/B operators can only accept their own hospital requests; patient sees only their own requests. Distances are fixed mock values relative to Accident Site; the entered location is descriptive. No real APIs, ML, payments, GPS, or complex authentication are included.

Reset via the unlocked Hospital desk restores only the JSON emergency seed, leaving legacy MySQL data untouched. Demo data is ignored by Git.

Known legacy issue: global `php artisan route:list` fails because an existing route references the missing `FeedbackController`. This does not prevent the emergency page or its JSON endpoints from serving. The legacy module repair is outside this MVP.

## Judges: key points

- Required care is checked before proximity, preventing an unsuitable nearest-hospital recommendation.
- Priority is activated by hospital verification rather than self-declaration.
- Temporary holds can be reclaimed without evicting occupied or confirmed patients.
- Rejection reasons, status transitions and an audit-style decision log explain allocation.
- A complete request → hospital acceptance → reservation → arrival flow works with no external integration.

---

## Original project documentation

```markdown
# Hospital Management System with Real-Time Blood Bank & Bed Inventory

A comprehensive hospital management system built with Laravel that provides real-time tracking of blood inventory, bed availability, and role-based module access. Features RESTful APIs for seamless data integration with government health departments.

## 🏥 Features
![My Image](Photos\Screenshot 2026-02-18 104054.png)
![My Image](C:\xampp\htdocs\Hospital\resources\Screenshot 2026-02-18 104114.png)
![Hospital Management System Dashboard Screenshot](C:\xampp\htdocs\Hospital\resources\Screenshot 2026-02-18 104054.png)
<img src="Photos\Screenshot 2026-02-18 104054.png" alt="Hospital">



### 🔐 Authentication & Authorization
- Secure login page with Laravel Breeze/Sanctum
- Multi-role support (Admin, Staff, Ambulance, Family)
- Module-level permissions using Spatie/laravel-permission
- Email verification & password reset

### 📊 Dashboard
- Real-time overview of hospital statistics
- Key metrics display (bed occupancy, blood inventory levels)
- Interactive charts using Chart.js
- Quick access to frequently used modules

### 🩸 Blood Bank Management
- Real-time blood inventory tracking
- Multiple blood types support (A+, A-, B+, B-, AB+, AB-, O+, O-)
- Automatic updates upon blood donation via API
- Historical donation records
- Blood expiry tracking and alerts
- Government reporting capabilities via API endpoints

### 🛏️ Bed Inventory Management
- Real-time bed occupancy tracking
- Multiple bed types (ICU, General, Emergency, Pediatric, NICU, CCU)
- Bed availability status with color coding
- Predictive discharge dates using ML algorithms
- Automatic bed count updates during admission/discharge
- Bed allocation history with patient details

### 📦 Module Management
- Dynamic module creation through admin panel
- Module assignment to specific roles
- Customizable access permissions
- Modular architecture with Laravel modules

### 🔄 Real-Time Updates
- Laravel WebSocket/Pusher for real-time updates
- API integration for blood donation records
- Automatic bed count updates during admission
- Discharge-triggered bed availability updates
- Live dashboard refresh without page reload

## 🚀 Technology Stack

### Backend
- Laravel 10.x - PHP Framework
- MySQL/PostgreSQL - Database
- Laravel Sanctum - API Authentication
- Laravel WebSockets - Real-time updates
- Spatie Laravel Permission - Role & Permissions
- Laravel Queue - Job processing
- Redis - Caching & Broadcasting

### Frontend
- Blade Templates / Livewire / Inertia.js
- Bootstrap 5 / Tailwind CSS
- Alpine.js / Vue.js (optional)
- Chart.js - Data visualization
- Axios - API calls

## 📋 Prerequisites

- PHP >= 8.1
- Composer
- MySQL
- Node.js & NPM
- Redis (optional)
- Git

## 🔧 Installation

1. Clone the repository
```bash
git clone https://github.com/developertanay/Hospital.git
cd Hospital
```

2. Install PHP dependencies
```bash
composer install
```

3. Install NPM dependencies
```bash
npm install && npm run dev
```

4. Environment setup
```bash
cp .env.example .env
php artisan key:generate
```

5. Configure database in `.env` file
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hospital_db
DB_USERNAME=root
DB_PASSWORD=

PUSHER_APP_ID=your_pusher_id
PUSHER_APP_KEY=your_pusher_key
PUSHER_APP_SECRET=your_pusher_secret
PUSHER_APP_CLUSTER=mt1
```

6. Run migrations and seeders
```bash
php artisan migrate --seed
```

7. Start the development server
```bash
php artisan serve
php artisan websockets:serve
```

## 📡 API Endpoints

### Authentication
- POST /api/login - User login
- POST /api/register - User registration  
- POST /api/logout - User logout
- GET /api/user - Get authenticated user

### Blood Bank API
- GET /api/blood/inventory - Get current blood inventory
- POST /api/blood/donate - Record new blood donation
- GET /api/blood/type/{type} - Get specific blood type details
- PUT /api/blood/update/{id} - Update blood record
- DELETE /api/blood/remove/{id} - Remove expired blood
- GET /api/blood/donations - Get donation history
- GET /api/blood/expiring - Get expiring blood units

### Bed Management API
- GET /api/beds - Get all beds
- GET /api/beds/available - Get available beds
- POST /api/beds/admit - Admit patient
- PUT /api/beds/discharge/{id} - Discharge patient
- GET /api/beds/occupancy - Get occupancy stats
- GET /api/beds/predictions - Get discharge predictions
- GET /api/beds/type/{type} - Get beds by type

### Module Management API
- GET /api/modules - Get all modules
- POST /api/modules/create - Create new module
- POST /api/modules/assign - Assign module to role
- GET /api/modules/user/{userId} - Get user modules
- PUT /api/modules/permissions - Update permissions

## 📁 Laravel Project Structure

```
hospital-management-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   ├── BloodBankController.php
│   │   │   ├── BedController.php
│   │   │   ├── ModuleController.php
│   │   │   └── DashboardController.php
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php
│   │   ├── BloodInventory.php
│   │   ├── Bed.php
│   │   ├── Module.php
│   │   └── Patient.php
│   └── Services/
├── database/
│   ├── migrations/
│   ├── seeders/
│   │   ├── RoleSeeder.php
│   │   ├── ModuleSeeder.php
│   │   └── UserSeeder.php
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   ├── auth/
│   │   ├── dashboard/
│   │   ├── blood-bank/
│   │   └── beds/
│   └── js/
├── routes/
│   ├── web.php
│   └── api.php
└── .env
```

## 🎯 Key Laravel Packages Used

- Laravel Sanctum - API authentication
- Spatie Laravel Permission - Role & permissions
- Laravel WebSockets - Real-time updates
- Laravel Excel - Export reports
- Laravel Debugbar - Development debugging
- Barryvdh/Laravel-DomPDF - PDF generation
- Laravel Telescope - Monitoring (optional)

## 🔒 Security Features

- Laravel's built-in CSRF protection
- XSS protection
- SQL injection prevention via Eloquent
- Rate limiting on APIs
- JWT/OAuth2 ready
- Role-based middleware
- Encrypted sensitive data

## 📊 Real-time Implementation

### Broadcasting Events
- BloodDonated event broadcasts to blood-inventory channel
- BedStatusUpdated event broadcasts to bed-occupancy channel
- PatientAdmitted event updates bed availability
- PatientDischarged event triggers bed release

### Listening with Laravel Echo
```javascript
window.Echo.channel('blood-inventory')
    .listen('BloodDonated', (e) => {
        updateBloodInventory(e.data);
    });
```

## 📈 Reporting Features

- Blood donation reports (Daily/Weekly/Monthly)
- Bed occupancy analytics
- Discharge predictions report
- Government compliance reports
- Export to Excel/PDF
- Email scheduled reports

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Authors

- Tanay Jorihar - [YourGitHub](https://github.com/developertanay)

## 📧 Contact

For any queries or support, please email: developertanay@gmail.com

---

**Made with ❤️ for better healthcare management**
```
