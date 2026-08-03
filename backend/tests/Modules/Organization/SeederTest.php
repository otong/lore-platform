<?php

declare(strict_types=1);

namespace Tests\Modules\Organization;

use App\Modules\Organization\Infrastructure\Persistence\Models\Department;
use App\Modules\Organization\Infrastructure\Persistence\Models\Organization;
use Database\Seeders\OrganizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_seeder_creates_default_records(): void
    {
        $this->seed(OrganizationSeeder::class);

        $this->assertDatabaseHas('organizations', [
            'slug' => 'lore-enterprise',
            'code' => 'LORE-CORP',
        ]);

        $this->assertDatabaseHas('departments', [
            'code' => 'ENG',
        ]);

        $this->assertDatabaseHas('departments', [
            'code' => 'HR',
        ]);
    }

    public function test_organization_seeder_is_idempotent_when_run_multiple_times(): void
    {
        $this->seed(OrganizationSeeder::class);
        $initialOrgCount = Organization::count();
        $initialDeptCount = Department::count();

        // Second execution
        $this->seed(OrganizationSeeder::class);

        $this->assertEquals($initialOrgCount, Organization::count());
        $this->assertEquals($initialDeptCount, Department::count());
    }
}
