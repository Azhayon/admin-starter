# 0002: Audit logging via model events

Status: accepted

## Decision
An `Auditable` trait hooks Eloquent created/updated/deleted events and writes to an
append-only `audit_logs` table (polymorphic `auditable`, JSON old/new values).
Pivot changes (role/permission assignment) are logged explicitly via `auditRelation()`,
because `sync()` fires no model events.

## Consequences
- Logs are written in the same DB transaction as the change: both persist or neither does.
- Passwords and tokens are excluded by default (`auditExcluded()`).
- Morph map stores aliases ("user"), not class names. Each auditable model needs an alias.
- Changes made through raw queries or `Model::query()->update()` bypass model events and are not logged.