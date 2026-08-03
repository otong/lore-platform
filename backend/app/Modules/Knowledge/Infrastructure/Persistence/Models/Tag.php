<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $table = 'tags';

    protected $fillable = [
        'uuid',
        'organization_id',
        'name',
        'slug',
    ];

    public function knowledges(): BelongsToMany
    {
        return $this->belongsToMany(Knowledge::class, 'knowledge_tags', 'tag_id', 'knowledge_id');
    }
}
