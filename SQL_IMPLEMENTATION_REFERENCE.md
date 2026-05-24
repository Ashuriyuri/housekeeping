# 📊 HOUSEKEEPING MANAGEMENT SYSTEM
## SQL IMPLEMENTATION REFERENCE

**Generated:** May 20, 2026  
**Purpose:** Ready-to-use SQL code for stored procedures, triggers, and transactions

---

## STORED PROCEDURE #1: CALCULATE APPOINTMENT TOTAL

### Purpose
Calculates the total cost of an appointment based on all assigned services with their pricing rules.

### SQL Code
```sql
DELIMITER //

DROP PROCEDURE IF EXISTS CalculateAppointmentTotal //

CREATE PROCEDURE CalculateAppointmentTotal(
    IN p_appointment_id BIGINT UNSIGNED,
    OUT p_total_cost DECIMAL(12, 2)
)
DETERMINISTIC
READS SQL DATA
COMMENT 'Calculate total cost for an appointment including all services and pricing'
BEGIN
    DECLARE v_service_cost DECIMAL(12, 2) DEFAULT 0;
    DECLARE v_area_sqm DECIMAL(10, 2);
    DECLARE v_default_rate DECIMAL(10, 2) DEFAULT 55.00;
    DECLARE v_appointment_exists INT;
    
    -- Check if appointment exists
    SELECT COUNT(*) INTO v_appointment_exists
    FROM appointments
    WHERE id = p_appointment_id;
    
    IF v_appointment_exists = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Appointment not found';
    END IF;
    
    -- Get appointment area
    SELECT area_sqm INTO v_area_sqm
    FROM appointments
    WHERE id = p_appointment_id;
    
    -- Calculate total from services with custom pricing
    SELECT COALESCE(SUM(
        CASE
            WHEN s.pricing_type = 'fixed' THEN
                aps.quantity * COALESCE(aps.custom_price, s.base_price)
            WHEN s.pricing_type = 'per_sqm' THEN
                COALESCE(aps.custom_price, s.base_price) * aps.quantity
            ELSE 0
        END
    ), 0) INTO v_service_cost
    FROM appointment_service aps
    INNER JOIN services s ON s.id = aps.service_id
    WHERE aps.appointment_id = p_appointment_id;
    
    -- If no services, calculate minimum based on area
    IF v_service_cost = 0 AND v_area_sqm IS NOT NULL AND v_area_sqm > 0 THEN
        SET v_service_cost = v_area_sqm * v_default_rate;
    END IF;
    
    -- Set output parameter
    SET p_total_cost = COALESCE(v_service_cost, 0);
    
END //

DELIMITER ;
```

### Usage Examples

#### Example 1: Calculate Single Appointment
```sql
-- Calculate total for appointment #1
CALL CalculateAppointmentTotal(1, @total);
SELECT CONCAT('₱', FORMAT(@total, 2)) AS 'Total Cost';
```

#### Example 2: Check All Pending Payments
```sql
-- Calculate totals for all completed appointments without payment
CREATE TEMPORARY TABLE temp_appointment_totals (
    appointment_id BIGINT,
    customer_name VARCHAR(255),
    total_cost DECIMAL(12, 2)
);

INSERT INTO temp_appointment_totals
SELECT a.id, a.customer_name, NULL
FROM appointments a
WHERE a.status = 'Completed'
AND NOT EXISTS (
    SELECT 1 FROM payments p WHERE p.appointment_id = a.id
);

-- Calculate for each
SELECT 
    apt.appointment_id,
    apt.customer_name
FROM temp_appointment_totals apt;

-- Then for each one:
-- CALL CalculateAppointmentTotal(apt.appointment_id, @total);
```

