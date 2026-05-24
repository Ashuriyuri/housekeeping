# 📋 STORED PROCEDURES IMPLEMENTATION GUIDE

## Housekeeping Management System - hk_db Database

**Date:** May 24, 2026  
**Status:** Ready for Production  
**Database:** hk_db (housekeeping_db)  
**Compatibility:** MySQL 5.7+, MariaDB 10.3+, XAMPP, phpMyAdmin

---

## 🎯 OVERVIEW

### What's Being Added

- **9 Production-Ready Stored Procedures**
- **No breaking changes** to existing system
- **No modifications** to existing tables, triggers, or views
- **Fully compatible** with existing Laravel application
- **Safe to call** from Laravel controllers or direct SQL

### What These Procedures Do

| Procedure                             | Function               | Call From                |
| ------------------------------------- | ---------------------- | ------------------------ |
| `sp_get_appointment_analytics`        | Dashboard statistics   | Admin Dashboard, Reports |
| `sp_get_monthly_revenue`              | Financial reporting    | Finance Module           |
| `sp_get_revenue_by_service`           | Service breakdown      | Revenue Analysis         |
| `sp_get_employee_performance`         | Employee metrics       | HR/Admin Dashboard       |
| `sp_get_overdue_appointments`         | Alert system           | Monitoring, Reports      |
| `sp_bulk_update_appointment_status`   | Batch updates          | Admin Operations         |
| `sp_get_appointment_with_details`     | Single query retrieval | API, Controllers         |
| `sp_get_employee_availability_status` | Scheduling checks      | Appointment Booking      |
| `sp_archive_old_appointments`         | Maintenance (Optional) | Database Cleanup         |

---

## 📦 IMPLEMENTATION STEPS

### Step 1: Backup Current Database

Before implementing, always backup:

```bash
# From Command Line (Windows)
mysqldump -u root -p hk_db > backup_hk_db_2026_05_24.sql

# Or use phpMyAdmin:
# 1. Go to phpMyAdmin
# 2. Select database: hk_db
# 3. Click "Export"
# 4. Select "SQL" format
# 5. Click "Go" to download
```

### Step 2: Execute SQL File in phpMyAdmin

**Method A: Using phpMyAdmin GUI**

```
1. Open phpMyAdmin → Select database 'hk_db'
2. Click "SQL" tab (top menu)
3. Click "Choose File" button
4. Select: database/create_stored_procedures.sql
5. Click "Go" to execute
6. Wait for success message: "✓ Statements executed successfully"
```

**Method B: Using MySQL Command Line**

```bash
# Open Command Prompt/PowerShell as Administrator
cd C:\xampp\mysql\bin

# Execute the SQL file
mysql -u root -p hk_db < C:\Users\admin\Desktop\FinalProject\housekeeping\database\create_stored_procedures.sql

# When prompted, enter password (leave blank if no password)
```

**Method C: Using Laravel Migration (Recommended)**

If you want Laravel to manage this:

```bash
# In VS Code Terminal
php artisan tinker

# In tinker shell:
DB::unprepared(file_get_contents('database/create_stored_procedures.sql'));
# Type: exit to quit
```

### Step 3: Verify Installation

```sql
-- In phpMyAdmin SQL tab, run this verification query:

SHOW PROCEDURE STATUS WHERE Db = 'hk_db';

-- Expected output: 8-9 rows showing all procedures as "DEFINER=root@localhost"
```

### Step 4: Test Each Procedure

See "Testing Instructions" section below.

---

## ✅ TESTING INSTRUCTIONS

### Test 1: Appointment Analytics

```sql
-- Get appointment statistics for May 2026
CALL sp_get_appointment_analytics('2026-05-01', '2026-05-31');

-- Expected output:
-- pending_count | in_progress_count | completed_count | total_appointments | earliest_appointment | latest_appointment
-- Should show counts for each status
```

### Test 2: Monthly Revenue Report

```sql
-- Get May 2026 revenue
CALL sp_get_monthly_revenue(2026, 5);

-- Expected output:
-- month_name | year | total_payments | paid_revenue | pending_revenue | total_revenue | completed_appointments
-- Shows financial summary for the month
```

### Test 3: Revenue by Service

```sql
-- Get service breakdown for May 2026
CALL sp_get_revenue_by_service('2026-05-01', '2026-05-31');

-- Expected output:
-- service_name | pricing_type | total_usage | appointment_count | total_revenue | average_revenue_per_service
-- Shows which services generate most revenue
```

### Test 4: Employee Performance

