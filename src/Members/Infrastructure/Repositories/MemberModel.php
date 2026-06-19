<?php

namespace Src\Members\Infrastructure\Repositories;

use Illuminate\Database\Eloquent\Model;
use Src\Members\Domain\Entities\Members;

class MemberModel extends Model
{
    protected $table = 'members';

    protected $fillable = [
        'identification',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'notes',
        'joined_at',
        'status',
        'created_by_oid',
        'updated_by_oid',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'date:Y-m-d',
            'status'    => 'boolean',
        ];
    }

    public function toEntity(): Members
    {
        return new Members(
            id:           $this->id,
            oid:          $this->oid,
            uuid:         $this->uuid,
            identification: $this->identification,
            firstName:    $this->first_name,
            lastName:     $this->last_name,
            email:        $this->email,
            phone:        $this->phone,
            address:      $this->address,
            notes:        $this->notes,
            joinedAt:     $this->joined_at?->format('Y-m-d') ?? '',
            status:       (bool) $this->status,
            createdByOid: $this->created_by_oid,
            updatedByOid: $this->updated_by_oid,
            createdAt:    $this->created_at?->toISOString(),
            updatedAt:    $this->updated_at?->toISOString(),
        );
    }
}
