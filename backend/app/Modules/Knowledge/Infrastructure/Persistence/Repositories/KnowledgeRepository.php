<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Infrastructure\Persistence\Repositories;

use App\Modules\Knowledge\Domain\Contracts\KnowledgeRepositoryInterface;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Attachment;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Category;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Knowledge;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class KnowledgeRepository implements KnowledgeRepositoryInterface
{
    public function createCategory(array $data): Category
    {
        if (empty($data['uuid'])) {
            $data['uuid'] = (string) Str::uuid();
        }

        if (empty($data['slug']) && ! empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return Category::create($data);
    }

    public function updateCategory(int $id, array $data): Category
    {
        $category = Category::findOrFail($id);
        $category->update($data);

        return $category;
    }

    public function findCategoryById(int $id): ?Category
    {
        return Category::with(['parent', 'children'])->find($id);
    }

    public function findCategoryBySlug(int $organizationId, string $slug): ?Category
    {
        return Category::where('organization_id', $organizationId)
            ->where('slug', $slug)
            ->first();
    }

    public function getCategoriesByOrganization(int $organizationId): Collection
    {
        return Category::where('organization_id', $organizationId)->get();
    }

    public function createKnowledge(array $data): Knowledge
    {
        if (empty($data['uuid'])) {
            $data['uuid'] = (string) Str::uuid();
        }

        if (empty($data['slug']) && ! empty($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        return Knowledge::create($data);
    }

    public function updateKnowledge(int $id, array $data): Knowledge
    {
        $knowledge = Knowledge::findOrFail($id);
        $knowledge->update($data);

        return $knowledge;
    }

    public function findKnowledgeById(int $id): ?Knowledge
    {
        return Knowledge::with(['category', 'tags', 'attachments'])->find($id);
    }

    public function findKnowledgeByUuid(string $uuid): ?Knowledge
    {
        return Knowledge::with(['category', 'tags', 'attachments'])
            ->where('uuid', $uuid)
            ->first();
    }

    public function findKnowledgeBySlug(int $organizationId, string $slug): ?Knowledge
    {
        return Knowledge::with(['category', 'tags', 'attachments'])
            ->where('organization_id', $organizationId)
            ->where('slug', $slug)
            ->first();
    }

    public function getKnowledgesByOrganization(int $organizationId): Collection
    {
        return Knowledge::with(['category', 'tags', 'attachments'])
            ->where('organization_id', $organizationId)
            ->get();
    }

    public function findOrCreateTag(int $organizationId, string $name): Tag
    {
        $slug = Str::slug($name);

        return Tag::firstOrCreate(
            [
                'organization_id' => $organizationId,
                'slug' => $slug,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => $name,
            ]
        );
    }

    public function syncTags(Knowledge $knowledge, array $tagIds): void
    {
        $knowledge->tags()->sync($tagIds);
    }

    public function addAttachment(Knowledge $knowledge, array $data): Attachment
    {
        if (empty($data['uuid'])) {
            $data['uuid'] = (string) Str::uuid();
        }

        return $knowledge->attachments()->create($data);
    }
}
