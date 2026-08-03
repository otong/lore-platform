<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Presentation\Http\Resources;

use App\Modules\Knowledge\Infrastructure\Persistence\Models\Knowledge;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Knowledge
 */
class KnowledgeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'status' => $this->status,
            'views_count' => $this->views_count,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),
            'published_at' => $this->published_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
