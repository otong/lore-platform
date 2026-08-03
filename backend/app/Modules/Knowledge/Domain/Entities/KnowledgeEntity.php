<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Domain\Entities;

class KnowledgeEntity
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $uuid,
        public readonly int $organizationId,
        public readonly ?int $categoryId,
        public readonly string $title,
        public readonly string $slug,
        public readonly string $content,
        public readonly string $status = 'draft',
        public readonly int $authorId = 0,
        public readonly int $viewsCount = 0,
        public readonly ?string $publishedAt = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'organization_id' => $this->organizationId,
            'category_id' => $this->categoryId,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'status' => $this->status,
            'author_id' => $this->authorId,
            'views_count' => $this->viewsCount,
            'published_at' => $this->publishedAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
