<?php

namespace App\Concerns;

use App\Models\AuditLog;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /** Laravel calls boot{TraitName}() once per model class and registers these listeners. */
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            $model->recordAudit('created', [], $model->auditSnapshot());
        });

        static::updated(function (Model $model) {
            // During the "updated" event, getChanges() = what changed and getOriginal() = the old values.
            $changes = Arr::except($model->getChanges(), $model->auditExcluded());

            if ($changes !== []) {
                $model->recordAudit('updated', Arr::only($model->getOriginal(), array_keys($changes)), $changes);
            }
        });

        static::deleted(function (Model $model) {
            $model->recordAudit('deleted', $model->auditSnapshot(), []);
        });
    }

    /** Attributes that must never be written to the log. Override per model. */
    public function auditExcluded(): array
    {
        return ['id', 'password', 'remember_token', 'created_at', 'updated_at'];
    }

    /** Human-readable name shown in the log (still readable after the row is deleted). */
    public function auditLabel(): string
    {
        return (string) ($this->name ?? $this->label ?? $this->getKey());
    }

    public function auditSnapshot(): array
    {
        return Arr::except($this->getAttributes(), $this->auditExcluded());
    }

    public function recordAudit(string $event, array $old = [], array $new = []): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),         // null for seeders, tinker and tests = "system"
            'event' => $event,
            'auditable_type' => $this->getMorphClass(),
            'auditable_id' => $this->getKey(),
            'auditable_label' => $this->auditLabel(),
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }

    /**
     * Run a relationship change (sync/attach/detach) and log it if the result differs.
     * Pivot changes fire no model events, so they need an explicit log entry.
     */
    public function auditRelation(string $relation, string $event, Closure $change, string $column = 'name'): void
    {
        $names = fn () => $this->{$relation}()->pluck($column)->sort()->values()->all();

        $before = $names();
        $change();
        $after = $names();

        if ($before !== $after) {
            $this->recordAudit($event, [$relation => $before], [$relation => $after]);
        }
    }
}