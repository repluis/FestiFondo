<?php

namespace Src\Games\Domain\Services;

use Src\Games\Domain\Exceptions\SecretSantaException;

/**
 * Sorteo de Amigo Secreto.
 *
 * Asigna a cada participante otra persona a quien regalar, de forma que:
 *   - nadie se regale a sí mismo,
 *   - cada persona reciba exactamente un regalo,
 *   - se respeten las exclusiones (ej. "a A no le puede tocar B").
 *
 * Usa backtracking aleatorio: en cada paso elige al participante con menos
 * opciones disponibles y prueba sus candidatos en orden aleatorio (CSPRNG,
 * sin semilla). Si no
 * existe ninguna asignación posible, lanza una excepción en lugar de colgarse
 * o repetir personas.
 */
class SecretSantaDrawer
{
    /**
     * @param  int[]                $participants  IDs de los participantes
     * @param  array<int, int[]>    $exclusions    giverId => [receiverIds prohibidos]
     * @return array<int, int>                     giverId => receiverId
     */
    public function draw(array $participants, array $exclusions = []): array
    {
        $participants = array_values(array_unique(array_map('intval', $participants)));

        if (count($participants) < 2) {
            throw new SecretSantaException('Se necesitan al menos 2 participantes para el sorteo.');
        }

        // Candidatos permitidos para cada participante
        $candidates = [];
        foreach ($participants as $giver) {
            $forbidden = array_map('intval', $exclusions[$giver] ?? []);
            $allowed   = array_values(array_filter(
                $participants,
                fn (int $r) => $r !== $giver && !in_array($r, $forbidden, true),
            ));

            if ($allowed === []) {
                throw new SecretSantaException(
                    "El participante #{$giver} no tiene a nadie disponible por las exclusiones.",
                    $giver,
                );
            }

            $candidates[$giver] = $allowed;
        }

        $assignment = [];
        $taken      = [];

        if (!$this->solve($candidates, $assignment, $taken)) {
            throw new SecretSantaException(
                'No es posible realizar el sorteo con las exclusiones actuales. Quita alguna exclusión e intenta de nuevo.',
            );
        }

        // Devolver en el orden original de los participantes
        $ordered = [];
        foreach ($participants as $giver) {
            $ordered[$giver] = $assignment[$giver];
        }

        return $ordered;
    }

    private function solve(array $candidates, array &$assignment, array &$taken): bool
    {
        if (count($assignment) === count($candidates)) {
            return true;
        }

        // Elegir al participante pendiente con menos opciones libres (más restringido)
        $nextGiver = null;
        $nextOpts  = null;
        foreach ($candidates as $giver => $options) {
            if (isset($assignment[$giver])) {
                continue;
            }
            $free = array_values(array_filter($options, fn (int $r) => !isset($taken[$r])));
            if ($nextOpts === null || count($free) < count($nextOpts)) {
                $nextGiver = $giver;
                $nextOpts  = $free;
                if ($free === []) {
                    return false; // callejón sin salida, retroceder
                }
            }
        }

        $this->secureShuffle($nextOpts);

        foreach ($nextOpts as $receiver) {
            $assignment[$nextGiver] = $receiver;
            $taken[$receiver]       = true;

            if ($this->solve($candidates, $assignment, $taken)) {
                return true;
            }

            unset($assignment[$nextGiver], $taken[$receiver]);
        }

        return false;
    }

    /**
     * Fisher–Yates con random_int(): usa el generador criptográfico del sistema
     * operativo (CSPRNG). No hay semilla ni estado reproducible, a diferencia de
     * shuffle()/mt_rand() que usan Mersenne Twister.
     */
    private function secureShuffle(array &$items): void
    {
        for ($i = count($items) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
        }
    }
}
