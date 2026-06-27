<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TollTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDO;

class TollController extends Controller
{
    // Rates configuration
    private $rates = [
        'Bike' => 50.00,
        'Car' => 100.00,
        'Microbus' => 150.00,
        'Ambulance' => 80.00,
        'Bus' => 250.00,
        'Truck' => 350.00,
    ];

    /**
     * Display public homepage with live stats, pricing, and transactions.
     */
    public function home()
    {
        // 1. Get recent transactions with operator details using a JOIN
        $transactions = DB::select("
            SELECT t.transaction_id, t.vehicle_number, t.vehicle_type, t.toll_amount, t.payment_method, t.created_at, u.name AS operator_name
            FROM toll_transactions t
            LEFT JOIN users u ON t.operator_id = u.user_id
            ORDER BY t.created_at DESC
            FETCH FIRST 10 ROWS ONLY
        ");

        // 2. Fetch statistics
        $stats = DB::selectOne("
            SELECT 
                COUNT(*) AS total_vehicles,
                NVL(SUM(toll_amount), 0) AS total_revenue,
                COUNT(CASE WHEN payment_method = 'Cash' THEN 1 END) AS cash_count,
                COUNT(CASE WHEN payment_method = 'Card' THEN 1 END) AS card_count,
                COUNT(CASE WHEN payment_method = 'RFID' THEN 1 END) AS rfid_count
            FROM toll_transactions
        ");

        $rates = $this->rates;

        return view('home', compact('transactions', 'stats', 'rates'));
    }

    /**
     * Member Dashboard.
     */
    public function operatorDashboard()
    {
        $user = Auth::user();
        if (!$user || (!$user->isOperator() && !$user->isAdmin())) {
            return redirect('/')->with('error', 'Unauthorized access.');
        }

        // Fetch recent transactions recorded by this operator using parameter binding
        $operatorTransactions = DB::select("
            SELECT transaction_id, vehicle_number, vehicle_type, toll_amount, payment_method, created_at
            FROM toll_transactions
            WHERE operator_id = :operator_id
            ORDER BY created_at DESC
        ", ['operator_id' => $user->user_id]);

        $rates = $this->rates;

        return view('operator.dashboard', compact('operatorTransactions', 'rates'));
    }

    /**
     * Insert new toll transaction (Member Privilege).
     */
    public function storeTransaction(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->isOperator() && !$user->isAdmin())) {
            return redirect('/')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'vehicle_number' => 'required|string|max:20',
            'vehicle_type' => 'required|string|in:Bike,Car,Bus,Truck,Microbus,Ambulance',
            'payment_method' => 'required|string|in:Cash,Card,RFID',
        ]);

        $vehicleNumber = strtoupper(str_replace(' ', '-', $request->input('vehicle_number')));
        $vehicleType = $request->input('vehicle_type');
        $tollAmount = $this->rates[$vehicleType];

        // 1. Vehicle Verification or Auto-Registration
        $vehicle = DB::selectOne("
            SELECT vehicle_id, status 
            FROM vehicles 
            WHERE registration_number = :reg_num
        ", ['reg_num' => $vehicleNumber]);

        if ($vehicle) {
            // Blocked vehicles are rejected
            if ($vehicle->status === 'BLOCKED') {
                return redirect()->back()->withInput()->with('error', "Vehicle {$vehicleNumber} is BLOCKED and cannot pass the toll station.");
            }
            $vehicleId = $vehicle->vehicle_id;
        } else {
            // Register vehicle automatically
            $vehicleId = DB::selectOne("SELECT vehicles_seq.NEXTVAL AS val FROM dual")->val;
            
            DB::insert("
                INSERT INTO vehicles (
                    vehicle_id, registration_number, owner_name, owner_phone,
                    vehicle_type, color, manufacturer, model, weight, registration_date, status, created_by, created_at
                )
                VALUES (
                    :vehicle_id, :reg_num, 'Auto-Registered', 'N/A',
                    :vehicle_type, 'Unknown', 'N/A', 'N/A', :weight, SYSDATE, 'ACTIVE', :created_by, SYSDATE
                )
            ", [
                'vehicle_id'          => $vehicleId,
                'reg_num'             => $vehicleNumber,
                'vehicle_type'        => strtoupper($vehicleType),
                'weight'              => 1500.00,
                'created_by'          => $user->user_id,
            ]);
        }

        // 2. Insert Toll Transaction linking to vehicle_id
        DB::insert("
            INSERT INTO toll_transactions (vehicle_number, vehicle_type, toll_amount, payment_method, operator_id, vehicle_id, created_at)
            VALUES (:vehicle_number, :vehicle_type, :toll_amount, :payment_method, :operator_id, :vehicle_id, SYSDATE)
        ", [
            'vehicle_number' => $vehicleNumber,
            'vehicle_type'   => $vehicleType,
            'toll_amount'    => $tollAmount,
            'payment_method' => $request->input('payment_method'),
            'operator_id'    => $user->user_id,
            'vehicle_id'     => $vehicleId,
        ]);

