<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'razao_social')) {
                $table->string('razao_social')->nullable()->after('name');
            }

            if (!Schema::hasColumn('suppliers', 'tipo')) {
                $table->string('tipo')->nullable()->after('cnpj');
            }

            if (!Schema::hasColumn('suppliers', 'categoria')) {
                $table->string('categoria')->nullable()->after('tipo');
            }

            if (!Schema::hasColumn('suppliers', 'contato')) {
                $table->string('contato')->nullable()->after('categoria');
            }

            if (!Schema::hasColumn('suppliers', 'status')) {
                $table->tinyInteger('status')->default(1)->after('contato');
            }

            if (!Schema::hasColumn('suppliers', 'avaliacao')) {
                $table->decimal('avaliacao', 3, 1)->default(0)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $columns = [
                'razao_social',
                'tipo',
                'categoria',
                'contato',
                'status',
                'avaliacao',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('suppliers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
