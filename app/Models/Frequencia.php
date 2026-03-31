<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Frequencia extends Model
{
    protected $fillable = [
        'nome',
        'descricao',
        'intervalo_dias',
        'tipo_intervalo',
        'icone',
        'cor',
        'ativo'
    ];

    // Regras de negócio: exemplo de mutator para garantir intervalo_dias positivo
    public function setIntervaloDiasAttribute($value)
    {
        $this->attributes['intervalo_dias'] = max(1, (int) $value);
    }

    // Regra de negócio: não permitir nome vazio
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->nome)) {
                throw new \InvalidArgumentException('O nome da frequência é obrigatório.');
            }
            if (!is_numeric($model->intervalo_dias) || $model->intervalo_dias <= 0) {
                throw new \InvalidArgumentException('Intervalo de dias deve ser um número positivo.');
            }
        });

        static::updating(function ($model) {
            if (isset($model->intervalo_dias) && (!is_numeric($model->intervalo_dias) || $model->intervalo_dias <= 0)) {
                throw new \InvalidArgumentException('Intervalo de dias deve ser um número positivo.');
            }
        });
    }
}
