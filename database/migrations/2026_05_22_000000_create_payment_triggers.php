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
        // Create trigger for auto-payment creation when appointment is completed
        DB::unprepared('DROP TRIGGER IF EXISTS trg_auto_create_payment');
        
        DB::unprepared('
            CREATE TRIGGER trg_auto_create_payment
            AFTER UPDATE ON appointments
            FOR EACH ROW
            BEGIN
              DECLARE total_amount DECIMAL(10, 2);
              
              IF NEW.status = "Completed" AND OLD.status != "Completed" THEN
                IF NOT EXISTS (SELECT 1 FROM payments WHERE appointment_id = NEW.id) THEN
                  
                  SELECT COALESCE(SUM(
                    CASE 
                      WHEN s.pricing_type = "fixed" 
                      THEN COALESCE(aps.custom_price, s.base_price) * COALESCE(aps.quantity, 1)
                      WHEN s.pricing_type = "per_sqm"
                      THEN COALESCE(aps.custom_price, s.base_price) * COALESCE(NEW.area_sqm, 1)
                      ELSE 0
                    END
                  ), 0) INTO total_amount
                  FROM appointment_service aps
                  JOIN services s ON aps.service_id = s.id
                  WHERE aps.appointment_id = NEW.id;
                  
                  IF total_amount = 0 AND COALESCE(NEW.area_sqm, 0) > 0 THEN
                    SET total_amount = NEW.area_sqm * 55;
                  END IF;
                  
                  INSERT INTO payments (
                    appointment_id,
                    amount,
                    payment_status,
                    created_at,
                    updated_at
                  ) VALUES (
                    NEW.id,
                    COALESCE(total_amount, 0),
                    "Pending",
                    NOW(),
                    NOW()
                  );
                END IF;
              END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_auto_create_payment');
    }
};
