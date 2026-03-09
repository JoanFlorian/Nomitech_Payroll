<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class NominaParameterService
{
    private static $params;

    public function get()
    {
        if (!self::$params) {
            self::$params = DB::table('payroll_parameters')->first();
        }

        return self::$params;
    }
}