<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // 1) Corrigir duplicidades ativas existentes para nao quebrar o indice unico
        $duplicates = DB::table('schedulings')
            ->select(
                'patient_id',
                'professional_id',
                'procedure_id',
                'date',
                'time',
                DB::raw('MIN(id) as keep_id'),
                DB::raw('COUNT(*) as total')
            )
            ->where('status', '!=', 'canceled')
            ->groupBy('patient_id', 'professional_id', 'procedure_id', 'date', 'time')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('schedulings')
                ->where('patient_id', $dup->patient_id)
                ->where('professional_id', $dup->professional_id)
                ->where('procedure_id', $dup->procedure_id)
                ->whereDate('date', $dup->date)
                ->where('time', $dup->time)
                ->where('status', '!=', 'canceled')
                ->where('id', '!=', $dup->keep_id)
                ->update([
                    'status' => 'canceled',
                    'canceled_at' => now(),
                    'cancellation_reason' => 'Cancelado automaticamente para adequacao da regra de nao duplicidade.',
                    'updated_at' => now(),
                ]);
        }

        // 2) Coluna gerada: registros cancelados recebem NULL e nao entram no bloqueio unico
        if (!Schema::hasColumn('schedulings', 'active_duplicate_guard')) {
            DB::statement("ALTER TABLE `schedulings` ADD COLUMN `active_duplicate_guard` TINYINT GENERATED ALWAYS AS (CASE WHEN `status` = 'canceled' THEN NULL ELSE 1 END) STORED");
        }

        // 3) Indice unico composto da regra de negocio
        $indexExists = DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', 'schedulings')
            ->where('index_name', 'uq_schedulings_business_unique')
            ->exists();

        if (!$indexExists) {
            DB::statement('ALTER TABLE `schedulings` ADD UNIQUE INDEX `uq_schedulings_business_unique` (`patient_id`, `professional_id`, `procedure_id`, `date`, `time`, `active_duplicate_guard`)');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $indexExists = DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', 'schedulings')
            ->where('index_name', 'uq_schedulings_business_unique')
            ->exists();

        if ($indexExists) {
            DB::statement('ALTER TABLE `schedulings` DROP INDEX `uq_schedulings_business_unique`');
        }

        if (Schema::hasColumn('schedulings', 'active_duplicate_guard')) {
            DB::statement('ALTER TABLE `schedulings` DROP COLUMN `active_duplicate_guard`');
        }
    }
};
