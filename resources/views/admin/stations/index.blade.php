@extends('layouts.layout')

@section('title', 'TollStation - Toll Stations')

@section('content')

    {{-- Page Header --}}
    <div class="section-header">
        <div>
            <h1 class="section-title">Toll Station Management</h1>
            <p class="section-desc">Register, manage and monitor all toll collection stations across the network.</p>
        </div>
        <div style="display:flex; gap:12px; align-items:center;">
            <span class="badge badge-danger">ADMIN ONLY</span>
            <a href="{{ route('admin.stations.create') }}" class="btn btn-primary btn-sm">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                Add Station
            </a>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="stats-container" style="margin-bottom:32px;">
        <div class="stat-card">
            <div class="stat-value" style="font-size:28px;">{{ $stats->total ?? 0 }}</div>
            <div class="stat-label">Total Stations</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="font-size:28px; color: var(--success);">{{ $stats->active ?? 0 }}</div>
            <div class="stat-label">Active</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="font-size:28px; color: var(--danger);">{{ $stats->inactive ?? 0 }}</div>
            <div class="stat-label">Inactive</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="font-size:28px; color: var(--warning);">{{ $stats->maintenance ?? 0 }}</div>
            <div class="stat-label">Under Maintenance</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="font-size:28px; color: var(--secondary);">{{ $stats->total_lanes ?? 0 }}</div>
            <div class="stat-label">Total Lanes</div>
        </div>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="card glass" style="margin-bottom:28px; padding:24px;">
        <form method="GET" action="{{ route('admin.stations.index') }}" id="filterForm">
            <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">

                {{-- Search --}}
                <div class="form-group" style="flex:2; min-width:200px; margin-bottom:0;">
                    <label class="form-label" style="font-size:12px;">Search Station</label>
                    <div style="position:relative;">
                        <svg style="position:absolute;left:12px;top:50%;transform:translateY(-50%);" width="16" height="16" fill="none" stroke="var(--text-muted)" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        <input type="text" name="search" class="form-control" placeholder="Search by station name..." value="{{ $search }}" style="padding-left:40px;">
                    </div>
                </div>

                {{-- District Filter --}}
                <div class="form-group" style="flex:1; min-width:140px; margin-bottom:0;">
                    <label class="form-label" style="font-size:12px;">District</label>
                    <select name="district" class="form-control">
                        <option value="">All Districts</option>
                        @foreach($districts as $d)
                            <option value="{{ $d->district }}" {{ $district === $d->district ? 'selected' : '' }}>{{ $d->district }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div class="form-group" style="flex:1; min-width:140px; margin-bottom:0;">
                    <label class="form-label" style="font-size:12px;">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="ACTIVE" {{ $status === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                        <option value="INACTIVE" {{ $status === 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                        <option value="UNDER_MAINTENANCE" {{ $status === 'UNDER_MAINTENANCE' ? 'selected' : '' }}>Under Maintenance</option>
                    </select>
                </div>

                {{-- Type Filter --}}
                <div class="form-group" style="flex:1; min-width:130px; margin-bottom:0;">
                    <label class="form-label" style="font-size:12px;">Type</label>
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="Bridge" {{ $type === 'Bridge' ? 'selected' : '' }}>Bridge</option>
                        <option value="Highway" {{ $type === 'Highway' ? 'selected' : '' }}>Highway</option>
                        <option value="Expressway" {{ $type === 'Expressway' ? 'selected' : '' }}>Expressway</option>
                    </select>
                </div>

                {{-- Sort --}}
                <div class="form-group" style="flex:1; min-width:150px; margin-bottom:0;">
                    <label class="form-label" style="font-size:12px;">Sort By</label>
                    <select name="sort" class="form-control">
                        <option value="created_at_desc" {{ $sort === 'created_at_desc' ? 'selected' : '' }}>Newest First</option>
                        <option value="created_at_asc"  {{ $sort === 'created_at_asc'  ? 'selected' : '' }}>Oldest First</option>
                        <option value="name_asc"         {{ $sort === 'name_asc'         ? 'selected' : '' }}>Name A→Z</option>
                        <option value="name_desc"        {{ $sort === 'name_desc'        ? 'selected' : '' }}>Name Z→A</option>
                        <option value="lanes_desc"       {{ $sort === 'lanes_desc'       ? 'selected' : '' }}>Most Lanes</option>
                        <option value="lanes_asc"        {{ $sort === 'lanes_asc'        ? 'selected' : '' }}>Fewest Lanes</option>
                        <option value="opening_date_desc"{{ $sort === 'opening_date_desc'? 'selected' : '' }}>Newest Opening</option>
                        <option value="opening_date_asc" {{ $sort === 'opening_date_asc' ? 'selected' : '' }}>Oldest Opening</option>
                    </select>
                </div>

                {{-- Buttons --}}
                <div style="display:flex; gap:8px; margin-bottom:0;">
                    <button type="submit" class="btn btn-primary btn-sm" style="padding:10px 20px;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        Filter
                    </button>
                    <a href="{{ route('admin.stations.index') }}" class="btn btn-outline btn-sm" style="padding:10px 16px;">Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Station Table --}}
    <div class="card glass">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 class="card-title" style="margin-bottom:0;">
                <svg width="20" height="20" fill="none" stroke="var(--primary)" stroke-width="2" viewBox="0 0 24 24"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                Registered Stations
            </h3>
            <span style="font-size:13px; color:var(--text-muted);">{{ count($stations) }} result(s)</span>
        </div>

        <div class="table-wrapper">
            @if(count($stations) > 0)
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Station Name</th>
                            <th>District</th>
                            <th>Highway</th>
                            <th>Type</th>
                            <th>Lanes</th>
                            <th>Opening Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stations as $s)
                            <tr>
                                <td><span style="font-family:var(--font-mono); font-weight:700;">#{{ $s->station_id }}</span></td>
                                <td>
                                    <div style="font-weight:700; color:var(--text-primary);">{{ $s->station_name }}</div>
                                    <div style="font-size:11px; color:var(--text-muted);">by {{ $s->created_by_name ?? 'System' }}</div>
                                </td>
                                <td>{{ $s->district }}</td>
                                <td style="font-size:12px; max-width:180px; white-space:normal;">{{ $s->highway }}</td>
                                <td>
                                    <span class="badge
                                        @if($s->station_type === 'Bridge') badge-info
                                        @elseif($s->station_type === 'Expressway') badge-warning
                                        @else badge-success @endif">
                                        {{ $s->station_type }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        <svg width="14" height="14" fill="none" stroke="var(--primary)" stroke-width="2" viewBox="0 0 24 24"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                                        <strong>{{ $s->lane_count }}</strong>
                                    </div>
                                </td>
                                <td style="font-size:13px;">
                                    @if($s->opening_date)
                                        {{ \Carbon\Carbon::parse($s->opening_date)->format('M d, Y') }}
                                    @else N/A @endif
                                </td>
                                <td>
                                    @if($s->status === 'ACTIVE')
                                        <span class="badge badge-success">ACTIVE</span>
                                    @elseif($s->status === 'INACTIVE')
                                        <span class="badge badge-danger">INACTIVE</span>
                                    @else
                                        <span class="badge badge-warning">MAINTENANCE</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display:flex; gap:6px;">
                                        <a href="{{ route('admin.stations.show', $s->station_id) }}" class="btn btn-outline btn-sm" title="View">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <a href="{{ route('admin.stations.edit', $s->station_id) }}" class="btn btn-primary btn-sm" title="Edit">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <form action="{{ route('admin.stations.destroy', $s->station_id) }}" method="POST"
                                              onsubmit="return confirm('Delete station &quot;{{ addslashes($s->station_name) }}&quot;? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm" style="background:rgba(239,68,68,0.12); color:var(--danger); border:1px solid rgba(239,68,68,0.25);" title="Delete">
                                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div style="text-align:center; padding:60px 40px; color:var(--text-muted);">
                    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24" style="margin-bottom:16px; opacity:0.3;"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                    <p style="font-size:16px; font-weight:600; margin-bottom:8px;">No stations found</p>
                    <p style="font-size:13px;">Try adjusting your search or filters, or <a href="{{ route('admin.stations.create') }}" style="color:var(--primary); font-weight:700;">add a new station</a>.</p>
                </div>
            @endif
        </div>
    </div>

@endsection

@section('scripts')
<script>
    // Auto-submit on dropdown changes
    ['district','status','type','sort'].forEach(name => {
        const el = document.querySelector(`select[name="${name}"]`);
        if (el) el.addEventListener('change', () => document.getElementById('filterForm').submit());
    });
</script>
@endsection
