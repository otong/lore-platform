<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Application\Services;

use App\Models\User;
use App\Modules\Knowledge\Domain\Contracts\KnowledgeSearchInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class SearchApplicationService
{
    public function __construct(
        protected KnowledgeSearchInterface $searchEngine
    ) {}

    public function search(int $organizationId, array $criteria, int $perPage = 15): LengthAwarePaginator
    {
        if (! empty($criteria['author_uuid'])) {
            $authorParam = (string) $criteria['author_uuid'];
            if (is_numeric($authorParam)) {
                $criteria['author_id'] = (int) $authorParam;
            } else {
                $user = User::where('uuid', $authorParam)->first();
                if ($user) {
                    $criteria['author_id'] = $user->id;
                }
            }
        }

        $paginator = $this->searchEngine->search($organizationId, $criteria, $perPage);

        // Generate excerpt snippets for search results
        $query = $criteria['query'] ?? null;
        $paginator->getCollection()->transform(function ($knowledge) use ($query) {
            $knowledge->excerpt = $this->generateExcerpt((string) $knowledge->content, $query);

            return $knowledge;
        });

        return $paginator;
    }

    public function getRecentKnowledges(int $organizationId, int $limit = 10): Collection
    {
        $collection = $this->searchEngine->searchRecent($organizationId, $limit);
        $collection->transform(function ($knowledge) {
            $knowledge->excerpt = Str::limit(strip_tags((string) $knowledge->content), 150);

            return $knowledge;
        });

        return $collection;
    }

    public function getPopularKnowledges(int $organizationId, int $limit = 10): Collection
    {
        $collection = $this->searchEngine->searchPopular($organizationId, $limit);
        $collection->transform(function ($knowledge) {
            $knowledge->excerpt = Str::limit(strip_tags((string) $knowledge->content), 150);

            return $knowledge;
        });

        return $collection;
    }

    protected function generateExcerpt(string $content, ?string $query = null): string
    {
        $plainText = strip_tags($content);

        if (empty($query)) {
            return Str::limit($plainText, 150);
        }

        $pos = mb_stripos($plainText, $query);
        if ($pos === false) {
            return Str::limit($plainText, 150);
        }

        $start = max(0, $pos - 50);
        $snippet = mb_substr($plainText, $start, 150);

        if ($start > 0) {
            $snippet = '...'.$snippet;
        }

        if (($start + 150) < mb_strlen($plainText)) {
            $snippet .= '...';
        }

        return $snippet;
    }
}
