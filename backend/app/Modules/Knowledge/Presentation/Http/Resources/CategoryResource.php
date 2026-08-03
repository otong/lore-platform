<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Presentation\Http\Resources;

use App\Modules\Knowledge\Infrastructure\Persistence\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Category
 */
class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'parent_uuid' => $this->parent?->uuid,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
