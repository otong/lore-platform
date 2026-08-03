<?php

declare(strict_types=1);

namespace App\Modules\Organization\Domain\Entities;

class OrganizationEntity
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $uuid,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $code,
        public readonly string $status = 'active',
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'code' => $this->code,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
