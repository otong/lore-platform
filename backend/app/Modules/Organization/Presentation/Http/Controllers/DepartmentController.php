<?php

declare(strict_types=1);

namespace App\Modules\Organization\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Organization\Application\Services\OrganizationService;
use App\Modules\Organization\Presentation\Http\Requests\CreateDepartmentRequest;
use App\Modules\Organization\Presentation\Http\Resources\DepartmentResource;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class DepartmentController extends Controller
{
    public function __construct(
        protected OrganizationService $service
    ) {}

    public function store(CreateDepartmentRequest $request, string $uuid): JsonResponse
    {
        $organization = $this->service->getOrganizationByUuid($uuid);

        if (! $organization) {
            return response()->json([
                'success' => false,
                'message' => "Organization not found for UUID: {$uuid}",
            ], 404);
        }

        try {
            $department = $this->service->createDepartment($organization->id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Department created successfully',
                'data' => new DepartmentResource($department),
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
