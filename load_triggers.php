<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$db = app('db');

echo "╔═════════════════════════════════════════════════════════╗\n";
echo "║        LOADING ALL 25 TRIGGERS INTO phpMyADMIN         ║\n";
echo "╚═════════════════════════════════════════════════════════╝\n\n";

$triggers = [
    [
        'name' => 'trg_prevent_inactive_assignment',
        'sql' => "DROP TRIGGER IF EXISTS `trg_prevent_inactive_assignment`; CREATE TRIGGER `trg_prevent_inactive_assignment` BEFORE INSERT ON `appointment_employee` FOR EACH ROW BEGIN DECLARE emp_status VARCHAR(20); SELECT status INTO emp_status FROM employees WHERE id = NEW.employee_id; IF emp_status = 'Inactive' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot assign inactive employee'; END IF; END"
    ],
    [
        'name' => 'trg_update_employee_availability',
        'sql' => "DROP TRIGGER IF EXISTS `trg_update_employee_availability`; CREATE TRIGGER `trg_update_employee_availability` AFTER INSERT ON `appointment_employee` FOR EACH ROW BEGIN INSERT INTO employee_availability (employee_id, appointment_id, available_from, available_to, is_available, reason, created_at, updated_at) VALUES (NEW.employee_id, NEW.appointment_id, NOW(), DATE_ADD(NOW(), INTERVAL 2 HOUR), 0, 'Assignment', NOW(), NOW()); END"
    ],
    [
        'name' => 'trg_prevent_delete_paid_appointment',
        'sql' => "DROP TRIGGER IF EXISTS `trg_prevent_delete_paid_appointment`; CREATE TRIGGER `trg_prevent_delete_paid_appointment` BEFORE DELETE ON `appointments` FOR EACH ROW BEGIN IF EXISTS (SELECT 1 FROM payments WHERE appointment_id = OLD.id AND payment_status = 'Paid') THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot delete paid appointment'; END IF; END"
    ],
    [
        'name' => 'trg_auto_update_appointment_status',
        'sql' => "DROP TRIGGER IF EXISTS `trg_auto_update_appointment_status`; CREATE TRIGGER `trg_auto_update_appointment_status` AFTER INSERT ON `appointment_employee` FOR EACH ROW BEGIN UPDATE appointments SET status = 'In Progress' WHERE id = NEW.appointment_id AND status = 'Pending'; END"
    ],
    [
        'name' => 'trg_prevent_status_downgrade',
        'sql' => "DROP TRIGGER IF EXISTS `trg_prevent_status_downgrade`; CREATE TRIGGER `trg_prevent_status_downgrade` BEFORE UPDATE ON `appointments` FOR EACH ROW BEGIN IF (NEW.status = 'Pending' AND OLD.status IN ('In Progress', 'Completed')) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot downgrade appointment status'; END IF; END"
    ],
    [
        'name' => 'trg_validate_service_pricing',
        'sql' => "DROP TRIGGER IF EXISTS `trg_validate_service_pricing`; CREATE TRIGGER `trg_validate_service_pricing` BEFORE INSERT ON `services` FOR EACH ROW BEGIN IF NEW.base_price < 0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Service price cannot be negative'; END IF; END"
    ],
    [
        'name' => 'trg_validate_service_update',
        'sql' => "DROP TRIGGER IF EXISTS `trg_validate_service_update`; CREATE TRIGGER `trg_validate_service_update` BEFORE UPDATE ON `services` FOR EACH ROW BEGIN IF NEW.base_price < 0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Service price cannot be negative'; END IF; END"
    ],
    [
        'name' => 'trg_prevent_delete_active_service',
        'sql' => "DROP TRIGGER IF EXISTS `trg_prevent_delete_active_service`; CREATE TRIGGER `trg_prevent_delete_active_service` BEFORE DELETE ON `services` FOR EACH ROW BEGIN IF EXISTS (SELECT 1 FROM appointment_service WHERE service_id = OLD.id) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot delete service that is in use'; END IF; END"
    ],
    [
        'name' => 'trg_prevent_delete_active_employee',
        'sql' => "DROP TRIGGER IF EXISTS `trg_prevent_delete_active_employee`; CREATE TRIGGER `trg_prevent_delete_active_employee` BEFORE DELETE ON `employees` FOR EACH ROW BEGIN IF OLD.status = 'Active' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot delete active employee'; END IF; END"
    ],
    [
        'name' => 'trg_track_employee_status_change',
        'sql' => "DROP TRIGGER IF EXISTS `trg_track_employee_status_change`; CREATE TRIGGER `trg_track_employee_status_change` AFTER UPDATE ON `employees` FOR EACH ROW BEGIN IF NEW.status != OLD.status THEN UPDATE employee_availability SET is_available = 0 WHERE employee_id = NEW.id; END IF; END"
    ],
    [
        'name' => 'trg_auto_create_employee_availability',
        'sql' => "DROP TRIGGER IF EXISTS `trg_auto_create_employee_availability`; CREATE TRIGGER `trg_auto_create_employee_availability` AFTER INSERT ON `employees` FOR EACH ROW BEGIN INSERT INTO employee_availability (employee_id, is_available, created_at, updated_at) VALUES (NEW.id, 1, NOW(), NOW()); END"
    ],
    [
        'name' => 'trg_validate_payment_method',
        'sql' => "DROP TRIGGER IF EXISTS `trg_validate_payment_method`; CREATE TRIGGER `trg_validate_payment_method` BEFORE INSERT ON `payments` FOR EACH ROW BEGIN IF NEW.payment_method IS NOT NULL AND NEW.payment_method NOT IN ('Cash', 'GCash', 'Bank Transfer', 'Credit Card', 'Cheque') THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid payment method'; END IF; END"
    ],
    [
        'name' => 'trg_update_payment_status',
        'sql' => "DROP TRIGGER IF EXISTS `trg_update_payment_status`; CREATE TRIGGER `trg_update_payment_status` AFTER UPDATE ON `payments` FOR EACH ROW BEGIN IF NEW.payment_status = 'Paid' AND OLD.payment_status != 'Paid' THEN UPDATE appointments SET status = 'Completed' WHERE id = NEW.appointment_id; END IF; END"
    ],
    [
        'name' => 'trg_prevent_delete_completed_payment',
        'sql' => "DROP TRIGGER IF EXISTS `trg_prevent_delete_completed_payment`; CREATE TRIGGER `trg_prevent_delete_completed_payment` BEFORE DELETE ON `payments` FOR EACH ROW BEGIN IF OLD.payment_status = 'Paid' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot delete paid payment'; END IF; END"
    ],
    [
        'name' => 'trg_validate_payment_amount',
        'sql' => "DROP TRIGGER IF EXISTS `trg_validate_payment_amount`; CREATE TRIGGER `trg_validate_payment_amount` BEFORE INSERT ON `payments` FOR EACH ROW BEGIN IF NEW.amount <= 0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Payment amount must be positive'; END IF; END"
    ],
    [
        'name' => 'trg_validate_appointment_service',
        'sql' => "DROP TRIGGER IF EXISTS `trg_validate_appointment_service`; CREATE TRIGGER `trg_validate_appointment_service` BEFORE INSERT ON `appointment_service` FOR EACH ROW BEGIN IF NEW.quantity <= 0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Service quantity must be positive'; END IF; END"
    ],
    [
        'name' => 'trg_prevent_service_removal_completed',
        'sql' => "DROP TRIGGER IF EXISTS `trg_prevent_service_removal_completed`; CREATE TRIGGER `trg_prevent_service_removal_completed` BEFORE DELETE ON `appointment_service` FOR EACH ROW BEGIN DECLARE appt_status VARCHAR(20); SELECT status INTO appt_status FROM appointments WHERE id = OLD.appointment_id; IF appt_status = 'Completed' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot remove services from completed appointment'; END IF; END"
    ],
    [
        'name' => 'trg_recalculate_payment_on_service_update',
        'sql' => "DROP TRIGGER IF EXISTS `trg_recalculate_payment_on_service_update`; CREATE TRIGGER `trg_recalculate_payment_on_service_update` AFTER UPDATE ON `appointment_service` FOR EACH ROW BEGIN UPDATE payments SET updated_at = NOW() WHERE appointment_id = NEW.appointment_id; END"
    ],
    [
        'name' => 'trg_prevent_employee_removal_active',
        'sql' => "DROP TRIGGER IF EXISTS `trg_prevent_employee_removal_active`; CREATE TRIGGER `trg_prevent_employee_removal_active` BEFORE DELETE ON `appointment_employee` FOR EACH ROW BEGIN DECLARE appt_status VARCHAR(20); SELECT status INTO appt_status FROM appointments WHERE id = OLD.appointment_id; IF appt_status IN ('In Progress', 'Completed') THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot remove employees from active appointment'; END IF; END"
    ],
    [
        'name' => 'trg_clean_availability_on_employee_removal',
        'sql' => "DROP TRIGGER IF EXISTS `trg_clean_availability_on_employee_removal`; CREATE TRIGGER `trg_clean_availability_on_employee_removal` AFTER DELETE ON `appointment_employee` FOR EACH ROW BEGIN UPDATE employee_availability SET is_available = 1, appointment_id = NULL WHERE appointment_id = OLD.appointment_id; END"
    ],
    [
        'name' => 'trg_update_availability_on_completion',
        'sql' => "DROP TRIGGER IF EXISTS `trg_update_availability_on_completion`; CREATE TRIGGER `trg_update_availability_on_completion` AFTER UPDATE ON `appointments` FOR EACH ROW BEGIN IF NEW.status = 'Completed' AND OLD.status != 'Completed' THEN UPDATE employee_availability SET is_available = 1, appointment_id = NULL WHERE appointment_id = NEW.id; END IF; END"
    ],
    [
        'name' => 'trg_validate_availability_dates',
        'sql' => "DROP TRIGGER IF EXISTS `trg_validate_availability_dates`; CREATE TRIGGER `trg_validate_availability_dates` BEFORE INSERT ON `employee_availability` FOR EACH ROW BEGIN IF NEW.available_to <= NEW.available_from THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'End date must be after start date'; END IF; END"
    ],
    [
        'name' => 'trg_prevent_availability_overlap',
        'sql' => "DROP TRIGGER IF EXISTS `trg_prevent_availability_overlap`; CREATE TRIGGER `trg_prevent_availability_overlap` BEFORE INSERT ON `employee_availability` FOR EACH ROW BEGIN IF EXISTS (SELECT 1 FROM employee_availability WHERE employee_id = NEW.employee_id AND appointment_id IS NOT NULL AND (NEW.available_from < available_to AND NEW.available_to > available_from)) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Availability overlaps with existing appointment'; END IF; END"
    ],
    [
        'name' => 'trg_log_appointment_creation',
        'sql' => "DROP TRIGGER IF EXISTS `trg_log_appointment_creation`; CREATE TRIGGER `trg_log_appointment_creation` AFTER INSERT ON `appointments` FOR EACH ROW BEGIN INSERT INTO employee_availability (employee_id, appointment_id, is_available, reason, created_at, updated_at) VALUES (NULL, NEW.id, 0, CONCAT('New appointment created: ', NEW.customer_name), NOW(), NOW()); END"
    ]
];

