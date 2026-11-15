<?php

namespace Src\Games\Application\UseCases;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Src\Games\Application\DTOs\DTOSecretSantaDrawRequest;
use Src\Games\Domain\Exceptions\SecretSantaException;
use Src\Games\Domain\Repositories\GamesRepositoryInterface;
use Src\Games\Domain\Services\SecretSantaDrawer;

class DrawSecretSantaUseCase
{
    public function __construct(
        private GamesRepositoryInterface $repository,
        private SecretSantaDrawer        $drawer,
    ) {}

    /**
     * Realiza y guarda el sorteo. No devuelve a quién le tocó cada uno:
     * eso solo se ve abriendo el enlace personal.
     *
     * @return array{uuid: string, created_at: string, assignments: array}
     */
    public function execute(DTOSecretSantaDrawRequest $dto): array
    {
        Log::info('[DrawSecretSantaUseCase] Step 1 — Loading campaign members', ['campaign_uuid' => $dto->campaignUuid]);
        $campaignOid = $this->repository->getCampaignOidByUuid($dto->campaignUuid);
        if ($campaignOid === null) {
            throw new SecretSantaException('La campaña seleccionada no existe.');
        }

        $members = collect($this->repository->getCampaignMembers($dto->campaignUuid))->keyBy('oid');

        // Solo se aceptan participantes que realmente pertenecen a la campaña
        $invalid = array_diff($dto->participants, $members->keys()->all());
        if ($invalid !== []) {
            throw new SecretSantaException('Algunos participantes no pertenecen a la campaña seleccionada.');
        }

        $exclusions = [];
        foreach ($dto->exclusions as $e) {
            $exclusions[$e['giver']][] = $e['receiver'];
        }

        Log::info('[DrawSecretSantaUseCase] Step 2 — Drawing', [
            'participants' => count($dto->participants),
            'exclusions'   => count($dto->exclusions),
        ]);

        try {
            $pairs = $this->drawer->draw($dto->participants, $exclusions);
        } catch (SecretSantaException $e) {
            // Reemplazar el ID por el nombre en el mensaje para que sea legible
            if ($e->memberOid !== null && $members->has($e->memberOid)) {
                throw new SecretSantaException(
                    "{$members[$e->memberOid]['name']} no tiene a nadie disponible por las exclusiones.",
                    $e->memberOid,
                );
            }
            throw $e;
        }

        Log::info('[DrawSecretSantaUseCase] Step 3 — Saving draw');
        $assignments = [];
        foreach ($pairs as $giver => $receiver) {
            $assignments[] = [
                'giver_oid'    => $giver,
                'receiver_oid' => $receiver,
                'token'        => Str::random(40),
            ];
        }

        $drawUuid = $this->repository->createDraw($campaignOid, $dto->createdByOid, $assignments);

        return $this->repository->getDrawByUuid($drawUuid);
    }
}
