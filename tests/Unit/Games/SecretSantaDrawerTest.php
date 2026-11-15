<?php

namespace Tests\Unit\Games;

use PHPUnit\Framework\TestCase;
use Src\Games\Domain\Exceptions\SecretSantaException;
use Src\Games\Domain\Services\SecretSantaDrawer;

class SecretSantaDrawerTest extends TestCase
{
    private SecretSantaDrawer $drawer;

    protected function setUp(): void
    {
        $this->drawer = new SecretSantaDrawer();
    }

    public function test_nobody_gets_themselves_and_everyone_receives_once(): void
    {
        $participants = range(1, 12);

        for ($run = 0; $run < 200; $run++) {
            $pairs = $this->drawer->draw($participants);

            $this->assertSame($participants, array_keys($pairs));
            foreach ($pairs as $giver => $receiver) {
                $this->assertNotSame($giver, $receiver);
            }
            $receivers = array_values($pairs);
            sort($receivers);
            $this->assertSame($participants, $receivers);
        }
    }

    public function test_exclusions_are_respected(): void
    {
        $exclusions = [1 => [2, 3], 2 => [1], 4 => [5]];

        for ($run = 0; $run < 200; $run++) {
            $pairs = $this->drawer->draw([1, 2, 3, 4, 5], $exclusions);

            foreach ($exclusions as $giver => $forbidden) {
                $this->assertNotContains($pairs[$giver], $forbidden);
            }
        }
    }

    /** A usuario1 nunca le toca usuario1 (ni a nadie le toca él mismo), en 1000 sorteos. */
    public function test_nobody_ever_gets_themselves(): void
    {
        $participants = range(1, 12);

        for ($run = 0; $run < 1000; $run++) {
            $pairs = $this->drawer->draw($participants);

            foreach ($pairs as $giver => $receiver) {
                $this->assertNotSame($giver, $receiver, "Sorteo #{$run}: a usuario{$giver} le tocó él mismo");
            }
        }
    }

    /** Todos dan un regalo y todos reciben exactamente uno, siempre de alguien distinto a sí mismos. */
    public function test_everyone_gets_someone_different_and_nobody_is_left_out(): void
    {
        foreach ([2, 3, 4, 5, 7, 12, 30] as $size) {
            $participants = range(1, $size);

            for ($run = 0; $run < 200; $run++) {
                $pairs = $this->drawer->draw($participants);

                $this->assertCount($size, $pairs, "Con {$size} participantes alguien se quedó sin amigo secreto");
                $this->assertSame($participants, array_keys($pairs), 'Todos deben regalar');

                $receivers = array_values($pairs);
                $this->assertCount($size, array_unique($receivers), 'Alguien recibió dos regalos');
                $this->assertEqualsCanonicalizing($participants, $receivers, 'Alguien se quedó sin recibir');
            }
        }
    }

    /** Si a usuario1 se le excluyó usuario2 (ej. le tocó el año pasado), nunca le toca usuario2. */
    public function test_excluded_person_is_never_assigned(): void
    {
        $participants = range(1, 6);
        $exclusions   = [
            1 => [2],     // a usuario1 no le puede tocar usuario2
            2 => [1],     // ni al revés
            3 => [4, 5],  // a usuario3 no le pueden tocar usuario4 ni usuario5
            6 => [1],
        ];

        for ($run = 0; $run < 1000; $run++) {
            $pairs = $this->drawer->draw($participants, $exclusions);

            foreach ($exclusions as $giver => $forbidden) {
                $this->assertNotContains(
                    $pairs[$giver], $forbidden,
                    "Sorteo #{$run}: a usuario{$giver} le tocó usuario{$pairs[$giver]}, que estaba excluido",
                );
            }
            foreach ($pairs as $giver => $receiver) {
                $this->assertNotSame($giver, $receiver);
            }
        }
    }

    /** Con exclusiones que dejan una sola opción, el sorteo la encuentra siempre. */
    public function test_finds_the_only_possible_assignment(): void
    {
        // Única solución posible: 1→2, 2→3, 3→1
        $exclusions = [1 => [3], 2 => [1], 3 => [2]];

        for ($run = 0; $run < 100; $run++) {
            $this->assertSame([1 => 2, 2 => 3, 3 => 1], $this->drawer->draw([1, 2, 3], $exclusions));
        }
    }

    /** Cada persona puede recibir a cualquiera de sus opciones válidas (no hay resultado fijo). */
    public function test_results_are_random(): void
    {
        $seen = [];
        for ($run = 0; $run < 300; $run++) {
            $seen[$this->drawer->draw(range(1, 5))[1]] = true;
        }

        // A usuario1 le debieron tocar las 4 personas posibles (2, 3, 4 y 5) en algún sorteo
        $this->assertEqualsCanonicalizing([2, 3, 4, 5], array_keys($seen));
    }

    public function test_two_participants_swap(): void
    {
        $this->assertSame([1 => 2, 2 => 1], $this->drawer->draw([1, 2]));
    }

    public function test_fails_with_less_than_two_participants(): void
    {
        $this->expectException(SecretSantaException::class);
        $this->drawer->draw([1]);
    }

    public function test_fails_when_participant_has_no_options(): void
    {
        $this->expectException(SecretSantaException::class);
        $this->drawer->draw([1, 2, 3], [1 => [2, 3]]);
    }

    public function test_fails_when_no_valid_assignment_exists(): void
    {
        // 1 y 2 solo pueden regalarle a 3 → imposible
        $this->expectException(SecretSantaException::class);
        $this->drawer->draw([1, 2, 3], [1 => [2], 2 => [1]]);
    }
}