        return redirect()->route('operator.dashboard')->with('success', 'Toll transaction registered successfully!');
    }

    /**
     * Admin Dashboard.
     */
    public function adminDashboard()
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return redirect('/')->with('error', 'Unauthorized access.');
        }

        // Fetch all users with their roles using a JOIN query
        $users = DB::select("
            SELECT u.user_id, u.name, u.username, u.email, u.status, u.login_attempts, u.last_login, u.created_at, r.role_name
            FROM users u
            JOIN roles r ON u.role_id = r.role_id
            ORDER BY u.user_id ASC
        ");

        // Fetch all login logs using a JOIN
        $loginLogs = DB::select("
            SELECT l.log_id, l.login_time, l.login_status, l.ip_address, u.username
            FROM login_logs l
            JOIN users u ON l.user_id = u.user_id
            ORDER BY l.login_time DESC
            FETCH FIRST 20 ROWS ONLY
        ");

        return view('admin.dashboard', compact('users', 'loginLogs'));
    }

    /**
     * Unlock / Reactivate user (Admin action).
     */
    public function unlockUser($user_id)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return redirect('/')->with('error', 'Unauthorized.');
        }

        // Reset attempts and set status to ACTIVE using Oracle UPDATE query
        DB::update("
            UPDATE users 
            SET login_attempts = 0, status = 'ACTIVE' 
            WHERE user_id = :user_id
        ", ['user_id' => $user_id]);

        return redirect()->route('admin.dashboard')->with('success', 'User account unlocked and status set to ACTIVE.');
    }

    /**
     * Show PL/SQL Playground page.
     */
    public function showPlayground()
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return redirect('/')->with('error', 'Unauthorized.');
        }

        return view('admin.playground');
    }

    /**
     * Execute Lab PL/SQL Procedure.
     */
    public function runProcedure(Request $request)
    {
        $request->validate(['username' => 'required|string']);
        $username = $request->input('username');
        $result = '';

        try {
            // Retrieve PDO connection
            $pdo = DB::connection()->getPdo();
            
            // Execute the validate_login procedure
            $stmt = $pdo->prepare("
                DECLARE 
                    p_result VARCHAR2(100); 
                BEGIN 
                    validate_login(:username, p_result); 
                    :result := p_result; 
                END;
            ");
            
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':result', $result, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 100);
            $stmt->execute();

            return back()->with([
                'proc_result' => [
                    'username' => $username,
                    'result'   => $result,
                ]
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['proc_err' => $e->getMessage()]);
        }
    }

    /**
     * Execute Lab PL/SQL Function.
     */
    public function runFunction(Request $request)
    {
        $request->validate(['user_id' => 'required|integer']);
        $userId = $request->input('user_id');

        try {
            // Execute the get_user_role function using dual table
            $res = DB::selectOne("
                SELECT get_user_role(:user_id) AS role_name 
                FROM dual
            ", ['user_id' => $userId]);

            $roleName = $res ? $res->role_name : 'No role found or invalid User ID';

            return back()->with([
                'func_result' => [
                    'user_id' => $userId,
                    'result'  => $roleName,
                ]
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['func_err' => $e->getMessage()]);
        }
    }

    /**
     * Execute PL/SQL Loop + Control block and fetch DBMS_OUTPUT.
     */
    public function runLoop()
    {
        try {
            $pdo = DB::connection()->getPdo();
            
            // 1. Enable DBMS_OUTPUT
            $pdo->exec("BEGIN DBMS_OUTPUT.ENABLE(20000); END;");
            
            // 2. Run the Loop block
            $pdo->exec("
                DECLARE
                    v_name VARCHAR2(100);
                BEGIN
                    FOR rec IN (SELECT * FROM users) LOOP
                        IF rec.status = 'ACTIVE' THEN
                            DBMS_OUTPUT.PUT_LINE(rec.username || ' is active');
                        END IF;
                    END LOOP;
                END;
            ");
            
            // 3. Fetch logs from buffer
            $output = $this->getDbmsOutput($pdo);

            return back()->with([
                'loop_result' => [
                    'logs' => $output,
                ]
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['loop_err' => $e->getMessage()]);
        }
    }

    /**
     * Execute PL/SQL Record Example and fetch DBMS_OUTPUT.
     */
    public function runRecord(Request $request)
    {
        $request->validate(['record_user_id' => 'required|integer']);
        $userId = $request->input('record_user_id');

        try {
            $pdo = DB::connection()->getPdo();
            
            // 1. Enable DBMS_OUTPUT
            $pdo->exec("BEGIN DBMS_OUTPUT.ENABLE(20000); END;");
            
            // 2. Run the Record block
            $stmt = $pdo->prepare("
                DECLARE
                    u_rec users%ROWTYPE;
                BEGIN
                    SELECT * INTO u_rec FROM users WHERE user_id = :user_id;
                    DBMS_OUTPUT.PUT_LINE('Name: ' || u_rec.name || ' | Email: ' || u_rec.email || ' | Status: ' || u_rec.status);
                EXCEPTION
                    WHEN NO_DATA_FOUND THEN
                        DBMS_OUTPUT.PUT_LINE('Error: User with ID ' || :user_id || ' not found.');
                END;
            ");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            // 3. Fetch logs from buffer
            $output = $this->getDbmsOutput($pdo);

            return back()->with([
                'record_result' => [
                    'logs' => $output,
                ]
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['record_err' => $e->getMessage()]);
        }
    }

    /**
     * Helper to read DBMS_OUTPUT lines.
     */
    private function getDbmsOutput($pdo)
    {
        $lines = [];
        $status = 0;
        $line = '';

        // DBMS_OUTPUT.GET_LINE requires variables to bind to
        $stmt = $pdo->prepare("BEGIN DBMS_OUTPUT.GET_LINE(:line, :status); END;");
        $stmt->bindParam(':line', $line, PDO::PARAM_STR, 1000);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);

        do {
            $stmt->execute();
            if ($status == 0) {
                $lines[] = $line;
            }
        } while ($status == 0);

        return $lines;
    }
}
