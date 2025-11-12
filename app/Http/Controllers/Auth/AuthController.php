<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\Services\AuthServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthController extends Controller
{
    protected AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Show login form
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if ($this->authService->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = $this->authService->user();

            // Check if user has dashboard privilege
            if ($user->hasPermissionTo('view-dashboard-privilege')) {
                return redirect()->intended(route('dashboard'))
                    ->with('success', 'Welcome back, ' . $user->name . '!');
            }

            return redirect()->intended(route('home'))
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show register form
     */
    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle register request
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = $this->authService->register($request->validated());

        $this->authService->attempt([
            'email' => $request->email,
            'password' => $request->password,
        ]);

        $request->session()->regenerate();

        return redirect()->route('home')
            ->with('success', 'Registration successful! Welcome, ' . $user->name . '!');
    }

    /**
     * Handle logout request
     */
    public function logout(): RedirectResponse
    {
        $this->authService->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'You have been logged out successfully.');
    }
}
