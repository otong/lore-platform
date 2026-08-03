<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Attachment;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Category;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Knowledge;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Tag;
use App\Modules\Organization\Infrastructure\Persistence\Models\Organization;
use App\Modules\Organization\Infrastructure\Persistence\Models\OrganizationMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class KnowledgeSearchApiTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $org;

    protected User $user;

    protected Category $category;

    protected Tag $tag;

    protected Knowledge $knowledge1;

    protected Knowledge $knowledge2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Search Feature Org',
            'slug' => 'search-feature-org',
            'code' => 'SFO',
            'status' => 'active',
        ]);

        $this->user = User::factory()->create();

        OrganizationMembership::create([
            'organization_id' => $this->org->id,
            'user_id' => $this->user->id,
            'role' => 'member',
        ]);

        $this->category = Category::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->org->id,
            'name' => 'Backend Engineering',
            'slug' => 'backend-engineering',
        ]);

        $this->tag = Tag::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->org->id,
            'name' => 'Laravel',
            'slug' => 'laravel',
        ]);

        $this->knowledge1 = Knowledge::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->org->id,
            'category_id' => $this->category->id,
            'title' => 'Laravel Rest API Best Practices',
            'slug' => 'laravel-rest-api-best-practices',
            'content' => 'Building scalable REST API endpoints using Laravel microservices architecture.',
            'status' => 'published',
            'views_count' => 150,
            'author_id' => $this->user->id,
        ]);

        $this->knowledge1->tags()->attach($this->tag->id);

        Attachment::create([
            'uuid' => (string) Str::uuid(),
            'knowledge_id' => $this->knowledge1->id,
            'file_name' => 'swagger_spec.pdf',
            'disk' => 'local',
            'path' => 'attachments/1/uuid/swagger_spec.pdf',
            'checksum' => 'hash123',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'uploader_id' => $this->user->id,
        ]);

        $this->knowledge2 = Knowledge::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->org->id,
            'title' => 'Frontend React Performance',
            'slug' => 'frontend-react-performance',
            'content' => 'Optimizing rendering cycle in modern React applications.',
            'status' => 'published',
            'views_count' => 50,
            'author_id' => $this->user->id,
        ]);
    }

    public function test_keyword_search_returns_matching_articles(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/v1/organizations/{$this->org->uuid}/knowledges/search?query=Laravel");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Search results retrieved successfully',
            ])
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.title', 'Laravel Rest API Best Practices');
    }

    public function test_search_by_attachment_filename(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/v1/organizations/{$this->org->uuid}/knowledges/search?query=swagger_spec");

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.uuid', $this->knowledge1->uuid);
    }

    public function test_search_filter_by_category_and_tag(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/v1/organizations/{$this->org->uuid}/knowledges/search?category_uuid={$this->category->uuid}&tag=Laravel");

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.uuid', $this->knowledge1->uuid);
    }

    public function test_search_filter_by_author_and_date_range(): void
    {
        Sanctum::actingAs($this->user);

        $today = now()->format('Y-m-d');
        $url = "/api/v1/organizations/{$this->org->uuid}/knowledges/search?author_uuid={$this->user->id}&created_from={$today}&created_until={$today}&sort=title&direction=asc";

        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.uuid', $this->knowledge2->uuid)
            ->assertJsonPath('data.1.uuid', $this->knowledge1->uuid);
    }

    public function test_sorting_and_pagination(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/v1/organizations/{$this->org->uuid}/knowledges/search?sort=views_count&direction=desc&per_page=1");

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.uuid', $this->knowledge1->uuid);
    }

    public function test_empty_search_results(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/v1/organizations/{$this->org->uuid}/knowledges/search?query=NonExistentTerm123");

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 0)
            ->assertJsonCount(0, 'data');
    }

    public function test_invalid_filter_validation_error(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/v1/organizations/{$this->org->uuid}/knowledges/search?created_from=invalid-date");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['created_from']);
    }

    public function test_recent_and_popular_endpoints(): void
    {
        Sanctum::actingAs($this->user);

        $recentResp = $this->getJson("/api/v1/organizations/{$this->org->uuid}/knowledges/recent?limit=5");
        $recentResp->assertStatus(200)->assertJsonPath('success', true);

        $popularResp = $this->getJson("/api/v1/organizations/{$this->org->uuid}/knowledges/popular?limit=5");
        $popularResp->assertStatus(200)->assertJsonPath('success', true);
    }

    public function test_guest_forbidden_from_search(): void
    {
        $response = $this->getJson("/api/v1/organizations/{$this->org->uuid}/knowledges/search?query=Laravel");
        $response->assertStatus(401);
    }
}
