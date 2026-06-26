<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show login form (supports role-wise styling/tab selection).
     */
    public function showLogin(Request $request)
    {
        $role = $request->query('role', 'operator'); // default tab
        return view('auth.login', compact('role'));
    }

    /**
     * Handle authentication request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'role'     => 'required|string|in:admin,operator',
        ]);

        $login        = trim($request->input('username')); // accepts username OR email
        $password     = $request->input('password');
        $selectedRole = strtoupper($request->input('role')); // ADMIN or OPERATOR

        // Step 1: Fetch User — search by username OR email
        // Oracle LOWER() ensures case-insensitive match on the email path.
        $userRecord = DB::selectOne("
            SELECT * FROM users
            WHERE username = :username
               OR LOWER(email) = LOWER(:email)
        ", [
            'username' => $login,
            'email'    => $login,
        ]);

        if (!$userRecord) {
            return back()
                ->withErrors(['username' => 'No account found with that username or email address.'])
                ->withInput();
        }

        // Step 2: Verify the user's role using our PL/SQL function `get_user_role`
        $roleResult = DB::selectOne(
            "SELECT get_user_role(:user_id) AS role_name FROM dual",
            ['user_id' => $userRecord->user_id]
        );
        $dbRole = $roleResult ? $roleResult->role_name : null;

        if ($dbRole !== $selectedRole) {
            return back()
                ->withErrors(['username' => "This account does not have the {$selectedRole} role."])
                ->withInput();
        }

        // Step 3: Check if account is locked
        if ($userRecord->status === 'INACTIVE') {
            return back()
                ->withErrors(['username' => 'This account is locked after 3 failed login attempts. Please contact the administrator.'])
                ->withInput();
        }

        // Step 4: Verify password
        if (Hash::check($password, $userRecord->password)) {
            // SUCCESSFUL LOGIN:
            // Reset login attempts and record last_login (triggers trg_user_login → inserts to login_logs).
            DB::update("
                UPDATE users
                SET login_attempts = 0, last_login = SYSDATE
                WHERE user_id = :user_id
            ", ['user_id' => $userRecord->user_id]);

            // Log in with Laravel auth
            $user = User::find($userRecord->user_id);
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->intended('/profile')->with('success', 'Logged in successfully!');

        } else {
            // FAILED LOGIN — increment attempts
            DB::update("
                UPDATE users
                SET login_attempts = login_attempts + 1
                WHERE user_id = :user_id
            ", ['user_id' => $userRecord->user_id]);

            // Lock the account if attempts reach 3
            DB::update("
                UPDATE users
                SET status = 'INACTIVE'
                WHERE user_id = :user_id AND login_attempts >= 3
            ", ['user_id' => $userRecord->user_id]);

            // Log the failed attempt
            DB::insert("
                INSERT INTO login_logs (user_id, login_time, login_status, ip_address)
                VALUES (:user_id, SYSDATE, 'FAILED', :ip)
            ", [
                'user_id' => $userRecord->user_id,
                'ip'      => $request->ip() ?: '127.0.0.1',
            ]);

            // Re-fetch to get updated attempts & status (Oracle returns NUMBERs as strings)
            $updatedRecord = DB::selectOne("
                SELECT login_attempts, status
                FROM users
                WHERE user_id = :user_id
            ", ['user_id' => $userRecord->user_id]);

            if ($updatedRecord->status === 'INACTIVE') {
                return back()->withErrors(['username' => 'Invalid password. Your account has now been locked after 3 failed attempts. Contact an administrator to unlock it.']);
            } else {
                $attemptsLeft = 3 - (int) $updatedRecord->login_attempts;
                return back()->withErrors(['username' => "Invalid password. {$attemptsLeft} attempt(s) remaining before account is locked."]);
            }
        }
    }

    /**
     * Show registration form (supports different roles via query parameter).
     */
    public function showRegister(Request $request)
    {
        $role = $request->query('role', 'operator'); // operator or admin
        return view('auth.register', compact('role'));
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|string|email|max:100|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|in:admin,operator',
        ]);

        $roleName = strtoupper($request->input('role')); // ADMIN or OPERATOR
        $role = Role::where('role_name', $roleName)->first();

        if (!$role) {
            return back()->withErrors(['role' => 'Selected role is invalid.'])->withInput();
        }

        // Create user using Eloquent
        User::create([
            'name' => $request->input('name'),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role_id' => $role->role_id,
            'status' => 'ACTIVE',
            'login_attempts' => 0,
            'created_at' => now(),
        ]);

        return redirect()->route('login', ['role' => $request->input('role')])
            ->with('success', 'Registration successful! Please log in.');
    }

    /**
     * Render the logged-in user profile dynamically (Admin vs Member Profile).
     */
    public function profile()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Fetch recent login logs for this user using a JOIN/where clause
        $loginLogs = DB::select("
            SELECT log_id, login_time, login_status, ip_address 
            FROM login_logs 
            WHERE user_id = :user_id 
            ORDER BY login_time DESC 
            FETCH FIRST 10 ROWS ONLY
        ", ['user_id' => $user->user_id]);

        return view('profile', compact('user', 'loginLogs'));
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Logged out successfully!');
    }

    /**
     * Show the Forgot Password form.
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle Forgot Password — reset password by verifying username + email.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'username'     => 'required|string',
            'email'        => 'required|email',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        // Find user matching BOTH username and email
        $userRecord = DB::selectOne("
            SELECT * FROM users
            WHERE username = :username
              AND LOWER(email) = LOWER(:email)
        ", [
            'username' => $request->input('username'),
            'email'    => $request->input('email'),
        ]);

        if (!$userRecord) {
            return back()
                ->withErrors(['username' => 'No account matched with those credentials. Please check your username and email.'])
                ->withInput();
        }

        // Update the password
        DB::update("
            UPDATE users SET password = :password WHERE user_id = :user_id
        ", [
            'password' => Hash::make($request->input('new_password')),
            'user_id'  => $userRecord->user_id,
        ]);

        return redirect()->route('login')
            ->with('success', 'Password reset successfully! You can now log in with your new password.');
    }

    /**
     * Handle Change Password for authenticated users.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        // Verify current password
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        // Update password in DB
        DB::update("
            UPDATE users SET password = :password WHERE user_id = :user_id
        ", [
            'password' => Hash::make($request->input('new_password')),
            'user_id'  => $user->user_id,
        ]);

        return back()->with('success', 'Password changed successfully!');
    }
}
