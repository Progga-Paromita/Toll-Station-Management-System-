@extends('layouts.layout')

@section('title', 'TollStation - Admin Dashboard')

@section('content')
    <div class="section-header">
        <div>
            <h1 class="section-title">Admin Operations Control</h1>
            <p class="section-desc">Manage system accounts, review lock status, and monitor global login sessions.</p>
        </div>
        <span class="badge badge-danger">ADMINISTRATOR ACCESS</span>
    </div>

    <!-- User Accounts Section -->
    <div class="card glass" style="margin-bottom: 32px;">
        <h3 class="card-title">
            <svg width="20" height="20" fill="none" stroke="var(--primary)" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Registered Users & Security Accounts
        </h3>
        <p class="card-subtitle">List of user accounts queried from the database. Admins can unlock locked members.</p>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Attempts</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                        <tr>
                            <td><span style="font-family: var(--font-mono); font-weight: 700;">#{{ $u->user_id }}</span></td>
                            <td><strong>{{ $u->name }}</strong></td>
                            <td>{{ $u->username }}</td>
                            <td>{{ $u->email }}</td>
                            <td>
                                <span class="badge {{ $u->role_name === 'ADMIN' ? 'badge-danger' : 'badge-info' }}">
                                    {{ $u->role_name }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $u->login_attempts >= 3 ? 'badge-danger' : ($u->login_attempts > 0 ? 'badge-warning' : 'badge-success') }}">
                                    {{ $u->login_attempts }} / 3
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $u->status === 'ACTIVE' ? 'badge-success' : 'badge-danger' }}">
                                    {{ $u->status }}
                                </span>
                            </td>
                            <td>
                                @if($u->status === 'INACTIVE' || $u->login_attempts > 0)
                                    <form action="{{ route('admin.unlock', ['user_id' => $u->user_id]) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button type="submit" class="btn btn-accent btn-sm">Unlock & Reset</button>
                                    </form>
                                @else
                                    <span style="font-size: 12px; color: var(--text-muted);">Secure</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Global Audit Session Logs (Oracle Trigger Output) -->
    <div class="card glass">
        <h3 class="card-title">
            <svg width="20" height="20" fill="none" stroke="var(--primary)" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 009 11M9 11V9c0-2.707 1.293-5.13 3.32-6.68m0 0A13.91 13.91 0 0115 11v2c0 .91.564 1.7 1.378 2.052M12 2.27c.961.438 1.83 1.05 2.58 1.8M15 11c0 3.517 1.009 6.799 2.753 9.571m-3.44-2.04l.054-.09a13.916 13.916 0 00-2.58-7.74M5 21H3a2 2 0 01-2-2v-2c0-1.1.9-2 2-2h2m14 6h2a2 2 0 002-2v-2c0-1.1-.9-2-2-2h-2"/>
            </svg>
            System-Wide Access Audit Log
        </h3>
        <p class="card-subtitle">Session events recorded globally. Logs are captured via the Oracle <code>trg_user_login</code> database trigger.</p>

        <div class="table-wrapper">
            @if(count($loginLogs) > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Log ID</th>
                            <th>Username</th>
                            <th>Login Time</th>
                            <th>IP Address</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($loginLogs as $log)
                            <tr>
                                <td><span style="font-family: var(--font-mono); font-weight: 700;">#{{ $log->log_id }}</span></td>
                                <td><strong>{{ $log->username }}</strong></td>
                                <td>{{ \Carbon\Carbon::parse($log->login_time)->format('Y-m-d H:i:s') }}</td>
                                <td><span style="font-family: var(--font-mono);">{{ $log->ip_address }}</span></td>
                                <td>
                                    <span class="badge {{ $log->login_status === 'SUCCESS' ? 'badge-success' : 'badge-danger' }}">
                                        {{ $log->login_status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                    No access log history available.
                </div>
            @endif
        </div>
    </div>
@endsection
