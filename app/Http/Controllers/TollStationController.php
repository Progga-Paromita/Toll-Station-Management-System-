<?php

namespace App\Http\Controllers;

use App\Models\TollStation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TollStationController extends Controller
{
    /**
     * Display the station list with search, filter, and sort.
     * Demonstrates: SELECT, WHERE, LIKE, ORDER BY
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return redirect('/')->with('error', 'Unauthorized access.');
        }

        $search   = $request->input('search', '');
        $district = $request->input('district', '');
        $status   = $request->input('status', '');
        $type     = $request->input('type', '');
        $sort     = $request->input('sort', 'created_at_desc');

        // Build dynamic Oracle SQL with WHERE / LIKE / ORDER BY
        $where  = [];
        $params = [];

        if ($search !== '') {
            $where[]              = "LOWER(station_name) LIKE LOWER(:search)";
            $params['search']     = '%' . $search . '%';
        }
        if ($district !== '') {
            $where[]              = "LOWER(district) = LOWER(:district)";
            $params['district']   = $district;
        }
        if ($status !== '') {
            $where[]              = "status = :status";
            $params['status']     = $status;
        }
        if ($type !== '') {
            $where[]              = "station_type = :type";
            $params['type']       = $type;
        }

        $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

        $orderMap = [
            'name_asc'          => 'station_name ASC',
            'name_desc'         => 'station_name DESC',
            'lanes_asc'         => 'lane_count ASC',
            'lanes_desc'        => 'lane_count DESC',
            'created_at_asc'    => 'created_at ASC',
            'created_at_desc'   => 'created_at DESC',
            'opening_date_asc'  => 'opening_date ASC',
            'opening_date_desc' => 'opening_date DESC',
        ];
        $orderClause = 'ORDER BY ' . ($orderMap[$sort] ?? 'created_at DESC');

        $stations = DB::select("
            SELECT s.station_id, s.station_name, s.district, s.highway,
                   s.lane_count, s.station_type, s.opening_date,
                   s.status, s.created_at, s.updated_at,
                   u.name AS created_by_name
            FROM toll_stations s
            LEFT JOIN users u ON s.created_by = u.user_id
            {$whereClause}
            {$orderClause}
        ", $params);

        // Fetch distinct districts for filter dropdown
        $districts = DB::select("SELECT DISTINCT district FROM toll_stations ORDER BY district ASC");

        // Summary stats
        $stats = DB::selectOne("
            SELECT
                COUNT(*) AS total,
                COUNT(CASE WHEN status = 'ACTIVE' THEN 1 END) AS active,
                COUNT(CASE WHEN status = 'INACTIVE' THEN 1 END) AS inactive,
                COUNT(CASE WHEN status = 'UNDER_MAINTENANCE' THEN 1 END) AS maintenance,
                NVL(SUM(lane_count), 0) AS total_lanes
            FROM toll_stations
        ");

        return view('admin.stations.index', compact(
            'stations', 'districts', 'stats',
            'search', 'district', 'status', 'type', 'sort'
        ));
    }

    /**
     * Show the create station form.
     */
    public function create()
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return redirect('/')->with('error', 'Unauthorized access.');
        }

        return view('admin.stations.create');
    }

    /**
     * Store a new station in Oracle DB.
     * Demonstrates: INSERT, UNIQUE check, NOT NULL, CHECK constraints
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return redirect('/')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'station_name' => 'required|string|max:100',
            'district'     => 'required|string|max:100',
            'highway'      => 'required|string|max:150',
            'lane_count'   => 'required|integer|min:1',
            'station_type' => 'required|string|in:Bridge,Highway,Expressway',
            'opening_date' => 'required|date',
            'status'       => 'required|string|in:ACTIVE,INACTIVE,UNDER_MAINTENANCE',
        ]);

        // Check for duplicate station name (Laravel-level, Oracle UNIQUE also protects)
        $existing = DB::selectOne("
            SELECT station_id FROM toll_stations
            WHERE LOWER(station_name) = LOWER(:name)
        ", ['name' => $request->input('station_name')]);

        if ($existing) {
            return back()
                ->withErrors(['station_name' => 'A station with this name already exists.'])
                ->withInput();
        }

        try {
            DB::insert("
                INSERT INTO toll_stations
                    (station_name, district, highway, lane_count, station_type,
                     opening_date, status, created_by, created_at)
                VALUES
                    (:station_name, :district, :highway, :lane_count, :station_type,
                     TO_DATE(:opening_date, 'YYYY-MM-DD'), :status, :created_by, SYSDATE)
            ", [
                'station_name' => $request->input('station_name'),
                'district'     => $request->input('district'),
                'highway'      => $request->input('highway'),
                'lane_count'   => (int) $request->input('lane_count'),
                'station_type' => $request->input('station_type'),
                'opening_date' => $request->input('opening_date'),
                'status'       => $request->input('status'),
                'created_by'   => $user->user_id,
            ]);
        } catch (\Exception $e) {
            return back()
                ->withErrors(['station_name' => 'Database error: ' . $e->getMessage()])
                ->withInput();
        }

        return redirect()->route('admin.stations.index')
            ->with('success', 'Toll station "' . $request->input('station_name') . '" created successfully!');
    }

    /**
     * Show a single station's details.
     * Demonstrates: SELECT with WHERE and JOIN
     */
    public function show($id)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return redirect('/')->with('error', 'Unauthorized access.');
        }

        $station = DB::selectOne("
            SELECT s.*, u.name AS created_by_name
            FROM toll_stations s
            LEFT JOIN users u ON s.created_by = u.user_id
            WHERE s.station_id = :id
        ", ['id' => $id]);

        if (!$station) {
            return redirect()->route('admin.stations.index')
                ->with('error', 'Station not found.');
        }

        // Count transactions for this station (referential integrity check)
        $txCount = DB::selectOne("
            SELECT COUNT(*) AS cnt
            FROM toll_transactions
            WHERE station_id = :id
        ", ['id' => $id]);
        $transactionCount = $txCount ? (int)$txCount->cnt : 0;

        return view('admin.stations.show', compact('station', 'transactionCount'));
    }

    /**
     * Show the edit form.
     */
    public function edit($id)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return redirect('/')->with('error', 'Unauthorized access.');
        }

        $station = DB::selectOne("
            SELECT * FROM toll_stations WHERE station_id = :id
        ", ['id' => $id]);

        if (!$station) {
            return redirect()->route('admin.stations.index')
                ->with('error', 'Station not found.');
        }

        return view('admin.stations.edit', compact('station'));
    }

    /**
     * Update a station record.
     * Demonstrates: UPDATE with WHERE, UNIQUE check on edit
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return redirect('/')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'station_name' => 'required|string|max:100',
            'district'     => 'required|string|max:100',
            'highway'      => 'required|string|max:150',
            'lane_count'   => 'required|integer|min:1',
            'station_type' => 'required|string|in:Bridge,Highway,Expressway',
            'opening_date' => 'required|date',
            'status'       => 'required|string|in:ACTIVE,INACTIVE,UNDER_MAINTENANCE',
        ]);

        // Check duplicate station name — but exclude current record
        $duplicate = DB::selectOne("
            SELECT station_id FROM toll_stations
            WHERE LOWER(station_name) = LOWER(:name)
              AND station_id != :id
        ", ['name' => $request->input('station_name'), 'id' => $id]);

        if ($duplicate) {
            return back()
                ->withErrors(['station_name' => 'Another station with this name already exists.'])
                ->withInput();
        }

        try {
            DB::update("
                UPDATE toll_stations SET
                    station_name  = :station_name,
                    district      = :district,
                    highway       = :highway,
                    lane_count    = :lane_count,
                    station_type  = :station_type,
                    opening_date  = TO_DATE(:opening_date, 'YYYY-MM-DD'),
                    status        = :status,
                    updated_at    = SYSDATE
                WHERE station_id  = :id
            ", [
                'station_name' => $request->input('station_name'),
                'district'     => $request->input('district'),
                'highway'      => $request->input('highway'),
                'lane_count'   => (int) $request->input('lane_count'),
                'station_type' => $request->input('station_type'),
                'opening_date' => $request->input('opening_date'),
                'status'       => $request->input('status'),
                'id'           => $id,
            ]);
        } catch (\Exception $e) {
            return back()
                ->withErrors(['station_name' => 'Database error: ' . $e->getMessage()])
                ->withInput();
        }

        return redirect()->route('admin.stations.index')
            ->with('success', 'Station updated successfully!');
    }

    /**
     * Delete a station — only if no toll transactions reference it.
     * Demonstrates: DELETE, referential integrity enforcement
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return redirect('/')->with('error', 'Unauthorized access.');
        }

        // Safety check: referential integrity
        $txCheck = DB::selectOne("
            SELECT COUNT(*) AS cnt
            FROM toll_transactions
            WHERE station_id = :id
        ", ['id' => $id]);

        if ($txCheck && (int)$txCheck->cnt > 0) {
            return redirect()->route('admin.stations.index')
                ->with('error', 'Cannot delete station — it has ' . $txCheck->cnt . ' associated toll transaction(s). Deactivate it instead.');
        }

        $station = DB::selectOne("SELECT station_name FROM toll_stations WHERE station_id = :id", ['id' => $id]);

        DB::delete("DELETE FROM toll_stations WHERE station_id = :id", ['id' => $id]);

        return redirect()->route('admin.stations.index')
            ->with('success', 'Station "' . ($station->station_name ?? '') . '" deleted successfully.');
    }
}
