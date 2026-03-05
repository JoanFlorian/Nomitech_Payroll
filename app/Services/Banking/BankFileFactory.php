<?php

namespace App\Services\Banking;

use Exception;

class BankFileFactory
{
    /**
     * Instancia el generador de archivos bancarios correspondiente.
     *
     * @param string $bank
     * @return BankFileGeneratorInterface
     * @throws Exception
     */
    public static function make(string $bank): BankFileGeneratorInterface
    {
        return match (strtolower($bank)) {
            'bancolombia' => new BancolombiaTxtGenerator(),
            default => throw new Exception("El banco '{$bank}' no está soportado actualmente para exportación."),
        };
    }
}
