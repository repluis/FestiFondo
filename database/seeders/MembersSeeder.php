<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Members\Application\DTOs\DTOCreateMembersRequest;
use Src\Members\Application\Services\MembersService;

class MembersSeeder extends Seeder
{
    public function run(MembersService $service): void
    {
        $members = [
            ['id' => 2,  'name' => 'Shirley Cedeño',       'email' => 'shirley.cedeno@familia.com'],
            ['id' => 3,  'name' => 'Katherine Cedeño',     'email' => 'katherine.cedeno@familia.com'],
            ['id' => 4,  'name' => 'Luis Palemon Cedeño',  'email' => 'luis.palemon.cedeno@familia.com'],
            ['id' => 5,  'name' => 'Dolores Posligua',     'email' => 'dolores.posligua@familia.com'],
            ['id' => 6,  'name' => 'Samara Guerrero',      'email' => 'samara.guerrero@familia.com'],
            ['id' => 7,  'name' => 'Ahilany Palma',        'email' => 'ahilany.palma@familia.com'],
            ['id' => 8,  'name' => 'Jean Molina',          'email' => 'jean.molina@familia.com'],
            ['id' => 9,  'name' => 'Jose Palma',           'email' => 'jose.palma@familia.com'],
            ['id' => 10, 'name' => 'Dereck Cedeño',        'email' => 'dereck.cedeno@familia.com'],
            ['id' => 11, 'name' => 'Jeremy Alava',         'email' => 'jeremy.alava@familia.com'],
            ['id' => 12, 'name' => 'Pedro Guerrero',       'email' => 'pedro.guerrero@familia.com'],
            ['id' => 1,  'name' => 'Luis Antonio Cedeño',  'email' => 'luis.antonio.cedeno@familia.com'],
        ];

        foreach ($members as $data) {
            $parts     = explode(' ', $data['name']);
            $lastName  = array_pop($parts);
            $firstName = implode(' ', $parts);

            $dto = new DTOCreateMembersRequest(
                identification: (string) $data['id'],
                firstName:      $firstName,
                lastName:       $lastName,
                email:          $data['email'],
                phone:          null,
                address:        null,
                notes:          null,
                joinedAt:       '2026-01-15',
                createdByOid:   0,
            );

            $result = $service->create($dto);

            $this->command->info("Created: {$result->firstName} {$result->lastName} — {$result->uuid}");
        }
    }
}
