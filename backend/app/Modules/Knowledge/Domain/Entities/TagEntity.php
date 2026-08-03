<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Domain\Entities;

class TagEntity
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $uuid,
        public readonly int $organizationId,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'organization_id' => $this->organizationId,
            'name' => $this->name,
            'slug' => $this->slug,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
