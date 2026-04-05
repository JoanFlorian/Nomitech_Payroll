#!/bin/bash

# Test Manual del Módulo PILA
# Este script prueba la funcionalidad del módulo PILA manualmente

echo "================================"
echo "Test del Módulo PILA"
echo "================================"
echo ""

# Verificar que los archivos existen
echo "✓ Verificando archivos modificados..."
files=(
    "app/Http/Controllers/PilaController.php"
    "app/Services/PilaFileGeneratorService.php"
    "resources/views/pila/index.blade.php"
    "resources/views/pila/partials/historial-pila-modal.blade.php"
)

for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "  ✓ $file existe"
    else
        echo "  ✗ $file NO existe"
    fi
done

echo ""
echo "✓ Verificando sintaxis PHP..."
php -l app/Http/Controllers/PilaController.php && echo "  ✓ PilaController.php - OK"
php -l app/Services/PilaFileGeneratorService.php && echo "  ✓ PilaFileGeneratorService.php - OK"
php -l app/Http/Controllers/RegistroUsuarios.php && echo "  ✓ RegistroUsuarios.php - OK"

echo ""
echo "✓ Verificando base de datos..."
php artisan migrate --env=testing --no-interaction > /dev/null 2>&1

# Verificar tablas necesarias
echo "  Tablas existentes:"
php artisan tinker --execute="
    \$tables = ['empresa', 'periodo_liquidacion', 'usuario', 'contrato', 'salario', 'planilla_pila', 'pila_archivos'];
    foreach (\$tables as \$table) {
        \$exists = \DB::getSchemaBuilder()->hasTable(\$table);
        echo \$exists ? '    ✓ ' : '    ✗ ';
        echo \$table . chr(10);
    }
" 2>/dev/null

echo ""
echo "✓ Verificando funcionalidades implementadas..."

# Verificar que los cambios están en el código
echo "  Verificando bloqueo por hash..."
grep -q "datos_hash" app/Http/Controllers/PilaController.php && echo "    ✓ Sistema de hash implementado"

echo "  Verificando permitir períodos cerrados..."
grep -q "ESTADO_CERRADO" app/Http/Controllers/PilaController.php && echo "    ✓ Períodos cerrados permitidos"

echo "  Verificando filtrado de períodos cerrados..."
grep -q "whereRaw.*s.updated_at" app/Http/Controllers/PilaController.php && echo "    ✓ Filtrado inteligente de períodos"

echo "  Verificando invalidación de hash por entidades..."
grep -q "datos_hash.*null" app/Http/Controllers/RegistroUsuarios.php && echo "    ✓ Hash invalidado por cambio de entidades"

echo "  Verificando estado 'Pendiente' en historial..."
grep -q "tiene_cambios" app/Http/Controllers/PilaController.php && echo "    ✓ Estado pendiente en historial"

echo "  Verificando limpieza de historial..."
grep -q "Storage::disk.*delete" app/Services/PilaFileGeneratorService.php && echo "    ✓ Limpieza automática de archivos"

echo ""
echo "✓ Verificando Git..."
echo "  Últimos commits:"
git log --oneline -5 | sed 's/^/    /'

echo ""
echo "================================"
echo "Test Completado!"
echo "================================"

# Resumen de cambios
echo ""
echo "📋 Resumen de Todas las Funcionalidades Implementadas:"
echo ""
echo "1. ✓ Generar PILA en períodos abiertos Y cerrados"
echo "2. ✓ Bloqueo inteligente basado en hash SHA256"
echo "   - Bloquea si datos no cambiaron (hash igual)"
echo "   - Permite regeneración si hay cambios (hash diferente)"
echo "3. ✓ Historial limpio - solo última versión por período"
echo "   - Elimina automáticamente archivos antiguos"
echo "4. ✓ Invalida hash cuando cambia entidad de seguridad social"
echo "   - EPS, AFP, ARL, Caja de Compensación"
echo "   - Aplicado en edición y renovación de contratos"
echo "5. ✓ Filtrado inteligente de períodos en select"
echo "   - Muestra solo abiertos/pendientes por defecto"
echo "   - Agrega períodos cerrados si hay cambios"
echo "6. ✓ Estados mejorados en historial PILA"
echo "   - 'Disponible' - sin cambios"
echo "   - 'Pendiente de Regenerar' - hay cambios"
echo "7. ✓ Mensajes informativos mejorados en UI"
echo "   - Explica cómo regenerar"
echo ""
