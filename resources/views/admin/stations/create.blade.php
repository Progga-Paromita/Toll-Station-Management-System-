@extends('layouts.layout')

@section('title', 'TollStation - Add New Station')

@section('content')

    {{-- Page Header --}}
    <div class="section-header">
        <div>
            <h1 class="section-title">Add New Toll Station</h1>
            <p class="section-desc">Register a new toll collection station. All fields marked <span style="color:var(--danger);">*</span> are required.</p>
        </div>
        <a href="{{ route('admin.stations.index') }}" class="btn btn-outline btn-sm">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Stations
        </a>
    </div>

    <div style="max-width: 800px;">

        {{-- Validation Errors --}}
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
                <svg width="20" height="20" fill="none" stroke="var(--primary)" stroke-width="2" viewBox="0 0 24 24"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                Station Information
            </h3>
            <p class="card-subtitle">Demonstrates Oracle <code>INSERT</code>, <code>UNIQUE</code>, <code>NOT NULL</code>, <code>CHECK</code>, and <code>DEFAULT</code> constraints.</p>

            <form action="{{ route('admin.stations.store') }}" method="POST" id="stationForm">
                @csrf

                {{-- Station Name --}}
                <div class="form-group">
                    <label for="station_name" class="form-label">Station Name <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="station_name" id="station_name" class="form-control"
                        placeholder="e.g. Padma Bridge Toll Plaza"
                        value="{{ old('station_name') }}" required maxlength="100" autocomplete="off">
                    <small style="color:var(--text-muted); font-size:12px; margin-top:4px; display:block;">Must be unique across all stations. Oracle UNIQUE constraint enforced.</small>
                </div>

                {{-- District & Highway --}}
                <div class="form-row">
                    <div class="form-group">
                        <label for="district" class="form-label">District <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="district" id="district" class="form-control"
                            placeholder="e.g. Shariatpur"
                            value="{{ old('district') }}" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="highway" class="form-label">Highway / Road Name <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="highway" id="highway" class="form-control"
                            placeholder="e.g. N8 - Dhaka-Khulna Highway"
                            value="{{ old('highway') }}" required maxlength="150">
                    </div>
                </div>

                {{-- Type & Lane Count --}}
                <div class="form-row">
                    <div class="form-group">
                        <label for="station_type" class="form-label">Station Type <span style="color:var(--danger);">*</span></label>
                        <select name="station_type" id="station_type" class="form-control" required>
                            <option value="">-- Select Type --</option>
                            <option value="Bridge"     {{ old('station_type') === 'Bridge'     ? 'selected' : '' }}>Bridge</option>
                            <option value="Highway"    {{ old('station_type') === 'Highway'    ? 'selected' : '' }}>Highway</option>
                            <option value="Expressway" {{ old('station_type') === 'Expressway' ? 'selected' : '' }}>Expressway</option>
                        </select>
                        <small style="color:var(--text-muted); font-size:12px; margin-top:4px; display:block;">CHECK constraint: only Bridge, Highway, Expressway allowed.</small>
                    </div>
                    <div class="form-group">
                        <label for="lane_count" class="form-label">Number of Lanes <span style="color:var(--danger);">*</span></label>
                        <input type="number" name="lane_count" id="lane_count" class="form-control"
                            placeholder="e.g. 6" min="1" max="50"
                            value="{{ old('lane_count') }}" required>
                        <small style="color:var(--text-muted); font-size:12px; margin-top:4px; display:block;">CHECK constraint: must be greater than 0.</small>
                    </div>
                </div>

                {{-- Opening Date & Status --}}
                <div class="form-row">
                    <div class="form-group">
                        <label for="opening_date" class="form-label">Opening Date <span style="color:var(--danger);">*</span></label>
                        <input type="date" name="opening_date" id="opening_date" class="form-control"
                            value="{{ old('opening_date') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="ACTIVE"            {{ old('status', 'ACTIVE') === 'ACTIVE'            ? 'selected' : '' }}>Active</option>
                            <option value="INACTIVE"          {{ old('status') === 'INACTIVE'          ? 'selected' : '' }}>Inactive</option>
                            <option value="UNDER_MAINTENANCE" {{ old('status') === 'UNDER_MAINTENANCE' ? 'selected' : '' }}>Under Maintenance</option>
                        </select>
                        <small style="color:var(--text-muted); font-size:12px; margin-top:4px; display:block;">DEFAULT = ACTIVE. CHECK constraint enforced.</small>
                    </div>
                </div>

                {{-- Oracle Constraint Info Box --}}
                <div style="background:var(--primary-glow); border:1px solid rgba(79,70,229,0.2); border-radius:var(--border-radius-sm); padding:16px; margin-bottom:24px;">
                    <p style="font-size:13px; font-weight:600; color:var(--primary); margin-bottom:8px;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle; margin-right:4px;"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                        Oracle Database Constraints Active
                    </p>
                    <ul style="font-size:12px; color:var(--text-secondary); list-style:none; display:flex; flex-wrap:wrap; gap:8px;">
                        <li style="background:var(--bg-secondary); padding:4px 10px; border-radius:20px;">PRIMARY KEY (station_id)</li>
                        <li style="background:var(--bg-secondary); padding:4px 10px; border-radius:20px;">UNIQUE (station_name)</li>
                        <li style="background:var(--bg-secondary); padding:4px 10px; border-radius:20px;">NOT NULL (name, district, highway, opening_date)</li>
                        <li style="background:var(--bg-secondary); padding:4px 10px; border-radius:20px;">CHECK lane_count &gt; 0</li>
                        <li style="background:var(--bg-secondary); padding:4px 10px; border-radius:20px;">CHECK status IN (...)</li>
                        <li style="background:var(--bg-secondary); padding:4px 10px; border-radius:20px;">DEFAULT status = 'ACTIVE'</li>
                        <li style="background:var(--bg-secondary); padding:4px 10px; border-radius:20px;">FK created_by → users</li>
                    </ul>
                </div>

                {{-- Action Buttons --}}
                <div style="display:flex; gap:12px; flex-wrap:wrap;">
                    <button type="submit" class="btn btn-primary">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                        Save Station
                    </button>
                    <button type="reset" class="btn btn-outline">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        Reset
                    </button>
                    <a href="{{ route('admin.stations.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
