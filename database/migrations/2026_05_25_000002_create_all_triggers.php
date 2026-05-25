<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Array of all 25 triggers
        $triggers = [
            // 1. Auto-create payment when appointment is completed
            "DROP TRIGGER IF EXISTS trg_auto_create_payment",
            "CREATE TRIGGER trg_auto_create_payment
            AFTER UPDATE ON appointments
            FOR EACH ROW
            BEGIN
              IF NEW.status = 'Completed' AND OLD.status != 'Completed' THEN
                IF NOT EXISTS (SELECT 1 FROM payments WHERE appointment_id = NEW.id) THEN
                  INSERT INTO payments (appointment_id, amount, payment_status, created_at, updated_at)
                  SELECT 
                    NEW.id,
                    COALESCE(SUM(
                      CASE 
                        WHEN s.pricing_type = 'fixed' 
                        THEN COALESCE(aps.custom_price, s.base_price) * COALESCE(aps.quantity, 1)
                        WHEN s.pricing_type = 'per_sqm'
                        THEN COALESCE(aps.custom_price, s.base_price) * COALESCE(NEW.area_sqm, aps.quantity, 1)
                        ELSE 0
                      END
                    ), 0),
                    'Pending',
                    NOW(),
                    NOW()
                  FROM appointment_service aps
                  JOIN services s ON aps.service_id = s.id
                  WHERE aps.appointment_id = NEW.id;
                END IF;
              END IF;
            END",

            // 2. Log appointment creation
            "DROP TRIGGER IF EXISTS trg_log_appointment_creation",
            "CREATE TRIGGER trg_log_appointment_creation
            AFTER INSERT ON appointments
            FOR EACH ROW
            BEGIN
              INSERT INTO employee_availability (employee_id, appointment_id, is_available, reason, created_at, updated_at)
              VALUES (NULL, NEW.id, 0, CONCAT('New appointment created: ', NEW.customer_name), NOW(), NOW());
            END",

            // 3. Prevent deleting paid appointments
            "DROP TRIGGER IF EXISTS trg_prevent_delete_paid_appointment",
            "CREATE TRIGGER trg_prevent_delete_paid_appointment
            BEFORE DELETE ON appointments
            FOR EACH ROW
            BEGIN
              IF EXISTS (SELECT 1 FROM payments WHERE appointment_id = OLD.id AND payment_status = 'Paid') THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot delete paid appointment';
              END IF;
            END",

            // 4. Prevent status downgrade
            "DROP TRIGGER IF EXISTS trg_prevent_status_downgrade",
            "CREATE TRIGGER trg_prevent_status_downgrade
            BEFORE UPDATE ON appointments
            FOR EACH ROW
            BEGIN
              IF (NEW.status = 'Pending' AND OLD.status IN ('In Progress', 'Completed')) THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot downgrade appointment status';
              END IF;
            END",

            // 5. Update employee availability on completion
            "DROP TRIGGER IF EXISTS trg_update_availability_on_completion",
            "CREATE TRIGGER trg_update_availability_on_completion
            AFTER UPDATE ON appointments
            FOR EACH ROW
            BEGIN
              IF NEW.status = 'Completed' AND OLD.status != 'Completed' THEN
                UPDATE employee_availability SET is_available = 1, appointment_id = NULL
                WHERE appointment_id = NEW.id;
              END IF;
            END",

            // 6. Validate appointment has services before completion
            "DROP TRIGGER IF EXISTS trg_validate_appointment_services",
            "CREATE TRIGGER trg_validate_appointment_services
            BEFORE UPDATE ON appointments
            FOR EACH ROW
            BEGIN
              IF NEW.status = 'Completed' AND OLD.status != 'Completed' THEN
                IF NOT EXISTS (SELECT 1 FROM appointment_service WHERE appointment_id = NEW.id) THEN
                  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot complete appointment without services';
                END IF;
              END IF;
            END",

            // 7. Prevent status downgrade on payment
            "DROP TRIGGER IF EXISTS trg_prevent_payment_status_downgrade",
            "CREATE TRIGGER trg_prevent_payment_status_downgrade
            BEFORE UPDATE ON payments
            FOR EACH ROW
            BEGIN
              IF NEW.payment_status = 'Pending' AND OLD.payment_status = 'Paid' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot change paid payment to pending';
              END IF;
            END",

            // 8. Update appointment status when payment is fully paid
            "DROP TRIGGER IF EXISTS trg_update_appointment_on_payment",
            "CREATE TRIGGER trg_update_appointment_on_payment
            AFTER UPDATE ON payments
            FOR EACH ROW
            BEGIN
              IF NEW.payment_status = 'Paid' AND OLD.payment_status != 'Paid' THEN
                UPDATE appointments SET status = 'Completed'
                WHERE id = NEW.appointment_id AND status = 'In Progress';
              END IF;
            END",

            // 9. Prevent deleting services in use
            "DROP TRIGGER IF EXISTS trg_prevent_delete_service_in_use",
            "CREATE TRIGGER trg_prevent_delete_service_in_use
            BEFORE DELETE ON services
            FOR EACH ROW
            BEGIN
              IF EXISTS (SELECT 1 FROM appointment_service WHERE service_id = OLD.id) THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot delete service in use';
              END IF;
            END",

            // 10. Validate service pricing
            "DROP TRIGGER IF EXISTS trg_validate_service_pricing",
            "CREATE TRIGGER trg_validate_service_pricing
            BEFORE INSERT ON services
            FOR EACH ROW
            BEGIN
              IF NEW.base_price < 0 THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Service price cannot be negative';
              END IF;
            END",

            // 11. Prevent employee deletion if assigned to active appointments
            "DROP TRIGGER IF EXISTS trg_prevent_delete_active_employee",
            "CREATE TRIGGER trg_prevent_delete_active_employee
            BEFORE DELETE ON employees
            FOR EACH ROW
            BEGIN
              IF EXISTS (
                SELECT 1 FROM appointment_employee ae
                JOIN appointments a ON ae.appointment_id = a.id
                WHERE ae.employee_id = OLD.id AND a.status IN ('Pending', 'In Progress')
              ) THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot delete employee assigned to active appointments';
              END IF;
            END",

            // 12. Validate employee phone number format
            "DROP TRIGGER IF EXISTS trg_validate_employee_phone",
            "CREATE TRIGGER trg_validate_employee_phone
            BEFORE INSERT ON employees
            FOR EACH ROW
            BEGIN
              IF NEW.phone IS NOT NULL AND (LENGTH(NEW.phone) < 10 OR LENGTH(NEW.phone) > 15) THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid phone number format';
              END IF;
            END",

            // 13. Auto-set appointment created_at
            "DROP TRIGGER IF EXISTS trg_set_appointment_created_at",
            "CREATE TRIGGER trg_set_appointment_created_at
            BEFORE INSERT ON appointments
            FOR EACH ROW
            BEGIN
              IF NEW.created_at IS NULL THEN
                SET NEW.created_at = NOW();
              END IF;
              SET NEW.updated_at = NOW();
            END",

            // 14. Prevent appointment scheduling in the past
            "DROP TRIGGER IF EXISTS trg_prevent_past_appointment",
            "CREATE TRIGGER trg_prevent_past_appointment
            BEFORE INSERT ON appointments
            FOR EACH ROW
            BEGIN
              IF NEW.schedule_date < CURDATE() THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot schedule appointment in the past';
              END IF;
            END",

            // 15. Validate appointment area
            "DROP TRIGGER IF EXISTS trg_validate_appointment_area",
            "CREATE TRIGGER trg_validate_appointment_area
            BEFORE INSERT ON appointments
            FOR EACH ROW
            BEGIN
              IF NEW.area_sqm <= 0 THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Appointment area must be greater than 0';
              END IF;
            END",

            // 16. Prevent deleting completed appointments
            "DROP TRIGGER IF EXISTS trg_prevent_delete_completed_appointment",
            "CREATE TRIGGER trg_prevent_delete_completed_appointment
            BEFORE DELETE ON appointments
            FOR EACH ROW
            BEGIN
              IF OLD.status = 'Completed' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot delete completed appointment';
              END IF;
            END",

            // 17. Validate availability dates
            "DROP TRIGGER IF EXISTS trg_validate_availability_dates",
            "CREATE TRIGGER trg_validate_availability_dates
            BEFORE INSERT ON employee_availability
            FOR EACH ROW
            BEGIN
              IF NEW.available_from >= NEW.available_to THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Available from must be before available to';
              END IF;
            END",

            // 18. Prevent availability overlap
            "DROP TRIGGER IF EXISTS trg_prevent_availability_overlap",
            "CREATE TRIGGER trg_prevent_availability_overlap
            BEFORE INSERT ON employee_availability
            FOR EACH ROW
            BEGIN
              IF NEW.employee_id IS NOT NULL AND EXISTS (
                SELECT 1 FROM employee_availability
                WHERE employee_id = NEW.employee_id
                AND available_from < NEW.available_to
                AND available_to > NEW.available_from
                AND id != NEW.id
              ) THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Employee availability overlaps with existing slot';
              END IF;
            END",

            // 19. Update employee availability status
            "DROP TRIGGER IF EXISTS trg_update_employee_availability",
            "CREATE TRIGGER trg_update_employee_availability
            AFTER UPDATE ON appointment_employee
            FOR EACH ROW
            BEGIN
              IF NEW.end_time IS NOT NULL THEN
                UPDATE employee_availability SET is_available = 1
                WHERE employee_id = NEW.employee_id AND appointment_id = NEW.appointment_id;
              END IF;
            END",

            // 20. Validate payment amount
            "DROP TRIGGER IF EXISTS trg_validate_payment_amount",
            "CREATE TRIGGER trg_validate_payment_amount
            BEFORE INSERT ON payments
            FOR EACH ROW
            BEGIN
              IF NEW.amount <= 0 THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Payment amount must be greater than 0';
              END IF;
            END",

            // 21. Prevent duplicate appointment service
            "DROP TRIGGER IF EXISTS trg_prevent_duplicate_service",
            "CREATE TRIGGER trg_prevent_duplicate_service
            BEFORE INSERT ON appointment_service
            FOR EACH ROW
            BEGIN
              IF EXISTS (
                SELECT 1 FROM appointment_service
                WHERE appointment_id = NEW.appointment_id AND service_id = NEW.service_id
              ) THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Service already assigned to appointment';
              END IF;
            END",

            // 22. Validate appointment service quantity
            "DROP TRIGGER IF EXISTS trg_validate_service_quantity",
            "CREATE TRIGGER trg_validate_service_quantity
            BEFORE INSERT ON appointment_service
            FOR EACH ROW
            BEGIN
              IF NEW.quantity <= 0 THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Service quantity must be greater than 0';
              END IF;
            END",

            // 23. Auto-update appointment status timestamp
            "DROP TRIGGER IF EXISTS trg_update_appointment_timestamp",
            "CREATE TRIGGER trg_update_appointment_timestamp
            BEFORE UPDATE ON appointments
            FOR EACH ROW
            BEGIN
              SET NEW.updated_at = NOW();
            END",

            // 24. Prevent duplicate appointment employee
            "DROP TRIGGER IF EXISTS trg_prevent_duplicate_employee",
            "CREATE TRIGGER trg_prevent_duplicate_employee
            BEFORE INSERT ON appointment_employee
            FOR EACH ROW
            BEGIN
              IF EXISTS (
                SELECT 1 FROM appointment_employee
                WHERE appointment_id = NEW.appointment_id AND employee_id = NEW.employee_id
              ) THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Employee already assigned to appointment';
              END IF;
            END",

            // 25. Validate appointment employee task duration
            "DROP TRIGGER IF EXISTS trg_validate_employee_task_duration",
            "CREATE TRIGGER trg_validate_employee_task_duration
            BEFORE INSERT ON appointment_employee
            FOR EACH ROW
            BEGIN
              IF NEW.start_time IS NOT NULL AND NEW.end_time IS NOT NULL AND NEW.start_time >= NEW.end_time THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Task start time must be before end time';
              END IF;
            END",
        ];

        // Execute each trigger
        foreach ($triggers as $trigger) {
            try {
                DB::statement($trigger);
            } catch (\Exception $e) {
                // Log but don't fail - trigger might already exist
                \Log::warning("Trigger creation warning: " . $e->getMessage());
            }
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $triggerNames = [
            'trg_auto_create_payment',
            'trg_log_appointment_creation',
            'trg_prevent_delete_paid_appointment',
            'trg_prevent_status_downgrade',
            'trg_update_availability_on_completion',
            'trg_validate_appointment_services',
            'trg_prevent_payment_status_downgrade',
            'trg_update_appointment_on_payment',
            'trg_prevent_delete_service_in_use',
            'trg_validate_service_pricing',
            'trg_prevent_delete_active_employee',
            'trg_validate_employee_phone',
            'trg_set_appointment_created_at',
            'trg_prevent_past_appointment',
            'trg_validate_appointment_area',
            'trg_prevent_delete_completed_appointment',
            'trg_validate_availability_dates',
            'trg_prevent_availability_overlap',
            'trg_update_employee_availability',
            'trg_validate_payment_amount',
            'trg_prevent_duplicate_service',
            'trg_validate_service_quantity',
            'trg_update_appointment_timestamp',
            'trg_prevent_duplicate_employee',
            'trg_validate_employee_task_duration',
        ];

        foreach ($triggerNames as $triggerName) {
            try {
                DB::statement("DROP TRIGGER IF EXISTS $triggerName");
            } catch (\Exception $e) {
                \Log::warning("Trigger drop warning: " . $e->getMessage());
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
