<?php

namespace Src\Auth\Domain\Repositories;

interface AuthenticationRepositoryInterface
{
    public function attempt(string $email, string $password, bool $remember = false): bool;

    public function register(string $name, string $username, string $email, string $password): void;

    public function logout(): void;
}
