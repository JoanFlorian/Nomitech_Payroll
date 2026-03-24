# Guía de Exportación de PILA a Excel

## Descripción General

Se ha implementado un sistema optimizado y separado para la exportación de registros PILA a Excel con diseño profesional, logo de la empresa y colores corporativos.

## Estructura Implementada

### 1. **Controlador Especializado** (`PilaExportController`)
   - `Ruta:` `app/Http/Controllers/PilaExportController.php`
   - **Responsabilidades:**
     - Manejar todas las solicitudes de exportación
     - Validar permisos y datos
     - Gestionar errores de manera consistente
     - Delegación al servicio de exportación

### 2. **Servicio de Exportación** (`PilaExportService`)
   - `Ruta:` `app/Services/PilaExportService.php`
   - **Responsabilidades:**
     - Centralizar la lógica de exportación
     - Generar las respuestas HTTP de descarga
     - Manejar la generación del nombre de archivo
     - Abstraer la complejidad de PhpSpreadsheet

### 3. **Clases de Exportación**
   
   #### `PilaHistorialExport`
   - **Archivo:** `app/Exports/PilaHistorialExport.php`
   - **Función:** Exportar historial completo de PILA
   - **Características:**
     - Logo de empresa (SVG o JPEG)
     - Colores corporativos consistentes
     - Tabla con alternancia de colores
     - Resumen con total de archivos
     - Encabezados profesionales
   
   #### `PilaRegistroExport`
   - **Archivo:** `app/Exports/PilaRegistroExport.php`
   - **Función:** Exportar un registro individual
   - **Características:**
     - Detalles completos del registro
     - Diseño de tarjeta/formulario
     - Logo y colores corporativos
     - Información de empresa

## Colores Corporativos Utilizados

```
PRIMARY:     #1a5276 (Azul Oscuro)
SECONDARY:   #2874a6 (Azul Medio)
ACCENT:      #2e86de (Azul Claro)
SUCCESS:     #27ae60 (Verde)
DANGER:      #c0392b (Rojo)
TEXT_DARK:   #2c3e50 (Gris Oscuro)
TEXT_LIGHT:  #ffffff (Blanco)
BG_LIGHT:    #f8f9fa (Fondo Gris Claro)
```

## Rutas Disponibles

### 1. Descargar Historial Completo
```
GET /pila/historial/excel/descargar
Nombre de ruta: pila.historial.excel
Permiso requerido: export_pila
Respuesta: Archivo Excel con toda la tabla
```

### 2. Descargar Registro Individual
```
GET /pila/export/registro/{id}
Nombre de ruta: pila.export.registro
Parámetros: id (ID del registro PILA)
Permiso requerido: export_pila
Respuesta: Archivo Excel con detalles del registro
```

### 3. Vista Previa del Historial (JSON)
```
GET /pila/export/preview
Nombre de ruta: pila.export.preview
Permiso requerido: view_pila
Respuesta: JSON con últimos 10 registros
```

## Uso en Vistas/Componentes

### Descargar Historial Completo
```blade
<a href="{{ route('pila.historial.excel') }}" 
   class="btn btn-success"
   title="Descargar todo el historial en Excel">
    <i class="bi bi-file-earmark-excel"></i> Descargar Historial
</a>
```

### Descargar Registro Individual
```blade
<a href="{{ route('pila.export.registro', ['id' => $registro->id]) }}" 
   class="btn btn-primary"
   title="Descargar este registro en Excel">
    <i class="bi bi-file-earmark-excel"></i> Descargar
</a>
```

## Características del Diseño

### Historial Completo
- **Encabezado:** Logo + Nombre de empresa + Fecha de generación
- **Tabla:** Filas alternadas con colores, bordes suaves
- **Resumen:** Total de archivos con fondo verde
- **Pie:** Información de empresa (NIT)

### Registro Individual
- **Formato:** Tarjeta/Formulario vertical
- **Detalles:** ID, Archivo, Periodo, Empleados, Fechas
- **Visual:** Etiquetas con color primario, valores con fondo claro
- **Pie:** Nota sobre generación automática

## Ventajas de la Nueva Estructura

✅ **Separación de responsabilidades:** Controlador → Servicio → Clase de Exportación
✅ **Reutilizable:** Las clases de exportación pueden usarse en otros contextos
✅ **Testeable:** Cada capa puede testearse independientemente
✅ **Mantenible:** Cambios en un lugar afectan toda la exportación
✅ **Escalable:** Fácil agregar nuevos formatos o tipos de exportación
✅ **Consistente:** Colores y estilos centralizados definidos

## Personalización

### Cambiar Colores Corporativos
En la clase correspondiente (`PilaHistorialExport` o `PilaRegistroExport`):

```php
private const COLOR_PRIMARY = 'FF1a5276'; // Cambiar este valor
```

### Agregar Nuevo Formato (Ej: PDF)
1. Crear nueva clase `PilaPdfExport extends` alguna clase base
2. Agregar método en `PilaExportService`: `exportarPdf()`
3. Agregar ruta en `routes/web.php`
4. Usar en vista

### Cambiar Logo
El sistema busca automáticamente en este orden:
1. `public/images/logo_nomitech.svg`
2. `public/images/logo nomitech.jpeg`
3. `public/images/logo_nomitech_blanco.svg`

Si deseas cambiar el orden o agregar nuevos logos, modifica el método `agregarLogo()` en las clases de exportación.

## Instalación/Setup

No se requiere instalación adicional. El sistema utiliza:
- **PhpOffice/PhpSpreadsheet** (ya incluido en el proyecto)
- **Carbon** (para manejo de fechas)
- **Eloquent** (para BD)

## Testing

Para probar las nuevas rutas:

```bash
# Descargar historial
curl -H "Authorization: Bearer {token}" \
     http://localhost/pila/historial/excel/descargar

# Descargar registro específico
curl -H "Authorization: Bearer {token}" \
     http://localhost/pila/export/registro/1
```

## Troubleshooting

### El archivo no descarga
- Verificar que el usuario tenga permiso `export_pila`
- Revisar logs en `storage/logs/laravel.log`

### Logo no aparece
- Verificar que exista en `public/images/`
- Usar ruta absoluta en el método `agregarLogo()`

### Errores de memoria
- Para muchos registros, considerar pagination
- O usar un job en background

## Notas Importantes

- Los archivos se generan en tiempo real (streaming)
- El nombre incluye NIT de empresa y timestamp
- Los permisos se validan automáticamente
- Los errores se manejan gracefully
