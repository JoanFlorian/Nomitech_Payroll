<?php

namespace App\Services\Banking;

interface BankFileGeneratorInterface
{
    /**
     * Genera el contenido del archivo bancario para un periodo dado.
     *
     * @param int $periodoId
     * @return string
     */
    public function generate(int $periodoId): string;
}
