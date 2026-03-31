<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Paciente;
use App\Services\EncryptionService;

class MigrateToMysqlCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'migrate:mysql {--force : Force migration without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Migrate data from SQLite to MySQL with encryption';

    protected EncryptionService $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        parent::__construct();
        $this->encryptionService = $encryptionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will migrate all data from SQLite to MySQL. Continue?')) {
                $this->info('Migration cancelled.');
                return;
            }
        }

        $this->info('Starting migration from SQLite to MySQL...');

        try {
            // Verifica se o MySQL está configurado
            if (config('database.default') !== 'mysql') {
                $this->error('MySQL is not configured as default database.');
                return;
            }

            // Executa as migrações
            $this->call('migrate', ['--force' => true]);

            // Migra dados dos pacientes
            $this->migratePatients();

            $this->info('Migration completed successfully!');
        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Migra dados dos pacientes
     */
    protected function migratePatients()
    {
        $this->info('Migrating patients data...');

        // Conecta ao SQLite temporariamente
        $sqliteConnection = config('database.connections.sqlite');
        $sqliteDb = new \PDO("sqlite:" . $sqliteConnection['database']);

        // Busca todos os pacientes do SQLite
        $stmt = $sqliteDb->query('SELECT * FROM pacientes');
        $patients = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->info("Found {count} patients to migrate", ['count' => count($patients)]);

        $bar = $this->output->createProgressBar(count($patients));
        $bar->start();

        foreach ($patients as $patientData) {
            try {
                // Criptografa dados sensíveis
                $encryptedData = $this->encryptionService->encryptPatientData($patientData);

                // Remove campos que não existem no MySQL
                unset($encryptedData['id']);

                // Cria o paciente no MySQL
                Paciente::create($encryptedData);

                $bar->advance();
            } catch (\Exception $e) {
                $this->warn("Failed to migrate patient {$patientData['name']}: " . $e->getMessage());
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Patients migration completed!');
    }
}
