@extends('layouts.layout')

@section('title', 'TollStation - Vehicles')

@section('content')

    {{-- Page Header --}}
    <div class="section-header">
        <div>
            <h1 class="section-title">Vehicle Management</h1>
            <p class="section-desc">Register, edit, monitor and verify all vehicles using the toll station network.</p>
        </div>
        <div style="display:flex; gap:12px; align-items:center;">
            @if(Auth::user()->isAdmin())
                <span class="badge badge-danger">ADMIN</span>
            @elseif(Auth::user()->isOperator())
                <span class="badge badge-info">OPERATOR</span>
            @else
                <span class="badge badge-success">VIEWER</span>
            @endif

            @if(Auth::user()->isAdmin() || Auth::user()->isViewer())
                <a href="{{ route('vehicles.report') }}" class="btn btn-outline btn-sm">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 17v-2m3 2v-4m3 4V9M9 3h6m-6 4h6m-6 4h6m-6 4h6M4 21h16a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2z"/></svg>
                    Reports
                </a>
            @endif

            @if(Auth::user()->isAdmin() || Auth::user()->isOperator())
                <a href="{{ route('vehicles.create') }}" class="btn btn-primary btn-sm">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                    Register Vehicle
                </a>
            @endif
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="stats-container" style="margin-bottom:32px;">
        <div class="stat-card">
            <div class="stat-value" style="font-size:28px;">{{ count($vehicles) }}</div>
            <div class="stat-label">Matching Vehicles</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="font-size:28px; color: var(--success);">
                {{ count(array_filter($vehicles, function($v) { return $v->status === 'ACTIVE'; })) }}
            </div>
            <div class="stat-label">Active</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="font-size:28px; color: var(--danger);">
                {{ count(array_filter($vehicles, function($v) { return $v->status === 'BLOCKED'; })) }}
            </div>
            <div class="stat-label">Blocked</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="font-size:28px; color: var(--warning);">
                {{ count(array_filter($vehicles, function($v) { return $v->status === 'EXPIRED'; })) }}
            </div>
            <div class="stat-label">Expired</div>
        </div>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="card glass" style="margin-bottom:28px; padding:24px;">
        <form method="GET" action="{{ route('vehicles.index') }}" id="filterForm">
            <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">

                {{-- Search --}}
                <div class="form-group" style="flex:2; min-width:200px; margin-bottom:0;">
                    <label class="form-label" style="font-size:12px;">Search Registration / Owner</label>
                    <div style="position:relative;">
                        <svg style="position:absolute;left:12px;top:50%;transform:translateY(-50%);" width="16" height="16" fill="none" stroke="var(--text-muted)" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        <input type="text" name="search" class="form-control" placeholder="Search registration no or owner..." value="{{ $search }}" style="padding-left:40px;">
                    </div>
                </div>

                {{-- Vehicle Type Filter --}}
                <div class="form-group" style="flex:1; min-width:140px; margin-bottom:0;">
                    <label class="form-label" style="font-size:12px;">Vehicle Type</label>
                    <select name="vehicle_type" class="form-control">
                        <option value="">All Types</option>
                        @foreach($types as $t)
                            <option value="{{ $t->vehicle_type }}" {{ $vehicleType === $t->vehicle_type ? 'selected' : '' }}>
                                {{ ucfirst(strtolower($t->vehicle_type)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div class="form-group" style="flex:1; min-width:140px; margin-bottom:0;">
                    <label class="form-label" style="font-size:12px;">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="ACTIVE" {{ $status === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                        <option value="BLOCKED" {{ $status === 'BLOCKED' ? 'selected' : '' }}>Blocked</option>
                        <option value="EXPIRED" {{ $status === 'EXPIRED' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>

                {{-- Manufacturer Filter --}}
                <div class="form-group" style="flex:1; min-width:140px; margin-bottom:0;">
                    <label class="form-label" style="font-size:12px;">Manufacturer</label>
                    <select name="manufacturer" class="form-control">
                        <option value="">All Manufacturers</option>
                        @foreach($manufacturers as $m)
                            <option value="{{ $m->manufacturer }}" {{ $manufacturer === $m->manufacturer ? 'selected' : '' }}>
                                {{ $m->manufacturer }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Sort --}}
                <div class="form-group" style="flex:1; min-width:150px; margin-bottom:0;">
                    <label class="form-label" style="font-size:12px;">Sort By</label>
                    <select name="sort" class="form-control">
                        <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest Registered</option>
                        <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Oldest Registered</option>
                        <option value="registration_number_asc" {{ $sort === 'registration_number_asc' ? 'selected' : '' }}>Reg No A→Z</option>
                        <option value="registration_number_desc" {{ $sort === 'registration_number_desc' ? 'selected' : '' }}>Reg No Z→A</option>
                        <option value="owner_name_asc" {{ $sort === 'owner_name_asc' ? 'selected' : '' }}>Owner A→Z</option>
                        <option value="owner_name_desc" {{ $sort === 'owner_name_desc' ? 'selected' : '' }}>Owner Z→A</option>
                        <option value="weight_desc" {{ $sort === 'weight_desc' ? 'selected' : '' }}>Heaviest First</option>
                        <option value="weight_asc" {{ $sort === 'weight_asc' ? 'selected' : '' }}>Lightest First</option>
                    </select>
                </div>

                {{-- Buttons --}}
                <div style="display:flex; gap:8px; margin-bottom:0;">
                    <button type="submit" class="btn btn-primary btn-sm" style="padding:10px 20px;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        Filter
                    </button>
                    <a href="{{ route('vehicles.index') }}" class="btn btn-outline btn-sm" style="padding:10px 16px;">Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Vehicles Table --}}
    <div class="card glass">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 class="card-title" style="margin-bottom:0;">
                <svg width="20" height="20" fill="none" stroke="var(--primary)" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/>
                    <circle cx="7" cy="17" r="2"/>
                    <path d="M9 17h6"/>
                    <circle cx="17" cy="17" r="2"/>
                </svg>
                Registered Vehicles
            </h3>
            <span style="font-size:13px; color:var(--text-muted);">{{ count($vehicles) }} result(s)</span>
        </div>

        <div class="table-wrapper">
            @if(count($vehicles) > 0)
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Registration No</th>
                            <th>Owner Name</th>
                            <th>Owner Phone</th>
                            <th>Vehicle Type</th>
                            <th>Color</th>
                            <th>Weight (KG)</th>
                            <th>Registered Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vehicles as $v)
                            <tr>
                                <td><span style="font-family:var(--font-mono); font-weight:700;">#{{ $v->vehicle_id }}</span></td>
                                <td>
                                    <div style="font-weight:800; font-family:var(--font-mono); color:var(--text-primary);">
                                        {{ $v->registration_number }}
                                    </div>
                                    <div style="font-size:11px; color:var(--text-muted);">by {{ $v->creator_name ?? 'System' }}</div>
                                </td>
                                <td>{{ $v->owner_name }}</td>
                                <td>{{ $v->owner_phone }}</td>
                                <td>
                                    <span class="badge
                                        @if($v->vehicle_type === 'CAR') badge-success
                                        @elseif($v->vehicle_type === 'BIKE') badge-info
                                        @elseif($v->vehicle_type === 'BUS') badge-warning
                                        @elseif($v->vehicle_type === 'TRUCK') badge-danger
                                        @else badge-secondary @endif">
                                        {{ $v->vehicle_type }}
                                    </span>
                                </td>
                                <td>{{ $v->color }}</td>
                                <td><strong>{{ number_format($v->weight) }}</strong></td>
                                <td style="font-size:13px;">
                                    @if($v->registration_date)
                                        {{ \Carbon\Carbon::parse($v->registration_date)->format('M d, Y') }}
                                    @else N/A @endif
                                </td>
                                <td>
                                    @if($v->status === 'ACTIVE')
                                        <span class="badge badge-success">ACTIVE</span>
                                    @elseif($v->status === 'BLOCKED')
                                        <span class="badge badge-danger">BLOCKED</span>
                                    @else
                                        <span class="badge badge-warning">EXPIRED</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display:flex; gap:6px;">
                                        <a href="{{ route('vehicles.show', $v->vehicle_id) }}" class="btn btn-outline btn-sm" title="View details & stats">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>

                                        @if(Auth::user()->isAdmin())
                                            <a href="{{ route('vehicles.edit', $v->vehicle_id) }}" class="btn btn-primary btn-sm" title="Edit Vehicle">
                                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </a>
                                            
                                            <form action="{{ route('vehicles.destroy', $v->vehicle_id) }}" method="POST"
                                                  onsubmit="return confirm('Delete vehicle &quot;{{ $v->registration_number }}&quot;? This checks referential integrity.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm" style="background:rgba(239,68,68,0.12); color:var(--danger); border:1px solid rgba(239,68,68,0.25);" title="Delete">
                                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div style="text-align:center; padding:48px; color:var(--text-muted);">
                    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:12px; opacity:0.6;"><path d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                    <h4>No Vehicles Found</h4>
                    <p style="font-size:13px; margin-top:4px;">Try modifying your search queries or filter attributes.</p>
                </div>
            @endif
        </div>
    </div>

@endsection
