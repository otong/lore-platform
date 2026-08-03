<?php

declare(strict_types=1);

namespace App\Modules\Organization\Infrastructure\Persistence\Repositories;

use App\Modules\Organization\Domain\Contracts\OrganizationRepositoryInterface;
use App\Modules\Organization\Infrastructure\Persistence\Models\Department;
use App\Modules\Organization\Infrastructure\Persistence\Models\Organization;
use App\Modules\Organization\Infrastructure\Persistence\Models\OrganizationMembership;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class OrganizationRepository implements OrganizationRepositoryInterface
{
    public function createOrganization(array $data): Organization
    {
        if (empty($data['uuid'])) {
            $data['uuid'] = (string) Str::uuid();
        }

        if (empty($data['slug']) && ! empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return Organization::create($data);
    }

    public function findOrganizationById(int $id): ?Organization
    {
        return Organization::with(['departments', 'memberships'])->find($id);
    }

    public function findOrganizationByUuid(string $uuid): ?Organization
    {
        return Organization::with(['departments', 'memberships'])->where('uuid', $uuid)->first();
    }

    public function findOrganizationBySlug(string $slug): ?Organization
    {
        return Organization::with(['departments', 'memberships'])->where('slug', $slug)->first();
    }

    public function getAllOrganizations(): Collection
    {
        return Organization::with(['departments', 'memberships'])->get();
    }

    public function createDepartment(array $data): Department
    {
        if (empty($data['uuid'])) {
            $data['uuid'] = (string) Str::uuid();
        }

        return Department::create($data);
    }

    public function findDepartmentById(int $id): ?Department
    {
        return Department::with(['organization', 'parent', 'children'])->find($id);
    }

    public function getDepartmentsByOrganization(int $organizationId): Collection
    {
        return Department::where('organization_id', $organizationId)->get();
    }

    public function assignMembership(array $data): OrganizationMembership
    {
        return OrganizationMembership::updateOrCreate(
            [
                'organization_id' => $data['organization_id'],
                'user_id' => $data['user_id'],
            ],
            [
                'department_id' => $data['department_id'] ?? null,
                'role' => $data['role'] ?? 'member',
                'status' => $data['status'] ?? 'active',
                'joined_at' => $data['joined_at'] ?? now(),
            ]
        );
    }

    public function findMembership(int $organizationId, int $userId): ?OrganizationMembership
    {
        return OrganizationMembership::where('organization_id', $organizationId)
            ->where('user_id', $userId)
            ->first();
    }
}
