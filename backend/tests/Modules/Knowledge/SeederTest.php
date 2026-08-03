<?php

declare(strict_types=1);

namespace Tests\Modules\Knowledge;

use App\Modules\Knowledge\Infrastructure\Persistence\Models\Category;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Knowledge;
use Database\Seeders\KnowledgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_knowledge_seeder_creates_default_records(): void
    {
        $this->seed(KnowledgeSeeder::class);

        $this->assertDatabaseHas('categories', [
            'slug' => 'general-guidelines',
        ]);

        $this->assertDatabaseHas('knowledges', [
            'slug' => 'getting-started-guide',
            'status' => 'published',
        ]);
    }

    public function test_knowledge_seeder_is_idempotent_when_run_multiple_times(): void
    {
        $this->seed(KnowledgeSeeder::class);
        $initialCategoryCount = Category::count();
        $initialKnowledgeCount = Knowledge::count();

        // Second execution
        $this->seed(KnowledgeSeeder::class);

        $this->assertEquals($initialCategoryCount, Category::count());
        $this->assertEquals($initialKnowledgeCount, Knowledge::count());
    }
}
