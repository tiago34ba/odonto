<?php

namespace App\Console\Commands;

use App\Services\PortalDentistSyncService;
use Illuminate\Console\Command;

class SyncPortalDentists extends Command
{
    protected $signature = 'portal:sync-dentists {--chunk=300 : Quantidade de registros por lote}';

    protected $description = 'Sincroniza dentistas cadastrados em funcionarios para a tabela employees do portal do paciente';

    public function __construct(private readonly PortalDentistSyncService $syncService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $chunk = max(50, (int) $this->option('chunk'));
        $stats = $this->syncService->syncAll($chunk);

        $this->info("Dentistas sincronizados: {$stats['synced']}");
        $this->info("Registros desativados no portal: {$stats['deactivated']}");

        return self::SUCCESS;
    }
}
