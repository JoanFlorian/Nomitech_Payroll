<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoHoraRecargo extends Model
{
    use HasFactory;

    protected $table = 'tipo_hora_recargo';
    protected $primaryKey = 'id_tipo_hora_recargo';
    protected $fillable = ['nombre', 'valor'];

    public function horasRecargoExtra()
    {
        return $this->hasMany(HoraRecargoExtra::class, 'id_tipo_hora_recargo', 'id_tipo_hora_recargo');
    }
}
