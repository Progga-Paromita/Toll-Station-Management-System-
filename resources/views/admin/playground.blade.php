@extends('layouts.layout')

@section('title', 'TollStation - PL/SQL Playground')

@section('content')
    <div class="section-header">
        <div>
            <h1 class="section-title">PL/SQL Laboratory Playground</h1>
            <p class="section-desc">Interact with compiled database procedures, functions, loops, and records inside Oracle.</p>
        </div>
        <span class="badge badge-danger">ORACLE SQL CONSOLE</span>
    </div>

    <!-- Error block for playground exceptions -->
    @if($errors->any())
        <div class="alert alert-danger" style="flex-direction: column; align-items: flex-start; gap: 4px;">
            @foreach($errors->all() as $error)
                <div style="display:flex; gap:8px; align-items:center;">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ $error }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <div class="grid-2">
        
        <!-- ==================== COLUMN 1: PL/SQL PROCEDURE & FUNCTION ==================== -->
        <div>
            
            <!-- 1. Stored Procedure -->
            <div class="card glass" style="margin-bottom: 32px;">
                <h3 class="card-title">
                    <span class="badge badge-warning">PROCEDURE</span>
                    validate_login
                </h3>
                <p class="card-subtitle">
                    Validates user status and returns <code>VALID</code> or <code>INVALID</code> via an output parameter.
                </p>

                <form action="{{ route('admin.playground.procedure') }}" method="POST" style="margin-bottom: 20px;">
                    @csrf
                    <div class="form-group">
                        <label for="username_input" class="form-label">Username to Validate</label>
                        <div style="display: flex; gap: 12px;">
                            <input type="text" name="username" id="username_input" class="form-control" placeholder="e.g. admin, john, nonexistent" required value="{{ session('proc_result.username') }}">
                            <button type="submit" class="btn btn-primary" style="white-space: nowrap;">Run Procedure</button>
                        </div>
                    </div>
                </form>

                <!-- Console Output -->
                <div class="console">
                    <div class="console-header">
                        <span>Oracle Database Console</span>
                        <div class="console-dots">
                            <span class="console-dot red"></span>
                            <span class="console-dot yellow"></span>
                            <span class="console-dot green"></span>
                        </div>
                    </div>
                    <div class="console-body">
                        @if(session('proc_result'))
                            <div class="console-output">p_result => "{{ session('proc_result.result') }}"</div>
                            <div class="console-info">Procedure executed successfully.</div>
                        @else
                            <div class="console-info">Enter a username and run the procedure to view output.</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 2. Stored Function -->
            <div class="card glass">
                <h3 class="card-title">
                    <span class="badge badge-info">FUNCTION</span>
                    get_user_role
                </h3>
                <p class="card-subtitle">
                    Queries the <code>users</code> and <code>roles</code> tables using a <code>JOIN</code> and returns the role name for a given user ID.
                </p>

                <form action="{{ route('admin.playground.function') }}" method="POST" style="margin-bottom: 20px;">
                    @csrf
                    <div class="form-group">
                        <label for="user_id_input" class="form-label">User ID to Query</label>
                        <div style="display: flex; gap: 12px;">
                            <input type="number" name="user_id" id="user_id_input" class="form-control" placeholder="e.g. 1, 2, 999" required value="{{ session('func_result.user_id') }}">
                            <button type="submit" class="btn btn-primary" style="white-space: nowrap;">Run Function</button>
                        </div>
                    </div>
                </form>

                <!-- Console Output -->
                <div class="console">
                    <div class="console-header">
                        <span>Oracle Database Console</span>
                        <div class="console-dots">
                            <span class="console-dot red"></span>
                            <span class="console-dot yellow"></span>
                            <span class="console-dot green"></span>
                        </div>
                    </div>
                    <div class="console-body">
                        @if(session('func_result'))
                            <div class="console-output">Result: "{{ session('func_result.result') }}"</div>
                            <div class="console-info">Function returned role name.</div>
                        @else
                            <div class="console-info">Enter a User ID and execute the function query.</div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        <!-- ==================== COLUMN 2: PL/SQL LOOP & RECORD ==================== -->
        <div>
            
            <!-- 3. Loop + Control Statement -->
            <div class="card glass" style="margin-bottom: 32px;">
                <h3 class="card-title">
                    <span class="badge badge-success">LOOP + CONTROL</span>
                    Cursor Loop Block
                </h3>
                <p class="card-subtitle">
                    Runs an anonymous PL/SQL block looping through users. If a user's status is <code>ACTIVE</code>, it prints the username using <code>DBMS_OUTPUT</code>.
                </p>

                <form action="{{ route('admin.playground.loop') }}" method="POST" style="margin-bottom: 20px;">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Execute Loop Block</button>
                </form>

                <!-- Console Output -->
                <div class="console">
                    <div class="console-header">
                        <span>DBMS_OUTPUT Stream</span>
                        <div class="console-dots">
                            <span class="console-dot red"></span>
                            <span class="console-dot yellow"></span>
                            <span class="console-dot green"></span>
                        </div>
                    </div>
                    <div class="console-body">
                        @if(session('loop_result'))
                            <div class="console-info">--- DBMS_OUTPUT ---</div>
                            @if(count(session('loop_result.logs')) > 0)
                                @foreach(session('loop_result.logs') as $logLine)
                                    <div class="console-output">{{ $logLine }}</div>
                                @endforeach
                            @else
                                <div class="console-output" style="color: var(--text-muted);">No output lines printed.</div>
                            @endif
                            <div class="console-info">--- BLOCK COMPLETE ---</div>
                        @else
                            <div class="console-info">Execute block to fetch DBMS_OUTPUT buffer lines.</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 4. Record Example -->
            <div class="card glass">
                <h3 class="card-title">
                    <span class="badge badge-danger">RECORD (%ROWTYPE)</span>
                    User Record Fetcher
                </h3>
                <p class="card-subtitle">
                    Uses the <code>users%ROWTYPE</code> record data type to fetch a single user's entire row structure and prints details via <code>DBMS_OUTPUT</code>.
                </p>

                <form action="{{ route('admin.playground.record') }}" method="POST" style="margin-bottom: 20px;">
                    @csrf
                    <div class="form-group">
                        <label for="record_user_id" class="form-label">User ID to Fetch into Record</label>
                        <div style="display: flex; gap: 12px;">
                            <input type="number" name="record_user_id" id="record_user_id" class="form-control" placeholder="e.g. 1, 2" required value="{{ session('record_result.user_id') }}">
                            <button type="submit" class="btn btn-primary" style="white-space: nowrap;">Fetch Record</button>
                        </div>
                    </div>
                </form>

                <!-- Console Output -->
                <div class="console">
                    <div class="console-header">
                        <span>DBMS_OUTPUT Stream</span>
                        <div class="console-dots">
                            <span class="console-dot red"></span>
                            <span class="console-dot yellow"></span>
                            <span class="console-dot green"></span>
                        </div>
                    </div>
                    <div class="console-body">
                        @if(session('record_result'))
                            <div class="console-info">--- DBMS_OUTPUT ---</div>
                            @if(count(session('record_result.logs')) > 0)
                                @foreach(session('record_result.logs') as $logLine)
                                    <div class="console-output">{{ $logLine }}</div>
                                @endforeach
                            @else
                                <div class="console-output" style="color: var(--text-muted);">No output lines printed.</div>
                            @endif
                            <div class="console-info">--- BLOCK COMPLETE ---</div>
                        @else
                            <div class="console-info">Select a User ID and execute the record fetcher block.</div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection
