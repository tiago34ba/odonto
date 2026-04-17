<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class AbstractSaasCrudController extends Controller
{
    abstract protected function modelClass(): string;

    /**
     * @return array<string, mixed>
     */
    abstract protected function validationRules(bool $isUpdate = false): array;

    protected function query(Builder $query, Request $request): Builder
    {
        return $query;
    }

    public function index(Request $request): JsonResponse
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $this->modelClass();

        $query = $modelClass::query()->orderByDesc('id');
        $query = $this->query($query, $request);

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('codigo', 'like', "%{$search}%")
                    ->orWhere('descricao', 'like', "%{$search}%")
                    ->orWhere('owner_nome', 'like', "%{$search}%")
                    ->orWhere('owner_email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($submodulo = $request->input('submodulo')) {
            $query->where('submodulo', $submodulo);
        }

        $perPage = min(100, max(5, (int) $request->input('per_page', 10)));
        $paginated = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $paginated->items(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $this->modelClass();

        $validated = $request->validate($this->validationRules(false));
        /** @var Model $item */
        $item = $modelClass::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Registro criado com sucesso.',
            'data' => $item,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $this->modelClass();
        /** @var Model $item */
        $item = $modelClass::query()->findOrFail($id);

        $validated = $request->validate($this->validationRules(true));
        $item->fill($validated);
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Registro atualizado com sucesso.',
            'data' => $item,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $this->modelClass();
        $item = $modelClass::query()->findOrFail($id);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registro excluido com sucesso.',
        ]);
    }
}
