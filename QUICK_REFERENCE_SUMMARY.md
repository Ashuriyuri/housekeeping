# 📊 HOUSEKEEPING MANAGEMENT SYSTEM
## QUICK REFERENCE & SUMMARY

**Generated:** May 20, 2026  
**Scope:** Current system analysis only - NO modifications made

---

## SYSTEM OVERVIEW

Your Housekeeping Management System uses a **normalized relational database** with the following architecture:

```
┌─────────────────────────────────────────────────────────────┐
│        HOUSEKEEPING APPOINTMENT MANAGEMENT SYSTEM            │
├─────────────────────────────────────────────────────────────┤
│  8 Core Tables | 8 Foreign Keys | 7 Unique Constraints     │
│  MySQL Database | Laravel Framework | PHP Backend           │
└─────────────────────────────────────────────────────────────┘
```

---

## TABLE STRUCTURE AT A GLANCE

| Table | Purpose | PK | FK Count | Records |
|-------|---------|----|----|---------|
| `users` | Admin/User management | id | 0 | Session tracking |
| `appointments` | Service requests | id | 0 | Core business data |
| `services` | Service catalog | id | 0 | ~8 services |
| `employees` | Staff roster | id | 0 | Team members |
| `appointment_service` | Service assignments | id | 2 | Service bookings |
| `appointment_employee` | Staff assignments | id | 2 | Staff allocations |
| `payments` | Financial records | id | 1 | Payment tracking |
| `employee_availability` | Schedule tracking | id | 2 | Availability logs |

---

## RELATIONSHIP MAP

```
ONE-TO-MANY (1:N):
├─ Users → Appointments (admin creates appointments)
├─ Appointments → Appointment_Service (appointment has services)
├─ Appointments → Appointment_Employee (appointment has staff)
├─ Appointments → Employee_Availability (appointment tracked)
├─ Services → Appointment_Service (service in many appointments)
├─ Employees → Appointment_Employee (employee in many appointments)
└─ Employees → Employee_Availability (employee has availability records)

ONE-TO-ONE (1:1):
└─ Appointments ↔ Payments (one payment per appointment)

MANY-TO-MANY (M:N):
├─ Appointments ↔ Services (via appointment_service junction)
└─ Appointments ↔ Employees (via appointment_employee junction)
```

---

## CARDINALITY SUMMARY

| Relationship | Type | Pattern |
|--------------|------|---------|
| Users → Appointments | 1:N | One user manages many appointments |
| Appointments ↔ Services | N:M | Many services per appointment |
| Appointments ↔ Employees | N:M | Many staff per appointment |
| Appointments → Payments | 1:1 | Exactly one payment per appointment |
| Employees → Availability | 1:N | Many availability records per employee |

---

## BUSINESS RULES (KEY)

### Appointment Status Flow
```
Pending → In Progress → Completed (unidirectional only)
```

### Employee Status
```
Active (available for assignments) | Inactive (not available)
```

### Payment Processing
- Payment created when appointment status = 'Completed'
- Payment amount auto-calculated from services
- Payment method: Cash, GCash, or Bank Transfer
- Payment status: Pending → Paid (manual update)

### Service Pricing
```
Fixed: Flat rate (₱X fixed)
Per SQM: ₱X per square meter
```

### Unique Constraints
- users.email (email must be unique)
- services.service_name (service name must be unique)
- appointment_service (appointment_id, service_id) - no duplicates
- appointment_employee (appointment_id, employee_id) - no duplicates
- payments.appointment_id (one payment per appointment)
- employee_availability (employee_id, available_from) - one per timeframe

---

## DATABASE FEATURES IMPLEMENTED

### Feature #1: Stored Procedure
**CalculateAppointmentTotal** - Computes appointment cost
```sql
CALL CalculateAppointmentTotal(appointment_id, @total);
```

### Feature #2: Stored Procedure
**GetEmployeeAvailability** - Gets available time slots
```sql
CALL GetEmployeeAvailability(employee_id, date, slot_duration);
```

### Feature #3: Trigger
**trg_appointment_completion** - Auto-creates payment when appointment completes
- Calculates total cost automatically
- Creates payment record
- Logs action

### Feature #4: Transaction
**CreateAppointmentWithServices** - Atomically creates appointment with services
- All-or-nothing operation
- Automatic rollback on error

### Feature #5: Transaction
**CompleteAppointmentAndPayment** - Completes appointment and processes payment
- Updates appointment status
- Calculates and records payment
- Releases employees
- Atomic operation with verification

---

## STATUS & ENUM VALUES

### Appointment Statuses
| Status | Meaning | Next State |
|--------|---------|-----------|
| Pending | Created, not started | In Progress |
| In Progress | Service in progress | Completed |
| Completed | Service finished, ready for payment | (final) |

