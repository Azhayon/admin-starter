<?php

namespace App\Models;

use App\Concerns\Filterable;
use App\Concerns\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;

class AuditLog extends Model
{
    use Filterable, Sortable;

    public $timestamps = false; // we only have created_at, set explicitly

    protected $fillable = [
        'user_id', 'event', 'auditable_type', 'auditable_id', 'auditable_label',
        'old_values', 'new_values', 'ip_address', 'created_at',
    ];

    protected function casts(): array
    {
        return ['old_values' => 'array', 'new_values' => 'array', 'created_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        // Append-only: a log you can edit is not a log.
        static::updating(fn () => throw new LogicException('Audit logs are append-only.'));
        static::deleting(fn () => throw new LogicException('Audit logs are append-only.'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo(); // resolves to the User/Role row (null if it was deleted)
    }

    public function filterableFields(): array
    {
        return ['event' => 'exact', 'auditable_type' => 'exact', 'auditable_id' => 'exact', 'user_id' => 'exact'];
    }

    public function searchableFields(): array
    {
        return ['auditable_label', 'event'];
    }

    public function sortableFields(): array
    {
        return ['id', 'created_at', 'event', 'auditable_type'];
    }

    public function defaultSort(): string
    {
        return '-created_at';
    }
}