<?php

namespace Tests\Feature\Api;

use App\Models\Employee;
use App\Models\Paciente;
use App\Models\Procedure;
use App\Models\Scheduling;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchedulingApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
        Carbon::setTestNow('2026-03-30 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_store_creates_scheduling_successfully(): void
    {
        [$paciente, $employee, $procedure] = $this->seedSchedulingDependencies();

        $response = $this->postJson('/api/schedulings', [
            'patient_id' => $paciente->id,
            'professional_id' => $employee->id,
            'procedure_id' => $procedure->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '09:00',
            'duration' => 60,
            'obs' => 'Primeira consulta',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'agendado');

        $this->assertDatabaseHas('schedulings', [
            'patient_id' => $paciente->id,
            'professional_id' => $employee->id,
            'procedure_id' => $procedure->id,
            'date' => now()->addDay()->startOfDay()->toDateTimeString(),
            'time' => '09:00',
            'status' => 'scheduled',
        ]);
    }

    public function test_store_blocks_duplicate_business_key(): void
    {
        [$paciente, $employee, $procedure] = $this->seedSchedulingDependencies();

        Scheduling::create([
            'patient_id' => $paciente->id,
            'professional_id' => $employee->id,
            'procedure_id' => $procedure->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '09:00',
            'status' => 'scheduled',
            'duration' => 60,
        ]);

        $response = $this->postJson('/api/schedulings', [
            'patient_id' => $paciente->id,
            'professional_id' => $employee->id,
            'procedure_id' => $procedure->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '09:00',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Dados duplicados: já existe um agendamento com paciente, dentista, procedimento, data e hora iguais.');
    }

    public function test_store_blocks_time_conflict_for_professional(): void
    {
        [$paciente, $employee, $procedure] = $this->seedSchedulingDependencies();
        $otherPaciente = Paciente::factory()->create();

        Scheduling::create([
            'patient_id' => $paciente->id,
            'professional_id' => $employee->id,
            'procedure_id' => $procedure->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '09:00',
            'duration' => 60,
            'status' => 'confirmed',
        ]);

        $response = $this->postJson('/api/schedulings', [
            'patient_id' => $otherPaciente->id,
            'professional_id' => $employee->id,
            'procedure_id' => $procedure->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '09:30',
            'duration' => 60,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Conflito de horário detectado');
    }

    public function test_update_blocks_duplicate_business_key(): void
    {
        [$paciente, $employee, $procedure] = $this->seedSchedulingDependencies();
        $anotherPaciente = Paciente::factory()->create();

        $target = Scheduling::create([
            'patient_id' => $paciente->id,
            'professional_id' => $employee->id,
            'procedure_id' => $procedure->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '11:00',
            'status' => 'scheduled',
        ]);

        Scheduling::create([
            'patient_id' => $anotherPaciente->id,
            'professional_id' => $employee->id,
            'procedure_id' => $procedure->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '10:00',
            'status' => 'scheduled',
        ]);

        $response = $this->patchJson('/api/schedulings/' . $target->id, [
            'patient_id' => $anotherPaciente->id,
            'time' => '10:00',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Dados duplicados: já existe outro agendamento com paciente, dentista, procedimento, data e hora iguais.');
    }

    public function test_show_returns_404_when_scheduling_does_not_exist(): void
    {
        $this->getJson('/api/schedulings/999999')
            ->assertStatus(404)
            ->assertJsonPath('message', 'Agendamento não encontrado');
    }

    public function test_show_returns_formatted_payload_when_scheduling_exists(): void
    {
        $scheduling = $this->createScheduling(['status' => 'confirmed']);

        $this->getJson('/api/schedulings/' . $scheduling->id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $scheduling->id)
            ->assertJsonPath('data.status', 'confirmado');
    }

    public function test_update_blocks_time_conflict_when_rescheduling(): void
    {
        [$paciente, $employee, $procedure] = $this->seedSchedulingDependencies();
        $otherPaciente = Paciente::factory()->create();

        Scheduling::create([
            'patient_id' => $paciente->id,
            'professional_id' => $employee->id,
            'procedure_id' => $procedure->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '09:00',
            'duration' => 90,
            'status' => 'confirmed',
        ]);

        $target = Scheduling::create([
            'patient_id' => $otherPaciente->id,
            'professional_id' => $employee->id,
            'procedure_id' => $procedure->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '13:00',
            'duration' => 60,
            'status' => 'scheduled',
        ]);

        $response = $this->patchJson('/api/schedulings/' . $target->id, [
            'time' => '09:30',
            'duration' => 60,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Conflito de horário detectado');
    }

    public function test_confirm_cancel_and_complete_apply_status_transitions(): void
    {
        $scheduling = $this->createScheduling();

        $this->patchJson('/api/schedulings/' . $scheduling->id . '/confirm')
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmado');

        $this->patchJson('/api/schedulings/' . $scheduling->id . '/cancel', ['reason' => 'Paciente remarcou'])
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelado');

        $scheduling = $this->createScheduling(['time' => '15:00']);

        $this->patchJson('/api/schedulings/' . $scheduling->id . '/complete')
            ->assertOk()
            ->assertJsonPath('data.status', 'concluido');
    }

    public function test_available_slots_respects_existing_conflicts(): void
    {
        [, $employee] = $this->seedSchedulingDependencies();
        $this->createScheduling([
            'professional_id' => $employee->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '09:00',
            'duration' => 60,
            'status' => 'confirmed',
        ]);

        $response = $this->getJson('/api/schedulings/available-slots?professional_id=' . $employee->id . '&date=' . now()->addDay()->toDateString() . '&duration=60');

        $response->assertOk();
        $this->assertNotContains('09:00', $response->json('available_slots'));
    }

    public function test_today_and_this_week_endpoints_return_success(): void
    {
        $this->createScheduling([
            'date' => now()->toDateString(),
            'time' => '10:00',
        ]);

        $this->createScheduling([
            'date' => now()->addDays(2)->toDateString(),
            'time' => '14:00',
        ]);

        $this->getJson('/api/schedulings/today')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->getJson('/api/schedulings/this-week')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_index_uses_safe_fallback_for_invalid_sort_input(): void
    {
        $this->createScheduling();

        $response = $this->getJson('/api/schedulings?sort_by=1 desc;drop table users&sort_order=sideways');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'pagination']);
    }

    public function test_destroy_removes_scheduling(): void
    {
        $scheduling = $this->createScheduling();

        $this->deleteJson('/api/schedulings/' . $scheduling->id)
            ->assertOk()
            ->assertJsonPath('message', 'Agendamento deletado com sucesso');

        $this->assertDatabaseMissing('schedulings', ['id' => $scheduling->id]);
    }

    private function createScheduling(array $overrides = []): Scheduling
    {
        [$paciente, $employee, $procedure] = $this->seedSchedulingDependencies();

        return Scheduling::create(array_merge([
            'patient_id' => $paciente->id,
            'professional_id' => $employee->id,
            'procedure_id' => $procedure->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '09:00',
            'duration' => 60,
            'status' => 'scheduled',
            'return' => false,
        ], $overrides));
    }

    private function seedSchedulingDependencies(): array
    {
        $paciente = Paciente::first() ?? Paciente::factory()->create();

        $employee = Employee::first() ?? Employee::create([
            'name' => 'Dr. Teste',
            'phone' => '11999999999',
            'email' => 'dr.teste.' . uniqid() . '@mail.com',
            'role' => 'dentist',
        ]);

        $procedure = Procedure::first() ?? Procedure::create([
            'name' => 'Limpeza',
            'value' => 150,
            'time' => 60,
            'accepts_agreement' => true,
        ]);

        return [$paciente, $employee, $procedure];
    }
}
