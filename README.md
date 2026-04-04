# Nomitech Payroll - Sistema de Gestión de Nómina

Bienvenido a **Nomitech Payroll**, una solución integral para la gestión de nómina, contratos y beneficios sociales. Este documento proporciona las instrucciones necesarias para configurar el entorno de desarrollo local y entender los componentes críticos de automatización.

## 🚀 Requisitos del Sistema

- **PHP**: ^8.2
- **Composer**: Administrador de dependencias de PHP.
- **MySQL/MariaDB**: Motor de base de datos.
- **Node.js & NPM**: Para la compilación de activos (Vite).
- **Servidor Web**: Apache (XAMPP sugerido), Nginx o el servidor integrado de PHP.

---

## 🛠️ Instalación y Configuración

1.  **Clonar el repositorio:**
    ```bash
    git clone [url-del-repositorio]
    cd Nomitech_Payroll
    ```

2.  **Instalar dependencias de PHP:**
    ```bash
    composer install
    ```

3.  **Instalar dependencias de Frontend:**
    ```bash
    npm install
    ```

4.  **Configurar variables de entorno:**
    Copia el archivo de ejemplo y genera la clave de aplicación:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5.  **Configurar la base de datos:**
    Asegúrate de crear una base de datos en MySQL (por defecto `nomitech_uno`) y ajusta las credenciales en el `.env`. Luego ejecuta las migraciones y seeders:
    ```bash
    php artisan migrate --seed
    ```

6.  **Compilar activos:**
    ```bash
    npm run dev
    ```

---

## 📧 Configuración de Correo Electrónico

El sistema envía correos automáticos (ej. credenciales de acceso para nuevos empleados). Configura las siguientes variables en tu `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_usuario
MAIL_PASSWORD=tu_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@nomitech.com"
MAIL_FROM_NAME="${APP_NAME}"
```

> [!TIP]
> Para desarrollo local, puedes usar `MAIL_MAILER=log` para ver los correos en `storage/logs/laravel.log` o un servicio como Mailtrap.

---

## ⚙️ Componentes de Automatización (Workers y Colas)

Para que el software funcione plenamente, es **crítico** mantener activos los siguientes procesos en segundo plano.

### 1. Procesamiento de Colas (Emails)
**Comando:** `php artisan queue:work`

**¿Por qué es necesario? (Justificación):**
-   **Experiencia de Usuario (Explosividad):** El envío de un correo a través de servidores externos (SMTP) puede tardar varios segundos. Si se hiciera de forma síncrona, el usuario que registra un empleado vería la aplicación "congelada" hasta que el correo se envíe.
-   **Fiabilidad:** Si el servidor de correo falla temporalmente, Laravel reintentará el envío automáticamente sin perder la información ni afectar la transacción de la base de datos.
-   **Desacoplamiento:** Separa la lógica de negocio (registro) de la lógica de comunicación (email).

### 2. Programador de Tareas (Scheduler / Pagos Automáticos)
**Comando:** `php artisan schedule:work`

**¿Por qué es necesario? (Justificación):**
-   **Cierre Automático de Periodos:** El sistema cierra los periodos de nómina (`periods:auto-close`) y liquida provisiones (`provisions:auto-liquidate`) en fechas específicas programadas por la empresa.
-   **Consistencia:** Garantiza que los procesos recurrentes se ejecuten exactamente cuando deben, sin depender de que un humano haga clic en un botón.
-   **Precisión Financiera:** Los pagos programados y la generación de provisiones se ejecutan de forma masiva y controlada, evitando errores de cálculo manual.

---

## 🚦 Primeros Pasos en el Software

1.  Inicia el servidor local: `php artisan serve`.
2.  Abre una terminal adicional para las colas: `php artisan queue:work`.
3.  Abre una terminal adicional para el scheduler: `php artisan schedule:work`.
4.  Accede a `http://localhost:8000` con las credenciales generadas por el seeder (ver `DatabaseSeeder.php`).

---

## 📄 Licencia

Este software es propiedad de Nomitech y está licenciado bajo los términos definidos en el contrato de prestación de servicios.
