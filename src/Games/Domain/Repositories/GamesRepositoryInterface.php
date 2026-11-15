<?php

namespace Src\Games\Domain\Repositories;

interface GamesRepositoryInterface
{
    /** @return array<int, array{uuid: string, name: string}> */
    public function getCampaigns(): array;

    public function getCampaignOidByUuid(string $uuid): ?int;

    /** @return array<int, array{oid: int, name: string}> */
    public function getCampaignMembers(string $campaignUuid): array;

    /**
     * Guarda un sorteo con sus asignaciones.
     *
     * @param  array<int, array{giver_oid: int, receiver_oid: int, token: string}> $assignments
     * @return string  UUID del sorteo creado
     */
    public function createDraw(int $campaignOid, ?int $createdByOid, array $assignments): string;

    /**
     * Último sorteo activo de una campaña con sus asignaciones (sin nombre del receptor).
     *
     * @return array{uuid: string, created_at: string, assignments: array}|null
     */
    public function getLatestDraw(string $campaignUuid): ?array;

    /**
     * Sorteo por UUID (activo o reemplazado), sin nombre del receptor.
     *
     * @return array{uuid: string, active: bool, created_at: string, campaign_uuid: string, campaign_name: string, assignments: array}|null
     */
    public function getDrawByUuid(string $drawUuid): ?array;

    /**
     * Historial de sorteos (más recientes primero).
     *
     * @return array<int, array{uuid: string, active: bool, created_at: string, campaign_uuid: string, campaign_name: string, participants: int, viewed: int}>
     */
    public function listDraws(int $limit = 30): array;

    /** @return array<int, array{giver: int, receiver: int}> */
    public function getExclusions(int $campaignOid): array;

    /** @param array<int, array{giver: int, receiver: int}> $pairs  Ignora las que ya existen */
    public function addExclusions(int $campaignOid, array $pairs, ?int $createdByOid): void;

    public function removeExclusion(int $campaignOid, int $giverOid, int $receiverOid): void;

    /** @return array{giver_name: string, receiver_name: string, viewed_at: ?string}|null */
    public function findAssignmentByToken(string $token): ?array;

    /** Marca como vista solo si aún no se había visto. Devuelve true si este llamado la marcó. */
    public function markAssignmentViewed(string $token): bool;

    /** Permite volver a ver la asignación. Devuelve true si existía. */
    public function resetAssignmentView(string $token): bool;
}