### Employee Statuses
| Status | Meaning | Can Assign? |
|--------|---------|-----------|
| Active | Available for work | ✅ Yes |
| Inactive | Not available | ❌ No |

### Payment Method
- Cash
- GCash
- Bank Transfer

### Payment Status
| Status | Meaning |
|--------|---------|
| Pending | Not yet paid |
| Paid | Payment received |

### Pricing Type
| Type | Calculation |
|------|-----------|
| fixed | Flat rate regardless of area |
| per_sqm | Rate per square meter |

### Availability Flag
| Value | Meaning |
|-------|---------|
| 0 | Booked / Unavailable |
| 1 | Available |

---

## DATA INTEGRITY RULES

### Primary Keys
All tables have BIGINT UNSIGNED auto-increment primary keys

### Foreign Keys with CASCADE DELETE
- appointment_service → appointments
- appointment_service → services
- appointment_employee → appointments
- appointment_employee → employees
- payments → appointments
- employee_availability → employees
- employee_availability → appointments

### Check Constraints
```
appointments.area_sqm > 0 (if not null)
appointments.schedule_date >= NOW()
services.base_price > 0
services.pricing_type IN ('fixed', 'per_sqm')
appointment_service.quantity > 0
appointment_service.custom_price > 0 (if provided)
payments.amount > 0
payments.payment_method IN ('Cash', 'GCash', 'Bank Transfer')
```

---

## INDEXES FOR PERFORMANCE

```
SEARCH INDEXES:
├─ users.email - Login queries
├─ appointments.status - Status filtering
├─ appointments.schedule_date - Date range queries
├─ appointments.customer_name - Customer searches
├─ services.service_name - Service lookups
├─ services.pricing_type - Pricing model queries
├─ employees.status - Active employee filtering
└─ employees.name - Employee searches

JOIN INDEXES:
├─ appointment_service.appointment_id
├─ appointment_service.service_id
├─ appointment_employee.appointment_id
├─ appointment_employee.employee_id
├─ payments.appointment_id
├─ employee_availability.employee_id
├─ employee_availability.appointment_id
└─ employee_availability.available_from
```

---

## APPOINTMENT LIFECYCLE FLOW

```
[1] CREATE
    Input: customer_name, address, area_sqm, schedule_date, notes
    Status: Pending
    ↓

[2] ADD SERVICES
    Select services → Set quantities → Override prices (optional)
    Records created in appointment_service table
    ↓

[3] ASSIGN STAFF
    Choose employees → Define tasks → Set time slots
    Records created in appointment_employee table
    ↓

[4] UPDATE TO IN PROGRESS
    Appointment work begins
    Status: In Progress
    ↓

[5] COMPLETE APPOINTMENT
    Appointment work finished
    Status: Completed
    ↓
    [TRIGGER FIRES]
    Auto-calculate total
    Auto-create Payment record (Status: Pending)
    ↓

[6] RECORD PAYMENT
    Admin updates: payment_method, payment_status
    Status: Paid
    ↓

[7] ARCHIVE
    Completed appointment with recorded payment
    Available for reporting
```

---

## PAYMENT CALCULATION LOGIC

```
Total = SUM of Service Costs

FOR each service in appointment_service:
    IF service.pricing_type = 'fixed':
        cost = quantity × (custom_price OR base_price)
    
    ELSE IF service.pricing_type = 'per_sqm':
        cost = quantity(sqm) × (custom_price OR base_price)
    
    total += cost

RESULT: Total appointment cost
```

### Example Calculation
```
Appointment #1:
├─ Service 1: Deep Cleaning (per_sqm)
│  └─ 150 sqm × ₱75/sqm = ₱11,250
├─ Service 2: Sofa Cleaning (fixed)
│  └─ 1 × ₱1,500 = ₱1,500
└─ Total: ₱12,750
```

---

## EMPLOYEE SCHEDULING

```
AVAILABILITY TRACKING:

appointment_employee table:
├─ is_available: Slot availability (0=Booked, 1=Free)
├─ start_time: When employee starts
└─ end_time: When employee finishes

employee_availability table:
├─ available_from: Shift start
├─ available_to: Shift end
├─ is_available: Availability status
└─ reason: Why booked/unavailable
```

### Time Slot Logic
```
WORK HOURS: 06:00 - 22:00 (6 AM to 10 PM)

GetEmployeeAvailability() generates 1-hour slots:
├─ 06:00-07:00 ✓ Available
├─ 07:00-08:00 ✓ Available
├─ 08:00-09:00 ✗ Booked (Appointment #2)
├─ 09:00-10:00 ✗ Booked (Appointment #2)
├─ 10:00-11:00 ✓ Available
└─ ... continues to 22:00
```

---

## CURRENT SERVICES CATALOG

Based on migration `2026_05_19_000000_add_pricing_type_to_services_table.php`:

