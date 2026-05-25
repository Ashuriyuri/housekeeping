<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$db = app('db');

echo "╔════════════════════════════════════════════════════╗\n";
echo "║         CREATING ALL TRIGGERS IN PHPMYADMIN        ║\n";
echo "╚════════════════════════════════════════════════════╝\n\n";

// Array of all triggers
$triggers = [
    [
        'name' => 'trg_auto_create_payment',
        'table' => 'appointments',
        'time' => 'AFTER',
        'event' => 'UPDATE',
        'body' => "
            IF NEW.status = 'Completed' AND OLD.status != 'Completed' THEN
                IF NOT EXISTS (SELECT 1 FROM payments WHERE appointment_id = NEW.id) THEN
                    INSERT INTO payments (appointment_id, amount, payment_status, created_at, updated_at)
                    SELECT NEW.id,
                        COALESCE(SUM(CASE WHEN s.pricing_type = 'fixed' THEN aps.quantity * COALESCE(aps.custom_price, s.base_price) WHEN s.pricing_type = 'per_sqm' THEN COALESCE(aps.custom_price, s.base_price) * aps.quantity ELSE 0 END), 0),
                        'Pending', NOW(), NOW()
                    FROM appointment_service aps
                    JOIN services s ON aps.service_id = s.id
                    WHERE aps.appointment_id = NEW.id;
                END IF;
            END IF;
        "
    ],
    [
        'name' => 'trg_prevent_inactive_assignment',
        'table' => 'appointment_employee',
        'time' => 'BEFORE',
        'event' => 'INSERT',
        'body' => "
            DECLARE employee_status VARCHAR(20);
            SELECT status INTO employee_status FROM employees WHERE id = NEW.employee_id;
            IF employee_status = 'Inactive' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot assign Inactive employee to appointment';
            END IF;
        "
    ],
    [
        'name' => 'trg_update_employee_availability',
        'table' => 'appointment_employee',
        'time' => 'AFTER',
        'event' => 'INSERT',
        'body' => "
            INSERT INTO employee_availability (employee_id, appointment_id, available_from, available_to, is_available, reason, created_at, updated_at)
            VALUES (NEW.employee_id, NEW.appointment_id, 
                COALESCE(NEW.start_time, (SELECT schedule_date FROM appointments WHERE id = NEW.appointment_id)),
                COALESCE(NEW.end_time, DATE_ADD((SELECT schedule_date FROM appointments WHERE id = NEW.appointment_id), INTERVAL 2 HOUR)),
                0, CONCAT('Appointment #', NEW.appointment_id), NOW(), NOW())
            ON DUPLICATE KEY UPDATE updated_at = NOW(), reason = CONCAT('Appointment #', NEW.appointment_id);
        "
    ],
    [
        'name' => 'trg_prevent_delete_paid_appointment',
        'table' => 'appointments',
        'time' => 'BEFORE',
        'event' => 'DELETE',
        'body' => "
            IF EXISTS (SELECT 1 FROM payments WHERE appointment_id = OLD.id AND payment_status = 'Paid') THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot delete appointment with paid payment';
            END IF;
        "
    ],
    [
        'name' => 'trg_auto_update_appointment_status',
        'table' => 'appointment_employee',
        'time' => 'AFTER',
        'event' => 'INSERT',
        'body' => "
            DECLARE total_employees INT;
            SELECT COUNT(*) INTO total_employees FROM appointment_employee WHERE appointment_id = NEW.appointment_id;
            IF total_employees > 0 THEN
                UPDATE appointments SET status = 'In Progress' WHERE id = NEW.appointment_id AND status = 'Pending';
            END IF;
        "
    ],
    [
        'name' => 'trg_prevent_status_downgrade',
        'table' => 'appointments',
        'time' => 'BEFORE',
        'event' => 'UPDATE',
        'body' => "
            IF NEW.status = 'Pending' AND OLD.status IN ('In Progress', 'Completed') THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot downgrade appointment status';
            END IF;
        "
    ],
    [
        'name' => 'trg_validate_service_pricing',
        'table' => 'services',
        'time' => 'BEFORE',
        'event' => 'INSERT',
        'body' => "
            IF NEW.base_price < 0 THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Service price cannot be negative';
            END IF;
            IF NEW.pricing_type NOT IN ('fixed', 'per_sqm') THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid pricing type';
            END IF;
        "
    ],
    [
        'name' => 'trg_validate_service_update',
        'table' => 'services',
        'time' => 'BEFORE',
        'event' => 'UPDATE',
        'body' => "
            IF NEW.base_price < 0 THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Service price cannot be negative';
            END IF;
            IF NEW.pricing_type NOT IN ('fixed', 'per_sqm') THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid pricing type';
            END IF;
        "
    ],
    [
        'name' => 'trg_prevent_delete_active_service',
        'table' => 'services',
        'time' => 'BEFORE',
        'event' => 'DELETE',
        'body' => "
            IF OLD.status = 'Active' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot delete Active service';
            END IF;
        "
    ],
    [
        'name' => 'trg_prevent_delete_active_employee',
        'table' => 'employees',
        'time' => 'BEFORE',
        'event' => 'DELETE',
        'body' => "
            IF OLD.status = 'Active' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot delete Active employee';
            END IF;
        "
    ],
    [
        'name' => 'trg_track_employee_status_change',
        'table' => 'employees',
        'time' => 'AFTER',
        'event' => 'UPDATE',
        'body' => "
            IF NEW.status != OLD.status THEN
                UPDATE employee_availability SET is_available = 0, reason = CONCAT('Status changed to: ', NEW.status) WHERE employee_id = NEW.id AND is_available = 1;
            END IF;
        "
    ],
    [
        'name' => 'trg_validate_payment_method',
        'table' => 'payments',
        'time' => 'BEFORE',
        'event' => 'INSERT',
        'body' => "
            IF NEW.payment_method IS NOT NULL AND NEW.payment_method NOT IN ('Cash', 'GCash', 'Bank Transfer', 'Credit Card', 'Cheque') THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid payment method';
            END IF;
        "
    ],
    [
        'name' => 'trg_prevent_delete_completed_payment',
        'table' => 'payments',
        'time' => 'BEFORE',
        'event' => 'DELETE',
        'body' => "
            IF OLD.payment_status = 'Paid' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot delete completed payment';
            END IF;
        "
    ],
    [
        'name' => 'trg_validate_payment_amount',
        'table' => 'payments',
        'time' => 'BEFORE',
        'event' => 'INSERT',
        'body' => "
            IF NEW.amount <= 0 THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Payment amount must be greater than 0';
            END IF;
        "
    ],
    [
        'name' => 'trg_validate_appointment_service',
        'table' => 'appointment_service',
        'time' => 'BEFORE',
        'event' => 'INSERT',
        'body' => "
            IF NEW.quantity <= 0 THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Service quantity must be greater than 0';
            END IF;
        "
    ],
    [
        'name' => 'trg_prevent_service_removal_completed',
        'table' => 'appointment_service',
        'time' => 'BEFORE',
        'event' => 'DELETE',
        'body' => "
            DECLARE appt_status VARCHAR(20);
            SELECT status INTO appt_status FROM appointments WHERE id = OLD.appointment_id;
            IF appt_status = 'Completed' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot remove services from completed appointment';
            END IF;
        "
    ],
    [
        'name' => 'trg_prevent_employee_removal_active',
        'table' => 'appointment_employee',
        'time' => 'BEFORE',
        'event' => 'DELETE',
        'body' => "
            DECLARE appt_status VARCHAR(20);
            SELECT status INTO appt_status FROM appointments WHERE id = OLD.appointment_id;
            IF appt_status IN ('In Progress', 'Completed') THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot remove employees from active/completed appointment';
            END IF;
        "
    ],
    [
        'name' => 'trg_clean_availability_on_employee_removal',
        'table' => 'appointment_employee',
        'time' => 'AFTER',
        'event' => 'DELETE',
        'body' => "
            UPDATE employee_availability SET is_available = 1, appointment_id = NULL WHERE appointment_id = OLD.appointment_id AND employee_id = OLD.employee_id;
        "
    ],
    [
        'name' => 'trg_update_availability_on_completion',
        'table' => 'appointments',
        'time' => 'AFTER',
        'event' => 'UPDATE',
        'body' => "
            IF NEW.status = 'Completed' AND OLD.status != 'Completed' THEN
                UPDATE employee_availability SET is_available = 1, appointment_id = NULL, reason = 'Appointment completed' WHERE appointment_id = NEW.id;
            END IF;
        "
    ],
    [
        'name' => 'trg_validate_availability_dates',
        'table' => 'employee_availability',
        'time' => 'BEFORE',
        'event' => 'INSERT',
        'body' => "
            IF NEW.available_to <= NEW.available_from THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Availability end date must be after start date';
            END IF;
        "
    ],
    [
        'name' => 'trg_prevent_availability_overlap',
        'table' => 'employee_availability',
        'time' => 'BEFORE',
        'event' => 'INSERT',
        'body' => "
            IF EXISTS (SELECT 1 FROM employee_availability WHERE employee_id = NEW.employee_id AND appointment_id IS NOT NULL AND (NEW.available_from < available_to AND NEW.available_to > available_from)) THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Employee availability overlaps with existing appointment';
            END IF;
        "
    ]
];

$loaded = 0;
$failed = 0;

foreach ($triggers as $trigger) {
    $name = $trigger['name'];
    $table = $trigger['table'];
    $time = $trigger['time'];
    $event = $trigger['event'];
    $body = $trigger['body'];
    
    try {
        $sql = "DROP TRIGGER IF EXISTS `$name`;
        CREATE TRIGGER `$name` $time $event ON `$table`
        FOR EACH ROW
        BEGIN
            $body
        END;";
        
        $db->statement($sql);
        echo "  ✓ $name\n";
        $loaded++;
    } catch (\Exception $e) {
        echo "  ✗ $name: " . substr($e->getMessage(), 0, 50) . "\n";
        $failed++;
    }
}

echo "\n╔════════════════════════════════════════════════════╗\n";
echo "║              SUMMARY                              ║\n";
echo "╠════════════════════════════════════════════════════╣\n";
echo "║  ✓ Successfully created: $loaded triggers         ║\n";
echo "║  ✗ Failed: $failed triggers                        ║\n";
echo "║                                                    ║\n";
echo "║  Now visible in phpMyAdmin!                        ║\n";
echo "║  Go to: hk_db > Triggers tab                      ║\n";
echo "╚════════════════════════════════════════════════════╝\n";
