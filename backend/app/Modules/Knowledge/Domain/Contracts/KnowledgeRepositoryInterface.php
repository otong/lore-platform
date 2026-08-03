<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Domain\Contracts;

use App\Modules\Knowledge\Infrastructure\Persistence\Models\Attachment;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Category;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Knowledge;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Tag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface KnowledgeRepositoryInterface
{
    public function createCategory(array $data): Category;

    public function updateCategory(int $id, array $data): Category;

    public function deleteCategory(int $id): bool;

    public function findCategoryById(int $id): ?Category;

    public function findCategoryByUuid(string $uuid): ?Category;

    public function findCategoryBySlug(int $organizationId, string $slug): ?Category;

    public function getCategoriesByOrganization(int $organizationId): Collection;

    public function createKnowledge(array $data): Knowledge;

    public function updateKnowledge(int $id, array $data): Knowledge;

    public function deleteKnowledge(int $id): bool;

    public function findKnowledgeById(int $id): ?Knowledge;

    public function findKnowledgeByUuid(string $uuid): ?Knowledge;

    public function findKnowledgeBySlug(int $organizationId, string $slug): ?Knowledge;

    public function getKnowledgesByOrganization(int $organizationId): Collection;

    public function getKnowledgesByOrganizationPaginated(int $organizationId, int $perPage = 15): LengthAwarePaginator;

    public function findOrCreateTag(int $organizationId, string $name): Tag;

    public function syncTags(Knowledge $knowledge, array $tagIds): void;

    public function addAttachment(Knowledge $knowledge, array $data): Attachment;

    public function findAttachmentByUuid(string $uuid): ?Attachment;

    public function deleteAttachment(int $id): bool;
}
