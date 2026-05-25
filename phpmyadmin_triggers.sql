-- ============================================================================
-- HOUSEKEEPING MANAGEMENT SYSTEM - ALL TRIGGERS
-- Compatible with phpMyAdmin
-- Database: hk_db
-- Created: May 25, 2026
-- ============================================================================

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone="+00:00";

-- ============================================================================
-- TRIGGER 1: Auto-create payment when appointment is completed
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_auto_create_payment`;
CREATE TRIGGER `trg_auto_create_payment` AFTER UPDATE ON `appointments` 
FOR EACH ROW 
BEGIN
  IF NEW.status = 'Completed' AND OLD.status != 'Completed' THEN
    IF NOT EXISTS (SELECT 1 FROM payments WHERE appointment_id = NEW.id) THEN
      INSERT INTO payments (
        appointment_id,
        amount,
        payment_status,
        created_at,
        updated_at
      )
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
END;

-- ============================================================================
-- TRIGGER 2: Prevent inactive employee assignment
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_prevent_inactive_assignment`;
CREATE TRIGGER `trg_prevent_inactive_assignment` BEFORE INSERT ON `appointment_employee` 
FOR EACH ROW 
BEGIN
  DECLARE employee_status VARCHAR(20);
  
  SELECT status INTO employee_status
  FROM employees
  WHERE id = NEW.employee_id;
  
  IF employee_status = 'Inactive' THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Cannot assign Inactive employee to appointment';
  END IF;
END;

-- ============================================================================
-- TRIGGER 3: Update employee availability on assignment
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_update_employee_availability`;
CREATE TRIGGER `trg_update_employee_availability` AFTER INSERT ON `appointment_employee` 
FOR EACH ROW 
BEGIN
  INSERT INTO employee_availability (
    employee_id,
    appointment_id,
    available_from,
    available_to,
    is_available,
    reason,
    created_at,
    updated_at
  ) VALUES (
    NEW.employee_id,
    NEW.appointment_id,
    COALESCE(NEW.start_time, (SELECT schedule_date FROM appointments WHERE id = NEW.appointment_id)),
    COALESCE(NEW.end_time, DATE_ADD((SELECT schedule_date FROM appointments WHERE id = NEW.appointment_id), INTERVAL 2 HOUR)),
    0,
    CONCAT('Appointment #', NEW.appointment_id),
    NOW(),
    NOW()
  )
  ON DUPLICATE KEY UPDATE
    updated_at = NOW(),
    reason = CONCAT('Appointment #', NEW.appointment_id);
END;

-- ============================================================================
-- TRIGGER 4: Prevent deletion of paid appointments
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_prevent_delete_paid_appointment`;
CREATE TRIGGER `trg_prevent_delete_paid_appointment` BEFORE DELETE ON `appointments` 
FOR EACH ROW 
BEGIN
  IF EXISTS (SELECT 1 FROM payments WHERE appointment_id = OLD.id AND payment_status = 'Paid') THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Cannot delete appointment with paid payment';
  END IF;
END;

-- ============================================================================
-- TRIGGER 5: Auto-update appointment status based on employees
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_auto_update_appointment_status`;
CREATE TRIGGER `trg_auto_update_appointment_status` AFTER INSERT ON `appointment_employee` 
FOR EACH ROW 
BEGIN
  DECLARE total_employees INT;
  DECLARE active_count INT;
  
  SELECT COUNT(*) INTO total_employees FROM appointment_employee WHERE appointment_id = NEW.appointment_id;
  
  IF total_employees > 0 THEN
    UPDATE appointments 
    SET status = 'In Progress' 
    WHERE id = NEW.appointment_id AND status = 'Pending';
  END IF;
END;

-- ============================================================================
-- TRIGGER 6: Prevent status downgrade (Completed → Pending)
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_prevent_status_downgrade`;
CREATE TRIGGER `trg_prevent_status_downgrade` BEFORE UPDATE ON `appointments` 
FOR EACH ROW 
BEGIN
  IF NEW.status = 'Pending' AND OLD.status IN ('In Progress', 'Completed') THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Cannot downgrade appointment status';
  END IF;
END;

