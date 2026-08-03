<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Knowledge\Application\Services\KnowledgeService;
use App\Modules\Knowledge\Presentation\Http\Requests\UpdateCategoryRequest;
use App\Modules\Knowledge\Presentation\Http\Resources\CategoryResource;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class CategoryController extends Controller
{
    public function __construct(
        protected KnowledgeService $knowledgeService
    ) {}

    public function show(string $uuid): JsonResponse
    {
        $category = $this->knowledgeService->getCategoryByUuid($uuid);

        if (! $category) {
            return response()->json([
                'success' => false,
                'message' => "Category not found for UUID: {$uuid}",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category details retrieved successfully',
            'data' => new CategoryResource($category),
        ], 200);
    }

    public function update(UpdateCategoryRequest $request, string $uuid): JsonResponse
    {
        try {
            $category = $this->knowledgeService->updateCategory($uuid, array_filter($request->validated(), fn ($v) => $v !== null));

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => new CategoryResource($category),
            ], 200);
        } catch (InvalidArgumentException $e) {
            $status = str_contains($e->getMessage(), 'not found') ? 404 : 422;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $status);
        }
    }

    public function destroy(string $uuid): JsonResponse
    {
        try {
            $this->knowledgeService->deleteCategory($uuid);

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully',
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
