<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Infrastructure\Search\Engines;

use App\Modules\Knowledge\Domain\Contracts\KnowledgeSearchInterface;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Knowledge;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DatabaseSearchEngine implements KnowledgeSearchInterface
{
    public function search(int $organizationId, array $criteria, int $perPage = 15): LengthAwarePaginator
    {
        $query = Knowledge::with(['category', 'tags', 'attachments'])
            ->where('organization_id', $organizationId);

        if (! empty($criteria['status'])) {
            $query->where('status', $criteria['status']);
        }

        if (! empty($criteria['query'])) {
            $searchTerm = trim((string) $criteria['query']);
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('content', 'like', "%{$searchTerm}%")
                    ->orWhereHas('attachments', function (Builder $attQuery) use ($searchTerm) {
                        $attQuery->where('file_name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        if (! empty($criteria['category_uuid'])) {
            $query->whereHas('category', function (Builder $catQuery) use ($criteria) {
                $catQuery->where('uuid', $criteria['category_uuid']);
            });
        }

        if (! empty($criteria['tag'])) {
            $tagTerm = trim((string) $criteria['tag']);
            $query->whereHas('tags', function (Builder $tagQuery) use ($tagTerm) {
                $tagQuery->where('name', $tagTerm)->orWhere('slug', $tagTerm);
            });
        }

        if (! empty($criteria['author_id'])) {
            $query->where('author_id', (int) $criteria['author_id']);
        }

        if (! empty($criteria['created_from'])) {
            $query->where('created_at', '>=', $criteria['created_from'].' 00:00:00');
        }

        if (! empty($criteria['created_until'])) {
            $query->where('created_at', '<=', $criteria['created_until'].' 23:59:59');
        }

        $sortColumn = $criteria['sort'] ?? 'created_at';
        $sortDirection = strtolower((string) ($criteria['direction'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['created_at', 'updated_at', 'title', 'views_count'];
        if (! in_array($sortColumn, $allowedSorts, true)) {
            $sortColumn = 'created_at';
        }

        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate($perPage);
    }

    public function searchByCategory(int $organizationId, string $categoryUuid, int $perPage = 15): LengthAwarePaginator
    {
        return $this->search($organizationId, ['category_uuid' => $categoryUuid], $perPage);
    }

    public function searchByTag(int $organizationId, string $tagName, int $perPage = 15): LengthAwarePaginator
    {
        return $this->search($organizationId, ['tag' => $tagName], $perPage);
    }

    public function searchByAuthor(int $organizationId, int $authorId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->search($organizationId, ['author_id' => $authorId], $perPage);
    }

    public function searchRecent(int $organizationId, int $limit = 10): Collection
    {
        return Knowledge::with(['category', 'tags', 'attachments'])
            ->where('organization_id', $organizationId)
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function searchPopular(int $organizationId, int $limit = 10): Collection
    {
        return Knowledge::with(['category', 'tags', 'attachments'])
            ->where('organization_id', $organizationId)
            ->where('status', 'published')
            ->orderBy('views_count', 'desc')
            ->limit($limit)
            ->get();
    }
}
