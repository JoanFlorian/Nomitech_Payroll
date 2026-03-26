# 📊 PilaTxtToExcelExport - Documentación Completa

## 📋 Descripción

Clase profesional para convertir archivos PILA `.txt` a Excel con diseño corporativo, usando solo **PhpSpreadsheet** (sin Laravel Excel).

**Características principales:**
- ✅ Lectura de archivos PILA con líneas tipo 01 (encabezado) y 02 (empleados)
- ✅ Diseño profesional: colores corporativos, bordes, estilos
- ✅ Encabezados con información de empresa (razón social, NIT, período)
- ✅ Tabla con 13 columnas de datos PILA
- ✅ Filas alternas con colores suaves para legibilidad
- ✅ Fila de totales calculada automáticamente
- ✅ Congelación de headers para fácil navegación
- ✅ Formato numérico con separador de miles
- ✅ Ancho de columnas optimizado
- ✅ Validación de errores
- ✅ Integración con servicio existente `PilaExportService`

---

## 📁 Archivos Creados

```
app/Exports/PilaTxtToExcelExport.php          ← Clase principal de exportación
app/Services/PilaExportService.php            ← Servicio integrado (modificado)
app/Console/Commands/PilaTxtToExcelCommand.php ← Comando Artisan
tests/Unit/PilaTxtToExcelExportTest.php       ← Tests unitarios
ejemplo_pila.txt                               ← Archivo de prueba
PILA_EXCEL_EJEMPLOS.php                       ← Ejemplos de uso
```

---

## 🚀 Uso Rápido

### Opción 1: Comando Artisan (más sencillo)

```bash
php artisan pila:txt-to-excel ejemplo_pila.txt

# O especificar la salida
php artisan pila:txt-to-excel ejemplo_pila.txt --salida=mis_exportaciones/resultado.xlsx
```

### Opción 2: Directamente en código PHP

```php
<?php

use App\Exports\PilaTxtToExcelExport;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Crear la exportación
$export = new PilaTxtToExcelExport(storage_path('app/pila/archivo.txt'));
$spreadsheet = $export->export();

// Guardar
$writer = new Xlsx($spreadsheet);
$writer->save(storage_path('app/exports/resultado.xlsx'));
```

### Opción 3: Método estático

```php
$spreadsheet = PilaTxtToExcelExport::fromFile(
    storage_path('app/pila/archivo.txt')
);

// Ya está listo para descargar o guardar
```

### Opción 4: Con el servicio (recomendado en controladores)

```php
<?php

namespace App\Http\Controllers;

use App\Services\PilaExportService;
use App\Models\Empresa;
use Illuminate\Http\Request;

class PilaController extends Controller
{
    public function exportarExcel(Request $request, PilaExportService $service)
    {
        $empresa = Empresa::find($request->empresa_id);
        
        return $service->exportarPilaTxtAExcel(
            storage_path('app/pila/archivo.txt'),
            $empresa
        );
        // Automáticamente hace la descarga del archivo
    }
}
```

---

## 📄 Formato del Archivo .txt PILA

El archivo debe seguir el siguiente formato separado por `|`:

### Línea Tipo 01 (Encabezado - obligatoria)

```
01|NIT|NOMBRE_EMPRESA|NI|PERIODO|TOTAL_EMPLEADOS
01|830012345|EMPRESA ACME SAS|NI|2026-03|5
```

**Campos:**
- `01`: Código de tipo encabezado
- `NIT`: Número de identificación tributaria
- `NOMBRE_EMPRESA`: Razón social
- `NI`: Tipo de planilla (siempre "NI")
- `PERIODO`: Año-Mes (YYYY-MM)
- `TOTAL_EMPLEADOS`: Cantidad de empleados

### Líneas Tipo 02 (Empleado - una por cada empleado)

```
02|NIT|PERIODO|TIPO_DOC|DOCUMENTO|NOMBRE|CODIGO_EPS|CODIGO_AFP|CODIGO_ARL|IBC_SALUD|DIAS_COTIZADOS|APORTE_SALUD|APORTE_PENSION|VALOR_ARL|APORTE_CAJA|APORTE_FP
02|830012345|2026-03|CC|1070599004|JUAN CARLOS PEREZ|800100017|1|860006015|2000000|30|280000|320000|43680|80000|0
```

