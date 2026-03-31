<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Paciente;
use App\Models\Odontograma;

class OdontogramaController extends Controller
{
    /**
     * Obter odontograma do paciente
     */
    public function show($pacienteId): JsonResponse
    {
        $paciente = Paciente::find($pacienteId);
        
        if (!$paciente) {
            return response()->json(['message' => 'Paciente não encontrado'], 404);
        }

        $odontograma = Odontograma::where('paciente_id', $pacienteId)->first();
        
        if (!$odontograma) {
            // Criar odontograma vazio para o paciente
            $odontograma = $this->createEmptyOdontograma($pacienteId);
        }

        return response()->json($odontograma);
    }

    /**
     * Atualizar odontograma do paciente
     */
    public function update(Request $request, $pacienteId): JsonResponse
    {
        $paciente = Paciente::find($pacienteId);
        
        if (!$paciente) {
            return response()->json(['message' => 'Paciente não encontrado'], 404);
        }

        $validated = $request->validate([
            'dentes' => 'required|array',
            'dentes.*.numero' => 'required|integer|between:11,85',
            'dentes.*.faces' => 'required|array',
            'dentes.*.faces.oclusal' => 'nullable|string',
            'dentes.*.faces.mesial' => 'nullable|string',
            'dentes.*.faces.distal' => 'nullable|string',
            'dentes.*.faces.vestibular' => 'nullable|string',
            'dentes.*.faces.lingual' => 'nullable|string',
            'dentes.*.status' => 'required|string|in:normal,cariado,restaurado,extraido,ausente,implante,protese',
            'dentes.*.observacoes' => 'nullable|string|max:500',
            'observacoes_gerais' => 'nullable|string|max:1000',
        ]);

        $odontograma = Odontograma::updateOrCreate(
            ['paciente_id' => $pacienteId],
            [
                'dentes' => json_encode($validated['dentes']),
                'observacoes_gerais' => $validated['observacoes_gerais'] ?? null,
                'updated_at' => now()
            ]
        );

        return response()->json([
            'message' => 'Odontograma atualizado com sucesso',
            'odontograma' => $odontograma
        ]);
    }

    /**
     * Obter legendas e códigos do odontograma
     */
    public function getLegendas(): JsonResponse
    {
        $legendas = [
            'status' => [
                'normal' => 'Normal',
                'cariado' => 'Cariado',
                'restaurado' => 'Restaurado',
                'extraido' => 'Extraído',
                'ausente' => 'Ausente',
                'implante' => 'Implante',
                'protese' => 'Prótese',
            ],
            'procedimentos' => [
                'amf' => 'Amalgama',
                'res' => 'Resina',
                'ino' => 'Inlay/Onlay',
                'cor' => 'Coroa',
                'pon' => 'Ponte',
                'ppr' => 'Prótese Parcial Removível',
                'ppt' => 'Prótese Total',
                'imp' => 'Implante',
                'ext' => 'Extração',
                'car' => 'Cárie',
                'fra' => 'Fratura',
                'atr' => 'Atrição',
                'abr' => 'Abrasão',
            ],
            'faces' => [
                'oclusal' => 'Oclusal/Incisal',
                'mesial' => 'Mesial',
                'distal' => 'Distal',
                'vestibular' => 'Vestibular',
                'lingual' => 'Lingual/Palatina',
            ]
        ];

        return response()->json($legendas);
    }

    /**
     * Obter template de dentes
     */
    public function getDentesTemplate(): JsonResponse
    {
        $dentes = [
            // Dentes permanentes superiores (direita)
            18, 17, 16, 15, 14, 13, 12, 11,
            // Dentes permanentes superiores (esquerda)
            21, 22, 23, 24, 25, 26, 27, 28,
            // Dentes permanentes inferiores (direita)
            48, 47, 46, 45, 44, 43, 42, 41,
            // Dentes permanentes inferiores (esquerda)
            31, 32, 33, 34, 35, 36, 37, 38,
            // Dentes decíduos superiores (direita)
            55, 54, 53, 52, 51,
            // Dentes decíduos superiores (esquerda)
            61, 62, 63, 64, 65,
            // Dentes decíduos inferiores (direita)
            85, 84, 83, 82, 81,
            // Dentes decíduos inferiores (esquerda)
            71, 72, 73, 74, 75,
        ];

        $template = [];
        foreach ($dentes as $numero) {
            $template[] = [
                'numero' => $numero,
                'faces' => [
                    'oclusal' => null,
                    'mesial' => null,
                    'distal' => null,
                    'vestibular' => null,
                    'lingual' => null,
                ],
                'status' => 'normal',
                'observacoes' => null,
            ];
        }

        return response()->json(['dentes' => $template]);
    }

    /**
     * Exportar odontograma para PDF
     */
    public function exportPdf($pacienteId): JsonResponse
    {
        $paciente = Paciente::find($pacienteId);
        
        if (!$paciente) {
            return response()->json(['message' => 'Paciente não encontrado'], 404);
        }

        $odontograma = Odontograma::where('paciente_id', $pacienteId)->first();
        
        if (!$odontograma) {
            return response()->json(['message' => 'Odontograma não encontrado'], 404);
        }

        // Aqui você implementaria a geração do PDF
        // Por enquanto, retornamos apenas uma mensagem
        return response()->json([
            'message' => 'Funcionalidade de exportação em desenvolvimento',
            'paciente' => $paciente->name,
            'data' => now()->format('d/m/Y H:i')
        ]);
    }

    /**
     * Histórico de alterações do odontograma
     */
    public function historico($pacienteId): JsonResponse
    {
        $paciente = Paciente::find($pacienteId);
        
        if (!$paciente) {
            return response()->json(['message' => 'Paciente não encontrado'], 404);
        }

        // Buscar histórico de alterações (implementar auditoria)
        $historico = Odontograma::where('paciente_id', $pacienteId)
                                ->orderBy('updated_at', 'desc')
                                ->get();

        return response()->json(['historico' => $historico]);
    }

    /**
     * Métodos auxiliares privados
     */

    /**
     * Criar odontograma vazio para paciente
     */
    private function createEmptyOdontograma($pacienteId)
    {
        $template = $this->getDentesTemplate()->getData();
        
        return Odontograma::create([
            'paciente_id' => $pacienteId,
            'dentes' => json_encode($template->dentes),
            'observacoes_gerais' => null,
        ]);
    }
}
