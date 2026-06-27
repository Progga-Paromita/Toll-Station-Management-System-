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
        // 1. Create sequence for vehicle_id auto-increment
        DB::unprepared("
            CREATE SEQUENCE vehicles_seq
                START WITH 1
                INCREMENT BY 1
                NOCACHE
                NOCYCLE
        ");

        // 2. Create the vehicles table
        DB::unprepared("
            CREATE TABLE vehicles (
                vehicle_id           NUMBER          PRIMARY KEY,
                registration_number  VARCHAR2(20)    NOT NULL,
                owner_name           VARCHAR2(100)   NOT NULL,
                owner_phone          VARCHAR2(15)    NOT NULL,
                vehicle_type         VARCHAR2(20)    NOT NULL,
                color                VARCHAR2(30)    NOT NULL,
                manufacturer         VARCHAR2(50),
                model                VARCHAR2(50),
                weight               NUMBER(8,2)     NOT NULL,
                registration_date    DATE            DEFAULT SYSDATE,
                status               VARCHAR2(20)    DEFAULT 'ACTIVE' NOT NULL,
                created_by           NUMBER,
                created_at           DATE            DEFAULT SYSDATE,
                updated_at           DATE,

                -- Constraints
                CONSTRAINT uq_registration_number UNIQUE (registration_number),
                CONSTRAINT chk_vehicle_type_val   CHECK (vehicle_type IN ('CAR', 'BUS', 'TRUCK', 'BIKE', 'MICROBUS', 'AMBULANCE')),
                CONSTRAINT chk_vehicle_weight     CHECK (weight > 0),
                CONSTRAINT chk_vehicle_status     CHECK (status IN ('ACTIVE', 'BLOCKED', 'EXPIRED')),
                CONSTRAINT fk_vehicle_creator     FOREIGN KEY (created_by) REFERENCES users(user_id)
            )
        ");

        // 3. Create the auto-increment trigger
        DB::unprepared("
            CREATE OR REPLACE TRIGGER vehicles_trigger
            BEFORE INSERT ON vehicles
            FOR EACH ROW
            WHEN (NEW.vehicle_id IS NULL)
            BEGIN
                :NEW.vehicle_id := vehicles_seq.NEXTVAL;
            END;
        ");

        // 4. Alter toll_transactions to add vehicle_id column and foreign key
        DB::unprepared("
            ALTER TABLE toll_transactions ADD vehicle_id NUMBER
        ");

        DB::unprepared("
            ALTER TABLE toll_transactions ADD CONSTRAINT fk_txn_vehicle
            FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id)
        ");

        // 5. Drop the old vehicle type constraint on toll_transactions and add the expanded one
        try {
            DB::unprepared("
                ALTER TABLE toll_transactions DROP CONSTRAINT chk_vehicle_type
            ");
        } catch (\Exception $e) {
            // Safe fallback if constraint was named differently or didn't exist
        }

        DB::unprepared("
            ALTER TABLE toll_transactions ADD CONSTRAINT chk_vehicle_type
            CHECK (vehicle_type IN ('Bike', 'Car', 'Bus', 'Truck', 'Microbus', 'Ambulance', 'BIKE', 'CAR', 'BUS', 'TRUCK', 'MICROBUS', 'AMBULANCE'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop FK constraint first
        try {
            DB::unprepared("ALTER TABLE toll_transactions DROP CONSTRAINT fk_txn_vehicle");
        } catch (\Exception $e) {}

        // Drop vehicle_id column
        try {
            DB::unprepared("ALTER TABLE toll_transactions DROP COLUMN vehicle_id");
        } catch (\Exception $e) {}

        // Drop table and sequence
        try {
            DB::unprepared("DROP TRIGGER vehicles_trigger");
        } catch (\Exception $e) {}

        try {
            DB::unprepared("DROP TABLE vehicles");
        } catch (\Exception $e) {}

        try {
            DB::unprepared("DROP SEQUENCE vehicles_seq");
        } catch (\Exception $e) {}
    }
};
