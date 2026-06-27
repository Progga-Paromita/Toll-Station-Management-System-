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
        DB::statement('UPDATE toll_stations SET created_by = NULL');
        DB::statement('DELETE FROM toll_transactions');
        DB::statement('DELETE FROM vehicles');
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
        $adminId = DB::table('users')->where('username', 'admin')->value('user_id');
        $johnId = DB::table('users')->where('username', 'john')->value('user_id');
        $janeId = DB::table('users')->where('username', 'jane')->value('user_id');

        // Re-associate toll stations with the seeded admin user
        DB::statement('UPDATE toll_stations SET created_by = :admin_id', ['admin_id' => $adminId]);

        // ─────────────────────────────────────────────────────────────────────
        // 2.5 VEHICLES (Master Data)
        // ─────────────────────────────────────────────────────────────────────
        DB::table('vehicles')->insert([
            [
                'registration_number' => 'DHK-12-3456',
                'owner_name'          => 'Karim Rahman',
                'owner_phone'         => '01711223344',
                'vehicle_type'        => 'CAR',
                'color'               => 'Black',
                'manufacturer'        => 'Toyota',
                'model'               => 'Premio',
                'weight'              => 1500,
                'registration_date'   => now()->subMonths(6),
                'status'              => 'ACTIVE',
                'created_by'          => $adminId,
                'created_at'          => now()->subMonths(6),
            ],
            [
                'registration_number' => 'CTG-98-7654',
                'owner_name'          => 'Rahim Transport Ltd.',
                'owner_phone'         => '01822334455',
                'vehicle_type'        => 'TRUCK',
                'color'               => 'Blue',
                'manufacturer'        => 'Tata',
                'model'               => 'LPT 1615',
                'weight'              => 8500,
                'registration_date'   => now()->subMonths(12),
                'status'              => 'ACTIVE',
                'created_by'          => $adminId,
                'created_at'          => now()->subMonths(12),
            ],
            [
                'registration_number' => 'SYL-11-2233',
                'owner_name'          => 'Sajjad Hosen',
                'owner_phone'         => '01933445566',
                'vehicle_type'        => 'BIKE',
                'color'               => 'Red',
                'manufacturer'        => 'Yamaha',
                'model'               => 'FZ-S',
                'weight'              => 135,
                'registration_date'   => now()->subMonths(3),
                'status'              => 'ACTIVE',
                'created_by'          => $adminId,
                'created_at'          => now()->subMonths(3),
            ],
            [
                'registration_number' => 'RAJ-55-6677',
                'owner_name'          => 'Hanif Enterprise',
                'owner_phone'         => '01544556677',
                'vehicle_type'        => 'BUS',
                'color'               => 'Green',
                'manufacturer'        => 'Hino',
                'model'               => 'AK1J',
                'weight'              => 11000,
                'registration_date'   => now()->subMonths(8),
                'status'              => 'ACTIVE',
                'created_by'          => $adminId,
                'created_at'          => now()->subMonths(8),
            ],
            [
                'registration_number' => 'DHK-99-9999',
                'owner_name'          => 'Selim Reza',
                'owner_phone'         => '01355667788',
                'vehicle_type'        => 'MICROBUS',
                'color'               => 'Silver',
                'manufacturer'        => 'Toyota',
                'model'               => 'HiAce',
                'weight'              => 2200,
                'registration_date'   => now()->subMonths(4),
                'status'              => 'BLOCKED',
                'created_by'          => $adminId,
                'created_at'          => now()->subMonths(4),
            ],
            [
                'registration_number' => 'DHK-00-1122',
                'owner_name'          => 'Square Hospital',
                'owner_phone'         => '01766778899',
                'vehicle_type'        => 'AMBULANCE',
                'color'               => 'White',
                'manufacturer'        => 'Hyundai',
                'model'               => 'H1',
                'weight'              => 2400,
                'registration_date'   => now()->subMonths(2),
                'status'              => 'ACTIVE',
                'created_by'          => $adminId,
                'created_at'          => now()->subMonths(2),
            ],
            [
                'registration_number' => 'DHK-88-8888',
                'owner_name'          => 'Nabil Hasan',
                'owner_phone'         => '01877889900',
                'vehicle_type'        => 'CAR',
                'color'               => 'Grey',
                'manufacturer'        => 'Honda',
                'model'               => 'Civic',
                'weight'              => 1400,
                'registration_date'   => now()->subMonths(1),
                'status'              => 'EXPIRED',
                'created_by'          => $adminId,
                'created_at'          => now()->subMonths(1),
            ],
        ]);

        // Fetch vehicle IDs
        $carId       = DB::table('vehicles')->where('registration_number', 'DHK-12-3456')->value('vehicle_id');
        $truckId     = DB::table('vehicles')->where('registration_number', 'CTG-98-7654')->value('vehicle_id');
        $bikeId      = DB::table('vehicles')->where('registration_number', 'SYL-11-2233')->value('vehicle_id');
        $busId       = DB::table('vehicles')->where('registration_number', 'RAJ-55-6677')->value('vehicle_id');

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
                'vehicle_id'     => $carId,
                'created_at'     => now()->subMinutes(30),
            ],
            [
                'vehicle_number' => 'CTG-98-7654',
                'vehicle_type'   => 'Truck',
                'toll_amount'    => 350.00,
                'payment_method' => 'Card',
                'operator_id'    => $johnId,
                'vehicle_id'     => $truckId,
                'created_at'     => now()->subMinutes(20),
            ],
            [
                'vehicle_number' => 'SYL-11-2233',
                'vehicle_type'   => 'Bike',
                'toll_amount'    => 50.00,
                'payment_method' => 'RFID',
                'operator_id'    => $janeId,
                'vehicle_id'     => $bikeId,
                'created_at'     => now()->subMinutes(15),
            ],
            [
                'vehicle_number' => 'RAJ-55-6677',
                'vehicle_type'   => 'Bus',
                'toll_amount'    => 250.00,
                'payment_method' => 'Cash',
                'operator_id'    => $johnId,
                'vehicle_id'     => $busId,
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
