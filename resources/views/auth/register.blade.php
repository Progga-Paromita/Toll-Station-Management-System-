@extends('layouts.layout')

@section('title', 'TollStation - Sign Up')

@section('content')
    <div style="max-width: 480px; margin: 40px auto;">
        
        <!-- Role-Wise Signup Tabs -->
        <div class="tabs-container">
            <button type="button" class="tab-btn {{ $role === 'operator' ? 'active' : '' }}" onclick="switchRole('operator')">
                Member Register
            </button>
            <button type="button" class="tab-btn {{ $role === 'admin' ? 'active' : '' }}" onclick="switchRole('admin')">
                Admin Register
            </button>
        </div>

        <div class="card glass">
            <h2 class="card-title" id="registerCardTitle" style="margin-bottom:8px;">
                {{ $role === 'admin' ? 'Administrator Signup' : 'Member Registration' }}
            </h2>
            <p class="card-subtitle">
                {{ $role === 'admin' ? 'Create an admin account to manage systems & execute PL/SQL playground commands.' : 'Create a member account to log vehicle toll entries.' }}
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

            <form action="{{ route('register') }}" method="POST" id="registerForm">
                @csrf
                <input type="hidden" name="role" id="roleInput" value="{{ $role }}">

                <div class="form-group">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Enter full name" value="{{ old('name') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Choose a unique username" value="{{ old('username') }}" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Enter email address" value="{{ old('email') }}" required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter secure password" required>
                </div>

                <div class="form-group" style="margin-bottom: 28px;">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Confirm your password" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Create Secure Account
                </button>
            </form>

            <div style="margin-top: 24px; text-align: center; border-top: 1px solid var(--border-color); padding-top: 20px;">
                <p style="font-size: 13px; color: var(--text-secondary);">
                    Already have an account? 
                    <a href="{{ route('login', ['role' => $role]) }}" id="loginLink" style="color: var(--primary); font-weight: 700;">Sign In here</a>
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

            const title = document.getElementById('registerCardTitle');
            const subtitle = title.nextElementSibling;
            const loginLink = document.getElementById('loginLink');

            if (role === 'admin') {
                title.textContent = 'Administrator Signup';
                subtitle.textContent = 'Create an admin account to manage systems & execute PL/SQL playground commands.';
                loginLink.href = `{{ route('login') }}?role=admin`;
            } else {
                title.textContent = 'Member Registration';
                subtitle.textContent = 'Create a member account to log vehicle toll entries.';
                loginLink.href = `{{ route('login') }}?role=operator`;
            }
        }
    </script>
@endsection
