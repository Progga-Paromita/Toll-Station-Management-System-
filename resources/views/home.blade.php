@extends('layouts.layout')

@section('title', 'TollStation - Home')

@section('content')
    <!-- Hero Section -->
    <section class="hero">
        <span class="hero-badge">Toll station platform</span>
        <h1 class="hero-title">Automated <span>Toll Management</span> System</h1>
        <p class="hero-subtitle">
            A scalable and secure toll station platform with automated operations, detailed audit logging, and role-based access control to ensure efficient and reliable management.
        </p>

        @guest
            <div style="display:flex; justify-content:center; gap:16px;">
                <a href="{{ route('login', ['role' => 'operator']) }}" class="btn btn-primary">Member Gate</a>
                <a href="{{ route('login', ['role' => 'admin']) }}" class="btn btn-secondary">Admin Console</a>
            </div>
        @else
            <div>
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">Go to Admin Dashboard</a>
                @else
                    <a href="{{ route('operator.dashboard') }}" class="btn btn-primary">Go to Member Panel</a>
                @endif
            </div>
        @endguest
    </section>

    <!-- Live Statistics (Select Query Data) -->
    <div class="section-header">
        <div>
            <h2 class="section-title">Station Traffic & Analytics</h2>
            <p class="section-desc">Real-time statistics queried directly from Oracle using aggregation joins.</p>
        </div>
        <span class="badge badge-success" style="display:inline-flex; gap:6px; align-items:center;">
            <span style="width:8px; height:8px; background-color:var(--success); border-radius:50%; display:inline-block; animation: pulse 1.5s infinite;"></span>
            Live
        </span>
    </div>

    <div class="stats-container">
        <div class="stat-card glass">
            <div class="stat-value">{{ number_format($stats->total_vehicles) }}</div>
            <div class="stat-label">Vehicles Passed</div>
        </div>
        <div class="stat-card glass">
            <div class="stat-value">${{ number_format($stats->total_revenue, 2) }}</div>
            <div class="stat-label">Total Collection</div>
        </div>
        <div class="stat-card glass">
            <div class="stat-value">{{ number_format($stats->cash_count) }}</div>
            <div class="stat-label">Cash Payments</div>
        </div>
        <div class="stat-card glass">
            <div class="stat-value">{{ number_format($stats->card_count) }}</div>
            <div class="stat-label">Card Payments</div>
        </div>
        <div class="stat-card glass">
            <div class="stat-value">{{ number_format($stats->rfid_count) }}</div>
            <div class="stat-label">RFID Scans</div>
        </div>
    </div>

    <style>
        @keyframes pulse {
            0% { transform: scale(0.9); opacity: 0.6; }
            50% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(0.9); opacity: 0.6; }
        }
    </style>

    <!-- Grid: Pricing & Recent Activity -->
    <div class="grid-2">

        <!-- Toll Pricing Table -->
        <div class="card glass">
            <h3 class="card-title">
                <svg width="20" height="20" fill="none" stroke="var(--primary)" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                    <line x1="2" y1="10" x2="22" y2="10"/>
                </svg>
                Toll Pricing Rates
            </h3>
            <p class="card-subtitle">Rates charged based on vehicle weight and axle classifications.</p>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Vehicle Type</th>
                            <th>Rate Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rates as $type => $amount)
                            <tr>
                                <td><strong>{{ $type }}</strong></td>
                                <td>${{ number_format($amount, 2) }}</td>
                                <td><span class="badge badge-success">Active</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Dynamic CSS Bar Chart -->
            <div class="rates-chart">
                @foreach($rates as $type => $amount)
                    <div class="chart-bar-container">
                        <span class="chart-value">${{ intval($amount) }}</span>
                        <!-- Height is proportioned to max rate $350 (divided by 3.5 for percentage height) -->
                        <div class="chart-bar" style="height: {{ ($amount / 350) * 120 }}px;"></div>
                        <span class="chart-label">{{ $type }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Transactions Feed (Viewer Read-Only privilege) -->
        <div class="card glass">
            <h3 class="card-title">
                <svg width="20" height="20" fill="none" stroke="var(--primary)" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Recent Transactions Feed
            </h3>
            <p class="card-subtitle">Public ledger showing the latest 10 toll entries. Viewers have SELECT permission.</p>

            <div class="table-wrapper" style="max-height: 380px; overflow-y: auto;">
                @if(count($transactions) > 0)
                    <table>
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Member</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $tx)
                                <tr>
                                    <td><span style="font-family: var(--font-mono); font-weight: 700; color: var(--text-primary);">{{ $tx->vehicle_number }}</span></td>
                                    <td>{{ $tx->vehicle_type }}</td>
                                    <td>${{ number_format($tx->toll_amount, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $tx->payment_method == 'RFID' ? 'badge-info' : ($tx->payment_method == 'Card' ? 'badge-warning' : 'badge-success') }}">
                                            {{ $tx->payment_method }}
                                        </span>
                                    </td>
                                    <td><span style="font-size: 12px;">{{ $tx->operator_name ?? 'System' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                        No transactions registered yet.
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection
