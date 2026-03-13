# Pruebas de Caja Blanca - Módulo de Registro de Usuarios (`RegistroUsuarios.php`)

Enfoque: Cobertura de código, flujos lógicos y condiciones decisivas.

---

## Decisión D1 – RegistroUsuarios (Resolución de Empresa)

### 1.1 Descripción
Validar la lógica de fallback para determinar el ID de la empresa (`resolveCompanyId`), priorizando la sesión, luego el usuario autenticado y finalmente la base de datos.
**Expresión evaluada:**
`D1.1 = (sessionCompanyId > 0)` | `D1.2 = (authUser && method_exists(authUser, 'empresa'))` | `D1.3 = (fallbackCompanyId > 0)`

### 1.2 Condiciones de ejecución
- Entorno Laravel con acceso a sesión y base de datos.
- Modelo `Empresa` con datos de prueba.

### 1.3 Casos de prueba

| ID Caso | Método/Función | Tipo de Cobertura | Flujo Lógico / Camino | Resultado Técnico Esperado | Resultado Real | Estado |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **CB-01** | `resolveCompanyId()` | Cobertura de Decisiones | `sessionCompanyId > 0` es Verdadero. | Retorna ID de la sesión inmediatamente. | Ejecutado | Satisfactorio |
| **CB-02** | `resolveCompanyId()` | Cobertura de Decisiones | `sessionCompanyId <= 0` AND Usuario posee empresa vinculada. | Retorna ID de la empresa vinculada al usuario. | Ejecutado | Satisfactorio |
| **CB-03** | `resolveCompanyId()` | Cobertura de Decisiones | Session/Auth fallan; hay registros en tabla `Empresa`. | Retorna el primer ID de la tabla `empresa`. | Ejecutado | Satisfactorio |
| **CB-04** | `resolveCompanyId()` | Cobertura de Decisiones | Todo falla (Sin sesión, sin usuario, sin registros DB). | Retorna `null`. | Ejecutado | Satisfactorio |

---

## Decisión D2 – RegistroUsuarios (Contrato Indefinido)

### 2.1 Descripción
Validar la detección de contratos de tipo "indefinido" mediante la normalización de strings para asegurar que no se asigne fecha de fin.
**Expresión evaluada:**
`D2 = Str::contains($nombreNormalizado, 'indefinid')`

### 2.2 Condiciones de ejecución
- Tabla `tipo_contrato` poblada con variaciones ("Término Indefinido", "Indefinida", "Fijo").

### 2.3 Casos de prueba

| ID Caso | Entrada (`nombre`) | Resultado Esperado | Resultado Real | Estado |
| :--- | :--- | :--- | :--- | :--- |
| **CP-D2-01** | "Término Indefinido" | `true` | Ejecutado | Satisfactorio |
| **CP-D2-02** | "CONTRATO INDEFINIDO" | `true` | Ejecutado | Satisfactorio |
| **CP-D2-03** | "Término Fijo" | `false` | Ejecutado | Satisfactorio |
| **CP-D2-04** | `null` o vacío | `false` | Ejecutado | Satisfactorio |

---

## Decisión D3 – RegistroUsuarios (Integridad de Sesión)

### 3.1 Descripción
Validar que el proceso final (`storeFinal`) solo proceda si los pasos 1 y 2 de la sesión están presentes.
**Expresión evaluada:**
`D3 = (empty($step1) || empty($step2))`

### 3.2 Casos de prueba

| ID Caso | Método/Función | Tipo de Cobertura | Flujo Lógico / Camino | Resultado Técnico Esperado | Resultado Real | Estado |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **CB-05** | `storeFinal()` | Cobertura de Condiciones | `step1` es null O `step2` es null. | Retorna JSON error 400 "Sesión expirada". | Ejecutado | Satisfactorio |
| **CB-06** | `storeFinal()` | Cobertura de Condiciones | Ambos pasos existen en sesión. | Continúa a la transacción de base de datos. | Ejecutado | Satisfactorio |

---

## Decisión D4 – RegistroUsuarios (Creación de Cuenta Bancaria)

### 4.1 Descripción
Validar la creación condicional del registro en la tabla `cuenta` únicamente si se proporcionan datos bancarios en el paso final.
**Expresión evaluada:**
`D4 = (!empty($tipoCuenta) && !empty($numeroCuenta))`

### 4.2 Casos de prueba (Formato Condicional)

**CP-D4-01**
- **Entrada:** `tipo_cuenta` = 'Ahorros', `numero_cuenta` = '123456'
- **Resultado esperado:** Se ejecuta `Cuenta::updateOrCreate`.
- **Evaluación:** Ejecutado – Satisfactorio.

**CP-D4-02**
- **Entrada:** `tipo_cuenta` = '', `numero_cuenta` = '123456'
- **Resultado esperado:** No se crea registro de cuenta.
- **Evaluación:** Ejecutado – Satisfactorio.

**CP-D4-03**
- **Entrada:** `tipo_cuenta` = 'Corriente', `numero_cuenta` = null
- **Resultado esperado:** No se crea registro de cuenta.
- **Evaluación:** Ejecutado – Satisfactorio.

---

## Conclusión
Se ha alcanzado una cobertura de caminos críticos para el flujo de registro, asegurando la integridad de los datos laborales y la persistencia de la información bancaria bajo condiciones variables.
