<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KnowledgeSearchResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'excerpt' => $this->excerpt ?? null,
            'status' => $this->status,
            'category' => $this->whenLoaded('category', fn () => new CategoryResource($this->category)),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'updated_at' => $this->updated_at?->toISOString(),
            'attachment_count' => $this->relationLoaded('attachments') ? $this->attachments->count() : 0,
        ];
    }
}
