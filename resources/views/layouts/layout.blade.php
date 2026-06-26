<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Toll Station Management System')</title>

    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- Inline script to load theme instantly to prevent flashing -->
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body>

    <!-- Sticky Navigation Bar -->
    <nav class="navbar glass">
        <div class="container navbar-container">
            <a href="{{ route('home') }}" class="logo">
                <svg width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/>
                    <circle cx="7" cy="17" r="2"/>
                    <path d="M9 17h6"/>
                    <circle cx="17" cy="17" r="2"/>
                </svg>
                TollStation
            </a>

            <div class="nav-links">
                <a href="{{ route('home') }}" class="nav-link {{ Route::is('home') ? 'active' : '' }}">Home</a>

                @auth
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ Route::is('admin.dashboard') ? 'active' : '' }}">Admin Dashboard</a>
                        <a href="{{ route('admin.playground') }}" class="nav-link {{ Route::is('admin.playground') ? 'active' : '' }}">PL/SQL Playground</a>
                    @elseif(Auth::user()->isOperator())
                        <a href="{{ route('operator.dashboard') }}" class="nav-link {{ Route::is('operator.dashboard') ? 'active' : '' }}">Member Panel</a>
                    @endif
                @endauth
            </div>

            <div class="nav-actions">
                <!-- Theme Toggle Button -->
                <button id="themeToggleBtn" class="theme-toggle" title="Toggle Theme">
                    <!-- Sun Icon -->
                    <svg id="sunIcon" style="display:none;" viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                    <!-- Moon Icon -->
                    <svg id="moonIcon" style="display:none;" viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>

                @auth
                    <!-- User Dropdown Menu -->
                    <div class="user-menu" id="userMenu">
                        <button class="profile-btn" id="userMenuBtn" title="{{ Auth::user()->name }}" aria-haspopup="true" aria-expanded="false">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </button>

                        <div class="user-dropdown" id="userDropdown" role="menu">
                            <!-- User Info Header -->
                            <div class="dropdown-header">
                                <div class="dropdown-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                                <div>
                                    <div class="dropdown-name">{{ Auth::user()->name }}</div>
                                    <div class="dropdown-role">
                                        <span class="badge {{ Auth::user()->isAdmin() ? 'badge-danger' : 'badge-info' }}" style="font-size: 10px;">
                                            {{ Auth::user()->isAdmin() ? 'ADMIN' : 'MEMBER' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="dropdown-divider"></div>

                            <!-- Profile Link -->
                            <a href="{{ route('profile') }}" class="dropdown-item" role="menuitem">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                My Profile
                            </a>

                            <!-- Change Password Link -->
                            <a href="{{ route('profile') }}#change-password" class="dropdown-item" role="menuitem">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                                Change Password
                            </a>

                            <div class="dropdown-divider"></div>

                            <!-- Logout -->
                            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" class="dropdown-item dropdown-item-danger" role="menuitem">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                        <polyline points="16 17 21 12 16 7"/>
                                        <line x1="21" y1="12" x2="9" y2="12"/>
                                    </svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <!-- Dropdowns or separate buttons for Register & Login -->
                    <a href="{{ route('login', ['role' => 'operator']) }}" class="btn btn-outline btn-sm">Sign In</a>

                    <a href="{{ route('register', ['role' => 'operator']) }}" class="btn btn-primary btn-sm">Sign Up</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content Body -->
    <main style="padding: 40px 0;">
        <div class="container">
            <!-- Toast notification alerts -->
            @if(session('success'))
                <div class="alert alert-success">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; {{ date('Y') }} TollStation. Oracle DB Lab Project - Toll Management System.</p>
            <p style="font-size: 12px; margin-top: 8px; color: var(--text-muted);">
                Integrated Topics: Tables, Check Constraints, Triggers, Procedures, Functions, Joins, Loops, and Records.
            </p>
        </div>
    </footer>

    <!-- Theme Toggler Javascript Logic -->
    <script>
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const sunIcon = document.getElementById('sunIcon');
        const moonIcon = document.getElementById('moonIcon');

        function updateToggleIcons(theme) {
            if (theme === 'dark') {
                sunIcon.style.display = 'block';
                moonIcon.style.display = 'none';
            } else {
                sunIcon.style.display = 'none';
                moonIcon.style.display = 'block';
            }
        }

        // Initialize icons on load
        const currentTheme = document.documentElement.getAttribute('data-theme');
        updateToggleIcons(currentTheme);

        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateToggleIcons(newTheme);
        });

        // ── User Dropdown Menu ──────────────────────────
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdown = document.getElementById('userDropdown');

        if (userMenuBtn && userDropdown) {
            userMenuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = userDropdown.classList.toggle('open');
                userMenuBtn.setAttribute('aria-expanded', isOpen);
            });

            // Close when clicking outside
            document.addEventListener('click', (e) => {
                if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                    userDropdown.classList.remove('open');
                    userMenuBtn.setAttribute('aria-expanded', 'false');
                }
            });

            // Close on Escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    userDropdown.classList.remove('open');
                    userMenuBtn.setAttribute('aria-expanded', 'false');
                }
            });
        }

        // Auto-scroll to #change-password section if hash present
        if (window.location.hash === '#change-password') {
            setTimeout(() => {
                const el = document.getElementById('change-password');
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }
    </script>

    @yield('scripts')
</body>
</html>
