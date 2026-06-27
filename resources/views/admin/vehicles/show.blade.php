@extends('layouts.layout')

@section('title', 'TollStation - View Vehicle Details')

@section('content')

    {{-- Page Header --}}
    <div class="section-header">
        <div>
            <h1 class="section-title">Vehicle details: {{ $vehicle->registration_number }}</h1>
            <p class="section-desc">Inspect historical parameters, registration profile, and transaction ledger details.</p>
        </div>
        <div style="display:flex; gap:12px; align-items:center;">
            <a href="{{ route('vehicles.index') }}" class="btn btn-outline btn-sm">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5m7 7-7-7 7-7"/></svg>
                Back to List
            </a>
            @if(Auth::user()->isAdmin())
                <a href="{{ route('vehicles.edit', $vehicle->vehicle_id) }}" class="btn btn-primary btn-sm">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit Vehicle
                </a>
            @endif
        </div>
    </div>

    {{-- Stats Cards Row --}}
    <div class="stats-container" style="margin-bottom:32px;">
        <div class="stat-card">
            <div class="stat-value" style="font-size:28px; color:var(--primary);">{{ $stats->total_txns ?? 0 }}</div>
            <div class="stat-label">Total Toll Passages</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="font-size:28px; color:var(--success);">${{ number_format($stats->total_paid ?? 0, 2) }}</div>
            <div class="stat-label">Total Toll Paid</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="font-size:28px;">
                @if($vehicle->status === 'ACTIVE')
                    <span style="color:var(--success);">Active</span>
                @elseif($vehicle->status === 'BLOCKED')
                    <span style="color:var(--danger);">Blocked</span>
                @else
                    <span style="color:var(--warning);">Expired</span>
                @endif
            </div>
            <div class="stat-label">Verification Status</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="font-size:28px; color:var(--secondary);">{{ number_format($vehicle->weight) }} kg</div>
            <div class="stat-label">Vehicle Weight</div>
        </div>
    </div>

    <div class="grid-2">
        {{-- Vehicle Profile Card --}}
        <div class="card glass">
            <h3 class="card-title">
                <svg width="20" height="20" fill="none" stroke="var(--primary)" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><path d="M9 3v18m6-18v18"/></svg>
                Registration Details
            </h3>
            <p class="card-subtitle" style="margin-bottom:20px;">Master record fields fetched from Oracle database schema.</p>

            <table style="width:100%; border-collapse:collapse;" class="details-table">
                <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                    <td style="padding:12px 0; font-weight:700; color:var(--text-muted);">Vehicle ID</td>
                    <td style="padding:12px 0; text-align:right; font-family:var(--font-mono); font-weight:800;">#{{ $vehicle->vehicle_id }}</td>
                </tr>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                    <td style="padding:12px 0; font-weight:700; color:var(--text-muted);">Registration Number</td>
                    <td style="padding:12px 0; text-align:right; font-family:var(--font-mono); font-weight:800; color:var(--primary);">{{ $vehicle->registration_number }}</td>
                </tr>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                    <td style="padding:12px 0; font-weight:700; color:var(--text-muted);">Vehicle Type</td>
                    <td style="padding:12px 0; text-align:right; font-weight:700;">{{ $vehicle->vehicle_type }}</td>
                </tr>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                    <td style="padding:12px 0; font-weight:700; color:var(--text-muted);">Owner Name</td>
                    <td style="padding:12px 0; text-align:right; font-weight:700; color:var(--text-primary);">{{ $vehicle->owner_name }}</td>
                </tr>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                    <td style="padding:12px 0; font-weight:700; color:var(--text-muted);">Owner Phone</td>
                    <td style="padding:12px 0; text-align:right; font-family:var(--font-mono);">{{ $vehicle->owner_phone }}</td>
                </tr>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                    <td style="padding:12px 0; font-weight:700; color:var(--text-muted);">Color</td>
                    <td style="padding:12px 0; text-align:right;">{{ $vehicle->color }}</td>
                </tr>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                    <td style="padding:12px 0; font-weight:700; color:var(--text-muted);">Manufacturer / Model</td>
                    <td style="padding:12px 0; text-align:right;">
                        {{ $vehicle->manufacturer ?? 'N/A' }} {{ $vehicle->model ? ' - ' . $vehicle->model : '' }}
                    </td>
                </tr>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                    <td style="padding:12px 0; font-weight:700; color:var(--text-muted);">Registration Date</td>
                    <td style="padding:12px 0; text-align:right;">
                        {{ $vehicle->registration_date ? \Carbon\Carbon::parse($vehicle->registration_date)->format('M d, Y') : 'N/A' }}
                    </td>
                </tr>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.06);">
                    <td style="padding:12px 0; font-weight:700; color:var(--text-muted);">Registered By</td>
                    <td style="padding:12px 0; text-align:right;">{{ $vehicle->creator_name ?? 'System Seeder' }}</td>
                </tr>
                @if($vehicle->updated_at)
                    <tr>
                        <td style="padding:12px 0; font-weight:700; color:var(--text-muted);">Last Updated</td>
                        <td style="padding:12px 0; text-align:right;">
                            {{ \Carbon\Carbon::parse($vehicle->updated_at)->format('M d, Y H:i:s') }}
                        </td>
                    </tr>
                @endif
            </table>
        </div>

        {{-- Toll Passage ledger --}}
        <div class="card glass">
            <h3 class="card-title">
                <svg width="20" height="20" fill="none" stroke="var(--primary)" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Toll Passage History
            </h3>
            <p class="card-subtitle" style="margin-bottom:20px;">Displays the 10 most recent toll transactions logged for this vehicle.</p>

            <div class="table-wrapper" style="max-height: 400px; overflow-y: auto;">
                @if(count($transactions) > 0)
                    <table>
                        <thead>
                            <tr>
                                <th>Trans ID</th>
                                <th>Type</th>
                                <th>Fee Charged</th>
                                <th>Payment</th>
                                <th>Operator</th>
                                <th>Passed At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $tx)
                                <tr>
                                    <td><span style="font-family:var(--font-mono); font-weight:700;">#{{ $tx->transaction_id }}</span></td>
                                    <td>{{ $tx->vehicle_type }}</td>
                                    <td><strong>${{ number_format($tx->toll_amount, 2) }}</strong></td>
                                    <td>
                                        <span class="badge {{ $tx->payment_method === 'RFID' ? 'badge-info' : ($tx->payment_method === 'Card' ? 'badge-warning' : 'badge-success') }}">
                                            {{ $tx->payment_method }}
                                        </span>
                                    </td>
                                    <td>{{ $tx->operator_name ?? 'System' }}</td>
                                    <td style="font-size:12px;">{{ \Carbon\Carbon::parse($tx->created_at)->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="text-align:center; padding:64px 20px; color:var(--text-muted);">
                        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:12px; opacity:0.6;"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <h4>No Passage History</h4>
                        <p style="font-size:12px; margin-top:4px;">No toll records reference this vehicle yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection
