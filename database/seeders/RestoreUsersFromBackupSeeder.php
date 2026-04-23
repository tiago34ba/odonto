<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RestoreUsersFromBackupSeeder extends Seeder
{
    public function run(): void
    {
        $candidateFiles = [
            base_path('backup_tabela_users_20260401_132954.csv'),
            base_path('backup_tabela_users_20260401_131554.csv'),
            base_path('backup_usuarios_20260401_131400.csv'),
        ];

        $csvFile = collect($candidateFiles)->first(fn (string $path) => File::exists($path));

        if (!$csvFile) {
            $this->command?->warn('RestoreUsersFromBackupSeeder: nenhum arquivo de backup CSV encontrado.');
            return;
        }

        $handle = fopen($csvFile, 'r');

        if ($handle === false) {
            $this->command?->error('RestoreUsersFromBackupSeeder: nao foi possivel abrir o arquivo CSV.');
            return;
        }

        $header = fgetcsv($handle);

        if (!$header) {
            fclose($handle);
            $this->command?->warn('RestoreUsersFromBackupSeeder: arquivo CSV vazio.');
            return;
        }

        $restored = 0;
        $emails = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4) {
                continue;
            }

            [$id, $nome, $email, $passwordHash] = $row;

            if (!$email || !$passwordHash) {
                continue;
            }

            $displayName = trim((string) $nome) !== '' ? trim((string) $nome) : Str::before($email, '@');
            $emailLocalPart = Str::lower(Str::before($email, '@'));
            $isLegacySaasAdmin = $emailLocalPart === 'saasadmin';
            $username = $this->resolveUsername($emailLocalPart, $email);

            DB::table('users')->updateOrInsert(
                ['email' => $email],
                [
                    'name' => $displayName,
                    'nome' => trim((string) $nome) !== '' ? trim((string) $nome) : null,
                    'username' => $username,
                    'tipo' => $isLegacySaasAdmin ? 'saas_admin' : 'staff',
                    'ativo' => true,
                    'password' => $passwordHash,
                    'email_verified_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $emails[] = $email;
            $restored++;
        }

        fclose($handle);

        if (!empty($emails)) {
            DB::table('users')->where('email', 'test@example.com')->whereNotIn('email', $emails)->delete();
        }

        $this->command?->info("RestoreUsersFromBackupSeeder: {$restored} usuarios processados a partir de {$csvFile}.");
    }

    private function resolveUsername(string $emailLocalPart, string $email): ?string
    {
        if ($emailLocalPart === '') {
            return null;
        }

        $username = $emailLocalPart;
        $suffix = 2;

        while (DB::table('users')
            ->where('username', $username)
            ->where('email', '!=', $email)
            ->exists()) {
            $username = $emailLocalPart . '.' . $suffix;
            $suffix++;
        }

        return $username;
    }
}
