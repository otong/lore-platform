<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Infrastructure\Storage\Drivers;

use App\Modules\Knowledge\Infrastructure\Persistence\Models\Attachment;
use App\Modules\Knowledge\Infrastructure\Storage\Contracts\StorageDriverInterface;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class S3StorageDriver implements StorageDriverInterface
{
    public function store(UploadedFile $file, string $targetDirectory, ?string $disk = null): array
    {
        return [
            'file_name' => $file->getClientOriginalName(),
            'storage_disk' => $disk ?? 's3',
            'storage_provider' => 's3',
            'storage_path' => $targetDirectory.'/'.$file->hashName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ];
    }

    public function download(Attachment $attachment): Response
    {
        throw new \BadMethodCallException('S3 driver is not configured for direct binary download in local environment.');
    }

    public function generateTemporaryUrl(Attachment $attachment, int $expirationMinutes): string
    {
        return "https://s3.amazonaws.com/bucket/{$attachment->path}?expires=".(time() + ($expirationMinutes * 60));
    }

    public function delete(string $path, ?string $disk = null): bool
    {
        return true;
    }
}
