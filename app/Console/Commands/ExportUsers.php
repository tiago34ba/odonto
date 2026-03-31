<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ExportUsers extends Command
{
    protected $signature = 'users:export {filename=users_export.csv}';
    protected $description = 'Exporta lista de usuários e hashes de senha para arquivo CSV';

    public function handle()
    {
        $filename = $this->argument('filename');
        $users = User::all(['id','nome','email','password']);
        $fp = fopen($filename, 'w');
        fputcsv($fp, ['id', 'nome', 'email', 'password']);
        foreach ($users as $user) {
            fputcsv($fp, [$user->id, $user->nome, $user->email, $user->password]);
        }
        fclose($fp);
        $this->info("Arquivo exportado: $filename");
    }
}
