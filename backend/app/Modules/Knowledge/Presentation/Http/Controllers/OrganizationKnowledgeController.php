<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Knowledge\Application\Services\KnowledgeService;
use App\Modules\Knowledge\Presentation\Http\Requests\CreateKnowledgeRequest;
use App\Modules\Knowledge\Presentation\Http\Resources\KnowledgeResource;
use App\Modules\Organization\Application\Services\OrganizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class OrganizationKnowledgeController extends Controller
{
    public function __construct(
        protected OrganizationService $organizationService,
        protected KnowledgeService $knowledgeService
    ) {}

    public function index(Request $request, string $orgUuid): JsonResponse
    {
        $organization = $this->organizationService->getOrganizationByUuid($orgUuid);

        if (! $organization) {
            return response()->json([
                'success' => false,
                'message' => "Organization not found for UUID: {$orgUuid}",
            ], 404);
        }

        $perPage = (int) $request->query('per_page', 15);
        $paginated = $this->knowledgeService->getKnowledgesByOrganizationPaginated($organization->id, $perPage);

        return KnowledgeResource::collection($paginated)
            ->additional([
                'success' => true,
                'message' => 'Knowledge articles retrieved successfully',
            ])
            ->response();
    }

    public function store(CreateKnowledgeRequest $request, string $orgUuid): JsonResponse
    {
        $organization = $this->organizationService->getOrganizationByUuid($orgUuid);

        if (! $organization) {
            return response()->json([
                'success' => false,
                'message' => "Organization not found for UUID: {$orgUuid}",
            ], 404);
        }

        try {
            $authorId = (int) $request->user()->id;
            $knowledge = $this->knowledgeService->createKnowledge($organization->id, $authorId, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Knowledge article created successfully',
                'data' => new KnowledgeResource($knowledge->load(['category', 'tags', 'attachments'])),
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
