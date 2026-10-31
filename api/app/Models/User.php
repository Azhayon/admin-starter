<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Concerns\HasRoles;
use App\Concerns\Filterable;
use App\Concerns\Sortable;
use App\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles, Filterable, Sortable, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    public function filterableFields(): array
    {
        return [
            'name' => 'partial',
            'email' => 'partial',
            // A relationship filter: ?filter[role]=admin
            'role' => fn (Builder $q, $value) => $q->whereHas('roles', fn ($r) => $r->where('name', $value)),
        ];
    }

    public function searchableFields(): array
    {
        return ['name', 'email'];
    }

    public function sortableFields(): array
    {
        return ['id', 'name', 'email', 'created_at'];
    }

    public function auditLabel(): string
    {
        return $this->email; // names repeat, emails are unique
    }
}