#### Example 3: Create Payment with Calculated Total
```sql
DELIMITER //

CREATE PROCEDURE CreatePaymentForAppointment(
    IN p_appointment_id BIGINT UNSIGNED
)
MODIFIES SQL DATA
COMMENT 'Create payment record with automatically calculated total'
BEGIN
    DECLARE v_total DECIMAL(12, 2);
    DECLARE v_appointment_status VARCHAR(50);
    
    -- Verify appointment is completed
    SELECT status INTO v_appointment_status
    FROM appointments
    WHERE id = p_appointment_id;
    
    IF v_appointment_status != 'Completed' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Appointment must be Completed to create payment';
    END IF;
    
    -- Calculate total
    CALL CalculateAppointmentTotal(p_appointment_id, v_total);
    
    -- Insert payment
    INSERT INTO payments (appointment_id, amount, payment_status)
    VALUES (p_appointment_id, v_total, 'Pending')
    ON DUPLICATE KEY UPDATE
        amount = v_total,
        updated_at = NOW();
    
    SELECT 'Payment created successfully' AS message, v_total AS amount;
END //

DELIMITER ;

-- Usage:
-- CALL CreatePaymentForAppointment(1);
```

---

## STORED PROCEDURE #2: GET EMPLOYEE AVAILABILITY

### Purpose
Returns available time slots for an employee on a specific date, accounting for booked appointments.

### SQL Code
```sql
DELIMITER //

DROP PROCEDURE IF EXISTS GetEmployeeAvailability //

CREATE PROCEDURE GetEmployeeAvailability(
    IN p_employee_id BIGINT UNSIGNED,
    IN p_date DATE,
    IN p_slot_duration INT
)
DETERMINISTIC
READS SQL DATA
COMMENT 'Get available time slots for an employee on a specific date'
BEGIN
    DECLARE v_start_hour INT DEFAULT 6;
    DECLARE v_end_hour INT DEFAULT 22;
    DECLARE v_current_time DATETIME;
    DECLARE v_slot_end DATETIME;
    DECLARE v_is_booked INT;
    DECLARE v_employee_exists INT;
    DECLARE v_employee_status VARCHAR(50);
    
    -- Verify employee exists and is active
    SELECT COUNT(*) INTO v_employee_exists
    FROM employees
    WHERE id = p_employee_id;
    
    IF v_employee_exists = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Employee not found';
    END IF;
    
    SELECT status INTO v_employee_status
    FROM employees
    WHERE id = p_employee_id;
    
    IF v_employee_status = 'Inactive' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Employee is inactive';
    END IF;
    
    -- Validate slot duration
    IF p_slot_duration IS NULL OR p_slot_duration < 15 THEN
        SET p_slot_duration = 60; -- Default 1 hour
    END IF;
    
    -- Create temporary table for slots
    CREATE TEMPORARY TABLE temp_availability (
        slot_start DATETIME,
        slot_end DATETIME,
        is_available BOOLEAN,
        INDEX idx_slot_start (slot_start)
    );
    
    -- Generate time slots for the day
    SET v_current_time = CONCAT(p_date, ' ', LPAD(v_start_hour, 2, '0'), ':00:00');
    
    slot_loop: LOOP
        IF HOUR(v_current_time) >= v_end_hour THEN
            LEAVE slot_loop;
        END IF;
        
        SET v_slot_end = DATE_ADD(v_current_time, INTERVAL p_slot_duration MINUTE);
        
        -- Check if slot is booked
        SELECT COUNT(*) INTO v_is_booked
        FROM appointment_employee
        WHERE employee_id = p_employee_id
        AND DATE(start_time) = p_date
        AND start_time < v_slot_end
        AND end_time > v_current_time;
        
        -- Insert slot
        INSERT INTO temp_availability (slot_start, slot_end, is_available)
        VALUES (v_current_time, v_slot_end, v_is_booked = 0);
        
        SET v_current_time = DATE_ADD(v_current_time, INTERVAL p_slot_duration MINUTE);
    END LOOP;
    
    -- Return available slots
    SELECT 
        slot_start,
        slot_end,
        DATE_FORMAT(slot_start, '%H:%i') AS start_time_display,
        DATE_FORMAT(slot_end, '%H:%i') AS end_time_display,
        is_available,
        CASE 
            WHEN is_available THEN 'Available'
            ELSE 'Booked'
        END AS status
    FROM temp_availability
    ORDER BY slot_start;
    
    -- Clean up
    DROP TEMPORARY TABLE temp_availability;
    
END //

DELIMITER ;
```

