<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{
    /**
     * Display a listing of the vehicles.
     * Demonstrates: SELECT, WHERE, LIKE, ORDER BY
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/login')->with('error', 'Please log in first.');
        }

        $search       = $request->input('search', '');
        $vehicleType  = $request->input('vehicle_type', '');
        $status       = $request->input('status', '');
        $manufacturer = $request->input('manufacturer', '');
        $sort         = $request->input('sort', 'newest');

        // Build base query
        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = "(LOWER(registration_number) LIKE LOWER(:search) OR LOWER(owner_name) LIKE LOWER(:search_owner))";
            $params['search'] = '%' . $search . '%';
            $params['search_owner'] = '%' . $search . '%';
        }

        if ($vehicleType !== '') {
            $where[] = "vehicle_type = :vehicle_type";
            $params['vehicle_type'] = strtoupper($vehicleType);
        }

        if ($status !== '') {
            $where[] = "status = :status";
            $params['status'] = strtoupper($status);
        }

        if ($manufacturer !== '') {
            $where[] = "LOWER(manufacturer) = LOWER(:manufacturer)";
            $params['manufacturer'] = $manufacturer;
        }

        $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

        // Sorting map
        $sortMap = [
            'registration_number_asc'  => 'registration_number ASC',
            'registration_number_desc' => 'registration_number DESC',
            'owner_name_asc'           => 'owner_name ASC',
            'owner_name_desc'          => 'owner_name DESC',
            'weight_asc'               => 'weight ASC',
            'weight_desc'              => 'weight DESC',
            'newest'                   => 'created_at DESC',
            'oldest'                   => 'created_at ASC',
        ];

        $orderClause = 'ORDER BY ' . ($sortMap[$sort] ?? 'created_at DESC');

        // SQL Query
        $vehicles = DB::select("
            SELECT v.*, u.name AS creator_name
            FROM vehicles v
            LEFT JOIN users u ON v.created_by = u.user_id
            {$whereClause}
            {$orderClause}
        ", $params);

        // Fetch filter dropdown options
        $manufacturers = DB::select("SELECT DISTINCT manufacturer FROM vehicles WHERE manufacturer IS NOT NULL ORDER BY manufacturer ASC");
        $types = DB::select("SELECT DISTINCT vehicle_type FROM vehicles ORDER BY vehicle_type ASC");

        return view('admin.vehicles.index', compact(
            'vehicles',
            'search',
            'vehicleType',
            'status',
            'manufacturer',
            'sort',
            'manufacturers',
            'types'
        ));
    }

    /**
     * Show the form for creating a new vehicle.
     */
    public function create()
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isOperator())) {
            return redirect()->route('vehicles.index')->with('error', 'Unauthorized access.');
        }

        return view('admin.vehicles.create');
    }

    /**
     * Store a newly created vehicle.
     * Demonstrates: INSERT, NOT NULL, CHECK constraints
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isOperator())) {
            return redirect()->route('vehicles.index')->with('error', 'Unauthorized.');
        }

        $data = $request->validate([
            'registration_number' => 'required|string|max:20|unique:vehicles,registration_number',
            'owner_name'          => 'required|string|max:100',
            'owner_phone'         => 'required|string|max:15',
            'vehicle_type'        => 'required|string|in:Car,Bus,Truck,Bike,Microbus,Ambulance,CAR,BUS,TRUCK,BIKE,MICROBUS,AMBULANCE',
            'color'               => 'required|string|max:30',
            'manufacturer'        => 'nullable|string|max:50',
            'model'               => 'nullable|string|max:50',
            'weight'              => 'required|numeric|min:0.01',
            'status'              => 'required|string|in:Active,Blocked,Expired,ACTIVE,BLOCKED,EXPIRED',
        ]);

        $regNum = strtoupper(str_replace(' ', '-', $data['registration_number']));
        $vType = strtoupper($data['vehicle_type']);
        $status = strtoupper($data['status']);

        // Insert using raw SQL to show SQL INSERT concept
        DB::insert("
            INSERT INTO vehicles (
                registration_number, owner_name, owner_phone, vehicle_type, 
                color, manufacturer, model, weight, registration_date, status, created_by, created_at
            )
            VALUES (
                :registration_number, :owner_name, :owner_phone, :vehicle_type, 
                :color, :manufacturer, :model, :weight, SYSDATE, :status, :created_by, SYSDATE
            )
        ", [
            'registration_number' => $regNum,
            'owner_name'          => $data['owner_name'],
            'owner_phone'         => $data['owner_phone'],
            'vehicle_type'        => $vType,
            'color'               => $data['color'],
            'manufacturer'        => $data['manufacturer'] ?? null,
            'model'               => $data['model'] ?? null,
            'weight'              => $data['weight'],
            'status'              => $status,
            'created_by'          => $user->user_id,
        ]);

        return redirect()->route('vehicles.index')->with('success', "Vehicle {$regNum} registered successfully!");
    }

    /**
     * Display the specified vehicle.
     * Demonstrates: SELECT, Joins, Aggregate functions (COUNT, SUM)
     */
    public function show($id)
    {
        $vehicle = DB::selectOne("
            SELECT v.*, u.name AS creator_name
            FROM vehicles v
            LEFT JOIN users u ON v.created_by = u.user_id
            WHERE v.vehicle_id = :id
        ", ['id' => $id]);

        if (!$vehicle) {
            return redirect()->route('vehicles.index')->with('error', 'Vehicle not found.');
        }

        // Fetch transaction statistics using aggregate functions
        $stats = DB::selectOne("
            SELECT COUNT(*) AS total_txns, NVL(SUM(toll_amount), 0) AS total_paid
            FROM toll_transactions
            WHERE vehicle_id = :vehicle_id
        ", ['vehicle_id' => $id]);

        // Fetch recent transactions for this vehicle
        $transactions = DB::select("
            SELECT t.*, u.name AS operator_name
            FROM toll_transactions t
            LEFT JOIN users u ON t.operator_id = u.user_id
            WHERE t.vehicle_id = :vehicle_id
            ORDER BY t.created_at DESC
            FETCH FIRST 10 ROWS ONLY
        ", ['vehicle_id' => $id]);

        return view('admin.vehicles.show', compact('vehicle', 'stats', 'transactions'));
    }

    /**
     * Show the edit form.
     */
    public function edit($id)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return redirect()->route('vehicles.index')->with('error', 'Unauthorized access. Only Admins can modify vehicle details.');
        }

        $vehicle = DB::selectOne("SELECT * FROM vehicles WHERE vehicle_id = :id", ['id' => $id]);
        if (!$vehicle) {
            return redirect()->route('vehicles.index')->with('error', 'Vehicle not found.');
        }

        return view('admin.vehicles.edit', compact('vehicle'));
    }

    /**
     * Update the vehicle.
     * Demonstrates: UPDATE
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return redirect()->route('vehicles.index')->with('error', 'Unauthorized.');
        }

        $data = $request->validate([
            'owner_name'   => 'required|string|max:100',
            'owner_phone'  => 'required|string|max:15',
            'color'        => 'required|string|max:30',
            'manufacturer' => 'nullable|string|max:50',
            'model'        => 'nullable|string|max:50',
            'weight'       => 'required|numeric|min:0.01',
            'status'       => 'required|string|in:Active,Blocked,Expired,ACTIVE,BLOCKED,EXPIRED',
        ]);

        $status = strtoupper($data['status']);

        DB::update("
            UPDATE vehicles
            SET owner_name = :owner_name,
                owner_phone = :owner_phone,
                color = :color,
                manufacturer = :manufacturer,
                model = :model,
                weight = :weight,
                status = :status,
                updated_at = SYSDATE
            WHERE vehicle_id = :vehicle_id
        ", [
            'owner_name'   => $data['owner_name'],
            'owner_phone'  => $data['owner_phone'],
            'color'        => $data['color'],
            'manufacturer' => $data['manufacturer'] ?? null,
            'model'        => $data['model'] ?? null,
            'weight'       => $data['weight'],
            'status'       => $status,
            'vehicle_id'   => $id,
        ]);

        return redirect()->route('vehicles.show', $id)->with('success', 'Vehicle updated successfully!');
    }

    /**
     * Delete a vehicle.
     * Demonstrates: DELETE, Referential Integrity checks
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return redirect()->route('vehicles.index')->with('error', 'Unauthorized.');
        }

        // Referential Integrity check: check if vehicle has toll transactions
        $txCount = DB::selectOne("
            SELECT COUNT(*) AS cnt
            FROM toll_transactions
            WHERE vehicle_id = :vehicle_id
        ", ['vehicle_id' => $id])->cnt;

        if ($txCount > 0) {
            return redirect()->back()->with('error', 'Vehicle cannot be deleted because toll records exist.');
        }

        // Delete vehicle
        DB::delete("DELETE FROM vehicles WHERE vehicle_id = :vehicle_id", ['vehicle_id' => $id]);

        return redirect()->route('vehicles.index')->with('success', 'Vehicle deleted successfully.');
    }

    /**
     * Generate Reports.
     * Demonstrates: Aggregate functions (COUNT, AVG, MAX, MIN), GROUP BY, HAVING, BETWEEN, IN, NOT IN
     */
    public function report(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isViewer())) {
            return redirect()->route('vehicles.index')->with('error', 'Unauthorized access.');
        }

        // 1. General Aggregates
        $aggregates = DB::selectOne("
            SELECT 
                COUNT(*) AS total_vehicles,
                NVL(AVG(weight), 0) AS avg_weight,
                NVL(MAX(weight), 0) AS max_weight,
                NVL(MIN(weight), 0) AS min_weight,
                COUNT(CASE WHEN status = 'BLOCKED' THEN 1 END) AS blocked_count
            FROM vehicles
        ");

        // 2. Most Used Vehicle Type (Aggregate + Join)
        $mostUsedType = DB::selectOne("
            SELECT vehicle_type, COUNT(*) AS txn_count
            FROM toll_transactions
            GROUP BY vehicle_type
            ORDER BY COUNT(*) DESC
            FETCH FIRST 1 ROW ONLY
        ");

        // 3. GROUP BY and HAVING (Show vehicle counts and average weight grouped by type)
        // User can adjust HAVING count limit (default to 0)
        $havingLimit = (int) $request->input('having_limit', 0);
        $typeReports = DB::select("
            SELECT vehicle_type, COUNT(*) AS vehicle_count, AVG(weight) AS avg_weight
            FROM vehicles
            GROUP BY vehicle_type
            HAVING COUNT(*) > :having_limit
            ORDER BY vehicle_count DESC
        ", ['having_limit' => $havingLimit]);

        // Status report
        $statusReports = DB::select("
            SELECT status, COUNT(*) AS vehicle_count
            FROM vehicles
            GROUP BY status
        ");

        // 4. Advanced Selective Query Search (BETWEEN, NOT BETWEEN, IN, NOT IN, weight threshold)
        $useAdvanced = $request->has('advanced_search');
        $advancedResults = [];

        if ($useAdvanced) {
            $dateType = $request->input('date_filter_type', 'BETWEEN'); // BETWEEN or NOT_BETWEEN
            $startDate = $request->input('start_date', '2026-01-01');
            $endDate = $request->input('end_date', '2026-12-31');
            
            $typeFilterType = $request->input('type_filter_type', 'IN'); // IN or NOT_IN
            $selectedTypes = $request->input('types', ['CAR', 'BUS', 'TRUCK']);
            $weightOperator = $request->input('weight_operator', '>'); // >, <, =
            $weightVal = (float) $request->input('weight_value', 5000);

            // Construct advanced SQL safely
            $clauses = [];
            $params = [];

            // Weight filter
            if (in_array($weightOperator, ['>', '<', '='])) {
                $clauses[] = "weight {$weightOperator} :weight_val";
                $params['weight_val'] = $weightVal;
            }

            // Date BETWEEN/NOT BETWEEN filter
            $dateOp = ($dateType === 'NOT_BETWEEN') ? 'NOT BETWEEN' : 'BETWEEN';
            $clauses[] = "registration_date {$dateOp} TO_DATE(:start_date, 'YYYY-MM-DD') AND TO_DATE(:end_date, 'YYYY-MM-DD')";
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;

            // IN/NOT IN filter
            if (is_array($selectedTypes) && count($selectedTypes) > 0) {
                // Sanitize selected types and construct list
                $typePlaceholderList = [];
                foreach ($selectedTypes as $idx => $t) {
                    $key = 'type_' . $idx;
                    $typePlaceholderList[] = ':' . $key;
                    $params[$key] = strtoupper($t);
                }
                $typeInOp = ($typeFilterType === 'NOT_IN') ? 'NOT IN' : 'IN';
                $clauses[] = "vehicle_type {$typeInOp} (" . implode(', ', $typePlaceholderList) . ")";
            }

            $whereClause = count($clauses) > 0 ? 'WHERE ' . implode(' AND ', $clauses) : '';

            $advancedResults = DB::select("
                SELECT vehicle_id, registration_number, owner_name, vehicle_type, weight, registration_date, status
                FROM vehicles
                {$whereClause}
                ORDER BY registration_date DESC
            ", $params);
        }

        return view('admin.vehicles.report', compact(
            'aggregates',
            'mostUsedType',
            'typeReports',
            'statusReports',
            'havingLimit',
            'useAdvanced',
            'advancedResults'
        ));
    }
}
