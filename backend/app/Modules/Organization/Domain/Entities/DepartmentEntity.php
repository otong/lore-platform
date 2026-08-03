<?php

declare(strict_types=1);

namespace App\Modules\Organization\Domain\Entities;

class DepartmentEntity
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $uuid,
        public readonly int $organizationId,
        public readonly ?int $parentId,
        public readonly string $name,
        public readonly string $code,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'organization_id' => $this->organizationId,
            'parent_id' => $this->parentId,
            'name' => $this->name,
            'code' => $this->code,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