### Usage Examples

#### Example 1: Get 1-Hour Slots for Employee
```sql
-- Get availability for employee #1 on May 20, 2026 (1-hour slots)
CALL GetEmployeeAvailability(1, '2026-05-20', 60);
```

#### Example 2: Get 30-Minute Slots
```sql
-- Finer-grained scheduling
CALL GetEmployeeAvailability(1, '2026-05-20', 30);
```

#### Example 3: Use in Assignment Process
```sql
-- Find best employee for appointment
SELECT e.id, e.name, COUNT(ta.slot_start) as available_slots
FROM employees e
WHERE e.status = 'Active'
AND EXISTS (
    -- Employee has availability on appointment date
    SELECT 1
    FROM (CALL GetEmployeeAvailability(e.id, '2026-05-21', 60)) ta
    WHERE ta.is_available = TRUE
)
GROUP BY e.id
ORDER BY available_slots DESC
LIMIT 5;
```

---

## TRIGGER #1: APPOINTMENT COMPLETION AUTO-PAYMENT

### Purpose
Automatically creates a payment record when an appointment is marked as completed.

### SQL Code
```sql
DELIMITER //

DROP TRIGGER IF EXISTS trg_appointment_completion //

CREATE TRIGGER trg_appointment_completion
AFTER UPDATE ON appointments
FOR EACH ROW
BEGIN
    DECLARE v_total_cost DECIMAL(12, 2);
    DECLARE v_payment_exists INT;
    
    -- When appointment status changes to 'Completed'
    IF NEW.status = 'Completed' AND (OLD.status IS NULL OR OLD.status != 'Completed') THEN
        
        -- Check if payment already exists
        SELECT COUNT(*) INTO v_payment_exists
        FROM payments
        WHERE appointment_id = NEW.id;
        
        -- Only create if payment doesn't exist
        IF v_payment_exists = 0 THEN
            
            -- Calculate appointment total
            CALL CalculateAppointmentTotal(NEW.id, v_total_cost);
            
            -- Insert payment record
            INSERT INTO payments (
                appointment_id,
                amount,
                payment_status,
                payment_method
            ) VALUES (
                NEW.id,
                v_total_cost,
                'Pending',
                NULL
            );
        END IF;
    END IF;
END //

DELIMITER ;
```

### Trigger Verification

```sql
-- Verify trigger exists
SELECT TRIGGER_SCHEMA, TRIGGER_NAME, EVENT_MANIPULATION, ACTION_TIMING
FROM INFORMATION_SCHEMA.TRIGGERS
WHERE TRIGGER_NAME = 'trg_appointment_completion';

-- Test trigger
START TRANSACTION;
    -- Create test appointment
    INSERT INTO appointments (customer_name, address, schedule_date, status)
    VALUES ('Test Customer', '123 Test St', NOW() + INTERVAL 1 DAY, 'In Progress');
    
    SET @test_appointment_id = LAST_INSERT_ID();
    
    -- Add test service
    INSERT INTO appointment_service (appointment_id, service_id, quantity)
    SELECT @test_appointment_id, id, 100 FROM services LIMIT 1;
    
    -- Update to completed - should trigger payment creation
    UPDATE appointments SET status = 'Completed' WHERE id = @test_appointment_id;
    
    -- Verify payment was created
    SELECT 'Payment should exist:' AS check_name,
           COUNT(*) AS payment_count
    FROM payments WHERE appointment_id = @test_appointment_id;
    
ROLLBACK;
```

