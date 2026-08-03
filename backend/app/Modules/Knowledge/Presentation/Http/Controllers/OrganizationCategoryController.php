<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Knowledge\Application\Services\KnowledgeService;
use App\Modules\Knowledge\Presentation\Http\Requests\CreateCategoryRequest;
use App\Modules\Knowledge\Presentation\Http\Resources\CategoryResource;
use App\Modules\Organization\Application\Services\OrganizationService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class OrganizationCategoryController extends Controller
{
    public function __construct(
        protected OrganizationService $organizationService,
        protected KnowledgeService $knowledgeService
    ) {}

    public function index(string $orgUuid): JsonResponse
    {
        $organization = $this->organizationService->getOrganizationByUuid($orgUuid);

        if (! $organization) {
            return response()->json([
                'success' => false,
                'message' => "Organization not found for UUID: {$orgUuid}",
            ], 404);
        }

        $categories = $this->knowledgeService->getCategoriesByOrganization($organization->id);

        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully',
            'data' => CategoryResource::collection($categories),
        ], 200);
    }

    public function store(CreateCategoryRequest $request, string $orgUuid): JsonResponse
    {
        $organization = $this->organizationService->getOrganizationByUuid($orgUuid);

        if (! $organization) {
            return response()->json([
                'success' => false,
                'message' => "Organization not found for UUID: {$orgUuid}",
            ], 404);
        }

        try {
            $category = $this->knowledgeService->createCategory($organization->id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => new CategoryResource($category),
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
