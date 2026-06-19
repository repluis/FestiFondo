<?php

namespace Src\Auth\Infrastructure\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Src\Auth\Domain\Repositories\AuthenticationRepositoryInterface;

class EloquentAuthRepository implements AuthenticationRepositoryInterface
{
    public function attempt(string $email, string $password, bool $remember = false): bool
    {
        return Auth::attempt(
            credentials: ['email' => $email, 'password' => $password],
            remember: $remember,
        );
    }

    public function register(string $name, string $username, string $email, string $password): void
    {
        $user = User::create([
            'name'     => $name,
            'username' => $username,
            'email'    => $email,
            'password' => $password,
        ]);

        Auth::login($user);
    }

    public function logout(): void
    {
        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();
    }
}
