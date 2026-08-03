<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Knowledge extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'knowledges';

    protected $fillable = [
        'uuid',
        'organization_id',
        'category_id',
        'title',
        'slug',
        'content',
        'status',
        'author_id',
        'views_count',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'views_count' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'knowledge_tags', 'knowledge_id', 'tag_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'knowledge_id');
    }
}
