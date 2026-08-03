<?php

declare(strict_types=1);

namespace Tests\Modules\Knowledge;

use App\Models\User;
use App\Modules\Knowledge\Application\Services\SearchApplicationService;
use App\Modules\Knowledge\Domain\Contracts\KnowledgeSearchInterface;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Knowledge;
use App\Modules\Organization\Infrastructure\Persistence\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SearchApplicationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SearchApplicationService $service;

    protected Organization $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new SearchApplicationService(
            $this->app->make(KnowledgeSearchInterface::class)
        );

        $this->org = Organization::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Service Test Org',
            'slug' => 'service-test-org',
            'code' => 'STO',
            'status' => 'active',
        ]);
    }

    public function test_service_search_generates_excerpt_snippets(): void
    {
        $user = User::factory()->create();

        Knowledge::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->org->id,
            'title' => 'System Architecture',
            'slug' => 'system-architecture',
            'content' => 'This document describes the high-level architecture of Antigravity system and how components communicate.',
            'status' => 'published',
            'author_id' => $user->id,
        ]);

        $paginator = $this->service->search($this->org->id, [
            'query' => 'Antigravity',
            'author_uuid' => $user->uuid,
        ]);

        $this->assertEquals(1, $paginator->total());
        $item = $paginator->items()[0];
        $this->assertStringContainsString('Antigravity', $item->excerpt);
    }
}
