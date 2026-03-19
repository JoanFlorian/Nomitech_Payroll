<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Se lanza cuando se intenta liquidar un empleado que tiene una incapacidad
 * activa (IGE o IRL) registrada en un periodo anterior al que se está liquidando.
 * Normativa colombiana: la empresa solo paga los primeros 2 días de incapacidad EG;
 * los periodos siguientes son responsabilidad de la EPS/ARL.
 */
class EmpleadoIncapacitadoException extends RuntimeException
{
}
