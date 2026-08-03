<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Category;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Knowledge;
use App\Modules\Organization\Infrastructure\Persistence\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class KnowledgeApiTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->organization = Organization::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'LORE Test Org',
            'slug' => 'lore-test-org',
            'code' => 'LORE-TEST',
            'status' => 'active',
        ]);
    }

    public function test_authenticated_user_can_create_category(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/v1/organizations/{$this->organization->uuid}/categories", [
            'name' => 'Architecture Docs',
            'slug' => 'arch-docs',
            'description' => 'Documentation for system architecture',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => [
                    'name' => 'Architecture Docs',
                    'slug' => 'arch-docs',
                ],
            ]);

        $this->assertNotNull($response->json('data.uuid'));
    }

    public function test_authenticated_user_can_update_category(): void
    {
        Sanctum::actingAs($this->user);

        $category = Category::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->organization->id,
            'name' => 'Old Category',
            'slug' => 'old-category',
        ]);

        $response = $this->patchJson("/api/v1/categories/{$category->uuid}", [
            'name' => 'Updated Category',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => [
                    'name' => 'Updated Category',
                ],
            ]);
    }

    public function test_authenticated_user_can_delete_category(): void
    {
        Sanctum::actingAs($this->user);

        $category = Category::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->organization->id,
            'name' => 'To Be Deleted Category',
            'slug' => 'delete-cat',
        ]);

        $response = $this->deleteJson("/api/v1/categories/{$category->uuid}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Category deleted successfully',
            ]);

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_authenticated_user_can_create_knowledge(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/v1/organizations/{$this->organization->uuid}/knowledges", [
            'title' => 'Security Policies',
            'slug' => 'sec-policies',
            'content' => 'Full security guidelines content.',
            'status' => 'draft',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Knowledge article created successfully',
                'data' => [
                    'title' => 'Security Policies',
                    'slug' => 'sec-policies',
                    'status' => 'draft',
                ],
            ]);

        $this->assertNotNull($response->json('data.uuid'));
    }

    public function test_authenticated_user_can_update_knowledge(): void
    {
        Sanctum::actingAs($this->user);

        $knowledge = Knowledge::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->organization->id,
            'title' => 'Draft Article',
            'slug' => 'draft-article',
            'content' => 'Initial content',
            'author_id' => $this->user->id,
        ]);

        $response = $this->patchJson("/api/v1/knowledges/{$knowledge->uuid}", [
            'title' => 'Updated Article Title',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Knowledge article updated successfully',
                'data' => [
                    'title' => 'Updated Article Title',
                ],
            ]);
    }

    public function test_authenticated_user_can_delete_knowledge(): void
    {
        Sanctum::actingAs($this->user);

        $knowledge = Knowledge::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->organization->id,
            'title' => 'Temporary Article',
            'slug' => 'temp-article',
            'content' => 'Temporary content',
            'author_id' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/v1/knowledges/{$knowledge->uuid}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Knowledge article deleted successfully',
            ]);

        $this->assertSoftDeleted('knowledges', ['id' => $knowledge->id]);
    }

    public function test_knowledge_list_pagination(): void
    {
        Sanctum::actingAs($this->user);

        for ($i = 1; $i <= 5; $i++) {
            Knowledge::create([
                'uuid' => (string) Str::uuid(),
                'organization_id' => $this->organization->id,
                'title' => "Article {$i}",
                'slug' => "article-{$i}",
                'content' => "Content {$i}",
                'author_id' => $this->user->id,
            ]);
        }

        $response = $this->getJson("/api/v1/organizations/{$this->organization->uuid}/knowledges?per_page=2");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total'],
            ]);

        $this->assertEquals(5, $response->json('meta.total'));
        $this->assertEquals(2, count($response->json('data')));
    }

    public function test_authenticated_user_can_publish_and_archive_knowledge(): void
    {
        Sanctum::actingAs($this->user);

        $knowledge = Knowledge::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->organization->id,
            'title' => 'Publish Me',
            'slug' => 'publish-me',
            'content' => 'Content to publish',
            'status' => 'draft',
            'author_id' => $this->user->id,
        ]);

        $publishResponse = $this->postJson("/api/v1/knowledges/{$knowledge->uuid}/publish");
        $publishResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Knowledge article published successfully',
                'data' => [
                    'status' => 'published',
                ],
            ]);

        $archiveResponse = $this->postJson("/api/v1/knowledges/{$knowledge->uuid}/archive");
        $archiveResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Knowledge article archived successfully',
                'data' => [
                    'status' => 'archived',
                ],
            ]);
    }

    public function test_authenticated_user_can_sync_tags(): void
    {
        Sanctum::actingAs($this->user);

        $knowledge = Knowledge::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->organization->id,
            'title' => 'Tag Article',
            'slug' => 'tag-article',
            'content' => 'Content with tags',
            'author_id' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/knowledges/{$knowledge->uuid}/tags", [
            'tags' => ['Laravel', 'Architecture', 'CleanCode'],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Tags synced successfully',
            ]);

        $this->assertCount(3, $response->json('data.tags'));
    }

    public function test_authenticated_user_can_add_attachment_metadata(): void
    {
        Sanctum::actingAs($this->user);

        $knowledge = Knowledge::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->organization->id,
            'title' => 'Attachment Article',
            'slug' => 'att-article',
            'content' => 'Content with attachment',
            'author_id' => $this->user->id,
        ]);

        $response = $this->postJson("/api/v1/knowledges/{$knowledge->uuid}/attachments", [
            'file_name' => 'spec.pdf',
            'disk' => 'local',
            'path' => 'attachments/spec.pdf',
            'checksum' => 'abc123hash',
            'mime_type' => 'application/pdf',
            'file_size' => 2048,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Attachment metadata added successfully',
                'data' => [
                    'file_name' => 'spec.pdf',
                    'file_size' => 2048,
                ],
            ]);

        $this->assertNotNull($response->json('data.uuid'));
    }

    public function test_uuid_not_found_returns_404(): void
    {
        Sanctum::actingAs($this->user);

        $fakeUuid = (string) Str::uuid();

        $catResponse = $this->getJson("/api/v1/categories/{$fakeUuid}");
        $catResponse->assertStatus(404)->assertJson(['success' => false]);

        $knowResponse = $this->getJson("/api/v1/knowledges/{$fakeUuid}");
        $knowResponse->assertStatus(404)->assertJson(['success' => false]);
    }

    public function test_guest_forbidden_from_knowledge_endpoints(): void
    {
        $uuid = (string) Str::uuid();

        $this->getJson("/api/v1/organizations/{$uuid}/categories")->assertStatus(401);
        $this->postJson("/api/v1/organizations/{$uuid}/categories", ['name' => 'Fail'])->assertStatus(401);
        $this->getJson("/api/v1/categories/{$uuid}")->assertStatus(401);
        $this->patchJson("/api/v1/categories/{$uuid}", ['name' => 'Fail'])->assertStatus(401);
        $this->deleteJson("/api/v1/categories/{$uuid}")->assertStatus(401);

        $this->getJson("/api/v1/organizations/{$uuid}/knowledges")->assertStatus(401);
        $this->postJson("/api/v1/organizations/{$uuid}/knowledges", ['title' => 'Fail'])->assertStatus(401);
        $this->getJson("/api/v1/knowledges/{$uuid}")->assertStatus(401);
        $this->patchJson("/api/v1/knowledges/{$uuid}", ['title' => 'Fail'])->assertStatus(401);
        $this->deleteJson("/api/v1/knowledges/{$uuid}")->assertStatus(401);
    }
}
