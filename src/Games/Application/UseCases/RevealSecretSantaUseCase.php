<?php

namespace Src\Games\Application\UseCases;

use Illuminate\Support\Facades\Log;
use Src\Games\Domain\Exceptions\SecretSantaAlreadyViewedException;
use Src\Games\Domain\Exceptions\SecretSantaNotFoundException;
use Src\Games\Domain\Repositories\GamesRepositoryInterface;

class RevealSecretSantaUseCase
{
    public function __construct(private GamesRepositoryInterface $repository) {}

    /** @return array{giver_name: string, receiver_name: string} */
    public function execute(string $token): array
    {
        $assignment = $this->repository->findAssignmentByToken($token);
        if ($assignment === null) {
            throw new SecretSantaNotFoundException();
        }

        if (!$this->repository->markAssignmentViewed($token)) {
            throw new SecretSantaAlreadyViewedException();
        }

        Log::info('[RevealSecretSantaUseCase] Assignment revealed');

        return [
            'giver_name'    => $assignment['giver_name'],
            'receiver_name' => $assignment['receiver_name'],
        ];
    }
}
