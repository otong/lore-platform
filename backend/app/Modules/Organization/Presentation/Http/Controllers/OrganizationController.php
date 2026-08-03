<?php

declare(strict_types=1);

namespace App\Modules\Organization\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Organization\Application\Services\OrganizationService;
use App\Modules\Organization\Presentation\Http\Requests\AssignMembershipRequest;
use App\Modules\Organization\Presentation\Http\Requests\CreateOrganizationRequest;
use App\Modules\Organization\Presentation\Http\Resources\OrganizationMembershipResource;
use App\Modules\Organization\Presentation\Http\Resources\OrganizationResource;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class OrganizationController extends Controller
{
    public function __construct(
        protected OrganizationService $service
    ) {}

    public function store(CreateOrganizationRequest $request): JsonResponse
    {
        try {
            $organization = $this->service->createOrganization($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Organization created successfully',
                'data' => new OrganizationResource($organization),
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function index(): JsonResponse
    {
        $organizations = $this->service->getAllOrganizations();

        return response()->json([
            'success' => true,
            'message' => 'Organizations retrieved successfully',
            'data' => OrganizationResource::collection($organizations),
        ], 200);
    }

    public function show(string $uuid): JsonResponse
    {
        $organization = $this->service->getOrganizationByUuid($uuid);

        if (! $organization) {
            return response()->json([
                'success' => false,
                'message' => "Organization not found for UUID: {$uuid}",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Organization details retrieved successfully',
            'data' => new OrganizationResource($organization),
        ], 200);
    }

    public function assignMember(AssignMembershipRequest $request, string $uuid): JsonResponse
    {
        $organization = $this->service->getOrganizationByUuid($uuid);

        if (! $organization) {
            return response()->json([
                'success' => false,
                'message' => "Organization not found for UUID: {$uuid}",
            ], 404);
        }

        try {
            $membership = $this->service->assignUserToOrganization(
                organizationId: $organization->id,
                userId: (int) $request->validated('user_id'),
                departmentId: $request->validated('department_id') ? (int) $request->validated('department_id') : null,
                role: (string) ($request->validated('role') ?? 'member'),
                status: (string) ($request->validated('status') ?? 'active')
            );

            return response()->json([
                'success' => true,
                'message' => 'User assigned to organization successfully',
                'data' => new OrganizationMembershipResource($membership),
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
