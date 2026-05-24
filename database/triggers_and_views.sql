-- ============================================================
-- HOUSEKEEPING MANAGEMENT SYSTEM
-- TRIGGERS AND VIEWS IMPLEMENTATION
-- ============================================================

-- ============================================================
-- TRIGGER 1: Auto-Create Payment on Appointment Completion
-- ============================================================
DELIMITER //

DROP TRIGGER IF EXISTS trg_auto_create_payment //

CREATE TRIGGER trg_auto_create_payment
AFTER UPDATE ON appointments
FOR EACH ROW
BEGIN
    DECLARE v_total_cost DECIMAL(12, 2);
    
    IF NEW.status = 'Completed' AND OLD.status != 'Completed' THEN
        -- Calculate total cost
        SELECT COALESCE(SUM(
            CASE
                WHEN s.pricing_type = 'fixed' THEN
                    aps.quantity * COALESCE(aps.custom_price, s.base_price)
                WHEN s.pricing_type = 'per_sqm' THEN
                    COALESCE(aps.custom_price, s.base_price) * aps.quantity
                ELSE 0
            END
        ), 0) INTO v_total_cost
        FROM appointment_service aps
        INNER JOIN services s ON s.id = aps.service_id
        WHERE aps.appointment_id = NEW.id;
        
        -- Insert payment record if not exists
        IF NOT EXISTS (SELECT 1 FROM payments WHERE appointment_id = NEW.id) THEN
            INSERT INTO payments (appointment_id, amount, payment_status, created_at, updated_at)
            VALUES (NEW.id, v_total_cost, 'Pending', NOW(), NOW());
        END IF;
    END IF;
END //

DELIMITER ;

-- ============================================================
-- TRIGGER 2: Update Employee Availability on Assignment
-- ============================================================
DELIMITER //

DROP TRIGGER IF EXISTS trg_update_employee_availability //

CREATE TRIGGER trg_update_employee_availability
AFTER INSERT ON appointment_employee
FOR EACH ROW
BEGIN
    -- Create availability record when employee is assigned
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
END //

DELIMITER ;

-- ============================================================
-- TRIGGER 3: Prevent Inactive Employees from Being Assigned
-- ============================================================
DELIMITER //

DROP TRIGGER IF EXISTS trg_prevent_inactive_assignment //

CREATE TRIGGER trg_prevent_inactive_assignment
BEFORE INSERT ON appointment_employee
FOR EACH ROW
BEGIN
    DECLARE v_employee_status VARCHAR(255);
    
    SELECT status INTO v_employee_status FROM employees WHERE id = NEW.employee_id;
    
    IF v_employee_status = 'Inactive' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot assign Inactive employee to appointment';
    END IF;
END //

DELIMITER ;

-- ============================================================
-- VIEW 1: Appointment Details Summary
-- ============================================================
DROP VIEW IF EXISTS vw_appointment_details;

CREATE VIEW vw_appointment_details AS
SELECT 
    a.id as appointment_id,
    a.customer_name,
    a.address,
    a.area_sqm,
    a.schedule_date,
    a.status,
    a.notes,
    COUNT(DISTINCT aps.service_id) as total_services,
    COUNT(DISTINCT ae.employee_id) as assigned_employees,
    COALESCE(SUM(
        CASE
            WHEN s.pricing_type = 'fixed' THEN
                aps.quantity * COALESCE(aps.custom_price, s.base_price)
            WHEN s.pricing_type = 'per_sqm' THEN
                COALESCE(aps.custom_price, s.base_price) * aps.quantity
            ELSE 0
        END
    ), 0) as estimated_cost,
    CASE 
        WHEN p.payment_status = 'Paid' THEN 'Paid'
        WHEN p.payment_status = 'Pending' THEN 'Pending'
        ELSE 'Not Recorded'
    END as payment_status,
    COALESCE(p.amount, 0) as payment_amount,
    a.created_at,
    a.updated_at
FROM appointments a
LEFT JOIN appointment_service aps ON a.id = aps.appointment_id
LEFT JOIN services s ON aps.service_id = s.id
LEFT JOIN appointment_employee ae ON a.id = ae.appointment_id
LEFT JOIN payments p ON a.id = p.appointment_id
GROUP BY a.id, a.customer_name, a.address, a.area_sqm, a.schedule_date, a.status, a.notes, p.payment_status, p.amount, a.created_at, a.updated_at //

-- ============================================================
-- VIEW 2: Services per Appointment
-- ============================================================
DROP VIEW IF EXISTS vw_appointment_services //

CREATE VIEW vw_appointment_services AS
SELECT 
    a.id as appointment_id,
    a.customer_name,
    a.schedule_date,
    s.id as service_id,
    s.service_name,
    s.pricing_type,
    s.base_price,
    aps.quantity,
    aps.custom_price,
    CASE
        WHEN s.pricing_type = 'fixed' THEN
            aps.quantity * COALESCE(aps.custom_price, s.base_price)
        WHEN s.pricing_type = 'per_sqm' THEN
            COALESCE(aps.custom_price, s.base_price) * aps.quantity
        ELSE 0
    END as line_total
FROM appointments a
INNER JOIN appointment_service aps ON a.id = aps.appointment_id
INNER JOIN services s ON aps.service_id = s.id
ORDER BY a.schedule_date DESC, a.customer_name //

