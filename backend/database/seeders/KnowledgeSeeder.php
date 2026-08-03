<?php

namespace Database\Seeders;

use App\Modules\Knowledge\Infrastructure\Persistence\Models\Category;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Knowledge;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KnowledgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizationId = 1;

        $category = Category::firstOrCreate(
            [
                'organization_id' => $organizationId,
                'slug' => 'general-guidelines',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'General Guidelines',
                'description' => 'Standard operational guidelines and documentation',
            ]
        );

        Knowledge::firstOrCreate(
            [
                'organization_id' => $organizationId,
                'slug' => 'getting-started-guide',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'category_id' => $category->id,
                'title' => 'Getting Started Guide',
                'content' => 'Welcome to LORE platform knowledge base. This guide provides an overview of operations.',
                'status' => 'published',
                'author_id' => 1,
                'published_at' => now(),
            ]
        );
    }
}
