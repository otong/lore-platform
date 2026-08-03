<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Infrastructure\Storage\Contracts;

use App\Modules\Knowledge\Infrastructure\Persistence\Models\Attachment;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

interface StorageDriverInterface
{
    public function store(UploadedFile $file, string $targetDirectory, ?string $disk = null): array;

    public function download(Attachment $attachment): Response;

    public function generateTemporaryUrl(Attachment $attachment, int $expirationMinutes): string;

    public function delete(string $path, ?string $disk = null): bool;
}