-- ============================================================================
-- TRIGGER 7: Validate service pricing on insert
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_validate_service_pricing`;
CREATE TRIGGER `trg_validate_service_pricing` BEFORE INSERT ON `services` 
FOR EACH ROW 
BEGIN
  IF NEW.base_price < 0 THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Service price cannot be negative';
  END IF;
  
  IF NEW.pricing_type NOT IN ('fixed', 'per_sqm') THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Invalid pricing type. Use fixed or per_sqm';
  END IF;
END;

-- ============================================================================
-- TRIGGER 8: Validate service pricing on update
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_validate_service_update`;
CREATE TRIGGER `trg_validate_service_update` BEFORE UPDATE ON `services` 
FOR EACH ROW 
BEGIN
  IF NEW.base_price < 0 THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Service price cannot be negative';
  END IF;
  
  IF NEW.pricing_type NOT IN ('fixed', 'per_sqm') THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Invalid pricing type. Use fixed or per_sqm';
  END IF;
END;

-- ============================================================================
-- TRIGGER 9: Prevent deletion of active services
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_prevent_delete_active_service`;
CREATE TRIGGER `trg_prevent_delete_active_service` BEFORE DELETE ON `services` 
FOR EACH ROW 
BEGIN
  IF OLD.status = 'Active' THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Cannot delete Active service';
  END IF;
END;

-- ============================================================================
-- TRIGGER 10: Prevent deletion of active employees
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_prevent_delete_active_employee`;
CREATE TRIGGER `trg_prevent_delete_active_employee` BEFORE DELETE ON `employees` 
FOR EACH ROW 
BEGIN
  IF OLD.status = 'Active' THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Cannot delete Active employee';
  END IF;
END;

-- ============================================================================
-- TRIGGER 11: Track employee status changes
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_track_employee_status_change`;
CREATE TRIGGER `trg_track_employee_status_change` AFTER UPDATE ON `employees` 
FOR EACH ROW 
BEGIN
  IF NEW.status != OLD.status THEN
    UPDATE employee_availability 
    SET is_available = 0, reason = CONCAT('Status changed to: ', NEW.status)
    WHERE employee_id = NEW.id AND is_available = 1;
  END IF;
END;

-- ============================================================================
-- TRIGGER 12: Auto-create employee availability on employee creation
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_auto_create_employee_availability`;
CREATE TRIGGER `trg_auto_create_employee_availability` AFTER INSERT ON `employees` 
FOR EACH ROW 
BEGIN
  IF NEW.status = 'Active' THEN
    INSERT INTO employee_availability (
      employee_id,
      available_from,
      available_to,
      is_available,
      reason,
      created_at,
      updated_at
    ) VALUES (
      NEW.id,
      NOW(),
      DATE_ADD(NOW(), INTERVAL 30 DAY),
      1,
      'Auto-created on employee registration',
      NOW(),
      NOW()
    );
  END IF;
END;

-- ============================================================================
-- TRIGGER 13: Validate payment method
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_validate_payment_method`;
CREATE TRIGGER `trg_validate_payment_method` BEFORE INSERT ON `payments` 
FOR EACH ROW 
BEGIN
  IF NEW.payment_method IS NOT NULL 
    AND NEW.payment_method NOT IN ('Cash', 'GCash', 'Bank Transfer', 'Credit Card', 'Cheque') THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Invalid payment method';
  END IF;
END;

-- ============================================================================
-- TRIGGER 14: Auto-update payment status when marked as paid
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_update_payment_status`;
CREATE TRIGGER `trg_update_payment_status` AFTER UPDATE ON `payments` 
FOR EACH ROW 
BEGIN
  IF NEW.payment_status = 'Paid' AND OLD.payment_status != 'Paid' THEN
    UPDATE payments
    SET updated_at = NOW()
    WHERE id = NEW.id;
  END IF;
END;

-- ============================================================================
-- TRIGGER 15: Prevent deletion of completed payments
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_prevent_delete_completed_payment`;
CREATE TRIGGER `trg_prevent_delete_completed_payment` BEFORE DELETE ON `payments` 
FOR EACH ROW 
BEGIN
  IF OLD.payment_status = 'Paid' THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Cannot delete completed payment records';
  END IF;
END;

-- ============================================================================
-- TRIGGER 16: Validate payment amount
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_validate_payment_amount`;
CREATE TRIGGER `trg_validate_payment_amount` BEFORE INSERT ON `payments` 
FOR EACH ROW 
BEGIN
  IF NEW.amount <= 0 THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Payment amount must be greater than 0';
  END IF;
END;

