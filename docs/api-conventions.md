# API conventions

Base path: `/api/v1`. Auth: Sanctum session cookie (see ADR 0001).

## List endpoints
| Param | Example | Notes |
|---|---|---|
| `page`, `per_page` | `?page=2&per_page=25` | `per_page` capped at 100, default 15 |
| `sort` | `?sort=-created_at,name` | `-` = descending; only whitelisted columns, others ignored |
| `filter[field]` | `?filter[role]=admin` | whitelist in the model's `filterableFields()` |
| `search` | `?search=ann` | matches any column in `searchableFields()` |

Response: `{ "data": [...], "links": {...}, "meta": { "current_page", "per_page", "total", ... } }`

## Status codes
200 OK, 201 Created (POST), 204 No Content (DELETE), 401 not logged in,
403 missing permission, 404 not found, 422 validation (`{ message, errors: { field: [..] } }`).

## Adding a new resource
1. Model: `use Filterable, Sortable;` + `filterableFields()`, `searchableFields()`, `sortableFields()`
2. `make:request Store<Name>Request` / `Update<Name>Request`, and `make:resource <Name>Resource`
3. Controller extending `ResourceController` (set the config properties; override `*Record()` hooks if needed)
4. Route: `Route::apiResource('<names>', <Name>Controller::class)` inside the v1 group
5. Add `<names>.view|create|update|delete` to `config/rbac.php`, then `php artisan db:seed --class=RolePermissionSeeder`