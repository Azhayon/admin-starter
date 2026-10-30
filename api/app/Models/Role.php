<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Concerns\Filterable;
use App\Concerns\Sortable;

class Role extends Model
{
    use Filterable, Sortable;
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