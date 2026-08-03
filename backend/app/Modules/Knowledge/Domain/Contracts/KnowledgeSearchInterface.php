<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Domain\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface KnowledgeSearchInterface
{
    public function search(int $organizationId, array $criteria, int $perPage = 15): LengthAwarePaginator;

    public function searchByCategory(int $organizationId, string $categoryUuid, int $perPage = 15): LengthAwarePaginator;

    public function searchByTag(int $organizationId, string $tagName, int $perPage = 15): LengthAwarePaginator;

    public function searchByAuthor(int $organizationId, int $authorId, int $perPage = 15): LengthAwarePaginator;

    public function searchRecent(int $organizationId, int $limit = 10): Collection;

    public function searchPopular(int $organizationId, int $limit = 10): Collection;
}