```sql
-- Get employee metrics for May 2026
CALL sp_get_employee_performance('2026-05-01', '2026-05-31');

-- Expected output:
-- id | name | position | status | total_assignments | completed_appointments | in_progress_appointments | completion_rate_percent | total_earnings | paid_appointments | employee_since
-- Shows each employee's performance
```

### Test 5: Overdue Appointments

```sql
-- Find appointments overdue by 7+ days
CALL sp_get_overdue_appointments(7);

-- Expected output:
-- id | customer_name | address | schedule_date | status | days_overdue | assigned_employees | services_assigned | outstanding_payment | payment_status
-- Lists appointments that need attention
```

### Test 6: Bulk Update Status

```sql
-- Test with existing appointment ID (adjust ID as needed)
CALL sp_bulk_update_appointment_status('1,2,3', 'In Progress');

-- Expected output:
-- total_appointments | successfully_updated | failed_to_update | new_status | operation_timestamp
-- Shows operation results
```

### Test 7: Get Appointment Details

```sql
-- Get complete appointment info (adjust ID as needed)
CALL sp_get_appointment_with_details(1);

-- Expected output:
-- appointment_id | customer_name | address | area_sqm | schedule_date | status | notes | services (JSON) | employees (JSON) | payment_info (JSON)
-- Shows all appointment details in structured format
```

### Test 8: Employee Availability Status

```sql
-- Check employee 1 availability for today
CALL sp_get_employee_availability_status(1, CURDATE());

-- Expected output:
-- employee_id | employee_name | position | status | busy_slots | available_slots | next_busy_from | next_busy_to | availability_status
-- Shows if employee is available for scheduling
```

---

## 🔧 CALLING PROCEDURES FROM LARAVEL

### Method 1: Direct DB Call in Controller

```php
<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function getAnalytics()
    {
        $results = DB::select('CALL sp_get_appointment_analytics(?, ?)', [
            '2026-05-01',
            '2026-05-31'
        ]);

        return view('reports.analytics', ['data' => $results[0]]);
    }

    public function getRevenue()
    {
        $results = DB::select('CALL sp_get_monthly_revenue(?, ?)', [
            2026,  // year
            5      // month
        ]);

        return response()->json($results);
    }
}
```

### Method 2: Using a Query Builder

```php
$analytics = DB::statement('CALL sp_get_appointment_analytics(?, ?)', [
    '2026-05-01',
    '2026-05-31'
]);
```

### Method 3: Create a Model Method

```php
<?php
namespace App\Models;

use Illuminate\Support\Facades\DB;

class Report extends Model
{
    public static function appointmentAnalytics($startDate, $endDate)
    {
        return DB::select('CALL sp_get_appointment_analytics(?, ?)', [
            $startDate,
            $endDate
        ]);
    }

    public static function monthlyRevenue($year, $month)
    {
        return DB::select('CALL sp_get_monthly_revenue(?, ?)', [
            $year,
            $month
        ]);
    }
}
```

### Method 4: Route Example

```php
// routes/web.php
Route::get('/reports/analytics', function () {
    $data = DB::select('CALL sp_get_appointment_analytics(?, ?)', [
        '2026-05-01',
        '2026-05-31'
    ]);
    return response()->json($data);
});
```

---

## ⚠️ SAFETY CHECKS

### Before Executing

- [ ] Database backup created
- [ ] Using correct database (hk_db)
- [ ] No active transactions running
- [ ] All existing triggers verified (SHOW PROCEDURE STATUS)
- [ ] PHP/Laravel is not making simultaneous calls

### After Executing

```sql
-- Verify all procedures exist
SHOW PROCEDURE STATUS WHERE Db = 'hk_db';

-- Verify procedure code (check for errors)
SHOW CREATE PROCEDURE sp_get_appointment_analytics;

-- Test simple procedure call
CALL sp_get_appointment_analytics('2026-05-01', '2026-05-31');
```

### Performance Considerations

- All procedures have READS/MODIFIES SQL DATA declarations
- Optimize with indexes if dealing with large datasets:

```sql
-- Add indexes for performance (if needed)
ALTER TABLE appointments ADD INDEX idx_status (status);
ALTER TABLE payments ADD INDEX idx_payment_status (payment_status);
ALTER TABLE employees ADD INDEX idx_emp_status (status);
```

---

## 🔄 ROLLBACK / CLEANUP

### If You Need to Remove Procedures

