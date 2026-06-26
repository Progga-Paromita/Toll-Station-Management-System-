@extends('layouts.layout')

@section('title', 'TollStation - Forgot Password')

@section('content')
    <div style="max-width: 480px; margin: 40px auto;">

        <!-- Page Header -->
        <div style="text-align: center; margin-bottom: 32px;">
            <div style="
                width: 72px; height: 72px;
                border-radius: 50%;
                background: linear-gradient(135deg, var(--warning), #f97316);
                margin: 0 auto 20px auto;
                display: flex; align-items: center; justify-content: center;
                box-shadow: 0 8px 24px rgba(245, 158, 11, 0.35);
            ">
                <svg width="32" height="32" fill="none" stroke="#ffffff" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>
            <h1 style="font-size: 26px; font-weight: 800; margin-bottom: 8px;">Forgot Password?</h1>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.6;">
                Verify your identity with your registered <strong>username</strong> and <strong>email</strong>, then set a new password.
            </p>
        </div>

        <div class="card glass">

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="alert alert-danger" style="flex-direction: column; align-items: flex-start; gap: 4px; margin-bottom: 24px;">
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

            <form action="{{ route('password.forgot') }}" method="POST">
                @csrf

                <!-- Step 1: Identity Verification -->
                <p style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 16px;">
                    Step 1 — Verify Identity
                </p>

                <div class="form-group">
                    <label for="fp_username" class="form-label">Username</label>
                    <input
                        type="text"
                        name="username"
                        id="fp_username"
                        class="form-control"
                        placeholder="Enter your account username"
                        value="{{ old('username') }}"
                        required
                        autofocus
                        autocomplete="username"
                    >
                </div>

                <div class="form-group">
                    <label for="fp_email" class="form-label">Registered Email</label>
                    <input
                        type="email"
                        name="email"
                        id="fp_email"
                        class="form-control"
                        placeholder="Enter your registered email address"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                    >
                </div>

                <hr style="border: none; border-top: 1px solid var(--border-color); margin: 24px 0;">

                <!-- Step 2: New Password -->
                <p style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 16px;">
                    Step 2 — Set New Password
                </p>

                <div class="form-group" style="position: relative;">
                    <label for="fp_new_password" class="form-label">New Password</label>
                    <input
                        type="password"
                        name="new_password"
                        id="fp_new_password"
                        class="form-control"
                        placeholder="Minimum 6 characters"
                        required
                        autocomplete="new-password"
                    >
                    <button type="button" onclick="togglePassword('fp_new_password', this)" style="
                        position: absolute; right: 12px; top: 38px;
                        background: none; border: none; cursor: pointer;
                        color: var(--text-muted); padding: 4px;
                    ">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>

                <div class="form-group" style="margin-bottom: 28px; position: relative;">
                    <label for="fp_new_password_confirmation" class="form-label">Confirm New Password</label>
                    <input
                        type="password"
                        name="new_password_confirmation"
                        id="fp_new_password_confirmation"
                        class="form-control"
                        placeholder="Repeat your new password"
                        required
                        autocomplete="new-password"
                    >
                    <button type="button" onclick="togglePassword('fp_new_password_confirmation', this)" style="
                        position: absolute; right: 12px; top: 38px;
                        background: none; border: none; cursor: pointer;
                        color: var(--text-muted); padding: 4px;
                    ">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>

                <!-- Password strength indicator -->
                <div id="fp_strength_bar" style="margin-top: -12px; margin-bottom: 24px; display: none;">
                    <div style="height: 4px; border-radius: 4px; background: var(--border-color); overflow: hidden;">
                        <div id="fp_strength_fill" style="height: 100%; width: 0%; border-radius: 4px; transition: width 0.3s, background 0.3s;"></div>
                    </div>
                    <span id="fp_strength_label" style="font-size: 11px; color: var(--text-muted); margin-top: 4px; display: block;"></span>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M5 13l4 4L19 7"/>
                    </svg>
                    Reset Password
                </button>
            </form>

            <div style="margin-top: 24px; text-align: center; border-top: 1px solid var(--border-color); padding-top: 20px;">
                <p style="font-size: 13px; color: var(--text-secondary);">
                    Remembered your password?
                    <a href="{{ route('login') }}" style="color: var(--primary); font-weight: 700;">Sign In</a>
                </p>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
<script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        btn.style.color = isHidden ? 'var(--primary)' : 'var(--text-muted)';
    }

    // Password strength meter
    const pwInput = document.getElementById('fp_new_password');
    const strengthBar = document.getElementById('fp_strength_bar');
    const strengthFill = document.getElementById('fp_strength_fill');
    const strengthLabel = document.getElementById('fp_strength_label');

    pwInput.addEventListener('input', () => {
        const val = pwInput.value;
        strengthBar.style.display = val.length > 0 ? 'block' : 'none';

        let score = 0;
        if (val.length >= 6) score++;
        if (val.length >= 10) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const levels = [
            { label: 'Very Weak', color: '#ef4444', width: '20%' },
            { label: 'Weak',      color: '#f97316', width: '40%' },
            { label: 'Fair',      color: '#f59e0b', width: '60%' },
            { label: 'Strong',    color: '#10b981', width: '80%' },
            { label: 'Very Strong', color: '#059669', width: '100%' },
        ];

        const level = levels[Math.min(score, 4)];
        strengthFill.style.width = level.width;
        strengthFill.style.background = level.color;
        strengthLabel.textContent = level.label;
        strengthLabel.style.color = level.color;
    });
</script>
@endsection
