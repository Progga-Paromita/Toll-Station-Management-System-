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

            <!-- ── Change Password Card ─────────────────────── -->
            <div class="card glass change-password-card" id="change-password">
                <h3 class="card-title">
                    <svg width="20" height="20" fill="none" stroke="var(--warning)" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    Change Password
                </h3>
                <p class="card-subtitle">Update your account password. You must confirm your current password to proceed.</p>

                {{-- Success / Error flash for password change --}}
                @if(session('success'))
                    {{-- already shown in layout, no duplicate needed --}}
                @endif

                @if ($errors->has('current_password') || $errors->has('new_password') || $errors->has('new_password_confirmation'))
                    <div class="alert alert-danger" style="flex-direction: column; align-items: flex-start; gap: 4px; margin-bottom: 20px;">
                        @foreach ($errors->all() as $error)
                            <div style="display:flex; gap:8px; align-items:center;">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <span>{{ $error }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('password.change') }}" method="POST">
                    @csrf

                    <div class="form-group" style="position: relative;">
                        <label for="cp_current" class="form-label">Current Password</label>
                        <input
                            type="password"
                            name="current_password"
                            id="cp_current"
                            class="form-control"
                            placeholder="Enter your current password"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" onclick="togglePw('cp_current', this)" style="position:absolute;right:12px;top:38px;background:none;border:none;cursor:pointer;color:var(--text-muted);">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>

                    <div class="form-row">
                        <div class="form-group" style="position: relative;">
                            <label for="cp_new" class="form-label">New Password</label>
                            <input
                                type="password"
                                name="new_password"
                                id="cp_new"
                                class="form-control"
                                placeholder="Minimum 6 characters"
                                required
                                autocomplete="new-password"
                            >
                            <button type="button" onclick="togglePw('cp_new', this)" style="position:absolute;right:12px;top:38px;background:none;border:none;cursor:pointer;color:var(--text-muted);">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>

                        <div class="form-group" style="position: relative;">
                            <label for="cp_confirm" class="form-label">Confirm New Password</label>
                            <input
                                type="password"
                                name="new_password_confirmation"
                                id="cp_confirm"
                                class="form-control"
                                placeholder="Repeat new password"
                                required
                                autocomplete="new-password"
                            >
                            <button type="button" onclick="togglePw('cp_confirm', this)" style="position:absolute;right:12px;top:38px;background:none;border:none;cursor:pointer;color:var(--text-muted);">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Password strength bar for new password -->
                    <div id="cp_strength_wrap" style="margin-top: -4px; margin-bottom: 20px; display: none;">
                        <div style="height: 4px; border-radius: 4px; background: var(--border-color); overflow: hidden;">
                            <div id="cp_strength_fill" style="height:100%;width:0%;border-radius:4px;transition:width .3s,background .3s;"></div>
                        </div>
                        <span id="cp_strength_label" style="font-size:11px;color:var(--text-muted);margin-top:4px;display:block;"></span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm" style="padding: 10px 24px;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M5 13l4 4L19 7"/>
                        </svg>
                        Update Password
                    </button>
                </form>
            </div>

        </div>

    </div>
@endsection

@section('scripts')
<script>
    function togglePw(id, btn) {
        const input = document.getElementById(id);
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        btn.style.color = isHidden ? 'var(--primary)' : 'var(--text-muted)';
    }

    // Strength meter on new password field
    const cpNew = document.getElementById('cp_new');
    const cpWrap = document.getElementById('cp_strength_wrap');
    const cpFill = document.getElementById('cp_strength_fill');
    const cpLabel = document.getElementById('cp_strength_label');

    if (cpNew) {
        cpNew.addEventListener('input', () => {
            const v = cpNew.value;
            cpWrap.style.display = v.length > 0 ? 'block' : 'none';
            let score = 0;
            if (v.length >= 6) score++;
            if (v.length >= 10) score++;
            if (/[A-Z]/.test(v)) score++;
            if (/[0-9]/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;
            const levels = [
                { label: 'Very Weak', color: '#ef4444', width: '20%' },
                { label: 'Weak',      color: '#f97316', width: '40%' },
                { label: 'Fair',      color: '#f59e0b', width: '60%' },
                { label: 'Strong',    color: '#10b981', width: '80%' },
                { label: 'Very Strong', color: '#059669', width: '100%' },
            ];
            const lv = levels[Math.min(score, 4)];
            cpFill.style.width = lv.width;
            cpFill.style.background = lv.color;
            cpLabel.textContent = lv.label;
            cpLabel.style.color = lv.color;
        });
    }

    // Auto-open change-password section if navigated via hash
    if (window.location.hash === '#change-password') {
        const section = document.getElementById('change-password');
        if (section) {
            setTimeout(() => section.scrollIntoView({ behavior: 'smooth', block: 'start' }), 200);
        }
    }
</script>
@endsection
