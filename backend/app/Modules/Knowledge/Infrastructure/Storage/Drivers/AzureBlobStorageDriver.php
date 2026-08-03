<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Infrastructure\Storage\Drivers;

use App\Modules\Knowledge\Infrastructure\Persistence\Models\Attachment;
use App\Modules\Knowledge\Infrastructure\Storage\Contracts\StorageDriverInterface;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class AzureBlobStorageDriver implements StorageDriverInterface
{
    public function store(UploadedFile $file, string $targetDirectory, ?string $disk = null): array
    {
        return [
            'file_name' => $file->getClientOriginalName(),
            'storage_disk' => $disk ?? 'azure',
            'storage_provider' => 'azure',
            'storage_path' => $targetDirectory.'/'.$file->hashName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ];
    }

    public function download(Attachment $attachment): Response
    {
        throw new \BadMethodCallException('Azure Blob driver is not configured for direct binary download in local environment.');
    }

    public function generateTemporaryUrl(Attachment $attachment, int $expirationMinutes): string
    {
        return "https://account.blob.core.windows.net/container/{$attachment->path}?sig=dummy";
    }

    public function delete(string $path, ?string $disk = null): bool
    {
        return true;
    }
}
