<?php

declare(strict_types=1);

namespace Tests\Modules\Knowledge;

use App\Modules\Knowledge\Application\Services\KnowledgeService;
use App\Modules\Knowledge\Domain\Contracts\KnowledgeRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    protected KnowledgeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new KnowledgeService(
            $this->app->make(KnowledgeRepositoryInterface::class)
        );
    }

    public function test_service_creates_category_and_knowledge(): void
    {
        $category = $this->service->createCategory(1, [
            'name' => 'Engineering Handbook',
            'slug' => 'eng-handbook',
        ]);

        $this->assertEquals('Engineering Handbook', $category->name);

        $knowledge = $this->service->createKnowledge(1, 100, [
            'category_id' => $category->id,
            'title' => 'Coding Standards',
            'slug' => 'coding-standards',
            'content' => 'Follow PSR-12 conventions.',
        ]);

        $this->assertEquals('draft', $knowledge->status);
        $this->assertEquals(100, $knowledge->author_id);
    }

    public function test_service_enforces_organization_scoped_slug_uniqueness(): void
    {
        $this->service->createCategory(1, [
            'name' => 'Shared Category',
            'slug' => 'shared-cat',
        ]);

        // Creating category with same slug in Org 2 must succeed
        $org2Category = $this->service->createCategory(2, [
            'name' => 'Shared Category Org 2',
            'slug' => 'shared-cat',
        ]);
        $this->assertNotNull($org2Category);

        // Creating category with duplicate slug in same Org 1 must fail
        $this->expectException(InvalidArgumentException::class);
        $this->service->createCategory(1, [
            'name' => 'Duplicate Category Org 1',
            'slug' => 'shared-cat',
        ]);
    }

    public function test_service_publishes_and_archives_knowledge(): void
    {
        $knowledge = $this->service->createKnowledge(1, 100, [
            'title' => 'Release Notes',
            'slug' => 'release-notes',
            'content' => 'v1.0.0 released.',
        ]);

        $published = $this->service->publishKnowledge($knowledge->id);
        $this->assertEquals('published', $published->status);
        $this->assertNotNull($published->published_at);

        $archived = $this->service->archiveKnowledge($knowledge->id);
        $this->assertEquals('archived', $archived->status);
    }
}
