<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Consulta;

class ItemAnamnese extends Model
{
    protected $table = 'item_anamneses';

    protected $fillable = [
        'nome',
        'grupo_id',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    // Relacionamento com o grupo de anamnese
    public function grupo()
    {
        return $this->belongsTo(GroupAnamnese::class, 'grupo_id');
    }

    // Relacionamento com consultas
    public function consultas()
    {
        return $this->hasMany(Consulta::class, 'item_anamnese_id');
    }
}
