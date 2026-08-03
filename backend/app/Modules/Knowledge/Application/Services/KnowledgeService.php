<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Application\Services;

use App\Modules\Knowledge\Domain\Contracts\KnowledgeRepositoryInterface;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Attachment;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Category;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Knowledge;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class KnowledgeService
{
    public function __construct(
        protected KnowledgeRepositoryInterface $repository
    ) {}

    public function createCategory(int $organizationId, array $data): Category
    {
        $data['organization_id'] = $organizationId;
        $slug = $data['slug'] ?? ($data['name'] ?? null);

        if ($slug && $this->repository->findCategoryBySlug($organizationId, $slug)) {
            throw new InvalidArgumentException("Category slug '{$slug}' already exists in this organization.");
        }

        if (! empty($data['parent_id'])) {
            $parent = $this->repository->findCategoryById((int) $data['parent_id']);
            if (! $parent || $parent->organization_id !== $organizationId) {
                throw new InvalidArgumentException('Parent category must belong to the same organization.');
            }
        }

        return $this->repository->createCategory($data);
    }

    public function updateCategory(int $categoryId, array $data): Category
    {
        $category = $this->repository->findCategoryById($categoryId);
        if (! $category) {
            throw new InvalidArgumentException("Category with ID {$categoryId} not found.");
        }

        if (! empty($data['parent_id']) && (int) $data['parent_id'] === $categoryId) {
            throw new InvalidArgumentException('Category cannot be its own parent.');
        }

        return $this->repository->updateCategory($categoryId, $data);
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

        if (! empty($data['category_id'])) {
            $category = $this->repository->findCategoryById((int) $data['category_id']);
            if (! $category || $category->organization_id !== $organizationId) {
                throw new InvalidArgumentException('Category must belong to the same organization.');
            }
        }

        return $this->repository->createKnowledge($data);
    }

    public function publishKnowledge(int $knowledgeId): Knowledge
    {
        $knowledge = $this->repository->findKnowledgeById($knowledgeId);
        if (! $knowledge) {
            throw new InvalidArgumentException("Knowledge article with ID {$knowledgeId} not found.");
        }

        return $this->repository->updateKnowledge($knowledgeId, [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function archiveKnowledge(int $knowledgeId): Knowledge
    {
        $knowledge = $this->repository->findKnowledgeById($knowledgeId);
        if (! $knowledge) {
            throw new InvalidArgumentException("Knowledge article with ID {$knowledgeId} not found.");
        }

        return $this->repository->updateKnowledge($knowledgeId, [
            'status' => 'archived',
        ]);
    }

    public function syncTags(int $knowledgeId, array $tagNames): Knowledge
    {
        $knowledge = $this->repository->findKnowledgeById($knowledgeId);
        if (! $knowledge) {
            throw new InvalidArgumentException("Knowledge article with ID {$knowledgeId} not found.");
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

    public function addAttachment(int $knowledgeId, int $uploaderId, array $fileMetadata): Attachment
    {
        $knowledge = $this->repository->findKnowledgeById($knowledgeId);
        if (! $knowledge) {
            throw new InvalidArgumentException("Knowledge article with ID {$knowledgeId} not found.");
        }

        $fileMetadata['uploader_id'] = $uploaderId;

        return $this->repository->addAttachment($knowledge, $fileMetadata);
    }

    public function getKnowledgesByOrganization(int $organizationId): Collection
    {
        return $this->repository->getKnowledgesByOrganization($organizationId);
    }
}
