<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Infrastructure\Storage\Drivers;

use App\Modules\Knowledge\Infrastructure\Persistence\Models\Attachment;
use App\Modules\Knowledge\Infrastructure\Storage\Contracts\StorageDriverInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class LocalStorageDriver implements StorageDriverInterface
{
    public function store(UploadedFile $file, string $targetDirectory, ?string $disk = null): array
    {
        $targetDisk = $disk ?? config('knowledge.attachments.default_disk', 'local');
        $storedPath = $file->store($targetDirectory, $targetDisk);

        if ($storedPath === false) {
            throw new \RuntimeException("Failed to store file on local disk [{$targetDisk}]");
        }

        return [
            'file_name' => $file->getClientOriginalName(),
            'storage_disk' => $targetDisk,
            'storage_provider' => 'local',
            'storage_path' => $storedPath,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ];
    }

    public function download(Attachment $attachment): Response
    {
        $disk = $attachment->disk ?? $attachment->storage_disk ?? 'local';
        $path = $attachment->path ?? $attachment->storage_path;

        if (! Storage::disk($disk)->exists($path)) {
            throw new \InvalidArgumentException("Physical file not found at path: {$path}");
        }

        return Storage::disk($disk)->download($path, $attachment->file_name);
    }

    public function generateTemporaryUrl(Attachment $attachment, int $expirationMinutes): string
    {
        return URL::temporarySignedRoute(
            'api.v1.knowledge.attachments.download',
            now()->addMinutes($expirationMinutes),
            ['uuid' => $attachment->uuid]
        );
    }

    public function delete(string $path, ?string $disk = null): bool
    {
        $targetDisk = $disk ?? config('knowledge.attachments.default_disk', 'local');

        if (Storage::disk($targetDisk)->exists($path)) {
            return Storage::disk($targetDisk)->delete($path);
        }

        return true;
    }
}
