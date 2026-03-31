<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFuncionarioRequest;
use App\Models\Funcionario;
use App\Models\Cargo;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FuncionarioController extends Controller
{
    /**
     * Lista todos os funcionários com paginação.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);

        $funcionarios = Funcionario::with('cargo')
            ->when($request->filled('name'), fn ($q) => $q->byName($request->input('name')))
            ->when($request->filled('cargo_id'), fn ($q) => $q->byCargo($request->input('cargo_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN)))
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json([
            'data' => $funcionarios->items(),
            'pagination' => [
                'current_page' => $funcionarios->currentPage(),
                'last_page'    => $funcionarios->lastPage(),
                'per_page'     => $funcionarios->perPage(),
                'total'        => $funcionarios->total(),
                'from'         => $funcionarios->firstItem(),
                'to'           => $funcionarios->lastItem(),
            ],
        ]);
    }

    /**
     * Cadastra um novo funcionário.
     */
    public function store(StoreFuncionarioRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $pick = static function (array $data, array $keys, $default = null) {
            foreach ($keys as $key) {
                if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') {
                    return $data[$key];
                }
            }
            return $default;
        };

        $mappedData = [
            'name'         => $pick($validated, ['name']),
            'telefone'     => $pick($validated, ['telefone', 'phone']),
            'email'        => $pick($validated, ['email']),
            'cargo_id'     => $pick($validated, ['cargo_id', 'cargoId']),
            'data_cadastro' => $pick($validated, ['data_cadastro', 'dataCadastro']),
            'foto'         => $pick($validated, ['foto']),
            'cep'          => $pick($validated, ['cep']),
            'rua'          => $pick($validated, ['rua']),
            'numero'       => $pick($validated, ['numero']),
            'complemento'  => $pick($validated, ['complemento']),
            'bairro'       => $pick($validated, ['bairro']),
            'cidade'       => $pick($validated, ['cidade']),
            'estado'       => $pick($validated, ['estado']),
            'cro'          => $pick($validated, ['cro']),
            'intervalo'    => $pick($validated, ['intervalo']),
            'comissao'     => $pick($validated, ['comissao']),
            'chave_pix'    => $pick($validated, ['chave_pix', 'chavePix']),
            'status'       => array_key_exists('status', $validated) ? $validated['status'] : true,
        ];

        try {
            $funcionario = Funcionario::create($mappedData);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                $rawMessage = $e->getMessage();

                if (str_contains($rawMessage, 'funcionarios_email_unique')) {
                    return response()->json([
                        'message' => 'E-mail já cadastrado. Use outro e-mail ou edite o funcionário existente.',
                        'errors'  => ['email' => ['E-mail já cadastrado.']],
                    ], 422);
                }

                return response()->json([
                    'message' => 'Já existe um funcionário com os mesmos dados únicos. Revise o e-mail.',
                ], 422);
            }
            throw $e;
        }

        return response()->json([
            'message' => 'Funcionário cadastrado com sucesso',
            'data'    => $funcionario->load('cargo'),
        ], Response::HTTP_CREATED);
    }

    /**
     * Exibe um funcionário específico.
     */
    public function show(Funcionario $funcionario): JsonResponse
    {
        return response()->json($funcionario->load('cargo'));
    }

    /**
     * Atualiza um funcionário.
     */
    public function update(StoreFuncionarioRequest $request, Funcionario $funcionario): JsonResponse
    {
        $validated = $request->validated();

        $pick = static function (array $data, array $keys, $sentinel = '__ABSENT__') {
            foreach ($keys as $key) {
                if (array_key_exists($key, $data)) {
                    return $data[$key];
                }
            }
            return $sentinel;
        };

        $updateData = [];
        $fieldsMap = [
            'name'         => ['name'],
            'telefone'     => ['telefone', 'phone'],
            'email'        => ['email'],
            'cargo_id'     => ['cargo_id', 'cargoId'],
            'data_cadastro' => ['data_cadastro', 'dataCadastro'],
            'foto'         => ['foto'],
            'cep'          => ['cep'],
            'rua'          => ['rua'],
            'numero'       => ['numero'],
            'complemento'  => ['complemento'],
            'bairro'       => ['bairro'],
            'cidade'       => ['cidade'],
            'estado'       => ['estado'],
            'cro'          => ['cro'],
            'intervalo'    => ['intervalo'],
            'comissao'     => ['comissao'],
            'chave_pix'    => ['chave_pix', 'chavePix'],
            'status'       => ['status'],
        ];

        foreach ($fieldsMap as $column => $keys) {
            $value = $pick($validated, $keys);
            if ($value !== '__ABSENT__') {
                $updateData[$column] = $value;
            }
        }

        try {
            $funcionario->update($updateData);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'funcionarios_email_unique')) {
                return response()->json([
                    'message' => 'E-mail já cadastrado por outro funcionário.',
                    'errors'  => ['email' => ['E-mail já cadastrado.']],
                ], 422);
            }
            throw $e;
        }

        return response()->json([
            'message' => 'Funcionário atualizado com sucesso',
            'data'    => $funcionario->fresh()->load('cargo'),
        ]);
    }

    /**
     * Remove um funcionário.
     */
    public function destroy(Funcionario $funcionario): JsonResponse
    {
        $funcionario->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Lista cargos disponíveis para o select do formulário.
     */
    public function cargos(): JsonResponse
    {
        $defaultCargos = [
            'Dentista Clínico Geral',
            'Ortodontista',
            'Endodontista',
            'Periodontista',
            'Implantodontista',
            'Cirurgião Bucomaxilofacial',
            'Odontopediatra',
            'Prótese Dentária',
            'ASB (Auxiliar de Saúde Bucal)',
            'TSB (Técnico em Saúde Bucal)',
            'Recepcionista',
            'Gerente de Clínica',
            'Administrativo/Financeiro',
            'Auxiliar de Limeza',
            'Supervissor',
        ];

        foreach ($defaultCargos as $nomeCargo) {
            Cargo::firstOrCreate(
                ['nome' => $nomeCargo],
                [
                    'descricao' => null,
                    'nivel_acesso' => 1,
                    'ativo' => true,
                ]
            );
        }

        $cargos = Cargo::where('ativo', true)
            ->orderBy('nome')
            ->get(['id', 'nome', 'nivel_acesso']);

        return response()->json([
            'cargos' => $cargos,
            'data' => $cargos->map(fn ($cargo) => [
                'id' => $cargo->id,
                'name' => $cargo->nome,
            ])->values(),
        ]);
    }

    /**
     * Lista estados brasileiros para o select.
     */
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
