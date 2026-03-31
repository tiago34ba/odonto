<?php

namespace Tests\Feature\Api;

use App\Models\Employee;
use App\Models\Paciente;
use App\Models\Procedure;
use App\Models\Scheduling;
use App\Models\GrupoAcesso;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PortalPacienteFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-04-01 09:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_register_creates_user_and_patient_with_token(): void
    {
        $payload = [
            'name' => 'Paciente Portal',
            'email' => 'paciente.portal@example.com',
            'password' => 'SenhaSegura123',
            'password_confirmation' => 'SenhaSegura123',
            'telefone' => '11987654321',
            'cpf_cnpj' => '12345678901',
            'data_nascimento' => '1990-01-10',
        ];

        $response = $this->postJson('/api/portal/register', $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('user.email', 'paciente.portal@example.com')
            ->assertJsonStructure(['token', 'user' => ['id', 'paciente_id']]);

        $userId = (int) $response->json('user.id');
        $pacienteId = (int) $response->json('user.paciente_id');

        $this->assertDatabaseHas('users', [
            'id' => $userId,
            'email' => 'paciente.portal@example.com',
            'tipo' => 'paciente',
            'ativo' => 1,
        ]);

        $this->assertDatabaseHas('pacientes', [
            'id' => $pacienteId,
            'user_id' => $userId,
            'email' => 'paciente.portal@example.com',
            'name' => 'Paciente Portal',
        ]);
    }

    public function test_login_requires_patient_user_and_valid_password(): void
    {
        $patientUser = User::factory()->create([
            'email' => 'patient@example.com',
            'password' => Hash::make('SenhaSegura123'),
            'tipo' => 'paciente',
            'ativo' => true,
        ]);

        Paciente::factory()->create([
            'name' => $patientUser->name,
            'email' => $patientUser->email,
            'user_id' => $patientUser->id,
        ]);

        User::factory()->create([
            'email' => 'staff@example.com',
            'password' => Hash::make('SenhaSegura123'),
            'tipo' => 'staff',
            'ativo' => true,
        ]);

        $ok = $this->postJson('/api/portal/login', [
            'email' => 'patient@example.com',
            'password' => 'SenhaSegura123',
        ]);

        $ok->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['token', 'user' => ['id', 'paciente_id']]);

        $wrongType = $this->postJson('/api/portal/login', [
            'email' => 'staff@example.com',
            'password' => 'SenhaSegura123',
        ]);

        $wrongType->assertUnauthorized();

        $wrongPassword = $this->postJson('/api/portal/login', [
            'email' => 'patient@example.com',
            'password' => 'senha-invalida',
        ]);

        $wrongPassword->assertUnauthorized();
    }

    public function test_public_endpoints_list_dentists_procedures_and_available_slots(): void
    {
        $employee = Employee::create([
            'name' => 'Dra. Ana',
            'phone' => '11999998888',
            'email' => 'ana.dentista@example.com',
            'role' => 'dentist',
            'active' => true,
            'specialty' => 'Ortodontia',
            'cro' => 'CRO-12345',
        ]);

        $procedure = Procedure::create([
            'name' => 'Consulta Inicial',
            'value' => 120.50,
            'time' => '60',
            'description' => 'Consulta clínica',
            'category' => 'Clínico',
            'active' => true,
        ]);

        Scheduling::create([
            'patient_id' => Paciente::factory()->create()->id,
            'professional_id' => $employee->id,
            'procedure_id' => $procedure->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '09:00',
            'status' => 'scheduled',
            'duration' => 60,
        ]);

        $dentistas = $this->getJson('/api/portal/dentistas');
        $dentistas->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['id' => $employee->id, 'name' => 'Dra. Ana']);

        $procedimentos = $this->getJson('/api/portal/procedimentos');
        $procedimentos->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['id' => $procedure->id, 'name' => 'Consulta Inicial']);

        $horarios = $this->getJson('/api/portal/horarios-disponiveis?professional_id=' . $employee->id . '&date=' . now()->addDay()->toDateString() . '&procedure_id=' . $procedure->id);

        $horarios->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('professional_id', $employee->id);

        $slots = $horarios->json('available_slots');
        $this->assertIsArray($slots);
        $this->assertNotContains('09:00', $slots);
        $this->assertNotContains('09:30', $slots);
    }

    public function test_patient_can_schedule_list_profile_and_cancel_own_appointment(): void
    {
        $patientUser = User::factory()->create([
            'email' => 'flow@example.com',
            'password' => Hash::make('SenhaSegura123'),
            'tipo' => 'paciente',
            'ativo' => true,
        ]);

        $paciente = Paciente::factory()->create([
            'name' => $patientUser->name,
            'email' => $patientUser->email,
            'user_id' => $patientUser->id,
        ]);

        $employee = Employee::create([
            'name' => 'Dr. Bruno',
            'phone' => '11988887777',
            'email' => 'bruno.dentista@example.com',
            'role' => 'dentist',
            'active' => true,
            'specialty' => 'Clínica Geral',
            'cro' => 'CRO-54321',
        ]);

        $procedure = Procedure::create([
            'name' => 'Limpeza',
            'value' => 180.00,
            'time' => '45',
            'description' => 'Profilaxia',
            'category' => 'Preventivo',
            'active' => true,
        ]);

        Sanctum::actingAs($patientUser);

        $scheduleResponse = $this->postJson('/api/portal/agendar', [
            'professional_id' => $employee->id,
            'procedure_id' => $procedure->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '10:00',
            'obs' => 'Consulta de rotina',
        ]);

        $scheduleResponse->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.dentista', 'Dr. Bruno');

        $schedulingId = (int) $scheduleResponse->json('data.id');

        $this->assertDatabaseHas('schedulings', [
            'id' => $schedulingId,
            'patient_id' => $paciente->id,
            'professional_id' => $employee->id,
            'procedure_id' => $procedure->id,
            'status' => 'scheduled',
        ]);

        $meusAgendamentos = $this->getJson('/api/portal/meus-agendamentos');
        $meusAgendamentos->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('pagination.total', 1)
            ->assertJsonFragment(['id' => $schedulingId, 'dentista' => 'Dr. Bruno']);

        $perfil = $this->getJson('/api/portal/perfil');
        $perfil->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'flow@example.com')
            ->assertJsonPath('data.paciente.id', $paciente->id);

        $cancelar = $this->patchJson('/api/portal/agendamentos/' . $schedulingId . '/cancelar', [
            'motivo' => 'Imprevisto pessoal',
        ]);

        $cancelar->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('schedulings', [
            'id' => $schedulingId,
            'status' => 'canceled',
            'cancellation_reason' => 'Imprevisto pessoal',
        ]);
    }

    public function test_protected_endpoints_require_authentication_and_patient_role(): void
    {
        $employee = Employee::create([
            'name' => 'Dra. Paula',
            'phone' => '11977776666',
            'email' => 'paula.dentista@example.com',
            'role' => 'dentist',
            'active' => true,
        ]);

        $procedure = Procedure::create([
            'name' => 'Avaliação',
            'value' => 100,
            'time' => '30',
        ]);

        $unauthenticated = $this->postJson('/api/portal/agendar', [
            'professional_id' => $employee->id,
            'procedure_id' => $procedure->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '11:00',
        ]);

        $unauthenticated->assertUnauthorized();

        $staff = User::factory()->create([
            'tipo' => 'staff',
            'ativo' => true,
        ]);

        Sanctum::actingAs($staff);

        $forbidden = $this->postJson('/api/portal/agendar', [
            'professional_id' => $employee->id,
            'procedure_id' => $procedure->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '11:00',
        ]);

        $forbidden->assertForbidden();
    }

    public function test_portal_appointment_is_visible_for_secretary_admin_dentist_and_assistant(): void
    {
        $patientUser = User::factory()->create([
            'email' => 'visibilidade.portal@example.com',
            'password' => Hash::make('SenhaSegura123'),
            'tipo' => 'paciente',
            'ativo' => true,
        ]);

        $paciente = Paciente::factory()->create([
            'name' => $patientUser->name,
            'email' => $patientUser->email,
            'user_id' => $patientUser->id,
        ]);

        $employee = Employee::create([
            'name' => 'Dra. Visivel',
            'phone' => '11966665555',
            'email' => 'dra.visivel@example.com',
            'role' => 'dentist',
            'active' => true,
            'specialty' => 'Clínica Geral',
            'cro' => 'CRO-99999',
        ]);

        $procedure = Procedure::create([
            'name' => 'Consulta Portal Visibilidade',
            'value' => 150.00,
            'time' => '30',
            'description' => 'Consulta para teste de visibilidade',
            'category' => 'Clínico',
            'active' => true,
        ]);

        Sanctum::actingAs($patientUser);

        $scheduleResponse = $this->postJson('/api/portal/agendar', [
            'professional_id' => $employee->id,
            'procedure_id' => $procedure->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '14:00',
            'obs' => 'Agendamento criado pelo portal',
        ]);

        $scheduleResponse->assertCreated()->assertJsonPath('success', true);

        $schedulingId = (int) $scheduleResponse->json('data.id');

        $this->assertDatabaseHas('schedulings', [
            'id' => $schedulingId,
            'patient_id' => $paciente->id,
            'professional_id' => $employee->id,
            'status' => 'scheduled',
        ]);

        $profiles = [
            'secretaria.odonto' => 'Secretária',
            'admin' => 'Administrador',
            'dentista' => 'Dentista',
            'auxiliar de dentista' => 'Auxiliar Dentista',
        ];

        foreach ($profiles as $tipo => $grupoNome) {
            $internalUser = $this->createInternalUserWithSchedulingAccess($tipo, $grupoNome);
            Sanctum::actingAs($internalUser);

            $response = $this->getJson('/api/schedulings?per_page=100');

            $response
                ->assertOk()
                ->assertJsonPath('success', true);

            $rows = collect($response->json('data'));

            $this->assertTrue(
                $rows->contains(fn (array $row) => (int) ($row['id'] ?? 0) === $schedulingId),
                "O perfil {$tipo} deveria visualizar o agendamento criado pelo paciente."
            );
        }
    }

    private function createInternalUserWithSchedulingAccess(string $tipo, string $groupName): User
    {
        $grupo = GrupoAcesso::create([
            'nome' => $groupName,
            'descricao' => 'Grupo para teste de visibilidade da agenda',
            'cor' => '#2563EB',
            'permissoes' => ['SCHEDULINGS_VIEW', 'DASHBOARD_VIEW'],
            'ativo' => true,
        ]);

        return User::factory()->create([
            'tipo' => $tipo,
            'ativo' => true,
            'grupo_acesso_id' => $grupo->id,
        ]);
    }
}
