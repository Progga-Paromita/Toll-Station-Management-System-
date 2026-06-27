@extends('layouts.layout')

@section('title', 'TollStation - Edit Vehicle')

@section('content')

    <div class="section-header">
        <div>
            <h1 class="section-title">Edit Vehicle: {{ $vehicle->registration_number }}</h1>
            <p class="section-desc">Modify owner, specifications, or status records for this vehicle.</p>
        </div>
        <a href="{{ route('vehicles.show', $vehicle->vehicle_id) }}" class="btn btn-outline btn-sm">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5m7 7-7-7 7-7"/></svg>
            Cancel & Back
        </a>
    </div>

    <div style="max-width:760px; margin:0 auto;">
        <div class="card glass">
            <h3 class="card-title">
                <svg width="20" height="20" fill="none" stroke="var(--primary)" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Modify Vehicle Information
            </h3>
            <p class="card-subtitle" style="margin-bottom:28px;">
                Note: Registration Number and Vehicle Type cannot be altered to maintain referential data integrity.
            </p>

            <form action="{{ route('vehicles.update', $vehicle->vehicle_id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid-2">
                    {{-- Registration Number (Disabled) --}}
                    <div class="form-group">
                        <label class="form-label">Registration Number</label>
                        <input type="text" class="form-control" value="{{ $vehicle->registration_number }}" disabled style="opacity: 0.65; cursor: not-allowed; font-weight:800; font-family:var(--font-mono);">
                    </div>

                    {{-- Vehicle Type (Disabled) --}}
                    <div class="form-group">
                        <label class="form-label">Vehicle Type</label>
                        <input type="text" class="form-control" value="{{ $vehicle->vehicle_type }}" disabled style="opacity: 0.65; cursor: not-allowed; font-weight:800;">
                    </div>
                </div>

                <div class="grid-2">
                    {{-- Owner Name --}}
                    <div class="form-group">
                        <label for="owner_name" class="form-label">Owner Name *</label>
                        <input type="text" name="owner_name" id="owner_name" 
                               class="form-control @error('owner_name') is-invalid @enderror" 
                               value="{{ old('owner_name', $vehicle->owner_name) }}" required>
                        @error('owner_name')
                            <span class="error-msg" style="color:var(--danger); font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Owner Phone --}}
                    <div class="form-group">
                        <label for="owner_phone" class="form-label">Owner Phone Number *</label>
                        <input type="text" name="owner_phone" id="owner_phone" 
                               class="form-control @error('owner_phone') is-invalid @enderror" 
                               value="{{ old('owner_phone', $vehicle->owner_phone) }}" required>
                        @error('owner_phone')
                            <span class="error-msg" style="color:var(--danger); font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid-2">
                    {{-- Color --}}
                    <div class="form-group">
                        <label for="color" class="form-label">Vehicle Color *</label>
                        <input type="text" name="color" id="color" 
                               class="form-control @error('color') is-invalid @enderror" 
                               value="{{ old('color', $vehicle->color) }}" required>
                        @error('color')
                            <span class="error-msg" style="color:var(--danger); font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Weight --}}
                    <div class="form-group">
                        <label for="weight" class="form-label">Weight (in KG) *</label>
                        <input type="number" step="0.01" name="weight" id="weight" 
                               class="form-control @error('weight') is-invalid @enderror" 
                               value="{{ old('weight', $vehicle->weight) }}" required>
                        @error('weight')
                            <span class="error-msg" style="color:var(--danger); font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid-2">
                    {{-- Manufacturer --}}
                    <div class="form-group">
                        <label for="manufacturer" class="form-label">Manufacturer</label>
                        <input type="text" name="manufacturer" id="manufacturer" 
                               class="form-control" 
                               value="{{ old('manufacturer', $vehicle->manufacturer) }}">
                    </div>

                    {{-- Model --}}
                    <div class="form-group">
                        <label for="model" class="form-label">Vehicle Model</label>
                        <input type="text" name="model" id="model" 
                               class="form-control" 
                               value="{{ old('model', $vehicle->model) }}">
                    </div>
                </div>

                <div class="grid-2">
                    {{-- Status --}}
                    <div class="form-group">
                        <label for="status" class="form-label">Current Status *</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="ACTIVE" {{ old('status', $vehicle->status) === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                            <option value="BLOCKED" {{ old('status', $vehicle->status) === 'BLOCKED' ? 'selected' : '' }}>Blocked</option>
                            <option value="EXPIRED" {{ old('status', $vehicle->status) === 'EXPIRED' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>

                    {{-- Registration Date (Disabled) --}}
                    <div class="form-group">
                        <label class="form-label">Registration Date</label>
                        <input type="text" class="form-control" 
                               value="{{ \Carbon\Carbon::parse($vehicle->registration_date)->format('M d, Y H:i A') }}" 
                               disabled style="opacity: 0.65; cursor: not-allowed;">
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:32px; border-top:1px solid rgba(255,255,255,0.08); padding-top:24px;">
                    <a href="{{ route('vehicles.show', $vehicle->vehicle_id) }}" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary" style="padding:12px 32px;">Update Vehicle</button>
                </div>
            </form>
        </div>
    </div>

@endsection
