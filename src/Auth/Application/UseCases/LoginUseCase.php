<?php

namespace Src\Auth\Application\UseCases;

use Src\Auth\Application\DTOs\DTOLoginRequest;
use Src\Auth\Domain\Exceptions\InvalidCredentialsException;
use Src\Auth\Domain\Repositories\AuthenticationRepositoryInterface;

class LoginUseCase
{
    public function __construct(
        private readonly AuthenticationRepositoryInterface $repository,
    ) {}

    public function execute(DTOLoginRequest $dto): void
    {
        $authenticated = $this->repository->attempt(
            email: $dto->email,
            password: $dto->password,
            remember: $dto->remember,
        );

        if (! $authenticated) {
            throw new InvalidCredentialsException();
        }
    }
}
