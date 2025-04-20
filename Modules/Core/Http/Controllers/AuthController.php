<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Services\AuthService;
use Modules\Core\Http\Requests\LoginRequest;
use Modules\Core\Http\Requests\RegisterRequest;
use Modules\Core\Http\Requests\ResetPasswordRequest;
use Modules\Core\Http\Requests\ForgotPasswordRequest;
use Modules\Core\Http\Requests\ChangePasswordRequest;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * @var AuthService
     */
    protected $authService;

    /**
     * AuthController constructor.
     *
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Show the login form.
     * 
     * @return Renderable
     */
    public function showLoginForm()
    {
        return view('core::auth.login');
    }

    /**
     * Handle login request.
     * 
     * @param LoginRequest $request
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        $result = $this->authService->login(
            $request->username,
            $request->password,
            $request->ip()
        );
        
        if (!$result['success']) {
            return back()->withErrors([
                'message' => $result['message']
            ])->withInput($request->except('password'));
        }
        
        // Si requiere 2FA, redirigir a pantalla 2FA
        if ($result['requires_2fa']) {
            return redirect()->route('core.auth.2fa');
        }
        
        return redirect()->intended(route('core.dashboard'));
    }

    /**
     * Handle logout request.
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->authService->logout($request->ip());
        
        return redirect()->route('core.auth.login');
    }

    /**
     * Show the registration form.
     * 
     * @return Renderable
     */
    public function showRegistrationForm()
    {
        return view('core::auth.register');
    }

    /**
     * Handle registration request.
     * 
     * @param RegisterRequest $request
     * @return \Illuminate\Http\Response
     */
    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register(
            $request->validated(),
            $request->ip()
        );
        
        if (!$result['success']) {
            return back()->withErrors([
                'message' => $result['message'],
                'errors' => $result['errors'] ?? []
            ])->withInput($request->except('password', 'password_confirmation'));
        }
        
        return redirect()->route('core.auth.login')
            ->with('success', 'Usuario registrado correctamente. Por favor inicie sesión.');
    }

    /**
     * Show the forgot password form.
     * 
     * @return Renderable
     */
    public function showForgotPasswordForm()
    {
        return view('core::auth.forgot-password');
    }

    /**
     * Handle forgot password request.
     * 
     * @param ForgotPasswordRequest $request
     * @return \Illuminate\Http\Response
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $result = $this->authService->requestPasswordReset(
            $request->email,
            $request->ip()
        );
        
        return redirect()->route('core.auth.login')
            ->with('success', $result['message']);
    }

    /**
     * Show the reset password form.
     * 
     * @param string $token
     * @return Renderable
     */
    public function showResetPasswordForm($token)
    {
        return view('core::auth.reset-password', ['token' => $token]);
    }

    /**
     * Handle reset password request.
     * 
     * @param ResetPasswordRequest $request
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $result = $this->authService->resetPassword(
            $request->token,
            $request->email,
            $request->password,
            $request->ip()
        );
        
        if (!$result['success']) {
            return back()->withErrors([
                'message' => $result['message'],
                'errors' => $result['errors'] ?? []
            ])->withInput($request->except('password', 'password_confirmation'));
        }
        
        return redirect()->route('core.auth.login')
            ->with('success', $result['message']);
    }

    /**
     * Show the change password form.
     * 
     * @return Renderable
     */
    public function showChangePasswordForm()
    {
        return view('core::auth.change-password');
    }

    /**
     * Handle change password request.
     * 
     * @param ChangePasswordRequest $request
     * @return \Illuminate\Http\Response
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $result = $this->authService->changePassword(
            Auth::id(),
            $request->current_password,
            $request->password,
            $request->ip()
        );
        
        if (!$result['success']) {
            return back()->withErrors([
                'message' => $result['message'],
                'errors' => $result['errors'] ?? []
            ]);
        }
        
        return back()->with('success', $result['message']);
    }
}