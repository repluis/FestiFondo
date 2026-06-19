<?php

namespace Src\Auth\Application\DTOs;

use Src\Auth\Infrastructure\Http\Requests\LoginRequest;

class DTOLoginRequest
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly bool $remember,
    ) {}

    public static function fromRequest(LoginRequest $request): self
    {
        return new self(
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
            remember: $request->boolean('remember'),
        );
    }
}
