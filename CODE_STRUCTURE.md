# 📁 Housekeeping System - Code Structure & Implementation

## Project Overview

This is a complete Laravel 11 housekeeping appointment management system with all CRUD operations, relationships, and workflows implemented.

## 🗂️ Directory Structure

```
housekeeping/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AppointmentController.php      # Full CRUD + service/employee assignment
│   │       ├── EmployeeController.php         # Employee management CRUD
│   │       ├── ServiceController.php          # Service catalog CRUD
│   │       ├── PaymentController.php          # Payment management
│   │       └── ProfileController.php          # User profile
│   └── Models/
│       ├── Appointment.php                    # Has many-to-many relations
│       ├── Service.php                        # Service catalog
│       ├── Employee.php                       # Employee records
│       ├── Payment.php                        # One-to-one with Appointment
│       └── User.php                           # Admin user
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2026_05_18_131046_create_appointments_table.php
│   │   ├── 2026_05_18_131047_create_employees_table.php
│   │   ├── 2026_05_18_131048_create_services_table.php
│   │   ├── 2026_05_18_131049_create_appointment_service_table.php
│   │   ├── 2026_05_18_131050_create_appointment_employee_table.php
│   │   └── 2026_05_18_131051_create_payments_table.php
│   └── seeders/
│       └── DatabaseSeeder.php                 # Seed with services and employees
├── resources/
│   ├── css/
│   │   └── app.css                            # Tailwind + custom utilities
│   ├── js/
│   │   ├── app.js
│   │   └── bootstrap.js
│   └── views/
│       ├── dashboard.blade.php                # Stats dashboard
│       ├── layouts/
│       │   ├── app.blade.php                  # Main layout
│       │   └── navigation.blade.php           # Updated navigation
│       ├── appointments/
│       │   ├── index.blade.php                # List all appointments
│       │   ├── create.blade.php               # Create appointment form
│       │   ├── edit.blade.php                 # Edit appointment form
│       │   └── show.blade.php                 # View appointment details
│       ├── employees/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   └── show.blade.php
│       ├── services/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   └── show.blade.php
│       └── payments/
│           ├── index.blade.php
│           ├── create.blade.php
│           └── edit.blade.php
├── routes/
│   ├── web.php                                # All application routes
│   ├── auth.php                               # Authentication routes (Breeze)
│   └── console.php
└── public/
    └── index.php                              # Application entry point
```

## 🗄️ Database Schema

### appointments table

```sql
id (PK)
customer_name (string)
address (string)
schedule_date (datetime)
status (enum: Pending, In Progress, Completed)
notes (text, nullable)
created_at, updated_at (timestamps)
```

### employees table

```sql
id (PK)
name (string)
phone (string)
position (string)
status (enum: Active, Inactive)
created_at, updated_at (timestamps)
```

### services table

```sql
id (PK)
service_name (string)
description (text, nullable)
base_price (decimal 10,2)
created_at, updated_at (timestamps)
```

### appointment_service table (Pivot)

```sql
id (PK)
appointment_id (FK)
service_id (FK)
quantity (integer, default 1)
custom_price (decimal 10,2, nullable)
created_at, updated_at (timestamps)
```

### appointment_employee table (Pivot)

```sql
id (PK)
appointment_id (FK)
employee_id (FK)
task (string, nullable)
created_at, updated_at (timestamps)
```

### payments table

```sql
id (PK)
appointment_id (FK, unique)
amount (decimal 10,2)
payment_method (enum: Cash, GCash, Bank Transfer)
payment_status (enum: Pending, Paid)
created_at, updated_at (timestamps)
```

## 🔗 Model Relationships

### Appointment Model

```php
$appointment->services()          // BelongsToMany with pivot
$appointment->employees()         // BelongsToMany with pivot
$appointment->payment()           // HasOne
$appointment->total_price         // Calculated attribute
```

### Service Model

```php
$service->appointments()          // BelongsToMany with pivot
```

### Employee Model

```php
$employee->appointments()         // BelongsToMany with pivot
```

### Payment Model

```php
$payment->appointment()           // BelongsTo
```

## 🎮 Controller Methods

### AppointmentController

- `index()` - List all appointments with relations
- `create()` - Show form with services and employees
- `store()` - Create appointment with service/employee sync
- `show()` - Display appointment details with all relations
- `edit()` - Show edit form with current data
- `update()` - Update appointment and relations
- `destroy()` - Delete appointment

### EmployeeController

- `index()` - List all employees
- `create()` - Show employee form
- `store()` - Create employee
- `show()` - Display employee with appointments
- `edit()` - Edit employee
- `update()` - Update employee
- `destroy()` - Delete employee

### ServiceController

- `index()` - List all services (card layout)
- `create()` - Show service form
- `store()` - Create service
- `show()` - Display service details
- `edit()` - Edit service
- `update()` - Update service
- `destroy()` - Delete service

