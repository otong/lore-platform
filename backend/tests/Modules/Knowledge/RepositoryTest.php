<?php

declare(strict_types=1);

namespace Tests\Modules\Knowledge;

use App\Modules\Knowledge\Infrastructure\Persistence\Repositories\KnowledgeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected KnowledgeRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new KnowledgeRepository;
    }

    public function test_can_create_category_and_knowledge(): void
    {
        $category = $this->repository->createCategory([
            'organization_id' => 1,
            'name' => 'API Docs',
            'slug' => 'api-docs',
        ]);

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'slug' => 'api-docs']);

        $knowledge = $this->repository->createKnowledge([
            'organization_id' => 1,
            'category_id' => $category->id,
            'title' => 'Authentication Guide',
            'slug' => 'auth-guide',
            'content' => 'Content goes here.',
            'status' => 'draft',
            'author_id' => 10,
        ]);

        $this->assertDatabaseHas('knowledges', ['id' => $knowledge->id, 'slug' => 'auth-guide']);
    }

    public function test_can_sync_tags_and_add_attachment(): void
    {
        $knowledge = $this->repository->createKnowledge([
            'organization_id' => 1,
            'title' => 'Security Specs',
            'slug' => 'security-specs',
            'content' => 'Security documentation.',
            'author_id' => 10,
        ]);

        $tag1 = $this->repository->findOrCreateTag(1, 'Security');
        $tag2 = $this->repository->findOrCreateTag(1, 'Architecture');

        $this->repository->syncTags($knowledge, [$tag1->id, $tag2->id]);

        $this->assertCount(2, $knowledge->fresh()->tags);

        $attachment = $this->repository->addAttachment($knowledge, [
            'file_name' => 'security_policy.pdf',
            'disk' => 'local',
            'path' => 'attachments/security_policy.pdf',
            'checksum' => 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'uploader_id' => 10,
        ]);

        $this->assertDatabaseHas('attachments', ['id' => $attachment->id, 'file_name' => 'security_policy.pdf']);
    }

    public function test_soft_deletes_on_knowledge_and_category(): void
    {
        $category = $this->repository->createCategory([
            'organization_id' => 1,
            'name' => 'Archived Docs',
            'slug' => 'archived-docs',
        ]);

        $knowledge = $this->repository->createKnowledge([
            'organization_id' => 1,
            'category_id' => $category->id,
            'title' => 'Old Policy',
            'slug' => 'old-policy',
            'content' => 'Old content.',
            'author_id' => 1,
        ]);

        $knowledge->delete();
        $category->delete();

        $this->assertSoftDeleted('knowledges', ['id' => $knowledge->id]);
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }
}
