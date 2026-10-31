<?php

namespace App\Models;

use App\Concerns\Filterable;
use App\Concerns\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use Filterable, Sortable;

    protected $fillable = ['name'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function filterableFields(): array
    {
        return ['name' => 'partial'];
    }

    public function searchableFields(): array
    {
        return ['name'];
    }

    public function sortableFields(): array
    {
        return ['id', 'name'];
    }

    public function defaultSort(): string
    {
        return 'name';
    }
}
