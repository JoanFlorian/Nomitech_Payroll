<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDoc extends Model
{
    use HasFactory;

    protected $table = 'tipo_doc';
    protected $primaryKey = 'id_tipo_doc';
    protected $fillable = ['nombre'];

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'id_tipo_doc', 'id_tipo_doc');
    }
}
