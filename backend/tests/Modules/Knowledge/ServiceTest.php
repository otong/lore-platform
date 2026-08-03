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
            'category_uuid' => $category->uuid,
            'title' => 'Coding Standards',
            'slug' => 'coding-standards',
            'content' => 'Follow PSR-12 conventions.',
        ]);

        $this->assertEquals('draft', $knowledge->status);
        $this->assertEquals(100, $knowledge->author_id);
        $this->assertEquals($category->id, $knowledge->category_id);
    }

    public function test_service_resolves_and_deletes_category_and_knowledge_by_uuid(): void
    {
        $category = $this->service->createCategory(1, [
            'name' => 'Dev Category',
            'slug' => 'dev-cat',
        ]);

        $knowledge = $this->service->createKnowledge(1, 10, [
            'title' => 'Dev Guide',
            'slug' => 'dev-guide',
            'content' => 'Dev guide content',
        ]);

        $fetchedCat = $this->service->getCategoryByUuid($category->uuid);
        $this->assertNotNull($fetchedCat);

        $fetchedKnowledge = $this->service->getKnowledgeByUuid($knowledge->uuid);
        $this->assertNotNull($fetchedKnowledge);

        $this->assertTrue($this->service->deleteKnowledge($knowledge->uuid));
        $this->assertTrue($this->service->deleteCategory($category->uuid));

        $this->assertSoftDeleted('knowledges', ['id' => $knowledge->id]);
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
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

    public function test_service_publishes_archives_and_syncs_tags_by_uuid(): void
    {
        $knowledge = $this->service->createKnowledge(1, 100, [
            'title' => 'Release Notes',
            'slug' => 'release-notes',
            'content' => 'v1.0.0 released.',
        ]);

        $published = $this->service->publishKnowledge($knowledge->uuid);
        $this->assertEquals('published', $published->status);
        $this->assertNotNull($published->published_at);

        $archived = $this->service->archiveKnowledge($knowledge->uuid);
        $this->assertEquals('archived', $archived->status);

        $synced = $this->service->syncTags($knowledge->uuid, ['Release', 'v1.0']);
        $this->assertCount(2, $synced->tags);
    }
}