$loaded = 0;
$failed = 0;
$errors = [];

foreach ($triggers as $t) {
    try {
        // First DROP the trigger if it exists
        $dropSql = "DROP TRIGGER IF EXISTS `{$t['name']}`";
        $db->statement($dropSql);
        
        // Then CREATE it with a proper statement
        $createSql = substr($t['sql'], strpos($t['sql'], 'CREATE TRIGGER'));
        $db->statement($createSql);
        
        echo "  ✓ {$t['name']}\n";
        $loaded++;
    } catch (\Exception $e) {
        echo "  ✗ {$t['name']}\n";
        $failed++;
        $errors[] = $t['name'] . ': ' . substr($e->getMessage(), 0, 80);
    }
}

echo "\n╔═════════════════════════════════════════════════════════╗\n";
echo "║                  CREATION COMPLETE                      ║\n";
echo "╠═════════════════════════════════════════════════════════╣\n";
printf("║  ✓ Successfully created: %-30s ║\n", "$loaded / " . count($triggers));
printf("║  ✗ Failed: %-48s ║\n", $failed);
echo "╚═════════════════════════════════════════════════════════╝\n\n";

// Verify triggers in database
$result = $db->select("SELECT TRIGGER_NAME, TRIGGER_SCHEMA, EVENT_OBJECT_TABLE, EVENT_MANIPULATION FROM INFORMATION_SCHEMA.TRIGGERS WHERE TRIGGER_SCHEMA = 'hk_db' ORDER BY TRIGGER_NAME");

echo "TRIGGERS NOW IN phpMyADMIN: " . count($result) . "\n\n";
echo "Complete list:\n";
foreach ($result as $r) {
    echo "  • {$r->TRIGGER_NAME} ({$r->EVENT_MANIPULATION} on {$r->EVENT_OBJECT_TABLE})\n";
}

if ($failed > 0) {
    echo "\n\nErrors encountered:\n";
    foreach ($errors as $err) {
        echo "  - $err\n";
    }
}

echo "\n✓ Check phpMyAdmin at: http://localhost/phpmyadmin > hk_db > Triggers\n";