**Campos:**
| Campo | Descripción | Tipo | Ejemplo |
|-------|-------------|------|---------|
| Tipo Registro | Siempre "02" | String | `02` |
| NIT | NIT de la empresa | String | `830012345` |
| Período | Año-Mes | String | `2026-03` |
| Tipo Documento | Tipo de ID del empleado | String | `CC`, `CE`, `PA` |
| Documento | Número de documento | String | `1070599004` |
| Nombre | Nombre completo | String | `JUAN CARLOS PEREZ` |
| Código EPS | Código PILA de la EPS | String | `800100017` |
| Código AFP | Código PILA del fondo pensión | String | `1` |
| Código ARL | Código PILA de la ARL | String | `860006015` |
| IBC Salud | Ingreso Base Cotización | Número | `2000000` |
| Días Cotizados | Días trabajados/cotizados | Número | `30` |
| Aporte Salud | Total aporte salud | Número | `280000` |
| Aporte Pensión | Total aporte pensión | Número | `320000` |
| Valor ARL | Aporte ARL | Número | `43680` |
| Aporte Caja | Aporte caja compensación | Número | `80000` |
| Aporte FP | Aporte fondo pensiones | Número | `0` |

---

## 🎨 Diseño y Estilos

El Excel generado incluye:

### Encabezado (Filas 1-5)
- 🏢 **Razón Social**: Título principal en azul oscuro (#1a5276), tamaño 16
- 🏷️ **NIT**: Información en azul medio (#2874a6), tamaño 11
- 📅 **Período**: Formateado legible (ej: "Marzo de 2026")
- 👥 **Total Empleados**: Contador automático

### Tabla de Datos (Fila 6 en adelante)
- **Encabezados**: Fondo azul oscuro con texto blanco, bold
- **Columnas**: 13 columnas con ancho optimizado
- **Filas**: Alternancia de colores (blanco y gris muy suave #f8f9f9)
- **Bordes**: Gris suave en todos los datos
- **Números**: Formato con separador de miles (#,##0)

### Fila de Totales
- 📊 Suma automática de aportes
- Fondo azul medio con texto blanco bold
- Números con formato

### Funcionalidades
- 🔒 **Filas congeladas**: Headers no se desplazan al scroll
- 📏 Ancho de columnas optimizado
- 📋 Bordes profesionales

---

## ⚙️ Método Público Principal

```php
public function export(): Spreadsheet
```

Procesa el archivo .txt completo y devuelve un `Spreadsheet` de PhpSpreadsheet listo para:
- Guardar en disco
- Descargar al navegador
- Procesamiento adicional

### Método Estático

```php
public static function fromFile(string $rutaArchivo): Spreadsheet
```

Forma más rápida: crea la instancia, procesa y devuelve el Spreadsheet en una línea.

---

## 🔍 Validaciones y Errores

La clase valida automáticamente:

| Validación | Excepción | Solución |
|-----------|-----------|----------|
| Archivo no existe | `Archivo no encontrado: {ruta}` | Verificar ruta |
| Archivo vacío | `El archivo txt está vacío` | Agregar contenido |
| Sin encabezado tipo 01 | `No se encontró encabezado (tipo 01) en el archivo` | Agregar línea tipo 01 |
| Formato incorrecto | Comportamiento definido en parseado | Revisar separadores `\|` |

---

## 📝 Ejemplos Prácticos

### Ejemplo 1: Controlador con descarga

```php
<?php
namespace App\Http\Controllers;

use App\Services\PilaExportService;
use App\Models\Empresa;

class ExportController extends Controller
{
    public function descargarPila(PilaExportService $service, $empresaId)
    {
        try {
            $empresa = Empresa::find($empresaId);
            $ruta = storage_path('app/pila/planilla_2026_03.txt');
            
            return $service->exportarPilaTxtAExcel($ruta, $empresa);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
```

### Ejemplo 2: Procesar múltiples archivos

```php
<?php
use App\Exports\PilaTxtToExcelExport;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$archivos = glob(storage_path('app/pila/*.txt'));

foreach ($archivos as $archivo) {
    try {
        $export = new PilaTxtToExcelExport($archivo);
        $spreadsheet = $export->export();
        
        $nombreSalida = basename($archivo, '.txt') . '_' . now()->format('His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save(storage_path("app/exports/{$nombreSalida}"));
        
        \Log::info("✅ Exportado: {$nombreSalida}");
    } catch (\Exception $e) {
        \Log::error("❌ Error en {$archivo}: " . $e->getMessage());
    }
}
```

### Ejemplo 3: Con personalización adicional

```php
<?php
use App\Exports\PilaTxtToExcelExport;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$export = new PilaTxtToExcelExport(storage_path('app/pila/planilla.txt'));
$spreadsheet = $export->export();

// Agregar hoja adicional con análisis
$analisSheet = $spreadsheet->createSheet();
$analisSheet->setTitle('Análisis');
$analisSheet->setCellValue('A1', 'Resumen del Período');

// Agregar gráficos, fórmulas, etc.

$writer = new Xlsx($spreadsheet);
$writer->save(storage_path('app/exports/resultado_completo.xlsx'));
```

---

## 🧪 Pruebas Unitarias

Ejecutar tests:

```bash
php artisan test tests/Unit/PilaTxtToExcelExportTest.php
```

## 📊 Información Técnica

### Dependencias
- **PhpOffice/PhpSpreadsheet**: Incluida en Laravel 11
- **PHP**: 8.2+
- **Laravel**: 11+

### Paleta de Colores Usada
```
PRIMARY (Azul Oscuro):      #1a5276
SECONDARY (Azul Medio):     #2874a6
HEADER BG (Azul Claro):     #d4e6f1
ROW ALT (Gris Suave):       #f8f9f9
BORDER (Gris):              #90000000
TEXT HEADER (Blanco):       #ffffff
```

### Estructura del Spreadsheet

```
┌─────────────────────────────────────────────────┐
│ EMPRESA ACME SAS (Título Principal)             │
├─────────────────────────────────────────────────┤
│ NIT: 830012345                                  │
│ Periodo: Marzo de 2026                          │
│ Total Empleados: 5                              │
├─────────────────────────────────────────────────┤
│ Tipo | Doc | Nombre | EPS | AFP | ARL | ...    │
├─────────────────────────────────────────────────┤
│  02  | 107 | JUAN   | 800 |  1  | 860 | ...    │
│  02  | 107 | MARIA  | 800 |  1  | 860 | ...    │
│  02  | 107 | LUIS   | 800 |  1  | 860 | ...    │
├─────────────────────────────────────────────────┤
│ TOTAL APORTES              | $900.000 | ... ... │
└─────────────────────────────────────────────────┘
```

---

## 🔐 Seguridad

- ✅ Validación de archivo antes de procesar
- ✅ Control de ruta seguro con `storage_path()`
- ✅ Manejo de excepciones robusto
- ✅ Logging de errores
- ✅ Sin inyección de código

---

## 📞 Soporte

**Archivo de ejemplo:** `ejemplo_pila.txt`

**Para reportar problemas:**
- Revisar logs en `storage/logs/`
- Verificar formato del archivo .txt
- Asegurar permisos en carpeta `storage/`

---

## ✅ Checklist de Implementación

- [x] Crear clase `PilaTxtToExcelExport`
- [x] Crear servicio integrado en `PilaExportService`
- [x] Crear comando Artisan
- [x] Crear tests unitarios
- [x] Crear archivo de ejemplo
- [x] Crear documentación completa
- [x] Validación de errores
- [x] Diseño profesional con colores corporativos
- [x] Formato numérico con separador de miles
- [x] Congelación de headers

---

**Versión:** 1.0  
**Última actualización:** 2026-03-26  
**Estado:** ✅ Listo para producción
