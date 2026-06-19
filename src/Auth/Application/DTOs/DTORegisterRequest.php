<?php

namespace Src\Auth\Application\DTOs;

use Src\Auth\Infrastructure\Http\Requests\RegisterRequest;

class DTORegisterRequest
{
    public function __construct(
        public readonly string $name,
        public readonly string $username,
        public readonly string $email,
        public readonly string $password,
    ) {}

    public static function fromRequest(RegisterRequest $request): self
    {
        return new self(
            name: $request->string('name')->toString(),
            username: $request->string('username')->toString(),
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
        );
    }
}