-- ============================================================
-- VIEW 3: Employee Workload Summary
-- ============================================================
DROP VIEW IF EXISTS vw_employee_workload //

CREATE VIEW vw_employee_workload AS
SELECT 
    e.id as employee_id,
    e.name,
    e.position,
    e.status,
    e.phone,
    COUNT(DISTINCT ae.appointment_id) as total_appointments,
    COUNT(DISTINCT CASE WHEN a.status = 'Completed' THEN ae.appointment_id END) as completed_appointments,
    COUNT(DISTINCT CASE WHEN a.status = 'In Progress' THEN ae.appointment_id END) as in_progress_appointments,
    COUNT(DISTINCT CASE WHEN a.status = 'Pending' THEN ae.appointment_id END) as pending_appointments,
    GROUP_CONCAT(DISTINCT a.customer_name SEPARATOR ', ') as customer_names,
    GROUP_CONCAT(DISTINCT CONCAT('Appt#', ae.appointment_id, '-', ae.task) SEPARATOR ' | ') as task_details
FROM employees e
LEFT JOIN appointment_employee ae ON e.id = ae.employee_id
LEFT JOIN appointments a ON ae.appointment_id = a.id
GROUP BY e.id, e.name, e.position, e.status, e.phone //

-- ============================================================
-- VIEW 4: Payment Summary Report
-- ============================================================
DROP VIEW IF EXISTS vw_payment_summary //

CREATE VIEW vw_payment_summary AS
SELECT 
    p.id as payment_id,
    p.appointment_id,
    a.customer_name,
    a.schedule_date,
    COUNT(DISTINCT aps.service_id) as service_count,
    p.amount,
    p.payment_method,
    p.payment_status,
    CASE 
        WHEN p.payment_status = 'Paid' THEN 'Complete'
        WHEN p.payment_status = 'Pending' AND a.status = 'Completed' THEN 'Awaiting Payment'
        WHEN a.status != 'Completed' THEN 'Appointment Not Complete'
        ELSE 'Unknown'
    END as payment_stage,
    DATEDIFF(NOW(), a.schedule_date) as days_since_appointment,
    p.created_at,
    p.updated_at
FROM payments p
INNER JOIN appointments a ON p.appointment_id = a.id
LEFT JOIN appointment_service aps ON a.id = aps.appointment_id
GROUP BY p.id, p.appointment_id, a.customer_name, a.schedule_date, p.amount, p.payment_method, p.payment_status, a.status, p.created_at, p.updated_at //

-- ============================================================
-- VIEW 5: Employee Availability Status
-- ============================================================
DROP VIEW IF EXISTS vw_employee_availability_status //

CREATE VIEW vw_employee_availability_status AS
SELECT 
    e.id as employee_id,
    e.name,
    e.status,
    e.position,
    MAX(ea.available_from) as last_availability_from,
    MAX(ea.available_to) as last_availability_to,
    SUM(CASE WHEN ea.is_available = 1 THEN 1 ELSE 0 END) as available_slots,
    SUM(CASE WHEN ea.is_available = 0 THEN 1 ELSE 0 END) as booked_slots,
    COUNT(DISTINCT ea.appointment_id) as total_tracked_appointments,
    GROUP_CONCAT(DISTINCT ea.reason SEPARATOR ' | ') as unavailability_reasons
FROM employees e
LEFT JOIN employee_availability ea ON e.id = ea.employee_id
GROUP BY e.id, e.name, e.status, e.position //

-- ============================================================
-- VIEW 6: Appointment Status Distribution
-- ============================================================
DROP VIEW IF EXISTS vw_appointment_status_distribution //

CREATE VIEW vw_appointment_status_distribution AS
SELECT 
    a.status,
    COUNT(a.id) as total_appointments,
    COUNT(DISTINCT a.customer_name) as unique_customers,
    COALESCE(SUM(
        CASE
            WHEN s.pricing_type = 'fixed' THEN
                aps.quantity * COALESCE(aps.custom_price, s.base_price)
            WHEN s.pricing_type = 'per_sqm' THEN
                COALESCE(aps.custom_price, s.base_price) * aps.quantity
            ELSE 0
        END
    ), 0) as total_estimated_revenue,
    COALESCE(SUM(CASE WHEN p.payment_status = 'Paid' THEN p.amount ELSE 0 END), 0) as paid_revenue,
    AVG(
        CASE
            WHEN s.pricing_type = 'fixed' THEN
                aps.quantity * COALESCE(aps.custom_price, s.base_price)
            WHEN s.pricing_type = 'per_sqm' THEN
                COALESCE(aps.custom_price, s.base_price) * aps.quantity
            ELSE 0
        END
    ) as average_appointment_value
FROM appointments a
LEFT JOIN appointment_service aps ON a.id = aps.appointment_id
LEFT JOIN services s ON aps.service_id = s.id
LEFT JOIN payments p ON a.id = p.appointment_id
GROUP BY a.status //

DELIMITER ;

-- ============================================================
-- VERIFICATION: Show all created triggers
-- ============================================================
SHOW TRIGGERS FROM hk_db;

-- ============================================================
-- VERIFICATION: Show all created views
-- ============================================================
SHOW FULL TABLES FROM hk_db WHERE TABLE_TYPE = 'VIEW';
