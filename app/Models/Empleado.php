<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;

/**
 * Empleado Model
 * Extends Usuario to apply multi-company isolation without affecting the base Usuario model.
 */
class Empleado extends Usuario
{
    use BelongsToCompany;

    /**
     * Ensure the model uses the correct table but behaves as an Empleado.
     */
    protected $table = 'usuario';
}
