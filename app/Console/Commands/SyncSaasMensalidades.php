<?php

namespace App\Console\Commands;

use App\Services\SaasMensalidadeSyncService;
use Illuminate\Console\Command;

class SyncSaasMensalidades extends Command
{
    protected $signature = 'saas:mensalidades-sync';

    protected $description = 'Sincroniza clinicas e atualiza status de mensalidades SaaS';

    public function handle(SaasMensalidadeSyncService $syncService): int
    {
        $result = $syncService->sync();

        $this->info('Sincronizacao concluida com sucesso.');
        $this->line('Mensalidades atualizadas: ' . ($result['upserted'] ?? 0));

        return self::SUCCESS;
    }
}
