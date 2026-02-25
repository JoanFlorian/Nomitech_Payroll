# Arquitectura de Pruebas E2E para Formularios Dinámicos con Laravel Dusk

Este directorio contiene la suite de pruebas funcionales (E2E) para el panel de **SuperAdmin Actualizaciones**.

## Estructura de Directorios

- `tests/Browser/Pages/`: Implementación del patrón *Page Object*. `SuperAdminActualizacionesPage.php` centraliza la URL y los selectores base (`@alert-success`, botones agregar).
- `tests/Browser/Components/`: Componentes reutilizables. `DynamicFormComponent.php` maneja la interacción (rellenar inputs dinámicos, esperar el render) con el Modal de Agregar/Editar.
- `tests/Browser/DataProviders/`: Contiene `ModuleDataProvider.php` que enlista flujos parametrizables de datos (Happy Path, Validaciones) para minimizar duplicación de tests.
- `tests/Browser/Support/`: `FormDescriptor.php` estructura la definición de keys para cada módulo.
- `tests/Browser/Traits/`: `AuthenticatesSuperAdmin.php` aisla el setup de la autenticación administrativa.

## Flujo de Trabajo CI/CD y Categorización

Las pruebas han sido etiquetadas con grupos PHPUnit (`@group smoke`, `@group validation`) para flexibilizar la ejecución continua.

### 1. Ejecución Completa (Local/Nightly)
```bash
php artisan dusk
```
Esto correrá todas las pruebas. Ojo: La validación asíncrona puede tomar tiempo; asegúrese de que `.env.dusk.local` apunte a una DB de prueba (`nomitech_dusk_testing`).

### 2. Ejecución Smoke de CI/CD (Rápida)
Ideal para verificar que el "Happy path" de los CRUD no esté roto después de un PR.
```bash
php artisan dusk --group=smoke
```

## Preparativos (Headless y Base de Datos)
La clase `BaseDuskTestCase.php` ya está configurada con `--headless=new` y demás `ChromeOptions` vitales para ambientes Linux sin UI u optimización de memoria en GitHub Actions / GitLab CI.

Al iniciar el framework llama a `DatabaseMigrations`, haciendo que la DB quede fresca entre clases.

> **Importante:** La ejecución de estas pruebas requiere ChromeDriver actualizado acorde a la versión de Google Chrome instalada en el sistema host. En caso de desface:
```bash
php artisan dusk:chrome-driver --require-system-version
```

## Selectores Robustos en Blade
La filosofía adoptada ha sido agregar a las vistas Blade dependientes (`principal.blade.php`, modales) el atributo `@dusk("...")`.
Dusk es capaz de encontrar estos elementos omitiendo IDs u otras clases CSS mutables, reduciendo radicalmente el grado de *flakiness* natural de E2E tests.

Ej: `<button @dusk("btn-guardar-agregar")>...</button>` lo busca como `$browser->click('@btn-guardar-agregar')`.

## Extensibilidad a Futuro

Para dotar de paralelización nativa a los tests E2E y dividir el tiempo de ejecución en sistemas CI (Paratest / `php artisan dusk --processes=4`), el uso de `DatabaseMigrations` en `BaseDuskTestCase` y el asilamiento de cada test asegurará que no existan colisiones de datos. 

Simplemente se debe asegurar que el gestor de paralelismo genere bases de datos sufijadas por el _TestToken_ y el _Connection driver_ en el entorno lo soporte (ver documentación [Laravel Paratest Dusk](https://laravel.com/docs/11.x/dusk)).
