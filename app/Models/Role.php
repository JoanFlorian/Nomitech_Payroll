<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['company_id', 'name', 'description'];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'company_id', 'id_empresa');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id');
    }

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'user_roles', 'role_id', 'user_id');
    }
}
