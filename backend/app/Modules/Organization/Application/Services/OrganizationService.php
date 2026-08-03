<?php

declare(strict_types=1);

namespace App\Modules\Organization\Application\Services;

use App\Modules\Organization\Domain\Contracts\OrganizationRepositoryInterface;
use App\Modules\Organization\Infrastructure\Persistence\Models\Department;
use App\Modules\Organization\Infrastructure\Persistence\Models\Organization;
use App\Modules\Organization\Infrastructure\Persistence\Models\OrganizationMembership;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class OrganizationService
{
    public function __construct(
        protected OrganizationRepositoryInterface $repository
    ) {}

    /**
     * Create a new organization.
     */
    public function createOrganization(array $data): Organization
    {
        if ($this->repository->findOrganizationBySlug($data['slug'] ?? '')) {
            throw new InvalidArgumentException("Organization slug already exists: {$data['slug']}");
        }

        return $this->repository->createOrganization($data);
    }

    /**
     * Retrieve an organization by ID.
     */
    public function getOrganizationById(int $id): ?Organization
    {
        return $this->repository->findOrganizationById($id);
    }

    /**
     * Retrieve an organization by UUID.
     */
    public function getOrganizationByUuid(string $uuid): ?Organization
    {
        return $this->repository->findOrganizationByUuid($uuid);
    }

    /**
     * Retrieve an organization by slug.
     */
    public function getOrganizationBySlug(string $slug): ?Organization
    {
        return $this->repository->findOrganizationBySlug($slug);
    }

    /**
     * Retrieve all organizations.
     */
    public function getAllOrganizations(): Collection
    {
        return $this->repository->getAllOrganizations();
    }

    /**
     * Create a department inside an organization.
     */
    public function createDepartment(int $organizationId, array $data): Department
    {
        $organization = $this->repository->findOrganizationById($organizationId);
        if (! $organization) {
            throw new InvalidArgumentException("Organization with ID {$organizationId} does not exist.");
        }

        $data['organization_id'] = $organizationId;

        if (! empty($data['parent_id'])) {
            $parent = $this->repository->findDepartmentById((int) $data['parent_id']);

            if (! $parent) {
                throw new InvalidArgumentException("Parent department with ID {$data['parent_id']} does not exist.");
            }

            if ($parent->organization_id !== $organizationId) {
                throw new InvalidArgumentException('Parent department must belong to the same organization.');
            }
        }

        return $this->repository->createDepartment($data);
    }

    /**
     * Assign a user membership to an organization and optional department.
     */
    public function assignUserToOrganization(
        int $organizationId,
        int $userId,
        ?int $departmentId = null,
        string $role = 'member',
        string $status = 'active'
    ): OrganizationMembership {
        $organization = $this->repository->findOrganizationById($organizationId);
        if (! $organization) {
            throw new InvalidArgumentException("Organization with ID {$organizationId} does not exist.");
        }

        if ($departmentId !== null) {
            $department = $this->repository->findDepartmentById($departmentId);
            if (! $department || $department->organization_id !== $organizationId) {
                throw new InvalidArgumentException("Invalid department ID {$departmentId} for organization.");
            }
        }

        return $this->repository->assignMembership([
            'organization_id' => $organizationId,
            'user_id' => $userId,
            'department_id' => $departmentId,
            'role' => $role,
            'status' => $status,
            'joined_at' => now(),
        ]);
    }
}
