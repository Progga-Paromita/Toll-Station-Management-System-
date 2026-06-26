@extends('layouts.layout')

@section('title', 'TollStation - View Station')

@section('content')

    <div class="section-header">
        <div>
            <h1 class="section-title">{{ $station->station_name }}</h1>
            <p class="section-desc">Full details for this registered toll collection station.</p>
        </div>
        <div style="display:flex; gap:10px; align-items:center;">
            <a href="{{ route('admin.stations.edit', $station->station_id) }}" class="btn btn-primary btn-sm">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
            </a>
            <a href="{{ route('admin.stations.index') }}" class="btn btn-outline btn-sm">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                Back
            </a>
        </div>
    </div>

    <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap:28px;">

        {{-- Left: Core Details --}}
        <div>
            <div class="card glass" style="margin-bottom:24px;">
                <h3 class="card-title">
                    <svg width="20" height="20" fill="none" stroke="var(--primary)" stroke-width="2" viewBox="0 0 24 24"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                    Station Details
                </h3>

                <div class="profile-info-item">
                    <span class="profile-info-label">Station ID</span>
                    <span style="font-family:var(--font-mono); font-weight:700;">#{{ $station->station_id }}</span>
                </div>
                <div class="profile-info-item">
                    <span class="profile-info-label">Station Name</span>
                    <span style="font-weight:700;">{{ $station->station_name }}</span>
                </div>
                <div class="profile-info-item">
                    <span class="profile-info-label">District</span>
                    <span>{{ $station->district }}</span>
                </div>
                <div class="profile-info-item">
                    <span class="profile-info-label">Highway</span>
                    <span style="font-size:13px;">{{ $station->highway }}</span>
                </div>
                <div class="profile-info-item">
                    <span class="profile-info-label">Station Type</span>
                    <span class="badge
                        @if($station->station_type === 'Bridge') badge-info
                        @elseif($station->station_type === 'Expressway') badge-warning
                        @else badge-success @endif">
                        {{ $station->station_type }}
                    </span>
                </div>
                <div class="profile-info-item">
                    <span class="profile-info-label">Number of Lanes</span>
                    <span style="font-weight:800; font-size:18px; color:var(--primary);">{{ $station->lane_count }}</span>
                </div>
                <div class="profile-info-item">
                    <span class="profile-info-label">Opening Date</span>
                    <span>{{ $station->opening_date ? \Carbon\Carbon::parse($station->opening_date)->format('F d, Y') : 'N/A' }}</span>
                </div>
                <div class="profile-info-item">
                    <span class="profile-info-label">Status</span>
                    @if($station->status === 'ACTIVE')
                        <span class="badge badge-success">ACTIVE</span>
                    @elseif($station->status === 'INACTIVE')
                        <span class="badge badge-danger">INACTIVE</span>
                    @else
                        <span class="badge badge-warning">UNDER MAINTENANCE</span>
                    @endif
                </div>
                <div class="profile-info-item">
                    <span class="profile-info-label">Created By</span>
                    <span>{{ $station->created_by_name ?? 'System' }}</span>
                </div>
                <div class="profile-info-item">
                    <span class="profile-info-label">Created At</span>
                    <span style="font-size:13px;">{{ $station->created_at ? \Carbon\Carbon::parse($station->created_at)->format('M d, Y H:i') : 'N/A' }}</span>
                </div>
                @if($station->updated_at)
                <div class="profile-info-item">
                    <span class="profile-info-label">Last Updated</span>
                    <span style="font-size:13px;">{{ \Carbon\Carbon::parse($station->updated_at)->format('M d, Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Right: Transaction info + Actions --}}
        <div>
            {{-- Transaction Count Card --}}
            <div class="card glass" style="margin-bottom:24px; text-align:center;">
                <div style="font-size:56px; font-weight:800; color:{{ $transactionCount > 0 ? 'var(--primary)' : 'var(--text-muted)' }}; margin-bottom:8px;">{{ $transactionCount }}</div>
                <div style="font-size:14px; font-weight:600; color:var(--text-secondary); text-transform:uppercase; letter-spacing:1px;">Toll Transactions</div>
                <p style="font-size:12px; color:var(--text-muted); margin-top:8px;">
                    @if($transactionCount > 0)
                        This station has recorded transactions. <strong>It cannot be deleted</strong> (referential integrity).
                    @else
                        No transactions recorded yet. This station can be deleted.
                    @endif
                </p>
            </div>

            {{-- SQL Demonstrated --}}
            <div class="card glass" style="margin-bottom:24px;">
                <h3 class="card-title" style="font-size:16px;">
                    <svg width="16" height="16" fill="none" stroke="var(--success)" stroke-width="2" viewBox="0 0 24 24"><path d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                    SQL Demo — SELECT + JOIN
                </h3>
                <pre style="background:#0d1117; color:#58a6ff; font-family:var(--font-mono); font-size:11px; padding:12px; border-radius:8px; overflow-x:auto; line-height:1.6;">SELECT s.*, u.name AS created_by_name
FROM toll_stations s
LEFT JOIN users u
  ON s.created_by = u.user_id
WHERE s.station_id = {{ $station->station_id }};</pre>
            </div>

            {{-- Danger Zone --}}
            <div class="card glass" style="border:1px solid rgba(239,68,68,0.3); background:rgba(239,68,68,0.04);">
                <h3 class="card-title" style="color:var(--danger); font-size:16px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Danger Zone
                </h3>
                <p style="font-size:13px; color:var(--text-secondary); margin-bottom:16px;">
                    Deleting a station with existing transactions will be blocked by referential integrity rules.
                </p>
                <form action="{{ route('admin.stations.destroy', $station->station_id) }}" method="POST"
                      onsubmit="return confirm('Permanently delete this station? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm" style="background:rgba(239,68,68,0.15); color:var(--danger); border:1px solid rgba(239,68,68,0.3);">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        Delete This Station
                    </button>
                </form>
            </div>
        </div>

    </div>

@endsection