---

## TRANSACTION #1: CREATE APPOINTMENT WITH SERVICES

### Purpose
Atomically create an appointment with associated services and verify data integrity.

### SQL Code
```sql
DELIMITER //

DROP PROCEDURE IF EXISTS CreateAppointmentWithServices //

CREATE PROCEDURE CreateAppointmentWithServices(
    IN p_customer_name VARCHAR(255),
    IN p_address TEXT,
    IN p_area_sqm DECIMAL(10, 2),
    IN p_schedule_date DATETIME,
    IN p_notes TEXT,
    OUT p_appointment_id BIGINT UNSIGNED,
    OUT p_message VARCHAR(255)
)
MODIFIES SQL DATA
COMMENT 'Create appointment with services in atomic transaction'
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_message = 'ERROR: Transaction rolled back due to error';
        SET p_appointment_id = 0;
    END;
    
    START TRANSACTION;
    
        -- Validation
        IF p_customer_name IS NULL OR p_customer_name = '' THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Customer name is required';
        END IF;
        
        IF p_address IS NULL OR p_address = '' THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Address is required';
        END IF;
        
        IF p_schedule_date < NOW() THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Schedule date must be in future';
        END IF;
        
        -- Insert appointment
        INSERT INTO appointments (
            customer_name,
            address,
            area_sqm,
            schedule_date,
            status,
            notes
        ) VALUES (
            p_customer_name,
            p_address,
            p_area_sqm,
            p_schedule_date,
            'Pending',
            p_notes
        );
        
        SET p_appointment_id = LAST_INSERT_ID();
        
        -- Verify insertion
        IF p_appointment_id = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Failed to create appointment';
        END IF;
        
        COMMIT;
        SET p_message = 'Appointment created successfully';
        
END //

DELIMITER ;
```

### Usage Example
```sql
-- Create appointment
CALL CreateAppointmentWithServices(
    'John Doe',
    '123 Main Street, City, Country',
    150.50,
    DATE_ADD(NOW(), INTERVAL 5 DAY),
    'Special cleaning requirements',
    @appointment_id,
    @message
);

SELECT @message, @appointment_id;

-- Next, add services manually or create wrapper procedure:
CALL AddServiceToAppointment(@appointment_id, 1, 150.50, NULL);
CALL AddServiceToAppointment(@appointment_id, 5, 1, 800.00);
```

---

## TRANSACTION #2: COMPLETE APPOINTMENT & PROCESS PAYMENT

### Purpose
Atomically complete an appointment, calculate payment, and update employee status.

