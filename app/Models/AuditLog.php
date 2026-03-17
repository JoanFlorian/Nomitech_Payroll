<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'company_id',
        'user_id',
        'action',
        'module',
        'entity_id',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'company_id', 'id_empresa');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'user_id', 'doc');
    }
}
