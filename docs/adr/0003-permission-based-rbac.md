# 0003: Permission-based RBAC

Status: accepted

## Decision
Users have roles; roles bundle permissions named `resource.action` (e.g. `users.update`).
Code only ever checks permissions, never role names. Permissions are integrated into Laravel's
Gate via `Gate::before`, so `can:users.update` middleware and `$user->can()` work unchanged.
The catalog (`config/rbac.php`) is the source of truth; an idempotent seeder syncs it to the database.

## Consequences
- New roles need no code changes; new permissions need one config line plus a seeder run.
- Deny by default: an unknown ability is rejected.
- The frontend hides UI using the `permissions` array from `/me`, but the API is what enforces access.
- Sensitive abilities get their own permission (e.g. `users.assign-roles`) so `users.update` is not an escalation path.