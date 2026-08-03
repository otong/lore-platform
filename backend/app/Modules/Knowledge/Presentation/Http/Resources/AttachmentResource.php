<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Presentation\Http\Resources;

use App\Modules\Knowledge\Infrastructure\Persistence\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Attachment
 */
class AttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'file_name' => $this->file_name,
            'storage_disk' => $this->disk,
            'storage_provider' => $this->disk ?? 'local',
            'storage_path' => $this->path,
            'checksum_sha256' => $this->checksum,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'visibility' => 'private',
            'uploaded_at' => $this->created_at?->toISOString(),
            'deleted_from_storage_at' => null,
        ];
    }
}
