<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tratamento extends Model
{
    protected $fillable = [
        'nome',
        'descricao',
        'data_inicio',
        'data_fim',
        'frequencia_id',
        'paciente_id',
        'ativo'
    ];

    // Regra de negócio: datas válidas
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->nome)) {
                throw new \InvalidArgumentException('O nome do tratamento é obrigatório.');
            }
            if (isset($model->data_inicio, $model->data_fim) && $model->data_fim < $model->data_inicio) {
                throw new \InvalidArgumentException('A data final não pode ser anterior à data inicial.');
            }
        });

        static::updating(function ($model) {
            if (isset($model->data_inicio, $model->data_fim) && $model->data_fim < $model->data_inicio) {
                throw new \InvalidArgumentException('A data final não pode ser anterior à data inicial.');
            }
        });
    }

    // Relacionamento com Frequencia
    public function frequencia()
    {
        return $this->belongsTo(Frequencia::class);
    }
}
