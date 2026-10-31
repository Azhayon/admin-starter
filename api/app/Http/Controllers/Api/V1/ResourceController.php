<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Generic CRUD. A concrete controller only sets the config properties below
 * and overrides the *Record() hooks when it needs custom behaviour.
 */
abstract class ResourceController extends Controller
{
    protected string $model;         // Eloquent model (must use Filterable + Sortable)

    protected string $resource;      // API Resource class (response shape)

    protected string $permission;    // permission prefix: "users" => users.view / .create / .update / .delete

    protected string $storeRequest;  // FormRequest class for POST

    protected string $updateRequest; // FormRequest class for PATCH/PUT

    protected array $with = [];      // relations to eager load (avoids N+1 queries)

    protected array $withCount = []; // relation counts to include

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorizeAction('view');

        $search = $request->query('search');
        $sort = $request->query('sort');
        $perPage = max(1, min($request->integer('per_page', 15), 100)); // cap it, or ?per_page=1000000 is a DoS

        $records = $this->query()
            ->filter($request->array('filter'), is_string($search) ? trim($search) : null)
            ->sort(is_string($sort) ? $sort : null)
            ->paginate($perPage);

        return $this->resource::collection($records);
    }

    public function show(string $id): JsonResource
    {
        $this->authorizeAction('view');

        return $this->resource::make($this->find($id));
    }

    public function store(): JsonResponse
    {
        $this->authorizeAction('create');

        // Resolving a FormRequest from the container runs its authorize() + rules() automatically.
        $data = app($this->storeRequest)->validated();
        $record = DB::transaction(fn () => $this->createRecord($data));

        return $this->present($record)->response()->setStatusCode(201);
    }

    public function update(string $id): JsonResource
    {
        $this->authorizeAction('update');

        $record = $this->find($id);
        $data = app($this->updateRequest)->validated();
        $record = DB::transaction(fn () => $this->updateRecord($record, $data));

        return $this->present($record);
    }

    public function destroy(string $id): Response
    {
        $this->authorizeAction('delete');

        $record = $this->find($id);
        DB::transaction(fn () => $this->deleteRecord($record));

        return response()->noContent();
    }

    // ---- Hooks: override these in a concrete controller ----------------------

    protected function createRecord(array $data): Model
    {
        return $this->model::create($data);
    }

    protected function updateRecord(Model $record, array $data): Model
    {
        $record->update($data);

        return $record;
    }

    protected function deleteRecord(Model $record): void
    {
        $record->delete();
    }

    // ---- Helpers --------------------------------------------------------------

    protected function query(): Builder
    {
        return $this->model::query()->with($this->with)->withCount($this->withCount);
    }

    protected function find(string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    protected function present(Model $record): JsonResource
    {
        $record->unsetRelations()->load($this->with)->loadCount($this->withCount);

        return $this->resource::make($record);
    }

    protected function authorizeAction(string $action): void
    {
        // Goes through Gate::before from Step 3, so "users.view" is checked against the RBAC tables.
        // No matching permission => AuthorizationException => 403 JSON.
        Gate::authorize("{$this->permission}.{$action}");
    }
}
