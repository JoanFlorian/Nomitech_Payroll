# 🚀 PILA Txt to Excel - Inicio Rápido

## ⚡ Uso Inmediato

### 1️⃣ Comando Más Simple
```bash
php artisan pila:txt-to-excel ejemplo_pila.txt
```
✅ Genera automáticamente: `storage/app/exports/pila.xlsx`

### 2️⃣ Con salida personalizada
```bash
php artisan pila:txt-to-excel ejemplo_pila.txt --salida=mis_reportes/resultado.xlsx
```

---

## 💻 Código PHP - En tu Controlador

```php
<?php
namespace App\Http\Controllers;

use App\Services\PilaExportService;
use App\Models\Empresa;

class ReportesController extends Controller
{
    public function exportarPila($empresaId, PilaExportService $service)
    {
        $empresa = Empresa::find($empresaId);
        
        // Ya está listo para descargar
        return $service->exportarPilaTxtAExcel(
            storage_path('app/pila/planilla_marzo_2026.txt'),
            $empresa
        );
    }
}
```

---

## 📊 Ejemplo de Archivo .txt PILA

```txt
01|830012345|EMPRESA ACME SAS|NI|2026-03|2
02|830012345|2026-03|CC|1070599004|JUAN PEREZ|800100017|1|860006015|2000000|30|280000|320000|43680|80000|0
02|830012345|2026-03|CC|1070599005|MARIA GARCIA|800100017|1|860006015|2500000|30|350000|400000|54600|100000|0
```

**Estructura:**
- **Línea 01:** Tipo|NIT|Empresa|NI|Período|Total Empleados
- **Líneas 02:** Tipo|NIT|Período|TipoDoc|DOCUMENTO|Nombre|EPS|AFP|ARL|IBC|Días|Salud|Pensión|ARL|Caja|FP

---

## 🎨 Lo que Genera

Excel profesional con:
- ✅ Encabezado con empresa, NIT, período
- ✅ Tabla con 13 columnas de datos PILA
- ✅ Colores corporativos (azul)
- ✅ Filas alternas para legibilidad
- ✅ Formato de números con separador de miles
- ✅ Fila de totales automática
- ✅ Headers congelados

---

## 📁 Archivos Disponibles

| Archivo | Descripción |
|---------|-------------|
| `app/Exports/PilaTxtToExcelExport.php` | Clase principal |
| `app/Services/PilaExportService.php` | Servicio (modificado) |
| `app/Console/Commands/PilaTxtToExcelCommand.php` | Comando Artisan |
| `tests/Unit/PilaTxtToExcelExportTest.php` | Tests (10/10 ✅) |
| `ejemplo_pila.txt` | Archivo de prueba |
| `PILA_EXCEL_DOCUMENTACION.md` | Documentación completa |
| `PILA_EXCEL_EJEMPLOS.php` | 7 ejemplos de uso |

---

## 🧪 Validar que Funciona

```bash
# Ejecutar los tests
php artisan test tests/Unit/PilaTxtToExcelExportTest.php

# Esperado: 10 passed ✓
```

---

## 🔧 Métodos de Uso

### Opción 1: Método Estático (Más rápido)
```php
$spreadsheet = PilaTxtToExcelExport::fromFile(
    storage_path('app/pila/archivo.txt')
);
```

### Opción 2: Con instancia
```php
$export = new PilaTxtToExcelExport(storage_path('app/pila/archivo.txt'));
$spreadsheet = $export->export();
```

### Opción 3: Con servicio (Recomendado en controllers)
```php
$service = new PilaExportService();
return $service->exportarPilaTxtAExcel($ruta, $empresa);
```

### Opción 4: Comando Artisan (Más simple)
```bash
php artisan pila:txt-to-excel archivo.txt
```

---

## ❓ Preguntas Frecuentes

**P: ¿Qué versión de PHP necesita?**
A: PHP 8.2+ (Laravel 11+)

**P: ¿Usa Laravel Excel?**
A: No, solo PhpSpreadsheet

**P: ¿Los archivos .txt ya existen?**
A: Depende. Normalmente se generan con `PilaFileGeneratorService`

**P: ¿Puedo personalizar más el Excel?**
A: Sí, después de `$export->export()` puedes seguir modificando el Spreadsheet

**P: ¿Dónde se guarda el Excel?**
A: `storage/app/exports/` por defecto, o donde especifiques con `--salida`

---

## 📞 Archivos de Referencia

- **Documentación Completa:** `PILA_EXCEL_DOCUMENTACION.md`
- **7 Ejemplos de Uso:** `PILA_EXCEL_EJEMPLOS.php`
- **Este Readme:** `INICIO_RAPIDO.md`

---

**¡Listo para usar! 🚀**
