<?php

namespace Src\Auth\Infrastructure\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Src\Auth\Application\DTOs\DTOLoginRequest;
use Src\Auth\Application\DTOs\DTORegisterRequest;
use Src\Auth\Application\Services\AuthService;
use Src\Auth\Domain\Exceptions\InvalidCredentialsException;
use Src\Auth\Infrastructure\Http\Requests\LoginRequest;
use Src\Auth\Infrastructure\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('Auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            $this->authService->login(DTOLoginRequest::fromRequest($request));
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        } catch (InvalidCredentialsException $e) {
            return back()
                ->withErrors(['email' => $e->getMessage()])
                ->withInput($request->only('email', 'remember'));
        }
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('Auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $this->authService->register(DTORegisterRequest::fromRequest($request));
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->authService->logout();

        return redirect()->route('auth.login');
    }
}
