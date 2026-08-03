<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Application\Services;

use App\Modules\Knowledge\Domain\Contracts\KnowledgeRepositoryInterface;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Attachment;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Category;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Knowledge;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class KnowledgeService
{
    public function __construct(
        protected KnowledgeRepositoryInterface $repository
    ) {}

    public function getCategoryByUuid(string $uuid): ?Category
    {
        return $this->repository->findCategoryByUuid($uuid);
    }

    public function getCategoriesByOrganization(int $organizationId): Collection
    {
        return $this->repository->getCategoriesByOrganization($organizationId);
    }

    public function createCategory(int $organizationId, array $data): Category
    {
        $data['organization_id'] = $organizationId;
        $slug = $data['slug'] ?? ($data['name'] ?? null);

        if ($slug && $this->repository->findCategoryBySlug($organizationId, $slug)) {
            throw new InvalidArgumentException("Category slug '{$slug}' already exists in this organization.");
        }

        if (! empty($data['parent_uuid'])) {
            $parent = $this->repository->findCategoryByUuid((string) $data['parent_uuid']);
            if (! $parent || $parent->organization_id !== $organizationId) {
                throw new InvalidArgumentException('Parent category must belong to the same organization.');
            }
            $data['parent_id'] = $parent->id;
            unset($data['parent_uuid']);
        } elseif (! empty($data['parent_id'])) {
            $parent = $this->repository->findCategoryById((int) $data['parent_id']);
            if (! $parent || $parent->organization_id !== $organizationId) {
                throw new InvalidArgumentException('Parent category must belong to the same organization.');
            }
        }

        return $this->repository->createCategory($data);
    }

    public function updateCategory(int|string $categoryIdOrUuid, array $data): Category
    {
        $category = is_string($categoryIdOrUuid)
            ? $this->repository->findCategoryByUuid($categoryIdOrUuid)
            : $this->repository->findCategoryById($categoryIdOrUuid);

        if (! $category) {
            throw new InvalidArgumentException("Category '{$categoryIdOrUuid}' not found.");
        }

        if (! empty($data['parent_uuid'])) {
            $parent = $this->repository->findCategoryByUuid((string) $data['parent_uuid']);
            if (! $parent || $parent->organization_id !== $category->organization_id) {
                throw new InvalidArgumentException('Parent category must belong to the same organization.');
            }
            if ($parent->id === $category->id) {
                throw new InvalidArgumentException('Category cannot be its own parent.');
            }
            $data['parent_id'] = $parent->id;
            unset($data['parent_uuid']);
        } elseif (array_key_exists('parent_id', $data) && $data['parent_id'] !== null) {
            if ((int) $data['parent_id'] === $category->id) {
                throw new InvalidArgumentException('Category cannot be its own parent.');
            }
        }

        return $this->repository->updateCategory($category->id, $data);
    }

    public function deleteCategory(string $uuid): bool
    {
        $category = $this->repository->findCategoryByUuid($uuid);
        if (! $category) {
            throw new InvalidArgumentException("Category with UUID '{$uuid}' not found.");
        }

        return $this->repository->deleteCategory($category->id);
    }

    public function getKnowledgeByUuid(string $uuid): ?Knowledge
    {
        return $this->repository->findKnowledgeByUuid($uuid);
    }

    public function createKnowledge(int $organizationId, int $authorId, array $data): Knowledge
    {
        $data['organization_id'] = $organizationId;
        $data['author_id'] = $authorId;
        $data['status'] = $data['status'] ?? 'draft';

        $slug = $data['slug'] ?? ($data['title'] ?? null);
        if ($slug && $this->repository->findKnowledgeBySlug($organizationId, $slug)) {
            throw new InvalidArgumentException("Knowledge slug '{$slug}' already exists in this organization.");
        }

        if (! empty($data['category_uuid'])) {
            $category = $this->repository->findCategoryByUuid((string) $data['category_uuid']);
            if (! $category || $category->organization_id !== $organizationId) {
                throw new InvalidArgumentException('Category must belong to the same organization.');
            }
            $data['category_id'] = $category->id;
            unset($data['category_uuid']);
        } elseif (! empty($data['category_id'])) {
            $category = $this->repository->findCategoryById((int) $data['category_id']);
            if (! $category || $category->organization_id !== $organizationId) {
                throw new InvalidArgumentException('Category must belong to the same organization.');
            }
        }

        return $this->repository->createKnowledge($data);
    }

    public function updateKnowledge(string $uuid, array $data): Knowledge
    {
        $knowledge = $this->repository->findKnowledgeByUuid($uuid);
        if (! $knowledge) {
            throw new InvalidArgumentException("Knowledge article with UUID '{$uuid}' not found.");
        }

        if (array_key_exists('category_uuid', $data)) {
            if ($data['category_uuid'] !== null) {
                $category = $this->repository->findCategoryByUuid((string) $data['category_uuid']);
                if (! $category || $category->organization_id !== $knowledge->organization_id) {
                    throw new InvalidArgumentException('Category must belong to the same organization.');
                }
                $data['category_id'] = $category->id;
            } else {
                $data['category_id'] = null;
            }
            unset($data['category_uuid']);
        }

        return $this->repository->updateKnowledge($knowledge->id, $data);
    }

    public function deleteKnowledge(string $uuid): bool
    {
        $knowledge = $this->repository->findKnowledgeByUuid($uuid);
        if (! $knowledge) {
            throw new InvalidArgumentException("Knowledge article with UUID '{$uuid}' not found.");
        }

        return $this->repository->deleteKnowledge($knowledge->id);
    }

    public function publishKnowledge(int|string $knowledgeIdOrUuid): Knowledge
    {
        $knowledge = is_string($knowledgeIdOrUuid)
            ? $this->repository->findKnowledgeByUuid($knowledgeIdOrUuid)
            : $this->repository->findKnowledgeById($knowledgeIdOrUuid);

        if (! $knowledge) {
            throw new InvalidArgumentException("Knowledge article '{$knowledgeIdOrUuid}' not found.");
        }

        return $this->repository->updateKnowledge($knowledge->id, [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function archiveKnowledge(int|string $knowledgeIdOrUuid): Knowledge
    {
        $knowledge = is_string($knowledgeIdOrUuid)
            ? $this->repository->findKnowledgeByUuid($knowledgeIdOrUuid)
            : $this->repository->findKnowledgeById($knowledgeIdOrUuid);

        if (! $knowledge) {
            throw new InvalidArgumentException("Knowledge article '{$knowledgeIdOrUuid}' not found.");
        }

        return $this->repository->updateKnowledge($knowledge->id, [
            'status' => 'archived',
        ]);
    }

    public function syncTags(int|string $knowledgeIdOrUuid, array $tagNames): Knowledge
    {
        $knowledge = is_string($knowledgeIdOrUuid)
            ? $this->repository->findKnowledgeByUuid($knowledgeIdOrUuid)
            : $this->repository->findKnowledgeById($knowledgeIdOrUuid);

        if (! $knowledge) {
            throw new InvalidArgumentException("Knowledge article '{$knowledgeIdOrUuid}' not found.");
        }

        $tagIds = [];
        foreach ($tagNames as $name) {
            if (empty(trim((string) $name))) {
                continue;
            }

            $tag = $this->repository->findOrCreateTag($knowledge->organization_id, trim((string) $name));
            $tagIds[] = $tag->id;
        }

        $this->repository->syncTags($knowledge, $tagIds);

        return $knowledge->fresh(['tags']);
    }

    public function addAttachment(int|string $knowledgeIdOrUuid, int $uploaderId, array $fileMetadata): Attachment
    {
        $knowledge = is_string($knowledgeIdOrUuid)
            ? $this->repository->findKnowledgeByUuid($knowledgeIdOrUuid)
            : $this->repository->findKnowledgeById($knowledgeIdOrUuid);

        if (! $knowledge) {
            throw new InvalidArgumentException("Knowledge article '{$knowledgeIdOrUuid}' not found.");
        }

        $fileMetadata['uploader_id'] = $uploaderId;

        return $this->repository->addAttachment($knowledge, $fileMetadata);
    }

    public function getKnowledgesByOrganization(int $organizationId): Collection
    {
        return $this->repository->getKnowledgesByOrganization($organizationId);
    }

    public function getKnowledgesByOrganizationPaginated(int $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getKnowledgesByOrganizationPaginated($organizationId, $perPage);
    }
}
