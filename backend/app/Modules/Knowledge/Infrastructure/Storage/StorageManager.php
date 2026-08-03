<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Infrastructure\Storage;

use App\Modules\Knowledge\Infrastructure\Persistence\Models\Attachment;
use App\Modules\Knowledge\Infrastructure\Storage\Contracts\StorageDriverInterface;
use App\Modules\Knowledge\Infrastructure\Storage\Drivers\AzureBlobStorageDriver;
use App\Modules\Knowledge\Infrastructure\Storage\Drivers\LocalStorageDriver;
use App\Modules\Knowledge\Infrastructure\Storage\Drivers\MinioStorageDriver;
use App\Modules\Knowledge\Infrastructure\Storage\Drivers\S3StorageDriver;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class StorageManager
{
    /**
     * @var array<string, StorageDriverInterface>
     */
    protected array $drivers = [];

    public function __construct(array $drivers = [])
    {
        $this->drivers = $drivers ?: [
            'local' => app(LocalStorageDriver::class),
            's3' => app(S3StorageDriver::class),
            'minio' => app(MinioStorageDriver::class),
            'azure' => app(AzureBlobStorageDriver::class),
        ];
    }

    public function driver(?string $name = null): StorageDriverInterface
    {
        $key = $name ?? config('knowledge.attachments.default_disk', 'local');

        return $this->drivers[$key] ?? throw new InvalidArgumentException("Missing or unsupported storage driver: [{$key}]");
    }

    public function store(UploadedFile $file, string $targetDirectory, ?string $disk = null): array
    {
        return $this->driver($disk)->store($file, $targetDirectory, $disk);
    }

    public function download(Attachment $attachment): Response
    {
        $disk = $attachment->disk ?? $attachment->storage_disk ?? 'local';

        return $this->driver($disk)->download($attachment);
    }

    public function generateTemporaryUrl(Attachment $attachment, int $expirationMinutes): string
    {
        $disk = $attachment->disk ?? $attachment->storage_disk ?? 'local';

        return $this->driver($disk)->generateTemporaryUrl($attachment, $expirationMinutes);
    }

    public function delete(string $path, ?string $disk = null): bool
    {
        return $this->driver($disk)->delete($path, $disk);
    }
}
