<?php

namespace Database\Seeders;

use App\Modules\Organization\Infrastructure\Persistence\Models\Department;
use App\Modules\Organization\Infrastructure\Persistence\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organization = Organization::firstOrCreate(
            ['slug' => 'lore-enterprise'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'LORE Enterprise Corp',
                'code' => 'LORE-CORP',
                'status' => 'active',
            ]
        );

        Department::firstOrCreate(
            [
                'organization_id' => $organization->id,
                'code' => 'ENG',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Engineering Department',
            ]
        );

        Department::firstOrCreate(
            [
                'organization_id' => $organization->id,
                'code' => 'HR',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Human Resources Department',
            ]
        );
    }
}
