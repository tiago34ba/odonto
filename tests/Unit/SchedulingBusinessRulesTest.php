<?php

namespace Tests\Unit;

use App\Models\Scheduling;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchedulingBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_formatted_returns_humanized_label(): void
    {
        $scheduling = new Scheduling(['status' => 'confirmed']);

        $this->assertSame('Confirmado', $scheduling->status_formatted);
    }

    public function test_obs_is_encrypted_in_database_and_decrypted_in_accessor(): void
    {
        $scheduling = Scheduling::query()->create([
            'patient_id' => 1,
            'professional_id' => 1,
            'procedure_id' => 1,
            'date' => now()->toDateString(),
            'time' => '10:00',
            'status' => 'scheduled',
            'obs' => 'Paciente com dor moderada',
        ]);

        $rawObs = DB::table('schedulings')->where('id', $scheduling->id)->value('obs');

        $this->assertNotSame('Paciente com dor moderada', $rawObs);
        $this->assertSame('Paciente com dor moderada', $scheduling->fresh()->obs);
    }

    public function test_confirm_cancel_and_complete_methods_apply_expected_state(): void
    {
        $scheduling = Scheduling::query()->create([
            'patient_id' => 1,
            'professional_id' => 1,
            'procedure_id' => 1,
            'date' => now()->toDateString(),
            'time' => '10:00',
            'status' => 'scheduled',
        ]);

        $scheduling->confirm();
        $this->assertSame('confirmed', $scheduling->fresh()->status);
        $this->assertNotNull($scheduling->fresh()->confirmed_at);

        $scheduling->cancel('Paciente desistiu');
        $this->assertSame('canceled', $scheduling->fresh()->status);
        $this->assertSame('Paciente desistiu', $scheduling->fresh()->cancellation_reason);

        $scheduling->complete();
        $this->assertSame('completed', $scheduling->fresh()->status);
    }
}
