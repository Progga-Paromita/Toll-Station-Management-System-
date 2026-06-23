@extends('layouts.layout')

@section('title', 'TollStation - Sign In')

@section('content')
    <div style="max-width: 480px; margin: 40px auto;">

        <!-- Role-Wise Login Tabs -->
        <div class="tabs-container">
            <button type="button" class="tab-btn {{ $role === 'operator' ? 'active' : '' }}" onclick="switchRole('operator')">
                Member Sign In
            </button>
            <button type="button" class="tab-btn {{ $role === 'admin' ? 'active' : '' }}" onclick="switchRole('admin')">
                Admin Sign In
            </button>
        </div>

        <div class="card glass">
            <h2 class="card-title" id="loginCardTitle" style="margin-bottom:8px;">
                {{ $role === 'admin' ? 'Administrator Login' : 'Member Secure Login' }}
            </h2>
            <p class="card-subtitle">
                {{ $role === 'admin' ? 'Enter credentials to manage system settings & view PL/SQL playground.' : 'Enter your credentials to record toll transactions.' }}
            </p>

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="alert alert-danger" style="flex-direction: column; align-items: flex-start; gap: 4px;">
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

            <form action="{{ route('login') }}" method="POST" id="loginForm">
                @csrf
                <input type="hidden" name="role" id="roleInput" value="{{ $role }}">

                <div class="form-group">
                    <label for="username" class="form-label">Username or Email</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Enter your username or email" value="{{ old('username') }}" required autofocus autocomplete="username">
                </div>

                <div class="form-group" style="margin-bottom: 28px;">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter secure password" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    SignIn
                </button>
            </form>

            <div style="margin-top: 24px; text-align: center; border-top: 1px solid var(--border-color); padding-top: 20px;">
                <p style="font-size: 13px; color: var(--text-secondary);">
                    Don't have an account?
                    <span id="signupLinkContainer">
                        @if($role === 'admin')
                            <a href="{{ route('register', ['role' => 'admin']) }}" style="color: var(--primary); font-weight: 700;">Sign Up as Admin</a>
                        @else
                            <a href="{{ route('register', ['role' => 'operator']) }}" style="color: var(--primary); font-weight: 700;">Sign Up as Member</a>
                        @endif
                    </span>
                </p>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        function switchRole(role) {
            // Update URL search parameters without reloading
            const url = new URL(window.location);
            url.searchParams.set('role', role);
            window.history.pushState({}, '', url);

            // Update inputs, tabs and texts
            document.getElementById('roleInput').value = role;

            const tabs = document.querySelectorAll('.tab-btn');
            tabs[0].classList.toggle('active', role === 'operator');
            tabs[1].classList.toggle('active', role === 'admin');

            const title = document.getElementById('loginCardTitle');
            const subtitle = title.nextElementSibling;
            const signupContainer = document.getElementById('signupLinkContainer');

            if (role === 'admin') {
                title.textContent = 'Administrator Login';
                subtitle.textContent = 'Enter credentials to manage system settings & view PL/SQL playground.';
                signupContainer.innerHTML = `<a href="{{ route('register') }}?role=admin" style="color: var(--primary); font-weight: 700;">Sign Up as Admin</a>`;
            } else {
                title.textContent = 'Member Secure Login';
                subtitle.textContent = 'Enter your credentials to record toll transactions.';
                signupContainer.innerHTML = `<a href="{{ route('register') }}?role=operator" style="color: var(--primary); font-weight: 700;">Sign Up as Member</a>`;
            }
        }
    </script>
@endsection
