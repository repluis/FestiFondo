<?php

namespace Src\Games\Application\DTOs;

use Src\Games\Infrastructure\Http\Requests\SecretSantaDrawRequest;

class DTOSecretSantaDrawRequest
{
    /**
     * @param int[]                                        $participants
     * @param array<int, array{giver: int, receiver: int}> $exclusions
     */
    public function __construct(
        public readonly string $campaignUuid,
        public readonly array  $participants,
        public readonly array  $exclusions,
        public readonly ?int   $createdByOid = null,
    ) {}

    public static function fromRequest(SecretSantaDrawRequest $request): self
    {
        return new self(
            campaignUuid: $request->input('campaign_uuid'),
            participants: array_map('intval', $request->input('participants', [])),
            exclusions:   array_map(fn ($e) => [
                'giver'    => (int) $e['giver'],
                'receiver' => (int) $e['receiver'],
            ], $request->input('exclusions', [])),
            createdByOid: $request->user()?->oid,
        );
    }
}
