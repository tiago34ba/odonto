<?php

namespace App\Http\Controllers\Helpdesk;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class AbstractHelpdeskCrudController extends Controller
{
    abstract protected function modelClass(): string;

    /** @return array<string, mixed> */
    abstract protected function validationRules(bool $isUpdate = false, ?int $id = null): array;

    /** @return array<int, string> */
    protected function searchableFields(): array
    {
        return ['nome', 'codigo', 'descricao'];
    }

    /** @return array<int, string> */
    protected function filterableFields(): array
    {
        return ['status'];
    }

    protected function buildQuery(Request $request): Builder
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $this->modelClass();
        $query = $modelClass::query()->orderByDesc('id');

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                foreach ($this->searchableFields() as $index => $field) {
                    if ($index === 0) {
                        $builder->where($field, 'like', "%{$search}%");
                    } else {
                        $builder->orWhere($field, 'like', "%{$search}%");
                    }
                }
            });
        }

        foreach ($this->filterableFields() as $field) {
            $value = $request->input($field);
            if ($value !== null && $value !== '') {
                $query->where($field, $value);
            }
        }

        return $query;
    }

    public function index(Request $request): JsonResponse|StreamedResponse
    {
        $query = $this->buildQuery($request);
        $format = strtolower((string) $request->input('format', 'json'));

        if (in_array($format, ['excel', 'xml', 'html', 'print'], true)) {
            return $this->export($query->get(), $format);
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
        $item = $modelClass::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Registro criado com sucesso.',
            'data' => $item,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $this->modelClass();
        $item = $modelClass::query()->findOrFail($id);

        return response()->json(['success' => true, 'data' => $item]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $this->modelClass();
        $item = $modelClass::query()->findOrFail($id);
        $validated = $request->validate($this->validationRules(true, $id));
        $item->fill($validated)->save();

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

        return response()->json(['success' => true, 'message' => 'Registro excluido com sucesso.']);
    }

    protected function export($items, string $format): StreamedResponse
    {
        $rows = collect($items)->map(function ($item) {
            return is_object($item) && method_exists($item, 'toArray') ? $item->toArray() : (array) $item;
        })->values();

        if ($format === 'xml') {
            $xml = new \SimpleXMLElement('<items/>');
            foreach ($rows as $row) {
                $node = $xml->addChild('item');
                foreach ($row as $key => $value) {
                    $node->addChild((string) $key, htmlspecialchars(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value));
                }
            }

            return response()->streamDownload(static function () use ($xml): void {
                echo $xml->asXML();
            }, 'helpdesk-export.xml', ['Content-Type' => 'application/xml']);
        }

        if ($format === 'html' || $format === 'print') {
            $headers = array_keys($rows->first() ?? []);
            $html = '<html><head><meta charset="utf-8"><title>Export</title></head><body><table border="1" cellspacing="0" cellpadding="6"><thead><tr>';
            foreach ($headers as $header) {
                $html .= '<th>' . e((string) $header) . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            foreach ($rows as $row) {
                $html .= '<tr>';
                foreach ($headers as $header) {
                    $value = $row[$header] ?? '';
                    $html .= '<td>' . e(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table></body></html>';

            return response()->streamDownload(static function () use ($html): void {
                echo $html;
            }, $format === 'print' ? 'helpdesk-print.html' : 'helpdesk-export.html', ['Content-Type' => 'text/html']);
        }

        return response()->streamDownload(static function () use ($rows): void {
            $output = fopen('php://output', 'wb');
            $headers = array_keys($rows->first() ?? []);
            fputcsv($output, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($output, array_map(static fn ($value) => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value, $row), ';');
            }
            fclose($output);
        }, 'helpdesk-export.csv', ['Content-Type' => 'text/csv']);
    }
}