| Service | Type | Price |
|---------|------|-------|
| Normal Cleaning | per_sqm | ₱55.00/sqm |
| Deep Cleaning | per_sqm | ₱75.00/sqm |
| Sofa Cleaning | fixed | ₱1,500.00 |
| Carpet Cleaning | per_sqm | ₱65.00/sqm |
| Bathroom Cleaning | fixed | ₱800.00 |
| Kitchen Cleaning | fixed | ₱1,000.00 |
| Window Cleaning | fixed | ₱600.00 |
| Move-in/Move-out Cleaning | per_sqm | ₱85.00/sqm |

---

## NORMALIZATION COMPLIANCE

### 1NF (First Normal Form) ✅
- All attributes contain atomic values
- No repeating groups (M:M uses junction tables)
- Each row is uniquely identified

### 2NF (Second Normal Form) ✅
- Meets 1NF
- No partial dependencies
- All non-key attributes fully dependent on PK

### 3NF (Third Normal Form) ✅
- Meets 2NF
- No transitive dependencies
- All attributes depend only on PK

---

## QUERY EXAMPLES

### Get Appointment with All Details
```sql
SELECT 
    a.*,
    COUNT(DISTINCT aps.id) as service_count,
    COUNT(DISTINCT ae.id) as employee_count,
    p.payment_status
FROM appointments a
LEFT JOIN appointment_service aps ON aps.appointment_id = a.id
LEFT JOIN appointment_employee ae ON ae.appointment_id = a.id
LEFT JOIN payments p ON p.appointment_id = a.id
WHERE a.id = 1
GROUP BY a.id;
```

### Calculate Appointment Total
```sql
CALL CalculateAppointmentTotal(1, @total);
SELECT @total AS total_cost;
```

### Get Available Employees for Date
```sql
SELECT DISTINCT e.*
FROM employees e
WHERE e.status = 'Active'
AND e.id NOT IN (
    SELECT DISTINCT employee_id
    FROM appointment_employee
    WHERE DATE(start_time) = '2026-05-20'
);
```

### Get Pending Payments
```sql
SELECT 
    p.*,
    a.customer_name,
    a.address
FROM payments p
INNER JOIN appointments a ON a.id = p.appointment_id
WHERE p.payment_status = 'Pending'
ORDER BY p.created_at DESC;
```

---

## SYSTEM CAPABILITIES SUMMARY

### ✅ Currently Implemented
- Appointment creation with multiple services
- Many-to-many service assignment with custom pricing
- Staff allocation with task assignment
- Employee availability tracking
- Payment calculation and recording
- Status workflow management
- Time slot scheduling
- Price type flexibility (fixed vs per_sqm)

### ✅ Database Features
- 2 Stored Procedures for calculation and scheduling
- 1 Trigger for automatic payment creation
- 2 Transactions for atomic operations
- Full referential integrity with cascade deletes
- Comprehensive check constraints
- Optimized indexes for performance
- Audit capability (logs in trigger)

### ✅ Data Integrity
- Primary key constraints (all tables)
- Foreign key constraints (cascade delete)
- Unique constraints (no duplicates)
- Check constraints (value validation)
- Business rule enforcement

---

## FILES GENERATED

This analysis created the following documentation:

1. **DATABASE_COMPLETE_ERD_ANALYSIS.md** (Main Document)
   - Complete ERD with Mermaid diagram
   - Business rules and constraints
   - Full database schema with SQL
   - Logical and conceptual design
   - Stored procedures code
   - Trigger implementation
   - Transaction examples
   - Comprehensive database analysis

2. **QUICK_REFERENCE_SUMMARY.md** (This File)
   - System overview
   - Quick lookup tables
   - Key formulas and examples
   - Quick query templates

---

## IMPORTANT NOTES

### ⚠️ NO CHANGES MADE
This analysis is **READ-ONLY** documentation:
- ✅ Analyzed existing structure
- ✅ Documented current implementation
- ✅ Extracted business logic
- ❌ No code modifications
- ❌ No table changes
- ❌ No migrations created
- ❌ No refactoring done

### ✅ BASED ON CURRENT SYSTEM
All documentation reflects:
- Current Laravel migrations (exactly as implemented)
- Current Eloquent models (exactly as defined)
- Current database relationships
- Current business logic
- Current pricing strategies
- Current workflow processes

---

## NEXT STEPS (IF NEEDED)

If you want to extend this system, you could:
1. Add user roles/permissions table
2. Add appointment history/changelog tracking
3. Add customer feedback/ratings
4. Add employee performance metrics
5. Add report generation procedures
6. Add backup/archival procedures

**However, none of these are in scope for this analysis.**

---

**Analysis Complete** ✅  
**Date:** May 20, 2026  
**System Status:** Fully Operational & Documented  
**Scope:** Analysis Only - Current System Implementation
