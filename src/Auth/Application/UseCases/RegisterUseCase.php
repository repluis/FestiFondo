<?php

namespace Src\Auth\Application\UseCases;

use Illuminate\Support\Facades\Hash;
use Src\Auth\Application\DTOs\DTORegisterRequest;
use Src\Auth\Domain\Repositories\AuthenticationRepositoryInterface;

class RegisterUseCase
{
    public function __construct(
        private readonly AuthenticationRepositoryInterface $repository,
    ) {}

    public function execute(DTORegisterRequest $dto): void
    {
        $this->repository->register(
            name: $dto->name,
            username: $dto->username,
            email: $dto->email,
            password: Hash::make($dto->password),
        );
    }
}