### SQL Code
```sql
DELIMITER //

DROP PROCEDURE IF EXISTS CompleteAppointmentAndPayment //

CREATE PROCEDURE CompleteAppointmentAndPayment(
    IN p_appointment_id BIGINT UNSIGNED,
    IN p_payment_method VARCHAR(50),
    OUT p_total_cost DECIMAL(12, 2),
    OUT p_message VARCHAR(255)
)
MODIFIES SQL DATA
COMMENT 'Complete appointment, calculate and record payment atomically'
BEGIN
    DECLARE v_current_status VARCHAR(50);
    DECLARE v_employee_count INT;
    DECLARE v_service_count INT;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_message = 'ERROR: Transaction failed - All changes rolled back';
        SET p_total_cost = 0;
    END;
    
    START TRANSACTION ISOLATION LEVEL SERIALIZABLE;
    
        -- Verify appointment exists and is in progress
        SELECT status INTO v_current_status
        FROM appointments
        WHERE id = p_appointment_id
        FOR UPDATE; -- Lock row
        
        IF v_current_status IS NULL THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Appointment not found';
        END IF;
        
        IF v_current_status != 'In Progress' THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Appointment must be In Progress to complete';
        END IF;
        
        -- Count associated data
        SELECT COUNT(*) INTO v_employee_count
        FROM appointment_employee
        WHERE appointment_id = p_appointment_id;
        
        SELECT COUNT(*) INTO v_service_count
        FROM appointment_service
        WHERE appointment_id = p_appointment_id;
        
        -- Update appointment status
        UPDATE appointments
        SET status = 'Completed'
        WHERE id = p_appointment_id;
        
        -- Calculate total
        CALL CalculateAppointmentTotal(p_appointment_id, p_total_cost);
        
        -- Create/Update payment
        INSERT INTO payments (
            appointment_id,
            amount,
            payment_method,
            payment_status
        ) VALUES (
            p_appointment_id,
            p_total_cost,
            p_payment_method,
            CASE WHEN p_payment_method IS NOT NULL THEN 'Pending' ELSE 'Pending' END
        )
        ON DUPLICATE KEY UPDATE
            amount = p_total_cost,
            payment_method = COALESCE(p_payment_method, payment_method),
            updated_at = NOW();
        
        -- Release employees from this appointment
        UPDATE appointment_employee
        SET is_available = TRUE
        WHERE appointment_id = p_appointment_id;
        
        -- Verification: Confirm all updates
        IF (SELECT COUNT(*) FROM appointments 
            WHERE id = p_appointment_id AND status = 'Completed') = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Status update verification failed';
        END IF;
        
        COMMIT;
        SET p_message = CONCAT(
            'Appointment completed. ',
            'Payment: ₱', FORMAT(p_total_cost, 2), '. ',
            'Employees released: ', v_employee_count, '. ',
            'Services completed: ', v_service_count
        );
        
END //

DELIMITER ;
```

### Usage Example
```sql
-- Complete appointment with payment method
CALL CompleteAppointmentAndPayment(1, 'Cash', @total, @message);
SELECT @message AS status, CONCAT('₱', FORMAT(@total, 2)) AS amount;

-- Or without payment method (to be entered later)
CALL CompleteAppointmentAndPayment(1, NULL, @total, @message);
```

---

## UTILITY PROCEDURES

### Add Service to Appointment
```sql
DELIMITER //

CREATE PROCEDURE AddServiceToAppointment(
    IN p_appointment_id BIGINT UNSIGNED,
    IN p_service_id BIGINT UNSIGNED,
    IN p_quantity INTEGER,
    IN p_custom_price DECIMAL(10, 2)
)
MODIFIES SQL DATA
COMMENT 'Add or update a service in an appointment'
BEGIN
    DECLARE v_count INT;
    
    -- Verify appointment exists
    IF NOT EXISTS (SELECT 1 FROM appointments WHERE id = p_appointment_id) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Appointment not found';
    END IF;
    
    -- Verify service exists
    IF NOT EXISTS (SELECT 1 FROM services WHERE id = p_service_id) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Service not found';
    END IF;
    
    -- Insert or update
    INSERT INTO appointment_service (
        appointment_id,
        service_id,
        quantity,
        custom_price
    ) VALUES (
        p_appointment_id,
        p_service_id,
        p_quantity,
        p_custom_price
    )
    ON DUPLICATE KEY UPDATE
        quantity = p_quantity,
        custom_price = p_custom_price,
        updated_at = NOW();
    
    SELECT 'Service added/updated successfully' AS message;
END //

DELIMITER ;
```

