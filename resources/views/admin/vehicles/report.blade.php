@extends('layouts.layout')

@section('title', 'TollStation - Vehicle Reports')

@section('content')

    <div class="section-header">
        <div>
            <h1 class="section-title">Vehicle Summaries & Advanced Reports</h1>
            <p class="section-desc">Aggregate statistical analysis, GROUP BY groups, HAVING parameters, and selective queries.</p>
        </div>
        <a href="{{ route('vehicles.index') }}" class="btn btn-outline btn-sm">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5m7 7-7-7 7-7"/></svg>
            Back to Vehicles
        </a>
    </div>

    {{-- Aggregate Summary Cards --}}
    <div class="stats-container" style="margin-bottom:32px;">
        <div class="stat-card">
            <div class="stat-value" style="font-size:26px; color:var(--primary);">
                {{ number_format($aggregates->total_vehicles) }}
            </div>
            <div class="stat-label">Total Vehicles (COUNT)</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="font-size:26px; color:var(--success);">
                {{ number_format($aggregates->avg_weight, 2) }} kg
            </div>
            <div class="stat-label">Average Weight (AVG)</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="font-size:26px; color:var(--warning);">
                {{ number_format($aggregates->max_weight) }} / {{ number_format($aggregates->min_weight) }} kg
            </div>
            <div class="stat-label">Weight Range (MAX / MIN)</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="font-size:26px; color:var(--danger);">
                {{ $mostUsedType->vehicle_type ?? 'N/A' }}
            </div>
            <div class="stat-label">Most Used Type ({{ $mostUsedType->txn_count ?? 0 }} txns)</div>
        </div>
    </div>

    <div class="grid-2" style="margin-bottom:32px;">
        {{-- GROUP BY & HAVING --}}
        <div class="card glass">
            <h3 class="card-title">
                <svg width="20" height="20" fill="none" stroke="var(--primary)" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Vehicles Grouped by Type (GROUP BY)
            </h3>
            <p class="card-subtitle" style="margin-bottom:20px;">Aggregated counts and weights by vehicle category. Modify the HAVING filter below.</p>

            {{-- HAVING Form --}}
            <form method="GET" action="{{ route('vehicles.report') }}" style="margin-bottom:20px; display:flex; gap:8px; align-items:flex-end;">
                <div class="form-group" style="margin-bottom:0; flex:1;">
                    <label class="form-label" style="font-size:11px;">HAVING Count Greater Than</label>
                    <input type="number" name="having_limit" class="form-control" value="{{ $havingLimit }}" min="0" style="padding:6px 12px; font-size:13px;">
                </div>
                <button type="submit" class="btn btn-primary btn-sm" style="padding:9px 16px; font-size:13px;">Apply HAVING</button>
            </form>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Vehicle Type</th>
                            <th>Total Registered</th>
                            <th>Avg Weight</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($typeReports as $tr)
                            <tr>
                                <td><span class="badge badge-info">{{ $tr->vehicle_type }}</span></td>
                                <td><strong>{{ $tr->vehicle_count }}</strong></td>
                                <td>{{ number_format($tr->avg_weight, 1) }} kg</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Grouped by Status --}}
        <div class="card glass">
            <h3 class="card-title">
                <svg width="20" height="20" fill="none" stroke="var(--primary)" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Vehicles Grouped by Status
            </h3>
            <p class="card-subtitle" style="margin-bottom:20px;">Total registered vehicles split by active verification state.</p>

            <div class="table-wrapper" style="margin-top:54px;">
                <table>
                    <thead>
                        <tr>
                            <th>Status State</th>
                            <th>Vehicle Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statusReports as $sr)
                            <tr>
                                <td>
                                    @if($sr->status === 'ACTIVE')
                                        <span class="badge badge-success">ACTIVE</span>
                                    @elseif($sr->status === 'BLOCKED')
                                        <span class="badge badge-danger">BLOCKED</span>
                                    @else
                                        <span class="badge badge-warning">EXPIRED</span>
                                    @endif
                                </td>
                                <td><strong>{{ $sr->vehicle_count }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Advanced Selective Query Panel --}}
    <div class="card glass" style="margin-bottom:32px;">
        <h3 class="card-title">
            <svg width="20" height="20" fill="none" stroke="var(--primary)" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Advanced SQL Query Form (BETWEEN, IN, Weight Calculations)
        </h3>
        <p class="card-subtitle" style="margin-bottom:24px;">
            Simulate complex analytical queries showcasing BETWEEN dates, IN/NOT IN sets, and mathematical calculations.
        </p>

        <form method="GET" action="{{ route('vehicles.report') }}">
            <input type="hidden" name="advanced_search" value="1">
            
            <div class="grid-3">
                {{-- Date Filter Range (BETWEEN) --}}
                <div class="form-group">
                    <label class="form-label">Date Filter Mode</label>
                    <select name="date_filter_type" class="form-control">
                        <option value="BETWEEN" {{ request('date_filter_type') === 'BETWEEN' ? 'selected' : '' }}>BETWEEN (Inside range)</option>
                        <option value="NOT_BETWEEN" {{ request('date_filter_type') === 'NOT_BETWEEN' ? 'selected' : '' }}>NOT BETWEEN (Outside range)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date', '2026-01-01') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date', '2026-12-31') }}">
                </div>
            </div>

            <div class="grid-2">
                {{-- Set Membership (IN / NOT IN) --}}
                <div class="form-group">
                    <label class="form-label">Type Membership Mode</label>
                    <select name="type_filter_type" class="form-control">
                        <option value="IN" {{ request('type_filter_type') === 'IN' ? 'selected' : '' }}>IN (Matches selected)</option>
                        <option value="NOT_IN" {{ request('type_filter_type') === 'NOT_IN' ? 'selected' : '' }}>NOT IN (Excludes selected)</option>
                    </select>
                    
                    <div style="margin-top:12px; display:flex; flex-wrap:wrap; gap:12px;">
                        @foreach(['CAR', 'BUS', 'TRUCK', 'BIKE', 'MICROBUS', 'AMBULANCE'] as $t)
                            <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                                <input type="checkbox" name="types[]" value="{{ $t }}" 
                                    {{ in_array($t, request('types', ['CAR', 'BUS', 'TRUCK', 'BIKE', 'MICROBUS', 'AMBULANCE'])) ? 'checked' : '' }}>
                                {{ ucfirst(strtolower($t)) }}
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Numerical Weight filter --}}
                <div class="form-group">
                    <label class="form-label">Weight Comparison</label>
                    <div style="display:flex; gap:8px;">
                        <select name="weight_operator" class="form-control" style="flex:1;">
                            <option value=">" {{ request('weight_operator') === '>' ? 'selected' : '' }}>Greater Than (&gt;)</option>
                            <option value="<" {{ request('weight_operator') === '<' ? 'selected' : '' }}>Less Than (&lt;)</option>
                            <option value="=" {{ request('weight_operator') === '=' ? 'selected' : '' }}>Equals (=)</option>
                        </select>
                        <input type="number" name="weight_value" class="form-control" style="flex:2;" value="{{ request('weight_value', 2000) }}" placeholder="Weight in KG">
                    </div>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px; border-top:1px solid rgba(255,255,255,0.06); padding-top:20px;">
                <a href="{{ route('vehicles.report') }}" class="btn btn-outline">Clear Advanced Form</a>
                <button type="submit" class="btn btn-primary" style="padding:10px 24px;">Execute Advanced Query</button>
            </div>
        </form>

        {{-- Advanced Search Results --}}
        @if($useAdvanced)
            <div style="margin-top:32px; border-top:1px solid rgba(255,255,255,0.12); padding-top:24px;">
                <h4 style="margin-bottom:16px; color:var(--primary); display:flex; justify-content:space-between; align-items:center;">
                    <span>Advanced SQL Query Results</span>
                    <span style="font-size:12px; color:var(--text-muted);">{{ count($advancedResults) }} match(es)</span>
                </h4>

                <div class="table-wrapper">
                    @if(count($advancedResults) > 0)
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Registration Number</th>
                                    <th>Owner</th>
                                    <th>Type</th>
                                    <th>Weight (KG)</th>
                                    <th>Registration Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($advancedResults as $res)
                                    <tr>
                                        <td><span style="font-family:var(--font-mono); font-weight:700;">#{{ $res->vehicle_id }}</span></td>
                                        <td>
                                            <a href="{{ route('vehicles.show', $res->vehicle_id) }}" style="font-weight:800; font-family:var(--font-mono); color:var(--primary);">
                                                {{ $res->registration_number }}
                                            </a>
                                        </td>
                                        <td>{{ $res->owner_name }}</td>
                                        <td><span class="badge badge-info">{{ $res->vehicle_type }}</span></td>
                                        <td><strong>{{ number_format($res->weight) }}</strong></td>
                                        <td style="font-size:13px;">
                                            {{ $res->registration_date ? \Carbon\Carbon::parse($res->registration_date)->format('M d, Y') : 'N/A' }}
                                        </td>
                                        <td>
                                            @if($res->status === 'ACTIVE')
                                                <span class="badge badge-success">ACTIVE</span>
                                            @elseif($res->status === 'BLOCKED')
                                                <span class="badge badge-danger">BLOCKED</span>
                                            @else
                                                <span class="badge badge-warning">EXPIRED</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div style="text-align:center; padding:32px; color:var(--text-muted);">
                            No vehicles matched these advanced criteria combinations.
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

@endsection
