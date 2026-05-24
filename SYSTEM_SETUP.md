# 🏠 Laravel Housekeeping Appointment Management System

A complete production-ready Laravel-based housekeeping appointment management system with admin dashboard, appointment scheduling, employee management, service catalog, and payment processing.

## 🎯 Features

### Core Modules

- **Appointment Management**: Create, edit, delete, and track appointments with status updates
- **Service Catalog**: Manage housekeeping services with pricing (Deep Cleaning, Sofa Cleaning, Carpet Cleaning, etc.)
- **Employee Management**: Add, edit, and manage housekeeping employees
- **Service Assignment**: Assign multiple services to each appointment
- **Employee Assignment**: Assign employees to appointments with specific tasks
- **Payment System**: Record and track payments only for completed appointments

### Key Features

- Admin-only system (via Laravel Breeze Authentication)
- Status tracking: Pending → In Progress → Completed
- Many-to-Many relationships between Appointments & Services/Employees
- Automatic price calculation based on services and quantities
- Custom price overrides per service per appointment
- Payment methods: Cash, GCash, Bank Transfer
- Payment status tracking: Pending/Paid
- Responsive design with Tailwind CSS
- Dashboard with statistics

## 📋 Requirements

- PHP 8.1 or higher
- Composer
- MySQL/MariaDB
- Node.js & npm
- Laravel 11

## 🚀 Installation & Setup

### 1. Environment Setup

```bash
# Navigate to project directory
cd housekeeping

# Copy environment file
cp .env.example .env

# Edit .env and configure database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=housekeeping
DB_USERNAME=root
DB_PASSWORD=
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Generate application key
php artisan key:generate
```

### 3. Database Setup

```bash
# Run migrations to create all tables
php artisan migrate

# Seed database with sample data (services, employees, admin user)
php artisan db:seed

# Build frontend assets
npm run build
```

### 4. Login Credentials (After Seeding)

```
Email: admin@housekeeping.com
Password: password
```

### 5. Start Development Server

```bash
# Terminal 1: Start Laravel development server
php artisan serve

# Terminal 2: Build CSS/JS changes (if needed)
npm run dev
```

Visit: http://localhost:8000

## 📊 Database Schema

### Tables

- **users** - Admin users (via Laravel Breeze)
- **appointments** - Appointment records with customer info
- **employees** - Employee records
- **services** - Service catalog with pricing
- **appointment_service** - Many-to-Many pivot (services per appointment)
- **appointment_employee** - Many-to-Many pivot (employees per appointment)
- **payments** - Payment records (one-to-one with appointments)

## 🔄 System Workflow

1. **Create Appointment**
    - Enter customer name, address, schedule date
    - Add notes if needed

2. **Assign Services**
    - Select one or more services (Deep Cleaning, Sofa Cleaning, etc.)
    - Set quantity and custom pricing (optional)
    - System auto-calculates total

3. **Assign Employees**
    - Select employees to work on the appointment
    - Assign specific tasks for each employee

4. **Status Update**
    - Change status from Pending → In Progress → Completed

5. **Record Payment**
    - Payment only available for Completed appointments
    - Enter amount, payment method, and status
    - Can edit/delete payments as needed

## 🖥️ Page Structure

### Admin Dashboard

- Statistics overview (total appointments, pending, completed, employees, services)
- Quick action buttons

### Appointments

- List all appointments with status badges
- View appointment details
- Edit appointment details and assignments
- Delete appointments
- Record payments for completed appointments

### Employees

- List all active/inactive employees
- Add new employee
- Edit employee details
- View employee assignment history

### Services

- Browse service catalog
- Add new service
- Edit service details and pricing

### Payments

- View all recorded payments
- Filter by status
- Edit payment details
- Delete payment records

## 🎨 UI/UX Features

- **Status Badges**:
    - Pending (Yellow)
    - In Progress (Blue)
    - Completed (Green)
- **Employee Status**:
    - Active (Green)
    - Inactive (Red)

- **Payment Status**:
    - Pending (Yellow)
    - Paid (Green)

- Responsive design for mobile, tablet, and desktop
- Clean, professional interface
- Easy navigation with sidebar and top menu

## 🔐 Security

- CSRF protection on all forms
- Authenticated routes with Laravel Breeze
- Authorization checks on resource deletion
- Input validation on all forms
- Password hashing

## 📝 Models & Relationships

```
Appointment
  - hasMany Services (through AppointmentService)
  - hasMany Employees (through AppointmentEmployee)
  - hasOne Payment

Service
  - belongsToMany Appointments

Employee
  - belongsToMany Appointments

Payment
  - belongsTo Appointment
```

## 🛠️ Customization

### Adding New Services

Navigate to Services → Add Service and fill in:

- Service name
- Description
- Base price

### Managing Employees

Navigate to Employees → Add Employee:

- Name
- Phone number
- Position
- Status (Active/Inactive)

### Payment Methods

Current methods: Cash, GCash, Bank Transfer
To add more, edit:

- [PaymentController.php](app/Http/Controllers/PaymentController.php)
- [Payment model](app/Models/Payment.php)
- [Payment views](resources/views/payments/)

## 📧 Support & Maintenance

### Troubleshooting

**Database connection error:**

```bash
# Check database exists
# Verify .env database credentials
# Ensure MySQL is running
```

**Migrations not running:**

```bash
# Reset database (WARNING: deletes all data)
php artisan migrate:refresh --seed
```

**CSS/JS not loading:**

```bash
npm run build
php artisan view:clear
```

## 🎓 Learning Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Tailwind CSS](https://tailwindcss.com)
- [Blade Templates](https://laravel.com/docs/blade)
- [Database Migrations](https://laravel.com/docs/migrations)
- [Eloquent ORM](https://laravel.com/docs/eloquent)

## 📈 Future Enhancements

- Inventory/material tracking (supplies used per service)
- Invoice PDF generation
- Multi-branch/SaaS system
- Advanced analytics dashboard
- SMS/Email notifications
- Appointment reminders
- Customer portal (read-only for bookings)
- Integration with payment gateways
- Barcode/QR code tracking

## 📄 License

This project is open source and available under the MIT License.

## 👨‍💻 Development Notes

- All controllers implement full CRUD operations
- Uses Laravel Eloquent ORM for database operations
- Blade templates for dynamic views
- Tailwind CSS for styling
- JavaScript for interactive elements (service/employee selection)

---

**Version**: 1.0  
**Last Updated**: May 2026  
**Status**: Production Ready
