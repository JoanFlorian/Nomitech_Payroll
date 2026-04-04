---
description: Generar Diccionario de Datos Modular
---

### Propósito
Este flujo de trabajo se utiliza para generar el Diccionario de Datos Técnico del sistema Nomitech Payroll de forma modular y automatizada.

### Reglas Estrictas (¡NO OLVIDAR!)
1. **Formato Obligatorio de Tabla:**
   Cada tabla de la base de datos DEBE documentarse SIEMPRE con el siguiente formato Markdown exacto. NO debes cambiar el nombre de las columnas ni omitir ninguna:

| Campo / Atributo | Tipo de dato | Longitud | Descripción         | Valores permitidos | Obligatorio | Ejemplo    |
| ---------------- | ------------ | -------- | ------------------- | ------------------ | ----------- | ---------- |
| id               | INT          | 10       | Identificador único | Autonumérico       | Sí          | 1          |
| (Ejemplo de row) | (VARCHAR)    | (255)    | (Texto descriptivo) | (Rango o reglas)   | (Sí/No)     | (Valor)    |

2. **Modularidad:**
   El diccionario se guarda en la carpeta `docs/diccionario_datos/` subdividido por módulo.
   Módulos sugeridos:
   - Seguridad y Core (`users`, `roles`, `permissions`, `empresas`, etc.)
   - Catálogos Maestros (`paises`, `departamentos`, `ciudades`, `eps`, `bancos`, etc.)
   - Gestión de Empleados (`empleados`, `contratos`, `salarios`, etc.)
   - Novedades e Incidencias (`novedades`, `licencias`, `horas_extras`, etc.)
   - Nómina Operativa y Electrónica (`periodos_liquidacion`, `pagos`, `nomina_electronica`, etc.)

3. **Proceso a seguir:**
   a. El usuario o el asistente propone qué módulo documentar.
   b. El asistente DEBE generar un script de Laravel (Artisan console command) en `/routes/console.php` o similar, o correr una consulta SQL (usando `php artisan tinker`) para mapear la estructura real de la base de datos (tipos, longitud, llaves y nulabilidad).
   c. El asistente genera el archivo Markdown `.md` en la ruta correspondiente (ej. `docs/diccionario_datos/Seguridad/users.md`).
   d. El asistente rellena automáticamente con el script las siguientes columnas: "Campo / Atributo", "Tipo de dato", "Longitud" y "Obligatorio".
   e. El asistente intentará inferir la "Descripción" basada en el nombre del campo, y dejará en blanco o sugerirá los "Valores permitidos" y el "Ejemplo".
   f. Posteriormente, el asistente y el usuario refinan los campos manuales.