```sql
-- Drop individual procedures
DROP PROCEDURE IF EXISTS sp_get_appointment_analytics;
DROP PROCEDURE IF EXISTS sp_get_monthly_revenue;
DROP PROCEDURE IF EXISTS sp_get_revenue_by_service;
DROP PROCEDURE IF EXISTS sp_get_employee_performance;
DROP PROCEDURE IF EXISTS sp_get_overdue_appointments;
DROP PROCEDURE IF EXISTS sp_bulk_update_appointment_status;
DROP PROCEDURE IF EXISTS sp_get_appointment_with_details;
DROP PROCEDURE IF EXISTS sp_get_employee_availability_status;
DROP PROCEDURE IF EXISTS sp_archive_old_appointments;

-- Verify all removed
SHOW PROCEDURE STATUS WHERE Db = 'hk_db';
-- Should show 0 rows (no procedures)
```

### Restore from Backup

```bash
# In Command Prompt (if something goes wrong)
mysql -u root -p hk_db < backup_hk_db_2026_05_24.sql
```

---

## 📊 USAGE EXAMPLES

### Example 1: Dashboard Widget

```php
// Get today's appointment status
$today = now()->format('Y-m-d');
$analytics = DB::select('CALL sp_get_appointment_analytics(?, ?)', [
    $today,
    $today
])[0];

echo "Total: " . $analytics->total_appointments;
echo "Pending: " . $analytics->pending_count;
echo "In Progress: " . $analytics->in_progress_count;
echo "Completed: " . $analytics->completed_count;
```

### Example 2: Monthly Financial Report

```php
// Generate monthly P&L
$currentMonth = now()->month;
$currentYear = now()->year;
$revenue = DB::select('CALL sp_get_monthly_revenue(?, ?)', [
    $currentYear,
    $currentMonth
])[0];

echo "Revenue (Paid): ₱" . $revenue->paid_revenue;
echo "Pending: ₱" . $revenue->pending_revenue;
echo "Total Revenue: ₱" . $revenue->total_revenue;
```

### Example 3: Employee Performance Tracking

```php
// Generate payroll report
$startDate = now()->startOfMonth()->format('Y-m-d');
$endDate = now()->endOfMonth()->format('Y-m-d');

$employees = DB::select('CALL sp_get_employee_performance(?, ?)', [
    $startDate,
    $endDate
]);

foreach ($employees as $emp) {
    echo $emp->name . ": " . $emp->completion_rate_percent . "% complete";
}
```

### Example 4: Alert System

```php
// Find overdue appointments (older than 3 days)
$overdue = DB::select('CALL sp_get_overdue_appointments(?)', [3]);

if (!empty($overdue)) {
    // Send notification to admin
    foreach ($overdue as $appt) {
        Log::warning("Appointment #{$appt->id} is {$appt->days_overdue} days overdue");
    }
}
```

---

## 🧪 VERIFICATION CHECKLIST

After implementation, verify:

- [ ] All 9 procedures exist: `SHOW PROCEDURE STATUS WHERE Db = 'hk_db';`
- [ ] Each procedure returns expected columns
- [ ] Existing triggers still work (create new appointment, check triggers fired)
- [ ] Existing views still accessible: `SELECT * FROM vw_appointment_details;`
- [ ] Laravel queries still work
- [ ] No permissions errors when calling procedures
- [ ] Procedures respect existing data constraints

---

## 📞 SUPPORT & TROUBLESHOOTING

### Issue: "Access Denied for User"

**Solution:** Make sure you're logged in as root or admin user with CREATE ROUTINE privilege

```sql
GRANT CREATE ROUTINE ON hk_db.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
```

### Issue: "Procedure Already Exists"

**Solution:** The DROP IF EXISTS should handle this, but if error persists:

```sql
DROP PROCEDURE IF EXISTS sp_get_appointment_analytics;
```

### Issue: "Syntax Error in SQL Statement"

**Solution:**

- Copy exact SQL from create_stored_procedures.sql file
- Ensure DELIMITER is set correctly
- Check file encoding (should be UTF-8)

### Issue: "Can't Find Function"

**Solution:** Procedure might not be created. Run verification:

```sql
SHOW PROCEDURE STATUS WHERE Db = 'hk_db' AND Name = 'sp_get_appointment_analytics';
```

---

## ✨ BENEFITS OF STORED PROCEDURES

1. **Performance**: Single database call replaces multiple queries
2. **Security**: Encapsulates logic, reduces SQL injection risk
3. **Maintainability**: Business logic centralized in database
4. **Reusability**: Called from anywhere (PHP, Laravel, scripts)
5. **Consistency**: Same results every time
6. **Scalability**: Optimized by database engine

---

## 🎓 NEXT STEPS

1. ✅ Create backup
2. ✅ Execute SQL file
3. ✅ Run verification tests
4. ✅ Call from Laravel controllers
5. ✅ Add to API endpoints
6. ✅ Create reports/dashboards
7. ✅ Monitor performance

---

**Status:** Ready for Production  
**Last Updated:** May 24, 2026  
**Next Review:** After 2 weeks of production use
