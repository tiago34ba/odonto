<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HorarioFuncionamentoController extends Controller
{
    private string $storageFile = 'horarios_funcionamento.json';

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->readHorarios(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatePayload($request);
        $horarios = $this->readHorarios();

        $existingIndex = collect($horarios)->search(fn ($item) => ($item['dia'] ?? null) === $validated['dia']);

        if ($existingIndex !== false) {
            $id = $horarios[$existingIndex]['id'];
            $horarios[$existingIndex] = array_merge($validated, ['id' => $id]);
            $this->writeHorarios($horarios);

            return response()->json([
                'message' => 'Horário atualizado com sucesso.',
                'data' => $horarios[$existingIndex],
            ]);
        }

        $nextId = empty($horarios) ? 1 : (max(array_column($horarios, 'id')) + 1);
        $item = array_merge($validated, ['id' => $nextId]);
        $horarios[] = $item;
        $this->writeHorarios($horarios);

        return response()->json([
            'message' => 'Horário cadastrado com sucesso.',
            'data' => $item,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $this->validatePayload($request);
        $horarios = $this->readHorarios();

        $index = collect($horarios)->search(fn ($item) => (int) ($item['id'] ?? 0) === $id);
        if ($index === false) {
            return response()->json(['message' => 'Horário não encontrado.'], 404);
        }

        $horarios[$index] = array_merge($validated, ['id' => $id]);
        $this->writeHorarios($horarios);

        return response()->json([
            'message' => 'Horário atualizado com sucesso.',
            'data' => $horarios[$index],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $horarios = $this->readHorarios();
        $filtered = array_values(array_filter(
            $horarios,
            fn ($item) => (int) ($item['id'] ?? 0) !== $id
        ));

        if (count($filtered) === count($horarios)) {
            return response()->json(['message' => 'Horário não encontrado.'], 404);
        }

        $this->writeHorarios($filtered);
        return response()->json([], 204);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'dia' => 'required|string|max:40',
            'jornadaInicio' => 'required|string|max:5',
            'jornadaFim' => 'required|string|max:5',
            'almocoInicio' => 'nullable|string|max:5',
            'almocoFim' => 'nullable|string|max:5',
            'ativo' => 'required|boolean',
        ]);
    }

    private function readHorarios(): array
    {
        if (!Storage::exists($this->storageFile)) {
            return [];
        }

        $decoded = json_decode(Storage::get($this->storageFile), true);
        return is_array($decoded) ? $decoded : [];
    }

    private function writeHorarios(array $horarios): void
    {
        Storage::put($this->storageFile, json_encode($horarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
