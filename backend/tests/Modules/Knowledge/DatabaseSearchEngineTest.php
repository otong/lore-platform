<?php

declare(strict_types=1);

namespace Tests\Modules\Knowledge;

use App\Modules\Knowledge\Infrastructure\Persistence\Models\Category;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Knowledge;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Tag;
use App\Modules\Knowledge\Infrastructure\Search\Engines\DatabaseSearchEngine;
use App\Modules\Organization\Infrastructure\Persistence\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DatabaseSearchEngineTest extends TestCase
{
    use RefreshDatabase;

    protected DatabaseSearchEngine $engine;

    protected Organization $org;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new DatabaseSearchEngine;

        $this->org = Organization::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Search Test Org',
            'slug' => 'search-test-org',
            'code' => 'STO',
            'status' => 'active',
        ]);

        $this->category = Category::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->org->id,
            'name' => 'DevOps',
            'slug' => 'devops',
        ]);
    }

    public function test_search_by_query_and_category(): void
    {
        $k1 = Knowledge::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->org->id,
            'category_id' => $this->category->id,
            'title' => 'Docker Kubernetes Deployment',
            'slug' => 'docker-kubernetes-deployment',
            'content' => 'Comprehensive guide for container orchestration.',
            'status' => 'published',
            'author_id' => 1,
        ]);

        $k2 = Knowledge::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->org->id,
            'title' => 'Python Basics',
            'slug' => 'python-basics',
            'content' => 'Introduction to Python syntax.',
            'status' => 'published',
            'author_id' => 1,
        ]);

        $result = $this->engine->search($this->org->id, [
            'query' => 'Kubernetes',
            'category_uuid' => $this->category->uuid,
        ]);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Docker Kubernetes Deployment', $result->items()[0]->title);
    }

    public function test_search_by_tag_and_author(): void
    {
        $tag = Tag::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->org->id,
            'name' => 'Security',
            'slug' => 'security',
        ]);

        $k = Knowledge::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->org->id,
            'title' => 'OAuth2 Security Spec',
            'slug' => 'oauth2-security-spec',
            'content' => 'Deep dive into token auth.',
            'status' => 'published',
            'author_id' => 42,
        ]);

        $k->tags()->attach($tag->id);

        $resultTag = $this->engine->searchByTag($this->org->id, 'Security');
        $this->assertEquals(1, $resultTag->total());

        $resultAuthor = $this->engine->searchByAuthor($this->org->id, 42);
        $this->assertEquals(1, $resultAuthor->total());
    }

    public function test_recent_and_popular_searches(): void
    {
        Knowledge::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->org->id,
            'title' => 'Article One',
            'slug' => 'article-one',
            'content' => 'Content One',
            'status' => 'published',
            'views_count' => 10,
            'author_id' => 1,
        ]);

        Knowledge::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->org->id,
            'title' => 'Article Two',
            'slug' => 'article-two',
            'content' => 'Content Two',
            'status' => 'published',
            'views_count' => 500,
            'author_id' => 1,
        ]);

        $recent = $this->engine->searchRecent($this->org->id, 2);
        $this->assertCount(2, $recent);

        $popular = $this->engine->searchPopular($this->org->id, 2);
        $this->assertEquals('Article Two', $popular->first()->title);
    }
}
