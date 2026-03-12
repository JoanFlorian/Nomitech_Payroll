<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeveranceBatch extends Model
{
    protected $table = 'severance_batches';
    protected $primaryKey = 'id';

    protected $fillable = [
        'empresa_id',
        'year',
        'fondo',
        'generated_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id', 'id_empresa');
    }

    public function benefitLedgers()
    {
        return $this->hasMany(BenefitLedger::class, 'batch_id', 'id');
    }
}
