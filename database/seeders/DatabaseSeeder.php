<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the TollDatabase schema with initial roles, users, and sample transactions.
     *
     * Safe to re-run: existing data is deleted (cascade) and re-inserted cleanly.
     * Tables are seeded in dependency order:
     *   1. roles  →  2. users  →  3. toll_transactions
     * (login_logs are auto-populated by the trg_user_login trigger on successful logins)
     */
    public function run(): void
    {
        // ── Purge existing data in reverse FK order ───────────────────────────
        DB::statement('DELETE FROM toll_transactions');
        DB::statement('DELETE FROM login_logs');
        DB::statement('DELETE FROM users');
        DB::statement('DELETE FROM roles');

        // ─────────────────────────────────────────────────────────────────────
        // 1. ROLES
        // ─────────────────────────────────────────────────────────────────────
        DB::table('roles')->insert([
            [
                'role_name'   => 'ADMIN',
                'description' => 'Admin: full control over the system',
                'created_at'  => now(),
            ],
            [
                'role_name'   => 'OPERATOR',
                'description' => 'Member: insert and select toll transactions',
                'created_at'  => now(),
            ],
            [
                'role_name'   => 'VIEWER',
                'description' => 'Viewer: read-only access to open features',
                'created_at'  => now(),
            ],
        ]);

        // Retrieve auto-generated role IDs
        $adminRoleId    = DB::table('roles')->where('role_name', 'ADMIN')->value('role_id');
        $operatorRoleId = DB::table('roles')->where('role_name', 'OPERATOR')->value('role_id');

        // ─────────────────────────────────────────────────────────────────────
        // 2. USERS
        // ─────────────────────────────────────────────────────────────────────
        DB::table('users')->insert([
            [
                'name'           => 'System Admin',
                'username'       => 'admin',
                'email'          => 'admin@tollstation.com',
                'password'       => Hash::make('admin123'),
                'role_id'        => $adminRoleId,
                'status'         => 'ACTIVE',
                'login_attempts' => 0,
                'last_login'     => null,
                'created_at'     => now(),
            ],
            [
                'name'           => 'John Member',
                'username'       => 'john',
                'email'          => 'john@tollstation.com',
                'password'       => Hash::make('operator123'),
                'role_id'        => $operatorRoleId,
                'status'         => 'ACTIVE',
                'login_attempts' => 0,
                'last_login'     => null,
                'created_at'     => now(),
            ],
            [
                'name'           => 'Jane Member',
                'username'       => 'jane',
                'email'          => 'jane@tollstation.com',
                'password'       => Hash::make('operator123'),
                'role_id'        => $operatorRoleId,
                'status'         => 'ACTIVE',
                'login_attempts' => 0,
                'last_login'     => null,
                'created_at'     => now(),
            ],
        ]);

        // Retrieve auto-generated user IDs
        $johnId = DB::table('users')->where('username', 'john')->value('user_id');
        $janeId = DB::table('users')->where('username', 'jane')->value('user_id');

        // ─────────────────────────────────────────────────────────────────────
        // 3. SAMPLE TOLL TRANSACTIONS
        // ─────────────────────────────────────────────────────────────────────
        DB::table('toll_transactions')->insert([
            [
                'vehicle_number' => 'DHK-12-3456',
                'vehicle_type'   => 'Car',
                'toll_amount'    => 100.00,
                'payment_method' => 'Cash',
                'operator_id'    => $johnId,
                'created_at'     => now()->subMinutes(30),
            ],
            [
                'vehicle_number' => 'CTG-98-7654',
                'vehicle_type'   => 'Truck',
                'toll_amount'    => 350.00,
                'payment_method' => 'Card',
                'operator_id'    => $johnId,
                'created_at'     => now()->subMinutes(20),
            ],
            [
                'vehicle_number' => 'SYL-11-2233',
                'vehicle_type'   => 'Bike',
                'toll_amount'    => 50.00,
                'payment_method' => 'RFID',
                'operator_id'    => $janeId,
                'created_at'     => now()->subMinutes(15),
            ],
            [
                'vehicle_number' => 'RAJ-55-6677',
                'vehicle_type'   => 'Bus',
                'toll_amount'    => 250.00,
                'payment_method' => 'Cash',
                'operator_id'    => $johnId,
                'created_at'     => now()->subMinutes(5),
            ],
        ]);

        // ─────────────────────────────────────────────────────────────────────
        // 4. ORACLE DB ROLES (optional, idempotent — ignored if already exist)
        // ─────────────────────────────────────────────────────────────────────
        foreach (['ADMIN_ROLE', 'OPERATOR_ROLE', 'VIEWER_ROLE'] as $dbRole) {
            try {
                DB::unprepared("CREATE ROLE {$dbRole}");
            } catch (\Exception $e) {
                // Role already exists — safe to ignore
            }
        }
    }
}
