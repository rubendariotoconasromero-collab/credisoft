# CREDISOFT — Documentación Completa de Base de Datos

**Motor:** MySQL 5.7+  
**Base de datos:** `db_credisoft`  
**Framework:** Laravel 10+  
**Tipo de sistema:** Gestión de Créditos y Préstamos  
**Última actualización:** 2026-05-23

---

## Índice de Tablas

| # | Tabla | Categoría | Descripción |
|---|-------|-----------|-------------|
| 1 | [users](#1-users) | Autenticación | Usuarios del sistema |
| 2 | [rol](#2-rol) | Autenticación | Roles de acceso |
| 3 | [permiso](#3-permiso) | Autenticación | Permisos del sistema |
| 4 | [permiso_rol](#4-permiso_rol) | Autenticación | Pivot roles ↔ permisos |
| 5 | [password_reset_tokens](#5-password_reset_tokens) | Autenticación | Tokens de reseteo |
| 6 | [personal_access_tokens](#6-personal_access_tokens) | Autenticación | Tokens API (Sanctum) |
| 7 | [mi_empresa](#7-mi_empresa) | Empresa | Datos de la empresa |
| 8 | [socios](#8-socios) | Empresa | Socios/Accionistas |
| 9 | [actividades](#9-actividades) | Catálogo | Actividades económicas |
| 10 | [cliente](#10-cliente) | Clientes | Clientes del sistema |
| 11 | [codeudor](#11-codeudor) | Clientes | Codeudores/Avalistas |
| 12 | [solicitud_codeudor](#12-solicitud_codeudor) | Clientes | Pivot solicitudes ↔ codeudores |
| 13 | [direccion](#13-direccion) | Clientes | Direcciones de clientes/codeudores |
| 14 | [telefono](#14-telefono) | Clientes | Teléfonos de clientes/codeudores |
| 15 | [solicitud](#15-solicitud) | Créditos | Solicitudes de préstamo (**tabla central**) |
| 16 | [garantia](#16-garantia) | Créditos | Garantías/Prendas |
| 17 | [imagen](#17-imagen) | Créditos | Imágenes de garantías |
| 18 | [respaldo](#18-respaldo) | Créditos | Documentos de respaldo |
| 19 | [imagenes_respaldo](#19-imagenes_respaldo) | Créditos | Imágenes de respaldos |
| 20 | [plan_pago](#20-plan_pago) | Pagos | Plan de amortización |
| 21 | [cuota](#21-cuota) | Pagos | Cuotas del plan de pago |
| 22 | [pago](#22-pago) | Pagos | Pagos/amortizaciones |
| 23 | [pago_amortizacion](#23-pago_amortizacion) | Pagos | Detalle de amortizaciones |
| 24 | [pago_administrativo](#24-pago_administrativo) | Pagos | Comisiones administrativas |
| 25 | [desembolso](#25-desembolso) | Pagos | Desembolsos al cliente |
| 26 | [caja](#26-caja) | Caja/Bóveda | Control de caja diaria |
| 27 | [movimientos_caja](#27-movimientos_caja) | Caja/Bóveda | Movimientos de caja |
| 28 | [ingreso](#28-ingreso) | Caja/Bóveda | Ingresos clasificados |
| 29 | [egreso](#29-egreso) | Caja/Bóveda | Egresos clasificados |
| 30 | [motivo_ingreso](#30-motivo_ingreso) | Catálogo | Catálogo de motivos de ingreso |
| 31 | [motivo_gasto](#31-motivo_gasto) | Catálogo | Catálogo de motivos de gasto |
| 32 | [boveda](#32-boveda) | Caja/Bóveda | Bóveda centralizada |
| 33 | [movimientos_boveda](#33-movimientos_boveda) | Caja/Bóveda | Movimientos de bóveda |
| 34 | [transferencia_caja_boveda](#34-transferencia_caja_boveda) | Caja/Bóveda | Transferencias caja ↔ bóveda |
| 35 | [orden_pago_reprogramaciones](#35-orden_pago_reprogramaciones) | Auditoría | Órdenes de reprogramación |
| 36 | [solicitud_respaldo](#36-solicitud_respaldo) | Auditoría | Historial de solicitudes |
| 37 | [plan_pago_respaldo](#37-plan_pago_respaldo) | Auditoría | Historial de planes de pago |
| 38 | [cuota_respaldo](#38-cuota_respaldo) | Auditoría | Historial de cuotas |
| 39 | [failed_jobs](#39-failed_jobs) | Sistema | Jobs fallidos (Laravel) |

---

## Diagrama de Flujo Principal

```
CLIENTE ──────────────────────────────────────────────────────────────┐
    │                                                                  │
    ▼                                                                  │
SOLICITUD ──► GARANTIAS ──► IMAGEN                                    │
    │         RESPALDOS ──► IMAGENES_RESPALDO                         │
    │         CODEUDORES (pivot: solicitud_codeudor)                  │
    │                                                                  │
    ▼                                                                  │
PLAN_PAGO ─────────────────────────────────────────────────────────── ┘
    │
    ▼
CUOTA (1..N cuotas)
    │
    ▼
PAGO ──────────────────────► CAJA ──────────────► BOVEDA
PAGO_AMORTIZACION              │                     │
PAGO_ADMINISTRATIVO     MOVIMIENTOS_CAJA      MOVIMIENTOS_BOVEDA
DESEMBOLSO              INGRESO / EGRESO      TRANSFERENCIA_CAJA_BOVEDA

AUDITORÍA:
SOLICITUD_RESPALDO ──► PLAN_PAGO_RESPALDO ──► CUOTA_RESPALDO
ORDEN_PAGO_REPROGRAMACIONES
```

---

## Descripción Detallada por Tabla

---

### 1. `users`

**Descripción:** Usuarios del sistema que tienen acceso a la aplicación.  
**Modelo:** `App\Models\User`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `name` | varchar(255) | NO | — | Nombre de usuario (login único) |
| `personal` | varchar(255) | NO | — | Nombre personal/real del usuario |
| `email` | varchar(255) | SÍ | NULL | Email del usuario (único) |
| `email_verified_at` | timestamp | SÍ | NULL | Fecha de verificación de email |
| `password` | varchar(255) | NO | — | Contraseña hasheada (bcrypt) |
| `estado` | integer | NO | 1 | `1`=Activo, `0`=Inactivo |
| `id_rol` | bigint UNSIGNED | NO | — | FK → `rol.id` |
| `ci` | varchar(30) | SÍ | NULL | Cédula de identidad |
| `telefono` | varchar(20) | SÍ | NULL | Teléfono de contacto |
| `fecha_cambio_password` | date | SÍ | NULL | Fecha del último cambio de contraseña |
| `dias_vigencia` | integer | NO | 1 | Días de vigencia de la contraseña |
| `remember_token` | varchar(100) | SÍ | NULL | Token para "recordarme" |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Relaciones:**
- `rol()` → `belongsTo(Rol::class, 'id_rol')` — Rol asignado al usuario

**Índices:** `UNIQUE(name)`, `UNIQUE(email)`, `INDEX(id_rol)`

---

### 2. `rol`

**Descripción:** Roles disponibles en el sistema (Administrador, Cajero, Asesor, etc.).  
**Modelo:** `App\Models\Rol`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `nombre` | varchar(255) | NO | — | Nombre del rol |
| `estado` | integer | NO | 1 | `1`=Activo, `0`=Inactivo |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Relaciones:**
- `users()` → `hasMany(User::class, 'id_rol')`
- `permisos()` → `belongsToMany(Permiso::class, 'permiso_rol', 'id_rol', 'id_permiso')`

---

### 3. `permiso`

**Descripción:** Permisos del sistema que controlan el acceso a los módulos.  
**Modelo:** `App\Models\Permiso`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `nombre` | varchar(255) | NO | — | Nombre del permiso (clave del módulo) |
| `descripcion` | varchar(255) | NO | — | Descripción legible del permiso |
| `estado` | integer | NO | 1 | `1`=Activo, `0`=Inactivo |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Permisos disponibles:**

| nombre | Módulo que protege |
|--------|--------------------|
| `roles` | Gestión de roles |
| `usuarios` | Gestión de usuarios |
| `informacion` | Información de la empresa |
| `paneladministracion` | Panel de administración |
| `planpagos` | Planes de pago |
| `listadopagos` | Listado de pagos |
| `controlcaja` | Control de caja |
| `cliente` | Gestión de clientes |
| `codeudores` | Gestión de codeudores |
| `solicitudprestamos` | Solicitudes de préstamos |
| `reportes` | Reportes del sistema |
| `consultasfinancieras` | Consultas financieras |
| `socios` | Gestión de socios |
| `historial_clientes_creditos` | Historial de clientes y créditos |

**Relaciones:**
- `roles()` → `belongsToMany(Rol::class, 'permiso_rol', 'id_permiso', 'id_rol')`

---

### 4. `permiso_rol`

**Descripción:** Tabla pivot de la relación muchos-a-muchos entre roles y permisos.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id_permiso` | bigint UNSIGNED | FK → `permiso.id` (parte de PK compuesta) |
| `id_rol` | bigint UNSIGNED | FK → `rol.id` (parte de PK compuesta) |
| `created_at` | timestamp | Fecha de asignación |
| `updated_at` | timestamp | Fecha de actualización |

**Clave primaria compuesta:** `(id_permiso, id_rol)`

---

### 5. `password_reset_tokens`

**Descripción:** Tokens temporales para el proceso de restablecimiento de contraseña.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `email` | varchar(255) | PK — Email del usuario |
| `token` | varchar(255) | Token generado (hash) |
| `created_at` | timestamp | Fecha de generación |

---

### 6. `personal_access_tokens`

**Descripción:** Tokens de acceso personal para la API REST (Laravel Sanctum).

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `tokenable_type` | varchar(255) | NO | — | Tipo de modelo (polimórfico) |
| `tokenable_id` | bigint UNSIGNED | NO | — | ID del modelo |
| `name` | varchar(255) | NO | — | Nombre descriptivo del token |
| `token` | varchar(64) | NO | — | Token hasheado (único) |
| `abilities` | text | SÍ | NULL | JSON con habilidades permitidas |
| `last_used_at` | timestamp | SÍ | NULL | Último uso |
| `expires_at` | timestamp | SÍ | NULL | Fecha de expiración |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Índices:** `UNIQUE(token)`, `INDEX(tokenable_type, tokenable_id)`

---

### 7. `mi_empresa`

**Descripción:** Datos de la empresa que opera el sistema.  
**Modelo:** `App\Models\MiEmpresa`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | bigint UNSIGNED | Clave primaria |
| `nombre` | varchar(255) | Razón social de la empresa |
| `nit` | varchar(255) | NIT o número tributario |
| `direccion` | varchar(255) | Dirección principal |
| `telefono` | varchar(255) | Teléfono corporativo |
| `email` | varchar(255) | Email corporativo |
| `logo` | varchar(255) | Ruta al archivo de logo |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

---

### 8. `socios`

**Descripción:** Socios o accionistas del negocio.  
**Modelo:** `App\Models\Socio`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `nombres` | varchar(100) | NO | — | Nombres del socio |
| `apellidos` | varchar(100) | SÍ | NULL | Apellidos del socio |
| `ci` | varchar(20) | SÍ | NULL | Cédula de identidad (único) |
| `telefono` | varchar(20) | SÍ | NULL | Teléfono de contacto |
| `direccion` | varchar(255) | SÍ | NULL | Dirección del socio |
| `email` | varchar(100) | SÍ | NULL | Email del socio |
| `estado` | boolean | NO | true | `true`=Activo, `false`=Inactivo |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Índices:** `UNIQUE(ci)`

---

### 9. `actividades`

**Descripción:** Catálogo de actividades económicas u ocupaciones de clientes/codeudores.  
**Modelo:** `App\Models\Actividad`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `nombre` | text | NO | — | Nombre de la actividad/ocupación |
| `estado` | integer | NO | 1 | `1`=Activo, `0`=Inactivo |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

> **Nota:** Esta tabla se puebla mediante un script SQL (`script_poblacion_actividades.sql`).

---

### 10. `cliente`

**Descripción:** Información personal y financiera de los clientes que solicitan créditos.  
**Modelo:** `App\Models\Cliente`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `nombre` | varchar(255) | NO | — | Nombre completo |
| `fecha_nacimiento` | date | NO | — | Fecha de nacimiento |
| `ci` | varchar(255) | NO | — | Cédula de identidad |
| `lugar_expedicion` | varchar(255) | NO | — | Lugar de expedición del CI |
| `sexo` | varchar(255) | NO | — | `Masculino` / `Femenino` |
| `estado_civil` | varchar(255) | NO | — | `Soltero` / `Casado` / `Viudo` / etc. |
| `actividad` | varchar(255) | NO | — | Ocupación/profesión (referencia a `actividades.nombre`) |
| `vivienda` | varchar(255) | NO | — | `Propia` / `Alquilada` / `Familiar` / etc. |
| `imagen` | varchar(255) | SÍ | NULL | Ruta al archivo de foto del cliente |
| `ingreso_mensual` | float | NO | — | Ingresos mensuales declarados |
| `estado` | integer | NO | 1 | `1`=Activo, `0`=Inactivo |
| `created_at` | timestamp | SÍ | NULL | Fecha de registro |
| `updated_at` | timestamp | SÍ | NULL | Fecha de última modificación |

**Relaciones:**
- `solicitudes()` → `hasMany(Solicitud::class, 'id_cliente')` — Créditos del cliente
- `telefonos()` → `hasMany(Telefono::class, 'id_cliente')` — Teléfonos registrados
- `direcciones()` → `hasMany(Direccion::class, 'id_cliente')` — Direcciones registradas

---

### 11. `codeudor`

**Descripción:** Codeudores o avalistas que garantizan los créditos de clientes.  
**Modelo:** `App\Models\Codeudor` *(sin timestamps)*

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `nombre` | varchar(255) | NO | — | Nombre completo |
| `fecha_nacimiento` | date | NO | — | Fecha de nacimiento |
| `ci` | varchar(255) | NO | — | Cédula de identidad |
| `lugar_expedicion` | varchar(255) | NO | — | Lugar de expedición del CI |
| `sexo` | varchar(255) | NO | — | `Masculino` / `Femenino` |
| `estado_civil` | varchar(255) | NO | — | Estado civil |
| `actividad` | varchar(255) | NO | — | Actividad económica |
| `vivienda` | varchar(255) | NO | — | Tipo de vivienda |
| `imagen` | varchar(255) | SÍ | NULL | Ruta al archivo de foto |
| `ingreso_mensual` | float | NO | — | Ingresos mensuales |
| `tipo` | varchar(50) | NO | — | Tipo de codeudor |
| `estado` | integer | NO | 1 | `1`=Activo, `0`=Inactivo |

**Relaciones:**
- `solicitudes()` → `belongsToMany(Solicitud::class, 'solicitud_codeudor', 'id_codeudor', 'id_solicitud')`
- `telefonos()` → `hasMany(Telefono::class, 'id_codeudor')`
- `direcciones()` → `hasMany(Direccion::class, 'id_codeudor')`

---

### 12. `solicitud_codeudor`

**Descripción:** Tabla pivot de la relación muchos-a-muchos entre solicitudes y codeudores.  
**Modelo:** `App\Models\SolicitudCodeudor`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id_codeudor` | bigint UNSIGNED | FK → `codeudor.id` (parte de PK compuesta) |
| `id_solicitud` | bigint UNSIGNED | FK → `solicitud.id` (parte de PK compuesta) |

**Clave primaria compuesta:** `(id_codeudor, id_solicitud)`

---

### 13. `direccion`

**Descripción:** Direcciones de clientes y codeudores (domicilio, trabajo, etc.).  
**Modelo:** `App\Models\Direccion`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `tipo` | varchar(255) | NO | — | `Domicilio` / `Trabajo` / `Otro` |
| `departamento` | varchar(255) | NO | — | Departamento o región |
| `ciudad` | varchar(255) | SÍ | NULL | Ciudad |
| `zona` | varchar(255) | SÍ | NULL | Zona o barrio |
| `descripcion` | varchar(255) | NO | — | Calle, avenida, número, piso |
| `lat` | decimal(10,8) | SÍ | NULL | Latitud GPS |
| `lng` | decimal(11,8) | SÍ | NULL | Longitud GPS |
| `referencia` | varchar(255) | SÍ | NULL | Punto de referencia visual |
| `id_cliente` | bigint UNSIGNED | SÍ | NULL | FK → `cliente.id` |
| `id_codeudor` | bigint UNSIGNED | SÍ | NULL | FK → `codeudor.id` |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Relaciones:**
- `cliente()` → `belongsTo(Cliente::class, 'id_cliente')`
- `codeudor()` → `belongsTo(Codeudor::class, 'id_codeudor')`

> **Nota:** Solo una de las FK (`id_cliente` o `id_codeudor`) está poblada en cada registro.

---

### 14. `telefono`

**Descripción:** Números de teléfono de clientes y codeudores (pueden incluir referencias familiares).  
**Modelo:** `App\Models\Telefono`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `tipo` | varchar(255) | NO | — | `Celular` / `Fijo` / `Trabajo` |
| `numero` | varchar(255) | NO | — | Número de teléfono |
| `observacion` | varchar(255) | SÍ | NULL | Observación adicional |
| `nombre` | varchar(100) | SÍ | NULL | Nombre del titular del teléfono |
| `apellidos` | varchar(100) | SÍ | NULL | Apellidos del titular |
| `relacion` | varchar(100) | SÍ | NULL | Relación con el cliente (Esposo/a, Padre, etc.) |
| `id_cliente` | bigint UNSIGNED | SÍ | NULL | FK → `cliente.id` |
| `id_codeudor` | bigint UNSIGNED | SÍ | NULL | FK → `codeudor.id` |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Relaciones:**
- `cliente()` → `belongsTo(Cliente::class, 'id_cliente')`
- `codeudor()` → `belongsTo(Codeudor::class, 'id_codeudor')`

---

### 15. `solicitud`

**Descripción:** Solicitudes de préstamo o crédito. Es la **tabla central** del sistema.  
**Modelo:** `App\Models\Solicitud`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `importe_solicitud` | double(8,2) | NO | — | Monto solicitado por el cliente |
| `moneda` | varchar(255) | NO | — | Moneda (`BOB`, `USD`, etc.) |
| `lapso_capital` | varchar(255) | NO | — | Frecuencia de pago (`mensual`, `bimestral`, `trimestral`, etc.) |
| `nro_cuotas` | integer | NO | — | Número total de cuotas del crédito |
| `tasa` | decimal(11,2) | NO | — | Tasa de interés aplicada |
| `fecha` | date | SÍ | NULL | Fecha de registro de la solicitud |
| `fecha_desembolso` | date | NO | — | Fecha en que se entregó el dinero |
| `fecha_primera_cuota` | date | NO | — | Fecha de vencimiento de la primera cuota |
| `destino_prestamo` | varchar(255) | NO | — | Destino declarado del préstamo |
| `tipo_garantia` | varchar(255) | NO | — | `Prenda` / `Hipoteca` / `Personal` / etc. |
| `tipo_desembolso` | varchar(255) | NO | — | `Efectivo` / `Transferencia` / etc. |
| `estado` | integer | NO | 1 | Ver tabla de estados abajo |
| `id_cliente` | bigint UNSIGNED | NO | — | FK → `cliente.id` |
| `id_usuario` | bigint UNSIGNED | NO | — | FK → `users.id` (asesor que registró) |
| `monto_pago_adm` | decimal(10,2) | SÍ | 0.00 | Comisión/gasto administrativo a cobrar |
| `tipo_tasa` | varchar(50) | NO | `amortizable` | `amortizable` / `sobre_saldos` / etc. |
| `observacion` | varchar(250) | SÍ | `''` | Observaciones libres |
| `tipo_solicitud` | varchar(250) | NO | `Nuevo` | `Nuevo` / `Reprogramación` / `Refinanciamiento` |
| `cantidad_reprogramaciones` | integer | NO | 0 | Número de veces reprogramado |
| `cantidad_refinanciamientos` | integer | NO | 0 | Número de veces refinanciado |
| `monto_refinanciamiento` | integer | SÍ | 0 | Monto involucrado en refinanciamiento |
| `desembolso` | integer | SÍ | 0 | Flag de control de desembolso |
| `id_solicitud_origen` | bigint UNSIGNED | SÍ | NULL | FK → `solicitud.id` (solicitud original si es reprogramación) |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Estados de solicitud (`estado`):**

| Valor | Significado |
|-------|-------------|
| `1` | Nuevo / En revisión |
| `2` | Aprobado |
| `3` | Desembolsado |
| `4` | Rechazado |
| `5` | Cancelado |

**Relaciones:**
- `cliente()` → `belongsTo(Cliente::class, 'id_cliente')`
- `usuario()` → `belongsTo(User::class, 'id_usuario')`
- `garantias()` → `hasMany(Garantia::class, 'id_solicitud')`
- `codeudores()` → `belongsToMany(Codeudor::class, 'solicitud_codeudor', 'id_solicitud', 'id_codeudor')`
- `respaldos()` → `hasMany(Respaldo::class, 'id_solicitud')`
- `planPago()` → `hasOne(PlanPago::class, 'id_solicitud')`
- `ordenPagoReprogramacion()` → `hasOne(OrdenPagoReprogramacion::class, 'id_solicitud_nueva')`

**Índices:** `INDEX(id_cliente)`, `INDEX(id_usuario)`, `INDEX(id_solicitud_origen)`

---

### 16. `garantia`

**Descripción:** Garantías o prendas asociadas a cada solicitud de crédito.  
**Modelo:** `App\Models\Garantia`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `descripcion` | varchar(255) | NO | — | Descripción de la garantía |
| `id_solicitud` | bigint UNSIGNED | NO | — | FK → `solicitud.id` |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Relaciones:**
- `solicitud()` → `belongsTo(Solicitud::class, 'id_solicitud')`
- `imagenes()` → `hasMany(Imagen::class, 'id_garantia')`

---

### 17. `imagen`

**Descripción:** Archivos de imagen asociados a garantías.  
**Modelo:** `App\Models\Imagen`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `imagen` | varchar(255) | NO | — | Nombre/ruta del archivo de imagen |
| `id_garantia` | bigint UNSIGNED | NO | — | FK → `garantia.id` |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Relaciones:**
- `garantia()` → `belongsTo(Garantia::class, 'id_garantia')`

---

### 18. `respaldo`

**Descripción:** Documentos de respaldo o evidencia asociados a solicitudes de crédito.  
**Modelo:** `App\Models\Respaldo`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `descripcion` | varchar(255) | NO | — | Descripción del documento |
| `imagen` | varchar(255) | NO | — | Ruta del archivo |
| `id_solicitud` | bigint UNSIGNED | NO | — | FK → `solicitud.id` |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Relaciones:**
- `solicitud()` → `belongsTo(Solicitud::class, 'id_solicitud')`
- `lista_imagenes()` → `hasMany(ImagenRespaldo::class, 'id_respaldo')`

---

### 19. `imagenes_respaldo`

**Descripción:** Imágenes adicionales vinculadas a un respaldo de solicitud.  
**Modelo:** `App\Models\ImagenRespaldo`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `id_respaldo` | bigint UNSIGNED | NO | — | FK → `respaldo.id` (con CASCADE DELETE) |
| `imagen` | varchar(255) | NO | — | Ruta del archivo de imagen |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Relaciones:**
- `respaldo()` → `belongsTo(Respaldo::class, 'id_respaldo')`

---

### 20. `plan_pago`

**Descripción:** Plan de amortización de un crédito. Define el calendario completo de pagos.  
**Modelo:** `App\Models\PlanPago`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `fecha_inicio` | date | NO | — | Fecha de inicio del plan |
| `fecha_fin` | date | NO | — | Fecha de finalización del plan |
| `total_pagar` | decimal(12,2) | NO | — | Total a pagar (capital + intereses) |
| `estado` | integer | NO | 1 | Estado del plan |
| `desembolso` | integer | NO | 1 | `1`=Desembolsado, `0`=No desembolsado |
| `pago_administrativo` | integer | NO | 1 | `1`=Pagado, `0`=Pendiente |
| `id_solicitud` | bigint UNSIGNED | NO | — | FK → `solicitud.id` |
| `id_plan_aux` | bigint | NO | 0 | ID de plan auxiliar (para reprogramaciones) |
| `moneda` | varchar(255) | NO | `''` | Moneda del plan |
| `lapso_capital` | varchar(255) | NO | `''` | Frecuencia de cuotas |
| `nro_cuotas` | integer | NO | 0 | Total de cuotas |
| `tasa` | integer | NO | 0 | Tasa de interés |
| `fecha_ultima_amortizacion` | date | SÍ | NULL | Fecha del último pago recibido |
| `saldo_pendiente` | decimal(15,2) | SÍ | NULL | Saldo de capital pendiente |
| `fecha_registro` | date | SÍ | NULL | Fecha de registro del plan |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Relaciones:**
- `solicitud()` → `belongsTo(Solicitud::class, 'id_solicitud')`
- `cuotas()` → `hasMany(Cuota::class, 'id_plan_pago')`

---

### 21. `cuota`

**Descripción:** Cuotas individuales generadas por un plan de pago. Cada registro representa un pago periódico programado.  
**Modelo:** `App\Models\Cuota`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `numero` | integer | NO | — | Número de cuota (1, 2, 3...) |
| `fecha` | date | NO | — | Fecha de vencimiento de la cuota |
| `capital` | double(8,2) | NO | — | Monto de capital en esta cuota |
| `interes` | double(8,2) | NO | — | Monto de interés en esta cuota |
| `saldo_capital` | double(8,2) | NO | — | Saldo de capital tras pagar esta cuota |
| `ahorro` | double(8,2) | SÍ | NULL | Monto de ahorro (si aplica) |
| `seguro` | double(8,2) | SÍ | NULL | Monto de seguro (si aplica) |
| `total` | double(8,2) | NO | — | Total a pagar en esta cuota |
| `estado` | integer | NO | 1 | Ver tabla de estados abajo |
| `amortizado` | integer | NO | 0 | `1`=Amortizado completamente, `0`=No |
| `id_plan_pago` | bigint UNSIGNED | NO | — | FK → `plan_pago.id` |
| `capital_pagado` | decimal(10,2) | NO | 0 | Capital ya pagado en esta cuota |
| `interes_pagado` | decimal(10,2) | NO | 0 | Interés ya pagado |
| `mora_pagada` | decimal(10,2) | NO | 0 | Mora ya pagada |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Estados de cuota (`estado`):**

| Valor | Significado |
|-------|-------------|
| `0` | Anulada |
| `1` | Pendiente |
| `2` | Pagada completamente |
| `3` | Pago parcial |

**Relaciones:**
- `planPago()` → `belongsTo(PlanPago::class, 'id_plan_pago')`

---

### 22. `pago`

**Descripción:** Registro de pagos realizados por el cliente sobre una cuota específica.  
**Modelo:** `App\Models\Pago`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `codigo_transaccion` | varchar(50) | SÍ | NULL | Código único de transacción (indexado) |
| `fecha_pago` | date | NO | — | Fecha en que se realizó el pago |
| `monto_pago` | decimal(12,2) | NO | — | Monto total recibido |
| `estado` | integer | NO | 1 | Estado del pago |
| `dias_retrasados` | integer | SÍ | NULL | Días de atraso al momento del pago |
| `multa_dia` | decimal(12,2) | SÍ | NULL | Monto de multa por día |
| `multa_total` | decimal(12,2) | SÍ | NULL | Total de multa/mora cobrada |
| `monto_condonado` | decimal(12,2) | NO | 0.00 | Monto total perdonado |
| `motivo_condonacion` | varchar(255) | NO | `''` | Razón de la condonación |
| `forma_pago` | varchar(255) | NO | — | `Efectivo` / `Transferencia` / etc. |
| `imagen` | varchar(255) | NO | `''` | Ruta al comprobante de pago |
| `id_usuario` | bigint UNSIGNED | NO | — | FK → `users.id` (cajero) |
| `id_cuota` | bigint UNSIGNED | NO | — | FK → `cuota.id` |
| `id_caja` | bigint UNSIGNED | NO | — | FK → `caja.id` |
| `monto_cuota` | decimal(10,2) | SÍ | 0.00 | Monto original de la cuota al momento del pago |
| `pago_capital` | decimal(12,2) | NO | 0 | Porción del pago destinada a capital |
| `pago_interes` | decimal(12,2) | NO | 0 | Porción destinada a interés |
| `pago_mora` | decimal(12,2) | NO | 0 | Porción destinada a mora |
| `monto_condonado_interes` | decimal(12,2) | NO | 0 | Interés condonado en este pago |
| `monto_condonado_mora` | decimal(12,2) | NO | 0 | Mora condonada en este pago |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Relaciones:**
- `usuario()` → `belongsTo(User::class, 'id_usuario')`
- `cuota()` → `belongsTo(Cuota::class, 'id_cuota')`
- `caja()` → `belongsTo(Caja::class, 'id_caja')`

**Índices:** `INDEX(codigo_transaccion)`, `INDEX(id_cuota)`, `INDEX(id_caja)`, `INDEX(id_usuario)`

---

### 23. `pago_amortizacion`

**Descripción:** Registro detallado de cada amortización, equivalente al comprobante oficial de pago.  
**Modelo:** `App\Models\PagoAmortizacion`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `fecha` | date | NO | — | Fecha de pago |
| `monto_pago` | decimal(12,2) | NO | — | Monto total pagado |
| `capital_pagado` | decimal(12,2) | NO | — | Capital amortizado |
| `interes_pagado` | decimal(12,2) | NO | — | Interés pagado |
| `multa_pagada` | decimal(12,2) | NO | — | Mora/multa pagada |
| `saldo_pendiente` | decimal(12,2) | NO | — | Saldo de capital tras el pago |
| `forma_pago` | varchar(255) | NO | — | Forma de pago |
| `id_caja` | bigint UNSIGNED | NO | — | FK → `caja.id` |
| `id_plan_pago` | bigint UNSIGNED | NO | — | FK → `plan_pago.id` |
| `id_cuota` | bigint UNSIGNED | NO | — | FK → `cuota.id` |
| `id_plan_ligado` | integer | SÍ | NULL | ID de plan relacionado |
| `total_seguro` | decimal(12,2) | SÍ | 0 | Total de seguro pagado |
| `estado` | integer | SÍ | 0 | Estado del registro |
| `monto_desembolso` | decimal(10,2) | SÍ | 0.00 | Monto desembolsado asociado |
| `tipo` | varchar(255) | SÍ | `amortizacion` | `amortizacion` / `desembolso` |
| `fecha_desembolso` | date | SÍ | NULL | Fecha de desembolso (si aplica) |
| `numero_cuota` | integer | NO | 0 | Número de cuota amortizada |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Relaciones:**
- `caja()` → `belongsTo(Caja::class, 'id_caja')`
- `planPago()` → `belongsTo(PlanPago::class, 'id_plan_pago')`
- `cuota()` → `belongsTo(Cuota::class, 'id_cuota')`
- `usuario()` → `belongsTo(User::class, 'id_usuario')`

---

### 24. `pago_administrativo`

**Descripción:** Registro de pagos de comisiones o gastos administrativos del crédito.  
**Modelo:** `App\Models\PagoAdministrativo` *(sin timestamps)*

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `monto` | decimal(12,2) | NO | — | Monto del pago administrativo |
| `fecha` | datetime | NO | CURRENT | Fecha/hora del pago |
| `estado` | integer | NO | 0 | Estado del pago |
| `descripcion` | varchar(255) | NO | — | Descripción del concepto |
| `id_plan_pago` | bigint UNSIGNED | NO | — | FK → `plan_pago.id` |
| `id_usuario` | bigint UNSIGNED | NO | — | FK → `users.id` |
| `id_caja` | bigint UNSIGNED | NO | — | FK → `caja.id` |

**Relaciones:**
- `planPago()` → `belongsTo(PlanPago::class, 'id_plan_pago')`
- `usuario()` → `belongsTo(User::class, 'id_usuario')`
- `caja()` → `belongsTo(Caja::class, 'id_caja')`

---

### 25. `desembolso`

**Descripción:** Registro de los desembolsos de dinero al cliente.  
**Modelo:** `App\Models\Desembolso` *(sin timestamps)*

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `monto` | decimal(12,2) | NO | — | Monto entregado al cliente |
| `fecha` | datetime | NO | CURRENT | Fecha/hora del desembolso |
| `estado` | integer | NO | 0 | Estado |
| `id_plan_pago` | bigint UNSIGNED | NO | — | FK → `plan_pago.id` |
| `id_usuario` | bigint UNSIGNED | NO | — | FK → `users.id` |
| `id_caja` | bigint UNSIGNED | NO | — | FK → `caja.id` (caja origen del dinero) |

**Relaciones:**
- `planPago()` → `belongsTo(PlanPago::class, 'id_plan_pago')`
- `usuario()` → `belongsTo(User::class, 'id_usuario')`
- `caja()` → `belongsTo(Caja::class, 'id_caja')`

---

### 26. `caja`

**Descripción:** Control de sesiones de caja. Cada apertura genera un registro que agrupa todos los movimientos del turno.  
**Modelo:** `App\Models\Caja`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `fechahora_apertura` | datetime | NO | — | Fecha y hora de apertura de caja |
| `fechahora_cierre` | datetime | SÍ | NULL | Fecha y hora de cierre |
| `monto_inicial` | decimal(12,2) | NO | — | Efectivo inicial al abrir |
| `monto_final` | decimal(12,2) | SÍ | NULL | Efectivo final al cerrar |
| `efectivo_total` | decimal(12,2) | SÍ | NULL | Total efectivo del turno |
| `deposito_total` | decimal(12,2) | SÍ | NULL | Total depósitos del turno |
| `efectivo_venta` | decimal(12,2) | SÍ | NULL | Efectivo por cobros/ventas |
| `deposito_venta` | decimal(12,2) | SÍ | NULL | Depósitos por cobros/ventas |
| `efectivo_gasto` | decimal(12,2) | SÍ | NULL | Efectivo saliente por gastos |
| `deposito_gasto` | decimal(12,2) | SÍ | NULL | Depósitos por gastos |
| `total_ingreso` | decimal(12,2) | SÍ | NULL | Total ingresos del turno |
| `total_egreso` | decimal(12,2) | SÍ | NULL | Total egresos del turno |
| `diferencia` | decimal(12,2) | SÍ | NULL | Diferencia (sobrante o faltante) |
| `estado` | integer | NO | 1 | `1`=Abierta, `0`=Cerrada |
| `id_usuario` | bigint UNSIGNED | NO | — | FK → `users.id` |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Relaciones:**
- `usuario()` → `belongsTo(User::class, 'id_usuario')`
- `pagos()` → `hasMany(Pago::class, 'id_caja')`

---

### 27. `movimientos_caja`

**Descripción:** Detalle de cada movimiento de dinero dentro de una sesión de caja.  
**Modelo:** `App\Models\MovimientoCaja`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `tipo_movimiento` | varchar(255) | NO | — | `Ingreso` / `Egreso` / `Apertura` |
| `descripcion` | varchar(255) | NO | — | Descripción del movimiento |
| `monto` | decimal(12,2) | NO | — | Monto involucrado |
| `fecha` | datetime | NO | — | Fecha y hora del movimiento |
| `id_caja` | bigint UNSIGNED | NO | — | FK → `caja.id` |
| `id_usuario` | bigint UNSIGNED | NO | — | FK → `users.id` |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Relaciones:**
- `caja()` → `belongsTo(Caja::class, 'id_caja')`
- `usuario()` → `belongsTo(User::class, 'id_usuario')`

---

### 28. `ingreso`

**Descripción:** Ingresos de dinero clasificados dentro de una sesión de caja.  
**Modelo:** `App\Models\Ingreso`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `monto` | decimal(12,2) | NO | — | Monto del ingreso |
| `descripcion` | varchar(255) | NO | — | Descripción |
| `estado` | varchar(255) | NO | `'1'` | Estado del registro |
| `fecha` | date | NO | — | Fecha del ingreso |
| `id_usuario` | bigint UNSIGNED | NO | — | FK → `users.id` |
| `id_caja` | bigint UNSIGNED | NO | — | FK → `caja.id` |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Relaciones:**
- `usuario()` → `belongsTo(User::class, 'id_usuario')`
- `caja()` → `belongsTo(Caja::class, 'id_caja')`

---

### 29. `egreso`

**Descripción:** Egresos de dinero clasificados dentro de una sesión de caja.  
**Modelo:** `App\Models\Egreso`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `monto` | decimal(12,2) | NO | — | Monto del egreso |
| `descripcion` | varchar(255) | NO | — | Descripción |
| `estado` | varchar(255) | NO | `'1'` | Estado del registro |
| `fecha` | date | NO | — | Fecha del egreso |
| `id_usuario` | bigint UNSIGNED | NO | — | FK → `users.id` |
| `id_caja` | bigint UNSIGNED | NO | — | FK → `caja.id` |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Relaciones:**
- `usuario()` → `belongsTo(User::class, 'id_usuario')`
- `caja()` → `belongsTo(Caja::class, 'id_caja')`

---

### 30. `motivo_ingreso`

**Descripción:** Catálogo de motivos o razones para registrar ingresos en caja o bóveda.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `nombre` | varchar(255) | NO | — | Nombre del motivo |
| `tipo` | varchar(20) | NO | `caja` | `caja` / `boveda` |
| `estado` | integer | NO | 0 | `1`=Activo, `0`=Inactivo |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Datos de catálogo:**

| tipo | nombre |
|------|--------|
| `caja` | Cobro de cuota |
| `caja` | Transferencia desde Bóveda |
| `caja` | Ingreso por mora |
| `caja` | otro |
| `boveda` | Aporte de capital |
| `boveda` | Recuperación de préstamo |
| `boveda` | Transferencia desde Caja |
| `boveda` | Rendimientos financieros |
| `boveda` | otro |

---

### 31. `motivo_gasto`

**Descripción:** Catálogo de motivos o razones para registrar egresos en caja o bóveda.

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `nombre` | varchar(255) | NO | — | Nombre del motivo |
| `tipo` | varchar(20) | NO | `caja` | `caja` / `boveda` |
| `estado` | integer | NO | 0 | `1`=Activo, `0`=Inactivo |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Datos de catálogo:**

| tipo | nombre |
|------|--------|
| `caja` | Desembolso de préstamo |
| `caja` | Gasto administrativo |
| `caja` | Transferencia a Bóveda |
| `caja` | Pago de servicios |
| `caja` | otro |
| `boveda` | Pago de dividendos |
| `boveda` | Transferencia a Caja |
| `boveda` | Gastos de operación central |
| `boveda` | Capitalización |
| `boveda` | otro |

---

### 32. `boveda`

**Descripción:** Bóveda o caja fuerte centralizada de la empresa donde se concentra el capital.  
**Modelo:** `App\Models\Boveda`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `saldo_actual` | decimal(12,2) | NO | 0.00 | Saldo disponible en bóveda |
| `fecha_apertura` | datetime | NO | — | Fecha y hora de apertura |
| `id_usuario` | bigint UNSIGNED | SÍ | NULL | FK → `users.id` (responsable) |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

---

### 33. `movimientos_boveda`

**Descripción:** Registro de todos los movimientos de entrada y salida en la bóveda.  
**Modelo:** `App\Models\MovimientoBoveda`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `tipo_movimiento` | varchar(255) | NO | — | `Ingreso` / `Salida` |
| `monto` | decimal(12,2) | NO | — | Monto involucrado |
| `descripcion` | varchar(255) | NO | — | Descripción del movimiento |
| `fecha` | datetime | NO | — | Fecha y hora del movimiento |
| `id_boveda` | bigint UNSIGNED | NO | — | FK → `boveda.id` |
| `id_usuario` | bigint UNSIGNED | NO | — | FK → `users.id` |
| `id_socio` | bigint UNSIGNED | SÍ | NULL | FK → `socios.id` (beneficiario, si aplica) |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Relaciones:**
- `boveda()` → `belongsTo(Boveda::class, 'id_boveda')`
- `usuario()` → `belongsTo(User::class, 'id_usuario')`

---

### 34. `transferencia_caja_boveda`

**Descripción:** Registro de transferencias de dinero entre la caja del turno y la bóveda central.  
**Modelo:** `App\Models\TransferenciaCajaBoveda`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `tipo_transferencia` | varchar(255) | NO | — | `boveda_a_caja` / `caja_a_boveda` |
| `descripcion` | varchar(255) | NO | — | Descripción de la transferencia |
| `monto` | decimal(12,2) | NO | — | Monto transferido |
| `fecha` | datetime | NO | — | Fecha y hora |
| `id_boveda` | bigint UNSIGNED | NO | — | FK → `boveda.id` |
| `id_caja` | bigint UNSIGNED | NO | — | FK → `caja.id` |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Relaciones:**
- `boveda()` → `belongsTo(Boveda::class, 'id_boveda')`
- `caja()` → `belongsTo(Caja::class, 'id_caja')`

---

### 35. `orden_pago_reprogramaciones`

**Descripción:** Órdenes de pago generadas durante el proceso de reprogramación de créditos. Controla el cobro de intereses y moras acumuladas antes de activar un nuevo plan.  
**Modelo:** `App\Models\OrdenPagoReprogramacion`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `id_solicitud_nueva` | bigint UNSIGNED | NO | — | FK → `solicitud.id` (nueva solicitud reprogramada) |
| `id_plan_pago_origen` | bigint UNSIGNED | NO | — | FK → `plan_pago.id` (plan original a reprogramar) |
| `monto_interes_calculado` | decimal(10,2) | NO | 0 | Interés pendiente calculado |
| `monto_mora_calculado` | decimal(10,2) | NO | 0 | Mora acumulada calculada |
| `se_condono_interes` | boolean | NO | false | Se condonó el interés |
| `monto_condonado_interes` | decimal(10,2) | NO | 0 | Monto de interés condonado |
| `se_condono_mora` | boolean | NO | false | Se condonó la mora |
| `monto_condonado_mora` | decimal(10,2) | NO | 0 | Monto de mora condonada |
| `motivo_condonacion` | varchar(255) | SÍ | NULL | Razón de la condonación |
| `total_a_pagar` | decimal(10,2) | NO | — | Total efectivo a cobrar al cliente |
| `ids_cuotas_afectadas` | json | SÍ | NULL | Array JSON con IDs de cuotas involucradas |
| `estado` | tinyint | NO | 0 | Ver tabla de estados abajo |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Estados de orden de reprogramación (`estado`):**

| Valor | Significado |
|-------|-------------|
| `0` | No disponible (reprogramación aún no aprobada) |
| `1` | Por pagar (reprogramación aprobada, cliente debe pagar) |
| `2` | Pagado (dinero ingresó a caja) |
| `3` | Anulado (reprogramación rechazada) |

**Relaciones:**
- `solicitud()` → `belongsTo(Solicitud::class, 'id_solicitud_nueva')`
- `planPagoOrigen()` → `belongsTo(PlanPago::class, 'id_plan_pago_origen')`

---

### 36. `solicitud_respaldo`

**Descripción:** Tabla de auditoría que guarda el histórico de cambios en solicitudes de crédito.  
**Modelo:** `App\Models\SolicitudRespaldo`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `importe_solicitud` | float | SÍ | NULL | Monto (copia del estado) |
| `moneda` | varchar(255) | SÍ | NULL | Moneda |
| `lapso_capital` | varchar(255) | SÍ | NULL | Lapso |
| `nro_cuotas` | integer | SÍ | NULL | Número de cuotas |
| `tasa` | integer | SÍ | NULL | Tasa |
| `fecha` | date | SÍ | NULL | Fecha solicitud |
| `fecha_desembolso` | date | SÍ | NULL | Fecha desembolso |
| `fecha_primera_cuota` | date | SÍ | NULL | Fecha primera cuota |
| `destino_prestamo` | varchar(255) | SÍ | NULL | Destino |
| `tipo_garantia` | varchar(255) | SÍ | NULL | Tipo de garantía |
| `tipo_desembolso` | varchar(255) | SÍ | NULL | Tipo de desembolso |
| `tipo_tasa` | varchar(255) | NO | `amortizable` | Tipo de tasa |
| `monto_pago_adm` | decimal(10,2) | NO | 0 | Pago administrativo |
| `estado` | integer | NO | 1 | Estado |
| `id_cliente` | bigint UNSIGNED | SÍ | NULL | FK → `cliente.id` |
| `id_usuario` | bigint UNSIGNED | SÍ | NULL | FK → `users.id` (creador original) |
| `solicitud_id` | bigint UNSIGNED | NO | — | ID de la solicitud original |
| `accion` | varchar(255) | NO | — | `insert` / `update` / `delete` |
| `usuario_accion` | bigint UNSIGNED | SÍ | NULL | FK → `users.id` (quien hizo el cambio) |
| `created_at` | timestamp | SÍ | NULL | Fecha del cambio |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Relaciones:**
- `solicitudOriginal()` → `belongsTo(Solicitud::class, 'solicitud_id')`
- `cliente()` → `belongsTo(Cliente::class, 'id_cliente')`
- `usuarioCreador()` → `belongsTo(User::class, 'id_usuario')`
- `usuarioAccion()` → `belongsTo(User::class, 'usuario_accion')`

---

### 37. `plan_pago_respaldo`

**Descripción:** Tabla de auditoría que guarda el histórico de cambios en planes de pago.  
**Modelo:** `App\Models\PlanPagoRespaldo`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `fecha_inicio` | date | SÍ | NULL | Fecha inicio (copia) |
| `fecha_ultima_amortizacion` | date | SÍ | NULL | Última amortización |
| `fecha_fin` | date | SÍ | NULL | Fecha fin |
| `total_pagar` | decimal(12,2) | SÍ | NULL | Total a pagar |
| `saldo_pendiente` | decimal(12,2) | NO | 0 | Saldo pendiente |
| `moneda` | varchar(255) | SÍ | NULL | Moneda |
| `lapso_capital` | varchar(255) | SÍ | NULL | Lapso |
| `nro_cuotas` | integer | SÍ | NULL | Número de cuotas |
| `tasa` | integer | SÍ | NULL | Tasa |
| `tipo_tasa` | varchar(255) | NO | `amortizable` | Tipo de tasa |
| `estado` | integer | NO | 1 | Estado |
| `desembolso` | integer | NO | 1 | Desembolso |
| `pago_administrativo` | integer | NO | 1 | Pago administrativo |
| `id_solicitud` | bigint UNSIGNED | SÍ | NULL | Solicitud original |
| `id_plan_aux` | bigint | NO | 0 | Plan auxiliar |
| `solicitud_respaldo_id` | bigint UNSIGNED | NO | — | FK → `solicitud_respaldo.id` |
| `plan_pago_original_id` | bigint UNSIGNED | NO | — | ID del plan original |
| `accion` | varchar(255) | NO | — | `create` / `update` / `delete` |
| `usuario_accion` | bigint UNSIGNED | SÍ | NULL | FK → `users.id` |
| `ip_address` | varchar(45) | SÍ | NULL | IP del usuario |
| `user_agent` | text | SÍ | NULL | Navegador del usuario |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Relaciones:**
- `solicitudRespaldo()` → `belongsTo(SolicitudRespaldo::class, 'solicitud_respaldo_id')`
- `planPagoOriginal()` → `belongsTo(PlanPago::class, 'plan_pago_original_id')`
- `usuarioAccion()` → `belongsTo(User::class, 'usuario_accion')`

---

### 38. `cuota_respaldo`

**Descripción:** Tabla de auditoría que guarda el histórico de cambios en cuotas individuales.  
**Modelo:** `App\Models\CuotaRespaldo`

| Columna | Tipo | Nulo | Default | Descripción |
|---------|------|------|---------|-------------|
| `id` | bigint UNSIGNED | NO | auto | Clave primaria |
| `numero` | integer | SÍ | NULL | Número de cuota (copia) |
| `fecha` | date | SÍ | NULL | Fecha vencimiento |
| `capital` | decimal(12,2) | SÍ | NULL | Capital |
| `interes` | decimal(12,2) | SÍ | NULL | Interés |
| `saldo_capital` | decimal(12,2) | SÍ | NULL | Saldo capital |
| `ahorro` | decimal(12,2) | SÍ | NULL | Ahorro |
| `seguro` | decimal(12,2) | SÍ | NULL | Seguro |
| `total` | decimal(12,2) | SÍ | NULL | Total |
| `estado` | integer | NO | 1 | Estado |
| `amortizado` | integer | NO | 0 | Amortizado |
| `plan_pago_respaldo_id` | bigint UNSIGNED | NO | — | FK → `plan_pago_respaldo.id` |
| `cuota_original_id` | bigint UNSIGNED | NO | — | ID de la cuota original |
| `accion` | varchar(255) | NO | — | `create` / `update` / `delete` |
| `usuario_accion` | bigint UNSIGNED | SÍ | NULL | FK → `users.id` |
| `ip_address` | varchar(45) | SÍ | NULL | IP |
| `user_agent` | text | SÍ | NULL | User agent |
| `created_at` | timestamp | SÍ | NULL | Fecha de creación |
| `updated_at` | timestamp | SÍ | NULL | Fecha de actualización |

**Relaciones:**
- `planPagoRespaldo()` → `belongsTo(PlanPagoRespaldo::class, 'plan_pago_respaldo_id')`
- `cuotaOriginal()` → `belongsTo(Cuota::class, 'cuota_original_id')`
- `usuarioAccion()` → `belongsTo(User::class, 'usuario_accion')`

---

### 39. `failed_jobs`

**Descripción:** Tabla del sistema Laravel para almacenar los trabajos de cola que fallaron.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | bigint UNSIGNED | Clave primaria |
| `uuid` | varchar(255) | UUID único del job (UNIQUE) |
| `connection` | text | Conexión de cola utilizada |
| `queue` | text | Nombre de la cola |
| `payload` | longtext | Datos del job (JSON serializado) |
| `exception` | longtext | Stack trace del error |
| `failed_at` | timestamp | Fecha y hora del fallo |

---

## Consultas SQL de Referencia para Reportes

### Créditos activos por cliente
```sql
SELECT
    c.nombre AS cliente,
    c.ci,
    s.id AS id_solicitud,
    s.importe_solicitud,
    s.moneda,
    s.nro_cuotas,
    s.tasa,
    s.tipo_tasa,
    s.fecha_desembolso,
    pp.total_pagar,
    pp.saldo_pendiente,
    pp.fecha_fin
FROM solicitud s
JOIN cliente c ON s.id_cliente = c.id
JOIN plan_pago pp ON pp.id_solicitud = s.id
WHERE s.estado = 3  -- Desembolsado
ORDER BY c.nombre;
```

### Cuotas vencidas sin pagar (mora)
```sql
SELECT
    c.nombre AS cliente,
    s.id AS id_solicitud,
    cu.numero AS nro_cuota,
    cu.fecha AS fecha_vencimiento,
    DATEDIFF(CURDATE(), cu.fecha) AS dias_mora,
    cu.total AS monto_cuota,
    (cu.total - cu.capital_pagado - cu.interes_pagado) AS saldo_pendiente
FROM cuota cu
JOIN plan_pago pp ON cu.id_plan_pago = pp.id
JOIN solicitud s ON pp.id_solicitud = s.id
JOIN cliente c ON s.id_cliente = c.id
WHERE cu.estado IN (1, 3)  -- Pendiente o parcial
  AND cu.fecha < CURDATE()
ORDER BY dias_mora DESC;
```

### Pagos recibidos en un período
```sql
SELECT
    p.fecha_pago,
    c.nombre AS cliente,
    s.id AS id_solicitud,
    cu.numero AS nro_cuota,
    p.monto_pago,
    p.pago_capital,
    p.pago_interes,
    p.pago_mora,
    p.forma_pago,
    u.personal AS cajero,
    cj.fechahora_apertura AS sesion_caja
FROM pago p
JOIN cuota cu ON p.id_cuota = cu.id
JOIN plan_pago pp ON cu.id_plan_pago = pp.id
JOIN solicitud s ON pp.id_solicitud = s.id
JOIN cliente c ON s.id_cliente = c.id
JOIN users u ON p.id_usuario = u.id
JOIN caja cj ON p.id_caja = cj.id
WHERE p.fecha_pago BETWEEN :fecha_inicio AND :fecha_fin
ORDER BY p.fecha_pago;
```

### Cartera total y saldos pendientes por asesor
```sql
SELECT
    u.personal AS asesor,
    COUNT(DISTINCT s.id) AS total_creditos,
    SUM(s.importe_solicitud) AS cartera_total,
    SUM(pp.saldo_pendiente) AS saldo_pendiente_total
FROM solicitud s
JOIN users u ON s.id_usuario = u.id
JOIN plan_pago pp ON pp.id_solicitud = s.id
WHERE s.estado = 3
GROUP BY u.id, u.personal
ORDER BY saldo_pendiente_total DESC;
```

### Resumen de caja por período
```sql
SELECT
    DATE(cj.fechahora_apertura) AS fecha,
    u.personal AS cajero,
    cj.monto_inicial,
    cj.total_ingreso,
    cj.total_egreso,
    cj.monto_final,
    cj.diferencia,
    CASE cj.estado WHEN 1 THEN 'Abierta' ELSE 'Cerrada' END AS estado
FROM caja cj
JOIN users u ON cj.id_usuario = u.id
WHERE DATE(cj.fechahora_apertura) BETWEEN :fecha_inicio AND :fecha_fin
ORDER BY cj.fechahora_apertura;
```

### Clientes con múltiples créditos (historial)
```sql
SELECT
    c.id,
    c.nombre,
    c.ci,
    COUNT(s.id) AS total_creditos,
    SUM(s.importe_solicitud) AS total_prestado,
    MAX(s.fecha_desembolso) AS ultimo_desembolso
FROM cliente c
JOIN solicitud s ON s.id_cliente = c.id
WHERE s.estado IN (2, 3)
GROUP BY c.id, c.nombre, c.ci
HAVING total_creditos > 1
ORDER BY total_creditos DESC;
```

### Estado de reprogramaciones
```sql
SELECT
    c.nombre AS cliente,
    s_orig.id AS id_solicitud_original,
    s_nueva.id AS id_solicitud_nueva,
    s_nueva.tipo_solicitud,
    opr.monto_interes_calculado,
    opr.monto_mora_calculado,
    opr.monto_condonado_interes,
    opr.monto_condonado_mora,
    opr.total_a_pagar,
    CASE opr.estado
        WHEN 0 THEN 'No disponible'
        WHEN 1 THEN 'Por pagar'
        WHEN 2 THEN 'Pagado'
        WHEN 3 THEN 'Anulado'
    END AS estado_orden
FROM orden_pago_reprogramaciones opr
JOIN solicitud s_nueva ON opr.id_solicitud_nueva = s_nueva.id
JOIN solicitud s_orig ON s_nueva.id_solicitud_origen = s_orig.id
JOIN cliente c ON s_nueva.id_cliente = c.id
ORDER BY opr.created_at DESC;
```

---

## Relaciones entre Tablas (Mapa Visual)

```
users ──────────── rol ──────────── permiso_rol ──── permiso
  │
  ├── caja ─────── movimientos_caja
  │     │          ingreso
  │     │          egreso
  │     │
  │     ├── pago ──────────────────── cuota ─── plan_pago ─── solicitud
  │     │   pago_amortizacion                                      │
  │     │   pago_administrativo                                    │
  │     │   desembolso                                             │
  │     │                                                          │
  │     └── transferencia_caja_boveda ─── boveda ─── movimientos_boveda
  │                                                       │
  │                                                    socios
  │
  └── solicitud ──────────────────────────────────────────────────┐
          │                                                        │
          ├── cliente ──── telefono                                │
          │         │      direccion                               │
          │         │                                              │
          │         └── solicitud_codeudor ─── codeudor ─── telefono
          │                                              └── direccion
          │
          ├── garantia ─── imagen
          ├── respaldo ─── imagenes_respaldo
          ├── plan_pago ── cuota
          │
          └── orden_pago_reprogramaciones

AUDITORÍA:
solicitud_respaldo ─── plan_pago_respaldo ─── cuota_respaldo
```

---

## Notas Importantes para Reportes

### Identificar créditos activos
Un crédito está activo cuando `solicitud.estado = 3` (Desembolsado) y existen cuotas con `estado IN (1, 3)` (Pendiente o Parcial).

### Calcular saldo pendiente real
El saldo real de un crédito se obtiene desde `plan_pago.saldo_pendiente` o sumando `cuota.capital - cuota.capital_pagado` de las cuotas no pagadas.

### Identificar cuotas en mora
Una cuota está en mora cuando `cuota.fecha < CURDATE()` y `cuota.estado IN (1, 3)`.

### Calcular mora diaria
La mora se calcula sobre el capital pendiente a la tasa pactada (`solicitud.tasa`) dividida entre los días del período.

### Tipos de operaciones en `pago_amortizacion.tipo`
- `amortizacion` → Pago normal de cuota
- `desembolso` → Registro de entrega de dinero al cliente

### Tablas de auditoría
Las tablas `*_respaldo` guardan snapshots del estado de los registros antes de cada cambio. El campo `accion` indica qué operación se realizó (`insert`, `update`, `delete`).

### Condonaciones
Las condonaciones se registran en `pago.monto_condonado_interes` y `pago.monto_condonado_mora`. En reprogramaciones, se registran en `orden_pago_reprogramaciones`.
