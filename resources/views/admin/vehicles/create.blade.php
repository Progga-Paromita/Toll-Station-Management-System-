@extends('layouts.layout')

@section('title', 'TollStation - Register Vehicle')

@section('content')

    <div class="section-header">
        <div>
            <h1 class="section-title">Register New Vehicle</h1>
            <p class="section-desc">Add a new vehicle to the TollStation database with all specifications and owner details.</p>
        </div>
        <a href="{{ route('vehicles.index') }}" class="btn btn-outline btn-sm">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5m7 7-7-7 7-7"/></svg>
            Back to List
        </a>
    </div>

    <div style="max-width:760px; margin:0 auto;">
        <div class="card glass">
            <h3 class="card-title">
                <svg width="20" height="20" fill="none" stroke="var(--primary)" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Vehicle Registration Form
            </h3>
            <p class="card-subtitle" style="margin-bottom:28px;">
                Required fields are marked with asterisk (*). Oracle constraint validations will be triggered on submission.
            </p>

            <form action="{{ route('vehicles.store') }}" method="POST">
                @csrf

                <div class="grid-2">
                    {{-- Registration Number --}}
                    <div class="form-group">
                        <label for="registration_number" class="form-label">Registration Number *</label>
                        <input type="text" name="registration_number" id="registration_number" 
                               class="form-control @error('registration_number') is-invalid @enderror" 
                               placeholder="e.g. DHAKA-METRO-GA-123456" 
                               value="{{ old('registration_number') }}" required style="text-transform: uppercase;">
                        @error('registration_number')
                            <span class="error-msg" style="color:var(--danger); font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Vehicle Type --}}
                    <div class="form-group">
                        <label for="vehicle_type" class="form-label">Vehicle Type *</label>
                        <select name="vehicle_type" id="vehicle_type" 
                                class="form-control @error('vehicle_type') is-invalid @enderror" required>
                            <option value="">Select Type</option>
                            <option value="CAR" {{ old('vehicle_type') === 'CAR' ? 'selected' : '' }}>Car</option>
                            <option value="BUS" {{ old('vehicle_type') === 'BUS' ? 'selected' : '' }}>Bus</option>
                            <option value="TRUCK" {{ old('vehicle_type') === 'TRUCK' ? 'selected' : '' }}>Truck</option>
                            <option value="BIKE" {{ old('vehicle_type') === 'BIKE' ? 'selected' : '' }}>Bike</option>
                            <option value="MICROBUS" {{ old('vehicle_type') === 'MICROBUS' ? 'selected' : '' }}>Microbus</option>
                            <option value="AMBULANCE" {{ old('vehicle_type') === 'AMBULANCE' ? 'selected' : '' }}>Ambulance</option>
                        </select>
                        @error('vehicle_type')
                            <span class="error-msg" style="color:var(--danger); font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid-2">
                    {{-- Owner Name --}}
                    <div class="form-group">
                        <label for="owner_name" class="form-label">Owner Name *</label>
                        <input type="text" name="owner_name" id="owner_name" 
                               class="form-control @error('owner_name') is-invalid @enderror" 
                               placeholder="e.g. Karim Rahman" 
                               value="{{ old('owner_name') }}" required>
                        @error('owner_name')
                            <span class="error-msg" style="color:var(--danger); font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Owner Phone --}}
                    <div class="form-group">
                        <label for="owner_phone" class="form-label">Owner Phone Number *</label>
                        <input type="text" name="owner_phone" id="owner_phone" 
                               class="form-control @error('owner_phone') is-invalid @enderror" 
                               placeholder="e.g. 01711223344" 
                               value="{{ old('owner_phone') }}" required>
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
                               placeholder="e.g. Silver / White" 
                               value="{{ old('color') }}" required>
                        @error('color')
                            <span class="error-msg" style="color:var(--danger); font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Weight --}}
                    <div class="form-group">
                        <label for="weight" class="form-label">Weight (in KG) *</label>
                        <input type="number" step="0.01" name="weight" id="weight" 
                               class="form-control @error('weight') is-invalid @enderror" 
                               placeholder="e.g. 1500" 
                               value="{{ old('weight') }}" required>
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
                               placeholder="e.g. Toyota / Honda" 
                               value="{{ old('manufacturer') }}">
                    </div>

                    {{-- Model --}}
                    <div class="form-group">
                        <label for="model" class="form-label">Vehicle Model</label>
                        <input type="text" name="model" id="model" 
                               class="form-control" 
                               placeholder="e.g. Premio / Civic" 
                               value="{{ old('model') }}">
                    </div>
                </div>

                <div class="grid-2">
                    {{-- Status --}}
                    <div class="form-group">
                        <label for="status" class="form-label">Initial Status *</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="ACTIVE" {{ old('status') === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                            <option value="BLOCKED" {{ old('status') === 'BLOCKED' ? 'selected' : '' }}>Blocked</option>
                            <option value="EXPIRED" {{ old('status') === 'EXPIRED' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>

                    {{-- Date (Disabled, default is SYSDATE) --}}
                    <div class="form-group">
                        <label class="form-label">Registration Date</label>
                        <input type="text" class="form-control" value="Auto-generated on Save (SYSDATE)" disabled style="opacity: 0.65; cursor: not-allowed;">
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:32px; border-top:1px solid rgba(255,255,255,0.08); padding-top:24px;">
                    <button type="reset" class="btn btn-outline">Reset Form</button>
                    <button type="submit" class="btn btn-primary" style="padding:12px 32px;">Save Vehicle</button>
                </div>
            </form>
        </div>
    </div>

@endsection
