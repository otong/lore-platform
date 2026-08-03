<?php

declare(strict_types=1);

namespace Tests\Modules\Knowledge;

use App\Models\User;
use App\Modules\Knowledge\Application\Events\AttachmentDeleted;
use App\Modules\Knowledge\Application\Events\AttachmentUploaded;
use App\Modules\Knowledge\Application\Events\TemporaryLinkGenerated;
use App\Modules\Knowledge\Application\Services\AttachmentApplicationService;
use App\Modules\Knowledge\Domain\Contracts\KnowledgeRepositoryInterface;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Knowledge;
use App\Modules\Knowledge\Infrastructure\Security\AntivirusPipeline;
use App\Modules\Knowledge\Infrastructure\Storage\StorageManager;
use App\Modules\Organization\Infrastructure\Persistence\Models\Organization;
use App\Modules\Organization\Infrastructure\Persistence\Models\OrganizationMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AttachmentApplicationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AttachmentApplicationService $service;

    protected Organization $org;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->service = new AttachmentApplicationService(
            $this->app->make(KnowledgeRepositoryInterface::class),
            $this->app->make(StorageManager::class),
            $this->app->make(AntivirusPipeline::class)
        );

        $this->org = Organization::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Service Test Org',
            'slug' => 'service-test-org',
            'code' => 'STO',
            'status' => 'active',
        ]);
    }

    public function test_service_uploads_attachment_and_dispatches_event(): void
    {
        Event::fake([AttachmentUploaded::class]);

        $knowledge = Knowledge::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->org->id,
            'title' => 'Event Article',
            'slug' => 'event-article',
            'content' => 'Content',
            'author_id' => 1,
        ]);

        $file = UploadedFile::fake()->createWithContent('document.pdf', '%PDF-1.4 Fake PDF Content');

        $attachment = $this->service->uploadAttachment($knowledge->uuid, 1, $file);

        $this->assertNotNull($attachment);
        $this->assertEquals('document.pdf', $attachment->file_name);
        $this->assertNotEmpty($attachment->checksum);
        Event::assertDispatched(AttachmentUploaded::class);
    }

    public function test_service_generates_temporary_link_and_deletes_attachment(): void
    {
        Event::fake([TemporaryLinkGenerated::class, AttachmentDeleted::class]);

        $user = User::factory()->create();
        OrganizationMembership::create([
            'organization_id' => $this->org->id,
            'user_id' => $user->id,
            'role' => 'member',
        ]);

        $knowledge = Knowledge::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->org->id,
            'title' => 'Delete Article',
            'slug' => 'delete-article',
            'content' => 'Content',
            'author_id' => $user->id,
        ]);

        $file = UploadedFile::fake()->createWithContent('doc.pdf', '%PDF-1.4 Fake PDF Content');
        $attachment = $this->service->uploadAttachment($knowledge->uuid, $user->id, $file);

        $linkResult = $this->service->generateTemporaryLink($attachment->uuid, $user, 30);
        $this->assertArrayHasKey('temporary_url', $linkResult);
        Event::assertDispatched(TemporaryLinkGenerated::class);

        $deleted = $this->service->deleteAttachment($attachment->uuid, $user);
        $this->assertTrue($deleted);
        Event::assertDispatched(AttachmentDeleted::class);
    }
}
