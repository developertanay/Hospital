```markdown
# Hospital Management System with Real-Time Blood Bank & Bed Inventory

A comprehensive hospital management system built with Laravel that provides real-time tracking of blood inventory, bed availability, and role-based module access. Features RESTful APIs for seamless data integration with government health departments.

## 🏥 Features
![WhatsApp Image 2026-02-18 at 10 38 09 AM](https://github.com/user-attachments/assets/f2023c9e-6431-4feb-adfd-e55543e3c2f0)
![WhatsApp Image 2026-02-18 at 10 37 19 AM](https://github.com/user-attachments/assets/4bf37e09-4a29-452d-a3ab-a0b6a900510d)

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