### Assign Employee to Appointment
```sql
DELIMITER //

CREATE PROCEDURE AssignEmployeeToAppointment(
    IN p_appointment_id BIGINT UNSIGNED,
    IN p_employee_id BIGINT UNSIGNED,
    IN p_task VARCHAR(255),
    IN p_start_time DATETIME,
    IN p_end_time DATETIME
)
MODIFIES SQL DATA
COMMENT 'Assign employee to appointment with time slot'
BEGIN
    DECLARE v_employee_status VARCHAR(50);
    DECLARE v_appointment_date DATE;
    DECLARE v_conflict_count INT;
    
    -- Verify employee is active
    SELECT status INTO v_employee_status
    FROM employees WHERE id = p_employee_id;
    
    IF v_employee_status != 'Active' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Employee must be Active to assign';
    END IF;
    
    -- Check for time conflicts
    SELECT COUNT(*) INTO v_conflict_count
    FROM appointment_employee
    WHERE employee_id = p_employee_id
    AND start_time < p_end_time
    AND end_time > p_start_time;
    
    IF v_conflict_count > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Employee has conflicting appointment in this time slot';
    END IF;
    
    -- Assign
    INSERT INTO appointment_employee (
        appointment_id,
        employee_id,
        task,
        start_time,
        end_time,
        is_available
    ) VALUES (
        p_appointment_id,
        p_employee_id,
        p_task,
        p_start_time,
        p_end_time,
        TRUE
    )
    ON DUPLICATE KEY UPDATE
        task = p_task,
        start_time = p_start_time,
        end_time = p_end_time,
        updated_at = NOW();
    
    SELECT 'Employee assigned successfully' AS message;
END //

DELIMITER ;
```

---

## REPORTING QUERIES

### Pending Payments Report
```sql
SELECT 
    p.id AS payment_id,
    a.id AS appointment_id,
    a.customer_name,
    a.address,
    p.amount,
    p.payment_status,
    p.created_at AS appointment_date,
    DATEDIFF(NOW(), p.created_at) AS days_pending
FROM payments p
INNER JOIN appointments a ON a.id = p.appointment_id
WHERE p.payment_status = 'Pending'
ORDER BY p.created_at ASC;
```

### Appointment Revenue Summary
```sql
SELECT 
    DATE(a.schedule_date) AS appointment_date,
    COUNT(a.id) AS total_appointments,
    COUNT(CASE WHEN a.status = 'Completed' THEN 1 END) AS completed,
    COUNT(CASE WHEN a.status = 'In Progress' THEN 1 END) AS in_progress,
    COUNT(CASE WHEN a.status = 'Pending' THEN 1 END) AS pending,
    COALESCE(SUM(p.amount), 0) AS revenue
FROM appointments a
LEFT JOIN payments p ON p.appointment_id = a.id
GROUP BY DATE(a.schedule_date)
ORDER BY appointment_date DESC;
```

### Employee Utilization Report
```sql
SELECT 
    e.id,
    e.name,
    e.status,
    COUNT(DISTINCT ae.appointment_id) AS total_appointments,
    SUM(TIME_TO_SEC(TIMEDIFF(ae.end_time, ae.start_time)) / 3600) AS hours_worked,
    COUNT(DISTINCT DATE(ae.start_time)) AS days_worked
FROM employees e
LEFT JOIN appointment_employee ae ON ae.employee_id = e.id
GROUP BY e.id
ORDER BY total_appointments DESC;
```

---

## INSTALLATION INSTRUCTIONS

### Step 1: Create Procedures
```sql
SOURCE path/to/stored_procedures.sql
```

### Step 2: Create Trigger
```sql
SOURCE path/to/triggers.sql
```

### Step 3: Verify Installation
```sql
-- Check procedures
SHOW PROCEDURE STATUS WHERE DB = 'your_database';

-- Check triggers
SELECT * FROM INFORMATION_SCHEMA.TRIGGERS 
WHERE TRIGGER_SCHEMA = 'your_database';
```

### Step 4: Test
```sql
-- Test calculation
CALL CalculateAppointmentTotal(1, @total);
SELECT @total;

-- Test availability
CALL GetEmployeeAvailability(1, '2026-05-20', 60);

-- Test transaction
CALL CompleteAppointmentAndPayment(1, 'Cash', @cost, @msg);
SELECT @msg, @cost;
```

---

**SQL Reference Complete** ✅

All procedures, triggers, and transactions are ready for implementation in your database.
