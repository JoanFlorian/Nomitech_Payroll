<?php

namespace App\Services\Banking;

use App\Models\Empresa;

/**
 * Generador de archivos PAB (Pagos Automatizados de Bancolombia).
 * 
 * Formato: archivo plano de posiciones fijas (.txt)
 * Diseñado para ser extensible a otros bancos en el futuro.
 */
class PabFileGenerator
{
    /**
     * Mapeo de id_tipo_doc del sistema → código Bancolombia
     */
    private const TIPO_DOC_MAP = [
        1 => '1',  // Cedula de ciudadanía
        2 => '2',  // Cédula de extranjería
        3 => '3',  // NIT
        4 => '4',  // Tarjeta de identidad
        5 => '5',  // Pasaporte
        6 => '9',  // Registro civil
        7 => '7',  // NIT de otro país
    ];

    /**
     * Mapeo de tipo de transacción según tipo de cuenta / billetera
     */
    private const TIPO_TRANSACCION_MAP = [
        'ahorros'   => '37',
        'corriente' => '27',
        'billetera' => '38',
    ];

    private string $nit;
    private string $cuentaDebito;
    private string $tipoCuentaDebito; // 'S' = Ahorros, 'D' = Corriente
    private string $fecha;
    private string $secuencia;

    public function __construct(
        Empresa $empresa,
        string $cuentaDebito,
        string $tipoCuentaDebito,
        string $secuencia = 'A1'
    ) {
        $this->nit = str_pad($empresa->nit ?? '', 15, ' ', STR_PAD_RIGHT);
        $this->cuentaDebito = $cuentaDebito;
        $this->tipoCuentaDebito = strtoupper($tipoCuentaDebito) === 'D' ? 'D' : 'S';
        $this->fecha = now()->format('Ymd');
        $this->secuencia = $secuencia;
    }

    /**
     * Genera el contenido completo del archivo PAB.
     *
     * @param array $empleados Array de datos de empleados procesados
     * @return string Contenido del archivo PAB completo
     */
    public function generate(array $empleados): string
    {
        $totalRegistros = count($empleados);
        $totalValor = array_sum(array_column($empleados, 'valor'));

        $lines = [];
        $lines[] = $this->generateHeader($totalRegistros, $totalValor);

        foreach ($empleados as $empleado) {
            $lines[] = $this->generateDetailLine($empleado);
        }

        return implode("\r\n", $lines);
    }

    /**
     * Genera la línea de encabezado del archivo PAB.
     * 
     * Estructura (posiciones fijas):
     * - NIT de la empresa (15)
     * - Nombre aplicación (blanco, implementación) (vacío)
     * - Tipo de pago: 225 (3)
     * - Descripción del pago (vacío, 10)
     * - Aplicación: I (1)
     * - Secuencia (consecutivo, 2)
     * - Fecha de creación YYYYMMDD (8)
     * - Fecha de aplicación YYYYMMDD (8)
     * - Número de registros (6)
     * - Sumatoria de valores (17)
     * - Cuenta a debitar (11)
     * - Tipo cuenta a debitar S/D (1)
     */
    private function generateHeader(int $totalRegistros, float $totalValor): string
    {
        $line = '';

        // 1. NIT empresa (15 posiciones, justificado a la derecha, relleno con ceros)
        $line .= str_pad(preg_replace('/[^0-9]/', '', $this->nit), 15, '0', STR_PAD_LEFT);

        // 2. Nombre de aplicación (vacío, pero necesitamos mantener la estructura)
        // Se deja vacío con espacios (16 posiciones)
        $line .= str_repeat(' ', 16);
        
        // 3. Tipo de pago: 225 = Pago de Nómina (3 posiciones)
        $line .= '225';

        // 4. Descripción del pago (10 posiciones, vacío)
        $line .= str_repeat(' ', 10);

        // 5. Aplicación: I = Inmediata (1 posición)
        $line .= 'I';

        // 6. Secuencia (consecutiva, 4 posiciones A1, A2, etc. justificado izquierda)
        $line .= str_pad($this->secuencia, 4, ' ', STR_PAD_RIGHT);

        // 7. Fecha de creación YYYYMMDD (8 posiciones)
        $line .= $this->fecha;

        // 8. Fecha de aplicación (= fecha de creación)
        $line .= $this->fecha;

        // 9. Número de registros de detalle (6 posiciones, ceros a la izquierda)
        $line .= str_pad($totalRegistros, 6, '0', STR_PAD_LEFT);

        // 10. Sumatoria total de valores (17 posiciones, ceros a la izquierda, sin decimales)
        $valorEntero = str_pad(intval(round($totalValor * 100)), 17, '0', STR_PAD_LEFT);
        $line .= $valorEntero;

        // 11. Cuenta a debitar (11 posiciones, justificada a la derecha con ceros)
        $line .= str_pad(preg_replace('/[^0-9]/', '', $this->cuentaDebito), 11, '0', STR_PAD_LEFT);

        // 12. Tipo de cuenta a debitar S/D (1 posición)
        $line .= $this->tipoCuentaDebito;

        return $line;
    }

