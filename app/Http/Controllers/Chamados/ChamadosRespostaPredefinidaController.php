<?php

namespace App\Http\Controllers\Chamados;

use App\Http\Controllers\Helpdesk\AbstractHelpdeskCrudController;
use App\Models\Chamados\ChamadosRespostaPredefinida;

class ChamadosRespostaPredefinidaController extends AbstractHelpdeskCrudController
{
    protected function modelClass(): string
    {
        return ChamadosRespostaPredefinida::class;
    }

    protected function searchableFields(): array
    {
        return ['titulo', 'codigo', 'conteudo', 'atalho_teclado'];
    }

    protected function filterableFields(): array
    {
        return ['status', 'visivel_para', 'idioma'];
    }

    public function store(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        // Increment uso_count is handled on ticket reply — not on create.
        return parent::store($request);
    }

    protected function validationRules(bool $isUpdate = false, ?int $id = null): array
    {
        $r = $isUpdate ? 'sometimes' : 'required';
        return [
            'titulo'          => [$r, 'string', 'max:180'],
            'codigo'          => [$r, 'string', 'max:120', 'unique:chamados_respostas_predefinidas,codigo' . ($id ? ',' . $id : '')],
            'categoria_id'    => ['nullable', 'integer'],
            'conteudo'        => [$r, 'string'],
            'tags'            => ['nullable', 'array'],
            'visivel_para'    => ['nullable', 'string', 'in:todos,tecnicos,gestores,clientes'],
            'atalho_teclado'  => ['nullable', 'string', 'max:60'],
            'idioma'          => ['nullable', 'string', 'max:10'],
            'incluir_anexo'   => ['nullable', 'boolean'],
            'status'          => ['nullable', 'string', 'in:ativo,inativo'],
        ];
    }
}
