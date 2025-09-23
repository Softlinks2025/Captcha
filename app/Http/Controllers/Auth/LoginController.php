<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        \Log::info('Login attempt', ['email' => $request->email]);
        
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        \Log::debug('Login credentials validated', $credentials);
        
        // Debug: Check if user exists
        $user = \App\Models\User::where('email', $credentials['email'])->first();
        \Log::debug('User lookup result:', [
            'exists' => $user ? 'Yes' : 'No',
            'user' => $user ? $user->toArray() : null,
            'password_match' => $user ? (\Hash::check($credentials['password'], $user->password) ? 'Yes' : 'No') : 'N/A'
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();
            \Log::info('Login successful', ['user_id' => $user->id, 'roles' => $user->getRoleNames()]);
            
            // Clear any previous intended URL to prevent loops
            $intended = $request->session()->pull('url.intended', null);
            \Log::debug('Cleared intended URL', ['intended' => $intended]);
            
            // Redirect based on user role
            if ($user->hasRole('push-subadmin')) {
                return redirect()->route('admin.push-notification.index');
            } elseif ($user->hasRole('withdrawal-subadmin')) {
                return redirect()->route('admin.withdrawal-requests.index');
            } elseif ($user->hasRole('ticket-manager')) {
                return redirect()->route('admin.tickets.index');
            } else {
                // Default redirect for admin and other roles
                return redirect()->route('admin.dashboard');
            }
        }
        
        \Log::warning('Login failed', ['email' => $request->email]);

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
