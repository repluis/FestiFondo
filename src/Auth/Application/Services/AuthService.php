<?php

namespace Src\Auth\Application\Services;

use Src\Auth\Application\DTOs\DTOLoginRequest;
use Src\Auth\Application\DTOs\DTORegisterRequest;
use Src\Auth\Application\UseCases\LoginUseCase;
use Src\Auth\Application\UseCases\LogoutUseCase;
use Src\Auth\Application\UseCases\RegisterUseCase;

class AuthService
{
    public function __construct(
        private readonly LoginUseCase $loginUseCase,
        private readonly LogoutUseCase $logoutUseCase,
        private readonly RegisterUseCase $registerUseCase,
    ) {}

    public function login(DTOLoginRequest $dto): void
    {
        $this->loginUseCase->execute($dto);
    }

    public function register(DTORegisterRequest $dto): void
    {
        $this->registerUseCase->execute($dto);
    }

    public function logout(): void
    {
        $this->logoutUseCase->execute();
    }
}
