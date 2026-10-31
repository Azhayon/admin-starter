<?php

namespace App\Models;

use App\Concerns\Auditable;
use App\Concerns\Filterable;
use App\Concerns\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use Auditable, Filterable, Sortable;

    protected $fillable = ['name', 'label'];

    // Laravel infers the pivot tables (permission_role, role_user) from alphabetical model names.
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function filterableFields(): array
    {
        return ['name' => 'partial', 'label' => 'partial'];
    }

    public function searchableFields(): array
    {
        return ['name', 'label'];
    }

    public function sortableFields(): array
    {
        return ['id', 'name', 'label', 'created_at'];
    }
}
