<?php

declare(strict_types=1);

namespace App\Modules\Organization\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'organizations';

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'code',
        'status',
    ];

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class, 'organization_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(OrganizationMembership::class, 'organization_id');
    }
}
