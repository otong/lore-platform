<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Infrastructure\Search\Engines;

use App\Modules\Knowledge\Domain\Contracts\KnowledgeSearchInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class MeilisearchEngine implements KnowledgeSearchInterface
{
    public function search(int $organizationId, array $criteria, int $perPage = 15): LengthAwarePaginator
    {
        throw new \BadMethodCallException('Meilisearch driver is not installed. Please set KNOWLEDGE_SEARCH_DRIVER=database.');
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
        return new Collection;
    }

    public function searchPopular(int $organizationId, int $limit = 10): Collection
    {
        return new Collection;
    }
}
