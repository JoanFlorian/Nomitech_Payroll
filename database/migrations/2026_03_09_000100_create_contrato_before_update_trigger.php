<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar si existiera previamente
        DB::unprepared('DROP TRIGGER IF EXISTS tr_contrato_before_update');

        DB::unprepared(<<<SQL
CREATE TRIGGER tr_contrato_before_update
BEFORE UPDATE ON contrato
FOR EACH ROW
BEGIN
    -- Cambio de EPS
    IF NOT (OLD.id_eps <=> NEW.id_eps) THEN
        INSERT INTO historial_contrato (
            id_contrato, dato_anterior, dato_nuevo, tipo_novedad, fecha_cambio
        ) VALUES (
            OLD.id_contrato,
            CAST(OLD.id_eps AS CHAR),
            CAST(NEW.id_eps AS CHAR),
            'EPS',
            NOW()
        );
    END IF;

    -- Cambio de AFP
    IF NOT (OLD.id_afp <=> NEW.id_afp) THEN
        INSERT INTO historial_contrato (
            id_contrato, dato_anterior, dato_nuevo, tipo_novedad, fecha_cambio
        ) VALUES (
            OLD.id_contrato,
            CAST(OLD.id_afp AS CHAR),
            CAST(NEW.id_afp AS CHAR),
            'AFP',
            NOW()
        );
    END IF;

    -- Cambio de SALARIO
    IF NOT (OLD.salario_base <=> NEW.salario_base) THEN
        INSERT INTO historial_contrato (
            id_contrato, dato_anterior, dato_nuevo, tipo_novedad, fecha_cambio
        ) VALUES (
            OLD.id_contrato,
            CAST(OLD.salario_base AS CHAR),
            CAST(NEW.salario_base AS CHAR),
            'SALARIO',
            NOW()
        );
    END IF;
END
SQL
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS tr_contrato_before_update');
    }
};
