<?php

declare(strict_types=1);

namespace Tests\Modules\Organization;

use App\Modules\Organization\Application\Services\OrganizationService;
use App\Modules\Organization\Domain\Contracts\OrganizationRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OrganizationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OrganizationService(
            $this->app->make(OrganizationRepositoryInterface::class)
        );
    }

    public function test_service_creates_organization(): void
    {
        $org = $this->service->createOrganization([
            'name' => 'Stark Industries',
            'slug' => 'stark-ind',
            'code' => 'STARK-01',
        ]);

        $this->assertEquals('Stark Industries', $org->name);
        $this->assertNotNull($org->uuid);
    }

    public function test_service_throws_exception_on_duplicate_slug(): void
    {
        $this->service->createOrganization([
            'name' => 'Wayne Enterprises',
            'slug' => 'wayne-ent',
            'code' => 'WAYNE-01',
        ]);

        $this->expectException(InvalidArgumentException::class);

        $this->service->createOrganization([
            'name' => 'Wayne Enterprises Duplicate',
            'slug' => 'wayne-ent',
            'code' => 'WAYNE-02',
        ]);
    }

    public function test_service_validates_department_parent_organization_match(): void
    {
        $org1 = $this->service->createOrganization([
            'name' => 'Org One',
            'slug' => 'org-one',
            'code' => 'ORG-01',
        ]);

        $org2 = $this->service->createOrganization([
            'name' => 'Org Two',
            'slug' => 'org-two',
            'code' => 'ORG-02',
        ]);

        $dept1 = $this->service->createDepartment($org1->id, [
            'name' => 'Org1 Dept',
            'code' => 'O1D',
        ]);

        $this->expectException(InvalidArgumentException::class);

        // Attempting to assign dept1 as parent for dept in org2 must fail
        $this->service->createDepartment($org2->id, [
            'name' => 'Org2 Dept Invalid Parent',
            'code' => 'O2D',
            'parent_id' => $dept1->id,
        ]);
    }

    public function test_service_assigns_user_to_organization(): void
    {
        $org = $this->service->createOrganization([
            'name' => 'Cyberdyne',
            'slug' => 'cyberdyne',
            'code' => 'CYBER-01',
        ]);

        $membership = $this->service->assignUserToOrganization(
            organizationId: $org->id,
            userId: 500,
            role: 'owner'
        );

        $this->assertEquals($org->id, $membership->organization_id);
        $this->assertEquals(500, $membership->user_id);
        $this->assertEquals('owner', $membership->role);
    }
}
