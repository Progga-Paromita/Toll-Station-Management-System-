<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Master migration for TollDatabase schema.
 *
 * Creates all application tables under the TOLLDATABASE Oracle user/schema,
 * ensuring nothing is placed under the Oracle SYSTEM schema.
 *
 * Tables created:
 *   - roles
 *   - users
 *   - login_logs
 *   - toll_transactions
 *   - migrations (handled by Laravel automatically)
 *
 * PL/SQL objects created:
 *   - Sequences  : roles_seq, users_seq, login_logs_seq, toll_transactions_seq
 *   - Triggers   : roles_trigger, users_trigger, login_logs_trigger,
 *                  toll_transactions_trigger, trg_user_login
 *   - Function   : get_user_role
 *   - Procedure  : validate_login
 */
return new class extends Migration
{
    /**
     * Run all DDL statements to build the TollDatabase schema.
     */
    public function up(): void
    {
        // ─────────────────────────────────────────────────────────────────
        // 1. ROLES TABLE
        // ─────────────────────────────────────────────────────────────────
        DB::unprepared("
            CREATE TABLE roles (
                role_id    NUMBER          NOT NULL,
                role_name  VARCHAR2(50)    NOT NULL,
                description VARCHAR2(255),
                created_at TIMESTAMP       DEFAULT SYSTIMESTAMP,
                CONSTRAINT pk_roles PRIMARY KEY (role_id),
                CONSTRAINT uq_role_name UNIQUE (role_name)
            )
        ");

        DB::unprepared("CREATE SEQUENCE roles_seq START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE");

        DB::unprepared("
            CREATE OR REPLACE TRIGGER roles_trigger
            BEFORE INSERT ON roles
            FOR EACH ROW
            WHEN (NEW.role_id IS NULL)
            BEGIN
                :NEW.role_id := roles_seq.NEXTVAL;
            END;
        ");

        // ─────────────────────────────────────────────────────────────────
        // 2. USERS TABLE
        // ─────────────────────────────────────────────────────────────────
        DB::unprepared("
            CREATE TABLE users (
                user_id        NUMBER          NOT NULL,
                name           VARCHAR2(100)   NOT NULL,
                username       VARCHAR2(50)    NOT NULL,
                email          VARCHAR2(100)   NOT NULL,
                password       VARCHAR2(255)   NOT NULL,
                role_id        NUMBER          NOT NULL,
                status         VARCHAR2(10)    DEFAULT 'ACTIVE',
                login_attempts NUMBER(2)       DEFAULT 0,
                last_login     TIMESTAMP,
                created_at     TIMESTAMP       DEFAULT SYSTIMESTAMP,
                CONSTRAINT pk_users        PRIMARY KEY (user_id),
                CONSTRAINT uq_username     UNIQUE (username),
                CONSTRAINT uq_email        UNIQUE (email),
                CONSTRAINT fk_users_role   FOREIGN KEY (role_id) REFERENCES roles(role_id),
                CONSTRAINT chk_user_status CHECK (status IN ('ACTIVE', 'INACTIVE'))
            )
        ");

        DB::unprepared("CREATE SEQUENCE users_seq START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE");

        DB::unprepared("
            CREATE OR REPLACE TRIGGER users_trigger
            BEFORE INSERT ON users
            FOR EACH ROW
            WHEN (NEW.user_id IS NULL)
            BEGIN
                :NEW.user_id := users_seq.NEXTVAL;
            END;
        ");

        // ─────────────────────────────────────────────────────────────────
        // 3. LOGIN_LOGS TABLE
        // ─────────────────────────────────────────────────────────────────
        DB::unprepared("
            CREATE TABLE login_logs (
                log_id        NUMBER        NOT NULL,
                user_id       NUMBER        NOT NULL,
                login_time    TIMESTAMP     DEFAULT SYSTIMESTAMP,
                login_status  VARCHAR2(10)  DEFAULT 'SUCCESS',
                ip_address    VARCHAR2(45),
                CONSTRAINT pk_login_logs       PRIMARY KEY (log_id),
                CONSTRAINT fk_logs_user        FOREIGN KEY (user_id) REFERENCES users(user_id),
                CONSTRAINT chk_login_status    CHECK (login_status IN ('SUCCESS', 'FAILED'))
            )
        ");

        DB::unprepared("CREATE SEQUENCE login_logs_seq START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE");

        DB::unprepared("
            CREATE OR REPLACE TRIGGER login_logs_trigger
            BEFORE INSERT ON login_logs
            FOR EACH ROW
            WHEN (NEW.log_id IS NULL)
            BEGIN
                :NEW.log_id := login_logs_seq.NEXTVAL;
            END;
        ");

        // ─────────────────────────────────────────────────────────────────
        // 4. TOLL_TRANSACTIONS TABLE
        // ─────────────────────────────────────────────────────────────────
        DB::unprepared("
            CREATE TABLE toll_transactions (
                transaction_id  NUMBER          NOT NULL,
                vehicle_number  VARCHAR2(20)    NOT NULL,
                vehicle_type    VARCHAR2(20)    NOT NULL,
                toll_amount     NUMBER(10, 2)   NOT NULL,
                payment_method  VARCHAR2(10)    NOT NULL,
                operator_id     NUMBER          NOT NULL,
                created_at      TIMESTAMP       DEFAULT SYSTIMESTAMP,
                CONSTRAINT pk_toll_transactions     PRIMARY KEY (transaction_id),
                CONSTRAINT fk_txn_operator          FOREIGN KEY (operator_id) REFERENCES users(user_id),
                CONSTRAINT chk_vehicle_type         CHECK (vehicle_type IN ('Bike', 'Car', 'Bus', 'Truck')),
                CONSTRAINT chk_payment_method       CHECK (payment_method IN ('Cash', 'Card', 'RFID'))
            )
        ");

        DB::unprepared("CREATE SEQUENCE toll_transactions_seq START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE");

        DB::unprepared("
            CREATE OR REPLACE TRIGGER toll_transactions_trigger
            BEFORE INSERT ON toll_transactions
            FOR EACH ROW
            WHEN (NEW.transaction_id IS NULL)
            BEGIN
                :NEW.transaction_id := toll_transactions_seq.NEXTVAL;
            END;
        ");

        // ─────────────────────────────────────────────────────────────────
        // 5. TRIGGER: trg_user_login
        //    Fires after a successful login (last_login is updated) and
        //    automatically inserts a SUCCESS record into login_logs.
        // ─────────────────────────────────────────────────────────────────
        DB::unprepared("
            CREATE OR REPLACE TRIGGER trg_user_login
            AFTER UPDATE OF last_login ON users
            FOR EACH ROW
            WHEN (NEW.last_login IS NOT NULL)
            BEGIN
                INSERT INTO login_logs (user_id, login_time, login_status, ip_address)
                VALUES (:NEW.user_id, :NEW.last_login, 'SUCCESS', '127.0.0.1');
            END;
        ");

        // ─────────────────────────────────────────────────────────────────
        // 6. FUNCTION: get_user_role
        //    Returns the role name for a given user_id.
        // ─────────────────────────────────────────────────────────────────
        DB::unprepared("
            CREATE OR REPLACE FUNCTION get_user_role(p_user_id IN NUMBER)
            RETURN VARCHAR2
            IS
                v_role_name VARCHAR2(50);
            BEGIN
                SELECT r.role_name
                INTO   v_role_name
                FROM   users u
                JOIN   roles r ON u.role_id = r.role_id
                WHERE  u.user_id = p_user_id;

                RETURN v_role_name;
            EXCEPTION
                WHEN NO_DATA_FOUND THEN
                    RETURN NULL;
            END;
        ");

        // ─────────────────────────────────────────────────────────────────
        // 7. PROCEDURE: validate_login
        //    Checks if a username exists and is ACTIVE, returns a message.
        // ─────────────────────────────────────────────────────────────────
        DB::unprepared("
            CREATE OR REPLACE PROCEDURE validate_login(
                p_username IN  VARCHAR2,
                p_result   OUT VARCHAR2
            )
            IS
                v_status        VARCHAR2(10);
                v_attempts      NUMBER;
            BEGIN
                SELECT status, login_attempts
                INTO   v_status, v_attempts
                FROM   users
                WHERE  username = p_username;

                IF v_status = 'ACTIVE' THEN
                    p_result := 'User ' || p_username || ' is ACTIVE with ' || v_attempts || ' failed attempt(s).';
                ELSE
                    p_result := 'User ' || p_username || ' is INACTIVE (locked after failed attempts).';
                END IF;
            EXCEPTION
                WHEN NO_DATA_FOUND THEN
                    p_result := 'Error: Username ''' || p_username || ''' does not exist in TollDatabase.';
            END;
        ");
    }

    /**
     * Drop all TollDatabase schema objects (reverse migration).
     */
    public function down(): void
    {
        // Drop PL/SQL objects first
        foreach (['get_user_role'] as $fn) {
            try { DB::unprepared("DROP FUNCTION {$fn}"); } catch (\Exception $e) {}
        }
        foreach (['validate_login'] as $proc) {
            try { DB::unprepared("DROP PROCEDURE {$proc}"); } catch (\Exception $e) {}
        }
        foreach (['trg_user_login', 'toll_transactions_trigger', 'login_logs_trigger', 'users_trigger', 'roles_trigger'] as $trg) {
            try { DB::unprepared("DROP TRIGGER {$trg}"); } catch (\Exception $e) {}
        }

        // Drop tables (child first to respect FK constraints)
        foreach (['toll_transactions', 'login_logs', 'users', 'roles'] as $table) {
            try { DB::unprepared("DROP TABLE {$table} CASCADE CONSTRAINTS"); } catch (\Exception $e) {}
        }

        // Drop sequences
        foreach (['toll_transactions_seq', 'login_logs_seq', 'users_seq', 'roles_seq'] as $seq) {
            try { DB::unprepared("DROP SEQUENCE {$seq}"); } catch (\Exception $e) {}
        }
    }
};
