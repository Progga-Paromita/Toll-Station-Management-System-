@extends('layouts.layout')

@section('title', 'TollStation - Member Dashboard')

@section('content')
    <div class="section-header">
        <div>
            <h1 class="section-title">Member Control Panel</h1>
            <p class="section-desc">Welcome back, <strong>{{ Auth::user()->name }}</strong>. Register toll entries and monitor traffic.</p>
        </div>
        <span class="badge badge-info">MEMBER PRIVILEGES ACTIVATED</span>
    </div>

    <!-- Grid Layout: Insert Form & Personal Ledger -->
    <div class="grid-2">
        
        <!-- Toll Insertion Form -->
        <div class="card glass">
            <h3 class="card-title">
                <svg width="20" height="20" fill="none" stroke="var(--primary)" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Register Toll Passage
            </h3>
            <p class="card-subtitle">
                Inserting a new transaction. Member role is granted <code>INSERT</code> & <code>SELECT</code> privileges.
            </p>

            <form action="{{ route('operator.transaction.store') }}" method="POST" id="transactionForm">
                @csrf
                
                <div class="form-group">
                    <label for="vehicle_number" class="form-label">Vehicle Registration Number</label>
                    <input type="text" name="vehicle_number" id="vehicle_number" class="form-control" placeholder="e.g. DHK-12-3456" required style="text-transform: uppercase;">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="vehicle_type" class="form-label">Vehicle Type</label>
                        <select name="vehicle_type" id="vehicle_type" class="form-control" onchange="updateRate()" required>
                            <option value="Car">Car</option>
                            <option value="Bike">Bike</option>
                            <option value="Bus">Bus</option>
                            <option value="Truck">Truck</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-control" required>
                            <option value="Cash">Cash</option>
                            <option value="Card">Card</option>
                            <option value="RFID">RFID</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 28px;">
                    <label class="form-label">Toll Fee Charged</label>
                    <div style="position: relative; display: flex; align-items: center;">
                        <span style="position: absolute; left: 16px; font-weight: 700; color: var(--primary);">$</span>
                        <input type="text" id="toll_rate_display" class="form-control" style="padding-left: 32px; font-weight: 800; color: var(--primary);" readonly value="100.00">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Record Transaction
                </button>
            </form>
        </div>

        <!-- Personal Transaction History -->
        <div class="card glass">
            <h3 class="card-title">
                <svg width="20" height="20" fill="none" stroke="var(--primary)" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Your Recorded Passages
            </h3>
            <p class="card-subtitle">
                History of toll entries created by you.
            </p>

            <div class="table-wrapper" style="max-height: 380px; overflow-y: auto;">
                @if(count($operatorTransactions) > 0)
                    <table>
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Type</th>
                                <th>Fee</th>
                                <th>Method</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($operatorTransactions as $tx)
                                <tr>
                                    <td><span style="font-family: var(--font-mono); font-weight: 700; color: var(--text-primary);">{{ $tx->vehicle_number }}</span></td>
                                    <td>{{ $tx->vehicle_type }}</td>
                                    <td>${{ number_format($tx->toll_amount, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $tx->payment_method == 'RFID' ? 'badge-info' : ($tx->payment_method == 'Card' ? 'badge-warning' : 'badge-success') }}">
                                            {{ $tx->payment_method }}
                                        </span>
                                    </td>
                                    <td style="font-size: 12px;">{{ \Carbon\Carbon::parse($tx->created_at)->format('H:i:s') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                        No transactions registered today.
                    </div>
                @endif
            </div>
        </div>

    </div>


@endsection

@section('scripts')
    <script>
        const rates = {
            'Car': '100.00',
            'Bike': '50.00',
            'Bus': '250.00',
            'Truck': '350.00'
        };

        function updateRate() {
            const select = document.getElementById('vehicle_type');
            const display = document.getElementById('toll_rate_display');
            const selectedType = select.value;
            display.value = rates[selectedType];
        }

        // Run on load to set initial state
        updateRate();
    </script>
@endsection
