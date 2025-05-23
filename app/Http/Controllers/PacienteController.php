<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Paciente;
use Illuminate\Support\Facades\Http;

class PacienteController extends Controller
{
    /**
     * Display a listing of the patients.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $patients = Paciente::query()
            ->when($request->filled('name'), fn ($query, $name) => $query->where('name', 'like', "%{$name}%"))
            ->when($request->filled('convenio'), fn ($query, $convenio) => $query->where('convenio', $convenio))
            ->orderBy('name')
            ->paginate($perPage);

    return response()->json($patients);
    }

    /**
     * Store a newly created patient in storage.
     *
     * @param StorePatientRequest $request
     * @return JsonResponse
     */
    public function store(StorePatientRequest $request): JsonResponse
    {
        $patient = Paciente::create($request->validated());
        return response()->json($patient, Response::HTTP_CREATED);
    }


    /**
     * Display the specified patient.
     *
     * @param Patient $patient
     * @return JsonResponse
     */
    public function show(Paciente $patient): JsonResponse
    {
        return response()->json($patient);
    }

    /**
     * Update the specified patient in storage.
     *
     * @param UpdatePatientRequest $request
     * @param Patient $patient
     * @return JsonResponse
     */
    public function update(UpdatePatientRequest $request, Paciente $patient): JsonResponse
    {
        $patient->update($request->validated());

        return response()->json($patient);
    }

    /**
     * Remove the specified patient from storage.
     *
     * @param Patient $patient
     * @return JsonResponse
     */
    public function destroy(Paciente $patient): JsonResponse
    {
        $patient->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get a list of available convenios.
     *
     * @return JsonResponse
     */
    public function convenios(): JsonResponse
    {
        return response()->json([
            'convenios' => [
                'Amil',
                'ASSIM Saúde',
                'Athena Saúde',
                'Bradesco Saúde',
                'Care Plus',
                'FSFX',
                'Hapvida',
                'MedSênior',
                'OdontoPrev',
                'Omint',
                'Particular',
                'Porto Seguro Saúde',
                'Prevent Senior',
                'São Cristovão',
                'Sul América Saúde',
                'Trasmontano',
                'Unimed Belo Horizonte',
                'Unimed Campinas',
                'Unimed Campo Grande',
                'Unimed Cuiabá',
                'Unimed Curitiba',
                'Unimed de Belém',
                'Unimed de Blumenau',
                'Unimed de Ribeirão Preto',
                'Unimed de Santos',
                'Unimed do Estado de Santa Catarina',
                'Unimed FESP',
                'Unimed Fortaleza',
                'Unimed Goiânia',
                'Unimed Grande Florianópolis',
                'Unimed João Pessoa',
                'Unimed Leste Fluminense',
                'Unimed Londrina',
                'Unimed Maceió',
                'Unimed Natal',
                'Unimed Nacional',
                'Unimed Nordeste RS',
                'Unimed Paraná',
                'Unimed Piracicaba',
                'Unimed Porto Alegre',
                'Unimed Recife',
                'Unimed Regional Maringá',
                'Unimed Saúde',
                'Unimed São José dos Campos',
                'Unimed São José do Rio Preto',
                'Unimed Sergipe',
                'Unimed Sorocaba',
                'Unimed Teresina',
                'Unimed Uberlândia',
                'Unimed Vitória',
                'Vision Med',
            ],
        ]);
    }

    /**
     * Get a list of available estados.
     *
     * @return JsonResponse
     */
    public function estados(): JsonResponse
    {
        return response()->json([
            'estados' => [
                'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
                'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN',
                'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO',
            ],
        ]);
    }

    /**
     * Get a list of available sexos.
     *
     * @return JsonResponse
     */
    public function sexos(): JsonResponse
    {
        return response()->json([
            'sexos' => [
                'Masculino',
                'Feminino',
                'Outro',
            ],
        ]);
    }

    /**
     * Get a list of available estados civis.
     *
     * @return JsonResponse
     */
    public function estadosCivis(): JsonResponse
    {
        return response()->json([
            'estados_civis' => [
                'Solteiro(a)',
                'Casado(a)',
                'Divorciado(a)',
                'Viúvo(a)',
                'Outro',
            ],
        ]);
    }

    /**
     * Get a list of available tipos sanguíneos.
     *
     * @return JsonResponse
     */
    public function tiposSanguineos(): JsonResponse
    {
        return response()->json([
            'tipos_sanguineos' => [
                'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-',
            ],
        ]);
    }

}
