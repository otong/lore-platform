<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Knowledge\Domain\Contracts\KnowledgeRepositoryInterface;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Knowledge;
use App\Modules\Knowledge\Infrastructure\Security\Contracts\AntivirusScannerInterface;
use App\Modules\Knowledge\Infrastructure\Storage\StorageManager;
use App\Modules\Organization\Infrastructure\Persistence\Models\Organization;
use App\Modules\Organization\Infrastructure\Persistence\Models\OrganizationMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class KnowledgeAttachmentApiTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $org1;

    protected Organization $org2;

    protected User $userOrg1;

    protected User $userOrg2;

    protected Knowledge $knowledgeOrg1;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->org1 = Organization::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Org One',
            'slug' => 'org-one',
            'code' => 'ORG1',
            'status' => 'active',
        ]);

        $this->org2 = Organization::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Org Two',
            'slug' => 'org-two',
            'code' => 'ORG2',
            'status' => 'active',
        ]);

        $this->userOrg1 = User::factory()->create();
        $this->userOrg2 = User::factory()->create();

        OrganizationMembership::create([
            'organization_id' => $this->org1->id,
            'user_id' => $this->userOrg1->id,
            'role' => 'member',
        ]);

        OrganizationMembership::create([
            'organization_id' => $this->org2->id,
            'user_id' => $this->userOrg2->id,
            'role' => 'member',
        ]);

        $this->knowledgeOrg1 = Knowledge::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->org1->id,
            'title' => 'Org 1 Article',
            'slug' => 'org-1-article',
            'content' => 'Content Org 1',
            'author_id' => $this->userOrg1->id,
        ]);
    }

    public function test_upload_success(): void
    {
        Sanctum::actingAs($this->userOrg1);

        $file = UploadedFile::fake()->create('architecture.pdf', 500);

        $response = $this->postJson("/api/v1/knowledges/{$this->knowledgeOrg1->uuid}/attachments/upload", [
            'file' => $file,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Attachment uploaded successfully',
                'data' => [
                    'file_name' => 'architecture.pdf',
                ],
            ]);

        $this->assertNotNull($response->json('data.uuid'));
        $this->assertNotNull($response->json('data.checksum_sha256'));
    }

    public function test_upload_rollback_on_db_failure(): void
    {
        Sanctum::actingAs($this->userOrg1);

        $file = UploadedFile::fake()->create('fail_db.pdf', 100);

        $this->mock(KnowledgeRepositoryInterface::class, function ($mock) {
            $mock->shouldReceive('findKnowledgeByUuid')->andReturn($this->knowledgeOrg1);
            $mock->shouldReceive('addAttachment')->andThrow(new \RuntimeException('DB Error simulation'));
        });

        $response = $this->postJson("/api/v1/knowledges/{$this->knowledgeOrg1->uuid}/attachments/upload", [
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'message' => 'DB Error simulation']);
    }

    public function test_upload_rollback_on_storage_failure(): void
    {
        Sanctum::actingAs($this->userOrg1);

        $this->mock(StorageManager::class, function ($mock) {
            $mock->shouldReceive('store')->andThrow(new \RuntimeException('Storage disk write failure'));
        });

        $file = UploadedFile::fake()->create('fail_storage.pdf', 100);

        $response = $this->postJson("/api/v1/knowledges/{$this->knowledgeOrg1->uuid}/attachments/upload", [
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'message' => 'Storage disk write failure']);

        $this->assertDatabaseMissing('attachments', ['file_name' => 'fail_storage.pdf']);
    }

    public function test_download_success(): void
    {
        Sanctum::actingAs($this->userOrg1);

        $file = UploadedFile::fake()->create('download_test.pdf', 100);
        $uploadResp = $this->postJson("/api/v1/knowledges/{$this->knowledgeOrg1->uuid}/attachments/upload", ['file' => $file]);

        $uuid = $uploadResp->json('data.uuid');

        $downloadResp = $this->getJson("/api/v1/attachments/{$uuid}/download");
        $downloadResp->assertStatus(200);
    }

    public function test_temporary_link_generation(): void
    {
        Sanctum::actingAs($this->userOrg1);

        $file = UploadedFile::fake()->create('temp_link.pdf', 100);
        $uploadResp = $this->postJson("/api/v1/knowledges/{$this->knowledgeOrg1->uuid}/attachments/upload", ['file' => $file]);

        $uuid = $uploadResp->json('data.uuid');

        $response = $this->postJson("/api/v1/attachments/{$uuid}/temporary-link", [
            'expires_in' => 30,
        ]);

        $response->assertStatus(200)
            ->assertHeader('Pragma', 'no-cache')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['temporary_url', 'expires_at'],
            ]);
    }

    public function test_delete_attachment(): void
    {
        Sanctum::actingAs($this->userOrg1);

        $file = UploadedFile::fake()->create('delete_me.pdf', 100);
        $uploadResp = $this->postJson("/api/v1/knowledges/{$this->knowledgeOrg1->uuid}/attachments/upload", ['file' => $file]);

        $uuid = $uploadResp->json('data.uuid');

        $deleteResp = $this->deleteJson("/api/v1/attachments/{$uuid}");
        $deleteResp->assertStatus(200)->assertJson(['success' => true]);

        $this->assertSoftDeleted('attachments', ['uuid' => $uuid]);
    }

    public function test_403_cross_organization(): void
    {
        Sanctum::actingAs($this->userOrg1);

        $file = UploadedFile::fake()->create('cross_org.pdf', 100);
        $uploadResp = $this->postJson("/api/v1/knowledges/{$this->knowledgeOrg1->uuid}/attachments/upload", ['file' => $file]);

        $uuid = $uploadResp->json('data.uuid');

        // Login as User from Organization 2
        Sanctum::actingAs($this->userOrg2);

        $downloadResp = $this->getJson("/api/v1/attachments/{$uuid}/download");
        $downloadResp->assertStatus(403)->assertJson(['success' => false]);

        $linkResp = $this->postJson("/api/v1/attachments/{$uuid}/temporary-link");
        $linkResp->assertStatus(403)->assertJson(['success' => false]);

        $deleteResp = $this->deleteJson("/api/v1/attachments/{$uuid}");
        $deleteResp->assertStatus(403)->assertJson(['success' => false]);
    }

    public function test_404_soft_deleted_attachment(): void
    {
        Sanctum::actingAs($this->userOrg1);

        $file = UploadedFile::fake()->create('soft_del.pdf', 100);
        $uploadResp = $this->postJson("/api/v1/knowledges/{$this->knowledgeOrg1->uuid}/attachments/upload", ['file' => $file]);

        $uuid = $uploadResp->json('data.uuid');

        $this->deleteJson("/api/v1/attachments/{$uuid}");

        $downloadResp = $this->getJson("/api/v1/attachments/{$uuid}/download");
        $downloadResp->assertStatus(404)->assertJson(['success' => false]);
    }

    public function test_expired_temporary_link_invalid_signature(): void
    {
        Sanctum::actingAs($this->userOrg1);

        $file = UploadedFile::fake()->create('signed.pdf', 100);
        $uploadResp = $this->postJson("/api/v1/knowledges/{$this->knowledgeOrg1->uuid}/attachments/upload", ['file' => $file]);

        $uuid = $uploadResp->json('data.uuid');

        $invalidSignedUrl = "/api/v1/attachments/{$uuid}/download?expires=1000&signature=invalid_hash";

        $response = $this->getJson($invalidSignedUrl);
        $response->assertStatus(401);
    }

    public function test_invalid_mime(): void
    {
        Sanctum::actingAs($this->userOrg1);

        $file = UploadedFile::fake()->create('virus.exe', 100);

        $response = $this->postJson("/api/v1/knowledges/{$this->knowledgeOrg1->uuid}/attachments/upload", [
            'file' => $file,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['file']);
    }

    public function test_oversized_file(): void
    {
        Sanctum::actingAs($this->userOrg1);

        // Max is 20480 KB (20 MB), fake file with 25000 KB
        $file = UploadedFile::fake()->create('big_file.pdf', 25000);

        $response = $this->postJson("/api/v1/knowledges/{$this->knowledgeOrg1->uuid}/attachments/upload", [
            'file' => $file,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['file']);
    }

    public function test_antivirus_rejection(): void
    {
        Sanctum::actingAs($this->userOrg1);

        $this->mock(AntivirusScannerInterface::class, function ($mock) {
            $mock->shouldReceive('scan')->andReturn(false);
        });

        $file = UploadedFile::fake()->create('infected.pdf', 100);

        $response = $this->postJson("/api/v1/knowledges/{$this->knowledgeOrg1->uuid}/attachments/upload", [
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_missing_storage_driver(): void
    {
        Sanctum::actingAs($this->userOrg1);

        config(['knowledge.attachments.default_disk' => 'unsupported_disk_driver']);

        $file = UploadedFile::fake()->create('driver_fail.pdf', 100);

        $response = $this->postJson("/api/v1/knowledges/{$this->knowledgeOrg1->uuid}/attachments/upload", [
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }
}
