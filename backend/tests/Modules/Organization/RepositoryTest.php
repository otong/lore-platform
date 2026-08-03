<?php

declare(strict_types=1);

namespace Tests\Modules\Organization;

use App\Modules\Organization\Infrastructure\Persistence\Repositories\OrganizationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected OrganizationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new OrganizationRepository;
    }

    public function test_can_create_and_find_organization(): void
    {
        $org = $this->repository->createOrganization([
            'name' => 'Acme Corporation',
            'slug' => 'acme-corp',
            'code' => 'ACME-001',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('organizations', ['id' => $org->id, 'slug' => 'acme-corp']);

        $found = $this->repository->findOrganizationBySlug('acme-corp');
        $this->assertNotNull($found);
        $this->assertEquals('Acme Corporation', $found->name);
    }

    public function test_can_create_department_under_organization(): void
    {
        $org = $this->repository->createOrganization([
            'name' => 'Global Tech',
            'slug' => 'global-tech',
            'code' => 'GT-001',
        ]);

        $dept = $this->repository->createDepartment([
            'organization_id' => $org->id,
            'name' => 'Research & Development',
            'code' => 'RND',
        ]);

        $this->assertDatabaseHas('departments', ['id' => $dept->id, 'code' => 'RND']);
        $this->assertEquals($org->id, $dept->organization_id);
    }

    public function test_can_assign_membership(): void
    {
        $org = $this->repository->createOrganization([
            'name' => 'Innovate LLC',
            'slug' => 'innovate-llc',
            'code' => 'INN-001',
        ]);

        $membership = $this->repository->assignMembership([
            'organization_id' => $org->id,
            'user_id' => 999,
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('organization_memberships', [
            'organization_id' => $org->id,
            'user_id' => 999,
            'role' => 'admin',
        ]);
        $this->assertNotNull($membership);
    }
}