### PaymentController

- `index()` - List all payments
- `create()` - Show payment form (only for completed appointments)
- `store()` - Create/update payment
- `edit()` - Edit payment
- `update()` - Update payment
- `destroy()` - Delete payment

## 🛣️ Routes

```php
// Dashboard
GET /dashboard

// Appointments
GET    /appointments              (index)
GET    /appointments/create       (create)
POST   /appointments              (store)
GET    /appointments/{id}         (show)
GET    /appointments/{id}/edit    (edit)
PUT    /appointments/{id}         (update)
DELETE /appointments/{id}         (destroy)

// Employees
GET    /employees
GET    /employees/create
POST   /employees
GET    /employees/{id}
GET    /employees/{id}/edit
PUT    /employees/{id}
DELETE /employees/{id}

// Services
GET    /services
GET    /services/create
POST   /services
GET    /services/{id}
GET    /services/{id}/edit
PUT    /services/{id}
DELETE /services/{id}

// Payments
GET    /payments
GET    /appointments/{appointment}/payments/create
POST   /appointments/{appointment}/payments
GET    /payments/{id}/edit
PUT    /payments/{id}
DELETE /payments/{id}

// Profile
GET    /profile
PATCH  /profile
DELETE /profile
```

## 🎨 Blade Templates

### Layouts

- **app.blade.php** - Main layout with navigation
- **navigation.blade.php** - Navigation bar with all module links

### Components (Built-in)

- `x-app-layout` - Main wrapper
- `x-nav-link` - Navigation links
- `x-responsive-nav-link` - Mobile navigation
- `x-dropdown` - User dropdown menu

### Custom Features

- Status badge styling (Pending/In Progress/Completed)
- Service selection with quantity and price override
- Employee assignment with task input
- Responsive tables with hover effects
- Quick action buttons on dashboard

## 🔍 Key Implementation Details

### Service Assignment Logic

```php
// In store/update methods
if ($request->has('services')) {
    $serviceData = [];
    foreach ($request->services as $key => $serviceId) {
        $serviceData[$serviceId] = [
            'quantity' => $request->input("service_quantity.$key", 1),
            'custom_price' => $request->input("service_price.$key"),
        ];
    }
    $appointment->services()->sync($serviceData);
}
```

### Employee Assignment Logic

```php
// Similar to services, syncs with tasks
if ($request->has('employees')) {
    $employeeData = [];
    foreach ($request->employees as $key => $employeeId) {
        $employeeData[$employeeId] = [
            'task' => $request->input("employee_tasks.$key"),
        ];
    }
    $appointment->employees()->sync($employeeData);
}
```

### Payment Authorization

```php
// Only allow payment for completed appointments
if ($appointment->status !== 'Completed') {
    return redirect()->with('error', 'Payment can only be created for completed appointments.');
}
```

### Price Calculation

```php
// Accessor in Appointment model
public function getTotalPriceAttribute()
{
    return $this->services->sum(function ($service) {
        $price = $service->pivot->custom_price ?? $service->base_price;
        return $price * $service->pivot->quantity;
    });
}
```

## ✅ Validation Rules

### Appointments

- customer_name: required, string, max 255
- address: required, string, max 255
- schedule_date: required, date_format: Y-m-d\TH:i
- status: required, in: Pending, In Progress, Completed
- notes: nullable, string
- services: array, exists:services,id
- employees: array, exists:employees,id

### Services

- service_name: required, string, max 255
- description: nullable, string
- base_price: required, numeric, min 0

### Employees

- name: required, string, max 255
- phone: required, string, max 20
- position: required, string, max 255
- status: required, in: Active, Inactive

### Payments

- amount: required, numeric, min 0
- payment_method: required, in: Cash, GCash, Bank Transfer
- payment_status: required, in: Pending, Paid

## 🎯 Features Summary

✅ Full CRUD for all resources
✅ Many-to-Many relationships with pivot data
✅ Service assignment with quantity and custom pricing
✅ Employee assignment with task description
✅ Status tracking and updates
✅ Payment management with constraints
✅ Responsive design
✅ Dashboard with statistics
✅ Input validation
✅ CSRF protection
✅ Authentication via Laravel Breeze
✅ Database seeding with sample data
✅ Professional UI with status badges

## 🚀 Performance Considerations

- Eager loading of relations: `with('services', 'employees', 'payment')`
- Pivot data optimized in queries
- Indexes on foreign keys (created by migrations)
- Efficient route model binding

## 🔐 Security

- CSRF tokens on all forms
- Route middleware for authentication
- Password hashing (Breeze)
- Input validation on all forms
- Authorization checks
- SQL injection prevention (ORM)

---

This system is production-ready and fully functional.
