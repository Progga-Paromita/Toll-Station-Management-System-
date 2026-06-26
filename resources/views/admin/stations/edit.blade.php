@extends('layouts.layout')

@section('title', 'TollStation - Edit Station')

@section('content')

    <div class="section-header">
        <div>
            <h1 class="section-title">Edit Toll Station</h1>
            <p class="section-desc">Update station details for <strong>{{ $station->station_name }}</strong>. Demonstrates Oracle <code>UPDATE</code> with <code>WHERE</code>.</p>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="{{ route('admin.stations.show', $station->station_id) }}" class="btn btn-outline btn-sm">View</a>
            <a href="{{ route('admin.stations.index') }}" class="btn btn-outline btn-sm">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                Back
            </a>
        </div>
    </div>

    <div style="max-width:800px;">

        @if ($errors->any())
            <div class="alert alert-danger" style="flex-direction:column; align-items:flex-start; gap:4px; margin-bottom:24px;">
                @foreach ($errors->all() as $error)
                    <div style="display:flex; gap:8px; align-items:center;">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        <span>{{ $error }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="card glass">
            <h3 class="card-title">
                <svg width="20" height="20" fill="none" stroke="var(--warning)" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Editing: {{ $station->station_name }}
                <span style="margin-left:auto;">
                    @if($station->status === 'ACTIVE')
                        <span class="badge badge-success">ACTIVE</span>
                    @elseif($station->status === 'INACTIVE')
                        <span class="badge badge-danger">INACTIVE</span>
                    @else
                        <span class="badge badge-warning">MAINTENANCE</span>
                    @endif
                </span>
            </h3>

            <form action="{{ route('admin.stations.update', $station->station_id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="station_name" class="form-label">Station Name <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="station_name" id="station_name" class="form-control"
                        value="{{ old('station_name', $station->station_name) }}" required maxlength="100">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="district" class="form-label">District <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="district" id="district" class="form-control"
                            value="{{ old('district', $station->district) }}" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="highway" class="form-label">Highway / Road Name <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="highway" id="highway" class="form-control"
                            value="{{ old('highway', $station->highway) }}" required maxlength="150">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="station_type" class="form-label">Station Type <span style="color:var(--danger);">*</span></label>
                        <select name="station_type" id="station_type" class="form-control" required>
                            <option value="Bridge"     {{ old('station_type', $station->station_type) === 'Bridge'     ? 'selected' : '' }}>Bridge</option>
                            <option value="Highway"    {{ old('station_type', $station->station_type) === 'Highway'    ? 'selected' : '' }}>Highway</option>
                            <option value="Expressway" {{ old('station_type', $station->station_type) === 'Expressway' ? 'selected' : '' }}>Expressway</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="lane_count" class="form-label">Number of Lanes <span style="color:var(--danger);">*</span></label>
                        <input type="number" name="lane_count" id="lane_count" class="form-control"
                            value="{{ old('lane_count', $station->lane_count) }}" min="1" max="50" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="opening_date" class="form-label">Opening Date <span style="color:var(--danger);">*</span></label>
                        <input type="date" name="opening_date" id="opening_date" class="form-control"
                            value="{{ old('opening_date', $station->opening_date ? \Carbon\Carbon::parse($station->opening_date)->format('Y-m-d') : '') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="ACTIVE"            {{ old('status', $station->status) === 'ACTIVE'            ? 'selected' : '' }}>Active</option>
                            <option value="INACTIVE"          {{ old('status', $station->status) === 'INACTIVE'          ? 'selected' : '' }}>Inactive</option>
                            <option value="UNDER_MAINTENANCE" {{ old('status', $station->status) === 'UNDER_MAINTENANCE' ? 'selected' : '' }}>Under Maintenance</option>
                        </select>
                    </div>
                </div>

                <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:8px;">
                    <button type="submit" class="btn btn-primary">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                        Update Station
                    </button>
                    <a href="{{ route('admin.stations.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
