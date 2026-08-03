<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Application\Services;

use App\Models\User;
use App\Modules\Knowledge\Application\Events\AttachmentDeleted;
use App\Modules\Knowledge\Application\Events\AttachmentDownloaded;
use App\Modules\Knowledge\Application\Events\AttachmentUploaded;
use App\Modules\Knowledge\Application\Events\TemporaryLinkGenerated;
use App\Modules\Knowledge\Domain\Contracts\KnowledgeRepositoryInterface;
use App\Modules\Knowledge\Infrastructure\Persistence\Models\Attachment;
use App\Modules\Knowledge\Infrastructure\Security\AntivirusPipeline;
use App\Modules\Knowledge\Infrastructure\Storage\StorageManager;
use App\Modules\Organization\Infrastructure\Persistence\Models\OrganizationMembership;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class AttachmentApplicationService
{
    public function __construct(
        protected KnowledgeRepositoryInterface $repository,
        protected StorageManager $storageManager,
        protected AntivirusPipeline $antivirusPipeline
    ) {}

    public function uploadAttachment(string $knowledgeUuid, int $uploaderId, UploadedFile $file): Attachment
    {
        $knowledge = $this->repository->findKnowledgeByUuid($knowledgeUuid);

        if (! $knowledge) {
            throw new InvalidArgumentException("Knowledge article with UUID '{$knowledgeUuid}' not found.");
        }

        // 1. Antivirus Scan
        $this->antivirusPipeline->scan($file);

        // 2. SHA-256 Checksum
        $checksumSha256 = hash_file('sha256', $file->getRealPath());

        // 3. Store Physical File
        $targetDirectory = "attachments/{$knowledge->organization_id}/{$knowledge->uuid}";
        $storedData = $this->storageManager->store($file, $targetDirectory);

        // 4. DB Transaction with Rollback Handling
        try {
            DB::beginTransaction();

            $attachment = $this->repository->addAttachment($knowledge, [
                'uuid' => (string) Str::uuid(),
                'file_name' => $storedData['file_name'],
                'disk' => $storedData['storage_disk'],
                'path' => $storedData['storage_path'],
                'checksum' => $checksumSha256,
                'mime_type' => $storedData['mime_type'],
                'file_size' => $storedData['file_size'],
                'uploader_id' => $uploaderId,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            // Delete physical file on DB failure (No Orphan File)
            $this->storageManager->delete($storedData['storage_path'], $storedData['storage_disk']);
            throw $e;
        }

        // 5. Dispatch Event
        AttachmentUploaded::dispatch($attachment);

        return $attachment;
    }

    public function downloadAttachment(string $uuid, User $user): Response
    {
        $attachment = $this->authorizeAttachmentAccess($uuid, $user);

        AttachmentDownloaded::dispatch($attachment, (int) $user->id);

        return $this->storageManager->download($attachment);
    }

    public function generateTemporaryLink(string $uuid, User $user, int $expiresInMinutes = 30): array
    {
        $attachment = $this->authorizeAttachmentAccess($uuid, $user);

        $allowedTtls = config('knowledge.attachments.signed_url_ttl_minutes', [5, 30, 60]);
        if (! in_array($expiresInMinutes, $allowedTtls, true)) {
            $expiresInMinutes = 30;
        }

        $temporaryUrl = $this->storageManager->generateTemporaryUrl($attachment, $expiresInMinutes);
        $expiresAt = now()->addMinutes($expiresInMinutes)->toISOString();

        TemporaryLinkGenerated::dispatch($attachment, $temporaryUrl, $expiresInMinutes);

        return [
            'temporary_url' => $temporaryUrl,
            'expires_at' => $expiresAt,
        ];
    }

    public function deleteAttachment(string $uuid, User $user): bool
    {
        $attachment = $this->authorizeAttachmentAccess($uuid, $user);

        $this->repository->deleteAttachment($attachment->id);

        $deleteMode = config('knowledge.attachments.physical_delete_mode', 'immediate');
        if ($deleteMode === 'immediate') {
            $this->storageManager->delete($attachment->path, $attachment->disk);
        }

        AttachmentDeleted::dispatch($attachment);

        return true;
    }

    protected function authorizeAttachmentAccess(string $uuid, User $user): Attachment
    {
        $attachment = $this->repository->findAttachmentByUuid($uuid);

        if (! $attachment) {
            throw new InvalidArgumentException("Attachment not found for UUID: {$uuid}");
        }

        $knowledge = $attachment->knowledge;
        if (! $knowledge) {
            throw new InvalidArgumentException("Knowledge article for attachment UUID '{$uuid}' not found.");
        }

        // Cross-Organization Access Check via OrganizationMembership
        $hasMembership = OrganizationMembership::where('organization_id', $knowledge->organization_id)
            ->where('user_id', $user->id)
            ->exists();

        if (! $hasMembership) {
            throw new AuthorizationException("User does not have access to organization {$knowledge->organization_id}");
        }

        return $attachment;
    }
}
