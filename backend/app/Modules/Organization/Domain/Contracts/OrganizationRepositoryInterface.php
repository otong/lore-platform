<?php

declare(strict_types=1);

namespace App\Modules\Organization\Domain\Contracts;

use App\Modules\Organization\Infrastructure\Persistence\Models\Department;
use App\Modules\Organization\Infrastructure\Persistence\Models\Organization;
use App\Modules\Organization\Infrastructure\Persistence\Models\OrganizationMembership;
use Illuminate\Database\Eloquent\Collection;

interface OrganizationRepositoryInterface
{
    public function createOrganization(array $data): Organization;

    public function findOrganizationById(int $id): ?Organization;

    public function findOrganizationByUuid(string $uuid): ?Organization;

    public function findOrganizationBySlug(string $slug): ?Organization;

    public function getAllOrganizations(): Collection;

    public function createDepartment(array $data): Department;

    public function findDepartmentById(int $id): ?Department;

    public function getDepartmentsByOrganization(int $organizationId): Collection;

    public function assignMembership(array $data): OrganizationMembership;

    public function findMembership(int $organizationId, int $userId): ?OrganizationMembership;
}
