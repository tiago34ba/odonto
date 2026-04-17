<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDentistaRequest;
use App\Models\Dentista;
use App\Services\PortalDentistSyncService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DentistaController extends Controller
{
    public function __construct(private readonly PortalDentistSyncService $portalDentistSyncService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);

        $dentistas = Dentista::query()
            ->when($request->filled('name'), fn ($q) => $q->byName((string) $request->input('name')))
            ->when($request->filled('especialidade'), fn ($q) => $q->byEspecialidade((string) $request->input('especialidade')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN)))
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json([
            'data' => $dentistas->items(),
            'pagination' => [
                'current_page' => $dentistas->currentPage(),
                'last_page' => $dentistas->lastPage(),
                'per_page' => $dentistas->perPage(),
                'total' => $dentistas->total(),
                'from' => $dentistas->firstItem(),
                'to' => $dentistas->lastItem(),
            ],
        ]);
    }

    public function store(StoreDentistaRequest $request): JsonResponse
    {
        try {
            $dentista = Dentista::create($request->validated());
            $this->portalDentistSyncService->syncDentista($dentista);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Ja existe um dentista com os mesmos dados unicos (CRO, e-mail ou CPF).',
                ], 422);
            }

            throw $e;
        }

        return response()->json([
            'message' => 'Dentista cadastrado com sucesso.',
            'data' => $dentista,
        ], Response::HTTP_CREATED);
    }

    public function show(Dentista $dentista): JsonResponse
    {
        return response()->json($dentista);
    }

    public function update(StoreDentistaRequest $request, Dentista $dentista): JsonResponse
    {
        $originalEmail = $dentista->email;
        $originalCro = $dentista->cro;

        try {
            $dentista->update($request->validated());
            $this->portalDentistSyncService->syncDentista($dentista->fresh(), $originalEmail, $originalCro);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Ja existe um dentista com os mesmos dados unicos (CRO, e-mail ou CPF).',
                ], 422);
            }

            throw $e;
        }

        return response()->json([
            'message' => 'Dentista atualizado com sucesso.',
            'data' => $dentista->fresh(),
        ]);
    }

    public function destroy(Dentista $dentista): JsonResponse
    {
        $this->portalDentistSyncService->deactivateDentista($dentista);
        $dentista->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function especialidades(): JsonResponse
    {
        return response()->json([
            'especialidades' => [
                'Clinico Geral',
                'Ortodontia',
                'Endodontia',
                'Periodontia',
                'Implantodontia',
                'Odontopediatria',
                'Protesista',
                'Cirurgia Bucomaxilofacial',
                'Dentistica',
                'Radiologia Odontologica',
            ],
        ]);
    }

    public function estados(): JsonResponse
    {
        return response()->json([
            'estados' => [
                'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO',
                'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI',
                'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO',
            ],
        ]);
    }
}
