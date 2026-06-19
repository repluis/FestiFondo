<?php

namespace Src\Auth\Application\UseCases;

use Src\Auth\Domain\Repositories\AuthenticationRepositoryInterface;

class LogoutUseCase
{
    public function __construct(
        private readonly AuthenticationRepositoryInterface $repository,
    ) {}

    public function execute(): void
    {
        $this->repository->logout();
    }
}