-- ============================================================================
-- TRIGGER 17: Validate appointment service
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_validate_appointment_service`;
CREATE TRIGGER `trg_validate_appointment_service` BEFORE INSERT ON `appointment_service` 
FOR EACH ROW 
BEGIN
  IF NEW.quantity <= 0 THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Service quantity must be greater than 0';
  END IF;
END;

-- ============================================================================
-- TRIGGER 18: Prevent removal of services from completed appointments
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_prevent_service_removal_completed`;
CREATE TRIGGER `trg_prevent_service_removal_completed` BEFORE DELETE ON `appointment_service` 
FOR EACH ROW 
BEGIN
  DECLARE appt_status VARCHAR(20);
  
  SELECT status INTO appt_status FROM appointments WHERE id = OLD.appointment_id;
  
  IF appt_status = 'Completed' THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Cannot remove services from completed appointment';
  END IF;
END;

-- ============================================================================
-- TRIGGER 19: Recalculate payment on service update
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_recalculate_payment_on_service_update`;
CREATE TRIGGER `trg_recalculate_payment_on_service_update` AFTER UPDATE ON `appointment_service` 
FOR EACH ROW 
BEGIN
  DECLARE new_amount DECIMAL(12, 2);
  
  SELECT COALESCE(SUM(
    CASE 
      WHEN s.pricing_type = 'fixed' 
      THEN aps.quantity * COALESCE(aps.custom_price, s.base_price)
      WHEN s.pricing_type = 'per_sqm'
      THEN COALESCE(aps.custom_price, s.base_price) * aps.quantity
      ELSE 0
    END
  ), 0) INTO new_amount
  FROM appointment_service aps
  JOIN services s ON aps.service_id = s.id
  WHERE aps.appointment_id = NEW.appointment_id;
  
  UPDATE payments 
  SET amount = new_amount
  WHERE appointment_id = NEW.appointment_id;
END;

-- ============================================================================
-- TRIGGER 20: Prevent removal of employees from active appointments
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_prevent_employee_removal_active`;
CREATE TRIGGER `trg_prevent_employee_removal_active` BEFORE DELETE ON `appointment_employee` 
FOR EACH ROW 
BEGIN
  DECLARE appt_status VARCHAR(20);
  
  SELECT status INTO appt_status FROM appointments WHERE id = OLD.appointment_id;
  
  IF appt_status IN ('In Progress', 'Completed') THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Cannot remove employees from active/completed appointment';
  END IF;
END;

-- ============================================================================
-- TRIGGER 21: Clean availability on employee removal
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_clean_availability_on_employee_removal`;
CREATE TRIGGER `trg_clean_availability_on_employee_removal` AFTER DELETE ON `appointment_employee` 
FOR EACH ROW 
BEGIN
  UPDATE employee_availability 
  SET is_available = 1, appointment_id = NULL
  WHERE appointment_id = OLD.appointment_id AND employee_id = OLD.employee_id;
END;

-- ============================================================================
-- TRIGGER 22: Update availability on completion
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_update_availability_on_completion`;
CREATE TRIGGER `trg_update_availability_on_completion` AFTER UPDATE ON `appointments` 
FOR EACH ROW 
BEGIN
  IF NEW.status = 'Completed' AND OLD.status != 'Completed' THEN
    UPDATE employee_availability 
    SET is_available = 1, appointment_id = NULL, reason = 'Appointment completed'
    WHERE appointment_id = NEW.id;
  END IF;
END;

-- ============================================================================
-- TRIGGER 23: Validate availability dates
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_validate_availability_dates`;
CREATE TRIGGER `trg_validate_availability_dates` BEFORE INSERT ON `employee_availability` 
FOR EACH ROW 
BEGIN
  IF NEW.available_to <= NEW.available_from THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Availability end date must be after start date';
  END IF;
END;

-- ============================================================================
-- TRIGGER 24: Prevent availability overlap
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_prevent_availability_overlap`;
CREATE TRIGGER `trg_prevent_availability_overlap` BEFORE INSERT ON `employee_availability` 
FOR EACH ROW 
BEGIN
  IF EXISTS (
    SELECT 1 FROM employee_availability 
    WHERE employee_id = NEW.employee_id 
    AND appointment_id IS NOT NULL
    AND (
      (NEW.available_from < available_to AND NEW.available_to > available_from)
    )
  ) THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Employee availability overlaps with existing appointment';
  END IF;
END;

-- ============================================================================
-- TRIGGER 25: Log appointment creation
-- ============================================================================
DROP TRIGGER IF EXISTS `trg_log_appointment_creation`;
CREATE TRIGGER `trg_log_appointment_creation` AFTER INSERT ON `appointments` 
FOR EACH ROW 
BEGIN
  -- This trigger can be expanded to log to an audit table
  -- For now, it serves as a hook for future audit functionality
  UPDATE appointments SET notes = CONCAT(COALESCE(notes, ''), '\nCreated at: ', NOW()) WHERE id = NEW.id;
END;

COMMIT;
