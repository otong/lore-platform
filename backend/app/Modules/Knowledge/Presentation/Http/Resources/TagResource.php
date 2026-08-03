<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Presentation\Http\Resources;

use App\Modules\Knowledge\Infrastructure\Persistence\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Tag
 */
class TagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }
}