    /**
     * Genera una línea de detalle para un empleado.
     * 
     * @param array $data Con keys: tipo_doc_id, documento, nombre, tipo_transaccion, 
     *                     codigo_ach, numero_cuenta, email, valor, referencia
     */
    private function generateDetailLine(array $data): string
    {
        $line = '';

        // 1. Tipo de documento beneficiario (2 posiciones)
        $tipoDoc = self::TIPO_DOC_MAP[$data['tipo_doc_id'] ?? 1] ?? '1';
        $line .= str_pad($tipoDoc, 2, '0', STR_PAD_LEFT);

        // 2. Documento beneficiario (15 posiciones, justificado izquierda con espacios)
        $line .= str_pad($data['documento'] ?? '', 15, ' ', STR_PAD_RIGHT);

        // 3. Nombre beneficiario (30 posiciones, justificado a la izquierda)
        $nombre = mb_substr(strtoupper($this->sanitizeName($data['nombre'] ?? '')), 0, 30);
        $line .= str_pad($nombre, 30, ' ', STR_PAD_RIGHT);

        // 4. Código banco beneficiario / Código ACH (4 posiciones, ceros a la izquierda)
        $line .= str_pad($data['codigo_ach'] ?? '0000', 4, '0', STR_PAD_LEFT);

        // 5. Número de cuenta beneficiario (17 posiciones, justificado a la izquierda)
        $line .= str_pad($data['numero_cuenta'] ?? '', 17, ' ', STR_PAD_RIGHT);

        // 6. Indicador lugar de pago (1 posición, S = mismo banco, siempre S)
        $line .= 'S';

        // 7. Tipo de transacción (2 posiciones)
        $tipoTrans = $this->resolveTipoTransaccion($data['tipo_transaccion'] ?? 'ahorros');
        $line .= $tipoTrans;

        // 8. Valor de la transacción (17 posiciones, centavos, ceros a la izquierda)
        $valor = str_pad(intval(round(($data['valor'] ?? 0) * 100)), 17, '0', STR_PAD_LEFT);
        $line .= $valor;

        // 9. Fecha de aplicación YYYYMMDD (8 posiciones)
        $line .= $this->fecha;

        // 10. Referencia (21 posiciones, espacios)
        $line .= str_repeat(' ', 21);

        // 11. Número de factura (vacío, 30 posiciones)
        // Marcado como vacío para mantener compatibilidad
        
        // 12. Email del beneficiario (80 posiciones si se envía, o campo compacto)
        $email = mb_substr($data['email'] ?? '', 0, 80);
        $line .= str_pad($email, 80, ' ', STR_PAD_RIGHT);

        return $line;
    }

    /**
     * Mapea el tipo de documento del sistema al código Bancolombia.
     */
    public static function mapTipoDocumento(int $idTipoDoc): string
    {
        return self::TIPO_DOC_MAP[$idTipoDoc] ?? '1';
    }

    /**
     * Resuelve el tipo de transacción según el tipo de cuenta.
     */
    private function resolveTipoTransaccion(string $tipoCuenta): string
    {
        $normalizado = strtolower(trim($tipoCuenta));

        if (str_contains($normalizado, 'corriente')) {
            return self::TIPO_TRANSACCION_MAP['corriente'];
        }

        if (str_contains($normalizado, 'billetera') || str_contains($normalizado, 'digital') || str_contains($normalizado, 'deposito')) {
            return self::TIPO_TRANSACCION_MAP['billetera'];
        }

        // Default: ahorros
        return self::TIPO_TRANSACCION_MAP['ahorros'];
    }

    /**
     * Elimina caracteres especiales del nombre para compatibilidad con archivo plano.
     */
    private function sanitizeName(string $name): string
    {
        // Reemplazar acentos y caracteres especiales
        $replacements = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'ñ' => 'n', 'Ñ' => 'N', 'ü' => 'u', 'Ü' => 'U',
        ];

        $name = strtr($name, $replacements);
        // Solo letras, números y espacios
        $name = preg_replace('/[^A-Za-z0-9\s]/', '', $name);
        // Remover espacios dobles
        $name = preg_replace('/\s+/', ' ', trim($name));

        return $name;
    }
}
