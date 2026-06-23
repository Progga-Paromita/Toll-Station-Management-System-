@extends('layouts.layout')

@section('title', 'TollStation - Profile')

@section('content')
    <div class="section-header">
        <div>
            <h1 class="section-title">
                {{ Auth::user()->isAdmin() ? 'Admin Profile Console' : 'Member Profile' }}
            </h1>
            <p class="section-desc">Manage your account information and view database audit logs.</p>
        </div>
        <div>
            @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-sm">Admin Dashboard</a>
            @else
                <a href="{{ route('operator.dashboard') }}" class="btn btn-primary btn-sm">Member Panel</a>
            @endif
        </div>
    </div>

    <div class="profile-grid">
        
        <!-- Left Sidebar: Account Metadata -->
        <div class="profile-sidebar">
            <div class="card glass" style="text-align: center;">
                <div class="profile-avatar">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                
                <h2 style="font-size: 22px; font-weight: 700; margin-bottom: 4px;">{{ $user->name }}</h2>
                <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 24px;">{{ '@' . $user->username }}</p>

                <div class="profile-info-item">
                    <span class="profile-info-label">Account Role</span>
                    <span class="badge {{ $user->isAdmin() ? 'badge-danger' : 'badge-info' }}">
                        {{ $user->isAdmin() ? 'ADMIN' : 'MEMBER' }}
                    </span>
                </div>

                <div class="profile-info-item">
                    <span class="profile-info-label">Email Address</span>
                    <span>{{ $user->email }}</span>
                </div>

                <div class="profile-info-item">
                    <span class="profile-info-label">Account Status</span>
                    <span class="badge badge-success">{{ $user->status }}</span>
                </div>

                <div class="profile-info-item">
                    <span class="profile-info-label">Failed Attempts</span>
                    <span>{{ $user->login_attempts }} / 3</span>
                </div>

                <div class="profile-info-item">
                    <span class="profile-info-label">Joined On</span>
                    <span style="font-size: 13px;">{{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Right Content: Database Lab Trigger Logs -->
        <div class="profile-main">
            <div class="card glass" style="margin-bottom: 32px;">
                <h3 class="card-title">
                    <svg width="20" height="20" fill="none" stroke="var(--primary)" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Recent Session Audit Logs
                </h3>
                <p class="card-subtitle">
                    Latest session entries from the <code>login_logs</code> table. This data is populated automatically by the Oracle database trigger <code>trg_user_login</code> on successful logins.
                </p>

                <div class="table-wrapper">
                    @if(count($loginLogs) > 0)
                        <table>
                            <thead>
                                <tr>
                                    <th>Log ID</th>
                                    <th>Login Time</th>
                                    <th>IP Address</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($loginLogs as $log)
                                    <tr>
                                        <td><span style="font-family: var(--font-mono); font-weight: 700;">#{{ $log->log_id }}</span></td>
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
                            No session logs recorded.
                        </div>
                    @endif
                </div>

            </div>
        </div>

    </div>
@endsection
