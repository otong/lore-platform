<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Knowledge\Application\Services\SearchApplicationService;
use App\Modules\Knowledge\Presentation\Http\Requests\SearchKnowledgeRequest;
use App\Modules\Knowledge\Presentation\Http\Resources\KnowledgeSearchResultResource;
use App\Modules\Organization\Domain\Contracts\OrganizationRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KnowledgeSearchController extends Controller
{
    public function __construct(
        protected SearchApplicationService $searchService,
        protected OrganizationRepositoryInterface $organizationRepository
    ) {}

    public function search(SearchKnowledgeRequest $request, string $orgUuid): JsonResponse
    {
        $organization = $this->organizationRepository->findOrganizationByUuid($orgUuid);

        if (! $organization) {
            return response()->json([
                'success' => false,
                'message' => "Organization with UUID '{$orgUuid}' not found.",
            ], 404);
        }

        $criteria = $request->validated();
        $perPage = (int) $request->input('per_page', 15);

        $results = $this->searchService->search((int) $organization->id, $criteria, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Search results retrieved successfully',
            'data' => KnowledgeSearchResultResource::collection($results->items()),
            'links' => [
                'first' => $results->url(1),
                'last' => $results->url($results->lastPage()),
                'prev' => $results->previousPageUrl(),
                'next' => $results->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $results->currentPage(),
                'from' => $results->firstItem(),
                'last_page' => $results->lastPage(),
                'path' => $results->path(),
                'per_page' => $results->perPage(),
                'to' => $results->lastItem(),
                'total' => $results->total(),
            ],
        ], 200);
    }

    public function recent(Request $request, string $orgUuid): JsonResponse
    {
        $organization = $this->organizationRepository->findOrganizationByUuid($orgUuid);

        if (! $organization) {
            return response()->json([
                'success' => false,
                'message' => "Organization with UUID '{$orgUuid}' not found.",
            ], 404);
        }

        $limit = (int) $request->input('limit', 10);
        $results = $this->searchService->getRecentKnowledges((int) $organization->id, $limit);

        return response()->json([
            'success' => true,
            'message' => 'Recent knowledge articles retrieved successfully',
            'data' => KnowledgeSearchResultResource::collection($results),
        ], 200);
    }

    public function popular(Request $request, string $orgUuid): JsonResponse
    {
        $organization = $this->organizationRepository->findOrganizationByUuid($orgUuid);

        if (! $organization) {
            return response()->json([
                'success' => false,
                'message' => "Organization with UUID '{$orgUuid}' not found.",
            ], 404);
        }

        $limit = (int) $request->input('limit', 10);
        $results = $this->searchService->getPopularKnowledges((int) $organization->id, $limit);

        return response()->json([
            'success' => true,
            'message' => 'Popular knowledge articles retrieved successfully',
            'data' => KnowledgeSearchResultResource::collection($results),
        ], 200);
    }
}
