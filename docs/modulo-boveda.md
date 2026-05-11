# Documentación — Módulo Bóveda

**Proyecto:** Credisoft  
**Fecha de revisión:** 2026-05-10  
**Estado:** Activo — con bugs identificados pendientes de corrección

---

## 1. Descripción General

El módulo de **Bóveda** gestiona el fondo central de efectivo de la cooperativa/institución financiera. Actúa como el repositorio principal de dinero del que se nutren las cajas y del que se desembolsan créditos. Registra todos los movimientos de entrada y salida con trazabilidad de usuario.

### Responsabilidades del módulo

- Apertura inicial de la bóveda (registro único)
- Registro de ingresos manuales (aportes de capital, transferencias, etc.)
- Registro de retiros manuales (pago de dividendos, gastos, etc.)
- Consulta y filtrado de movimientos con paginación
- Visualización del saldo actual y datos de apertura
- Recibe movimientos automáticos desde `CajaController` al desembolsar créditos

---

## 2. Arquitectura del Módulo

### 2.1 Estructura de archivos

```
Frontend
└── resources/js/components/frmBoveda.vue       — Componente principal

Backend
├── app/Http/Controllers/BovedaController.php   — Controlador CRUD
├── app/Models/Boveda.php                       — Modelo tabla boveda
├── app/Models/MovimientoBoveda.php             — Modelo tabla movimientos_boveda
└── app/Models/TransferenciaCajaBoveda.php      — Modelo tabla transferencia_caja_boveda

Base de datos
├── boveda                                      — Registro de apertura + saldo actual
├── movimientos_boveda                          — Historial de entradas/salidas
└── transferencia_caja_boveda                   — Transferencias entre caja y bóveda

Rutas (routes/web.php)
├── GET  /boveda                     → BovedaController@indexBoveda
├── GET  /get_boveda                 → BovedaController@getBoveda
├── GET  /get_movimientos_boveda     → BovedaController@getMovimientosBoveda
├── POST /ingresar_boveda            → BovedaController@ingresarBoveda
├── POST /retirar_boveda             → BovedaController@retirarBoveda
├── POST /aperturar_boveda           → BovedaController@aperturarBoveda
└── GET  /verificar-boveda           → CajaController@verificarBoveda
```

### 2.2 Esquema de base de datos

**`boveda`**
| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | Auto-incremental |
| saldo_actual | decimal(12,2) | Saldo vigente en la bóveda |
| fecha_apertura | datetime | Fecha y hora de apertura |
| id_usuario | bigint FK → users | Usuario que aperturó (nullable) |
| created_at / updated_at | timestamp | Timestamps de Eloquent |

**`movimientos_boveda`**
| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | Auto-incremental |
| tipo_movimiento | string | `'ingreso'` o `'salida'` |
| monto | decimal(12,2) | Monto del movimiento |
| descripcion | string | Concepto/motivo |
| fecha | datetime | Fecha del movimiento |
| id_boveda | bigint FK → boveda | Bóveda afectada |
| id_usuario | bigint FK → users | Usuario que registró |
| id_socio | bigint FK → socios (nullable) | Socio vinculado (aportes/dividendos) |

**`transferencia_caja_boveda`**
| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | Auto-incremental |
| tipo_transferencia | string | `'boveda_a_caja'` o `'caja_a_boveda'` |
| descripcion | string | Concepto |
| monto | decimal(12,2) | Monto transferido |
| fecha | datetime | Fecha de la operación |
| id_boveda | bigint FK → boveda | Bóveda involucrada |
| id_caja | bigint FK → caja | Caja involucrada |

---

## 3. Flujo de Negocio

### 3.1 Apertura de bóveda

1. Al montar el componente se llama `GET /get_boveda`.
2. Si no existe ningún registro (`id_boveda === 0`), se muestra el estado "Bóveda Cerrada" y el botón **Aperturar**.
3. El usuario hace clic → `POST /aperturar_boveda` → inserta registro en `boveda` con `saldo_actual = 0`.
4. El componente refresca el estado con `getBoveda()`.

### 3.2 Ingreso de fondos

1. Usuario abre modal **Añadir Fondos** → selecciona monto y concepto.
2. Si el concepto es `"Aporte de capital"`, debe seleccionar el socio aportante.
3. `POST /ingresar_boveda` → inserta en `movimientos_boveda` (tipo `'ingreso'`) + actualiza `saldo_actual` via `DB::raw('saldo_actual + monto')`.
4. El componente refresca movimientos y saldo.

### 3.3 Retiro de fondos

1. Usuario abre modal **Retirar Fondos** → selecciona monto y concepto.
2. Si el concepto es `"Pago de dividendos"`, debe seleccionar el socio beneficiario.
3. El frontend valida que `saldo_actual - monto >= 0`.
4. `POST /retirar_boveda` → inserta en `movimientos_boveda` (tipo `'salida'`) + actualiza `saldo_actual` via `DB::raw('saldo_actual - monto')`.

### 3.4 Desembolso de créditos (flujo externo)

- `CajaController@actualizarDesembolso` realiza una salida automática de la bóveda al desembolsar un crédito.
- No pasa por `frmBoveda` — es un movimiento directo desde el módulo de Caja.

### 3.5 Filtrado de movimientos

- Filtros disponibles: tipo de movimiento (todos/ingreso/salida), rango de fechas.
- Los totales de Ingresos y Salidas mostrados en pantalla reflejan el filtro activo, no los totales absolutos históricos.
- Paginación: 40 registros por página.

---

## 4. Bugs y Fallas de Lógica Identificadas

### 🔴 Críticos

---

#### BUG-01 — Backend no valida saldo antes de retirar
**Archivo:** `BovedaController.php` → `retirarBoveda()`

El backend descuenta el monto sin verificar si `saldo_actual >= monto`. Solo el frontend valida esto. Cualquier request directo a `POST /retirar_boveda` puede dejar el saldo en negativo.

```php
// ACTUAL — sin validación de saldo
DB::table('boveda')->where('id', $id_boveda)->update([
    'saldo_actual' => DB::raw('saldo_actual - ' . $request->monto)
]);

// CORRECCIÓN
$boveda = DB::table('boveda')->where('id', $id_boveda)->first();
if ($boveda->saldo_actual < $request->monto) {
    return response()->json(['message' => 'Saldo insuficiente en bóveda'], 422);
}
```

---

#### BUG-02 — Sin validación de input numérico en backend (riesgo SQL)
**Archivo:** `BovedaController.php` → `ingresarBoveda()`, `retirarBoveda()`
**También:** `CajaController.php` → `actualizarDesembolso()`

`$request->monto` se concatena directamente en `DB::raw()` sin validar que sea numérico. Un valor malformado puede causar error SQL o inyección.

```php
// ACTUAL — vulnerable
DB::raw('saldo_actual + ' . $request->monto)

// CORRECCIÓN — agregar validación al inicio del método
$request->validate([
    'monto'      => 'required|numeric|min:0.01',
    'descripcion' => 'required|string|max:255',
]);
```

---

#### BUG-03 — `Exception` sin namespace en catch — rollback puede no ejecutarse
**Archivo:** `BovedaController.php` → `ingresarBoveda()`, `retirarBoveda()`

Dentro del namespace `App\Http\Controllers`, `Exception` sin `\` puede no resolver a la clase global `\Exception`. Si ocurre un error, el `DB::rollback()` no se ejecuta y la transacción queda abierta.

```php
// ACTUAL — incorrecto
} catch(Exception $e) {
    DB::rollback();
}

// CORRECCIÓN
} catch(\Exception $e) {
    DB::rollback();
}
```

> Nota: `aperturarBoveda()` ya usa `\Exception` correctamente.

---

#### BUG-04 — Ingreso/retiro no retorna respuesta HTTP
**Archivo:** `BovedaController.php` → `ingresarBoveda()`, `retirarBoveda()`

Ambos métodos no retornan nada (`void`). Si ocurre una excepción no capturada, el frontend no puede distinguirla de un éxito (recibe `200` vacío). La falta de respuesta estructurada impide un manejo de errores correcto.

```php
// CORRECCIÓN — añadir al final del bloque try
DB::commit();
return response()->json(['message' => 'Operación registrada correctamente'], 200);

// Y en el catch
return response()->json(['message' => 'Error al registrar la operación', 'error' => $e->getMessage()], 500);
```

---

### 🟡 Importantes

---

#### BUG-05 — Múltiples bóvedas posibles (sin unicidad)
**Archivo:** `BovedaController.php` → `aperturarBoveda()`

No existe ninguna restricción que impida crear más de un registro en la tabla `boveda`. Si alguien llama `POST /aperturar_boveda` directamente (o el frontend tiene un glitch), se crean múltiples bóvedas. Todos los métodos toman `orderBy('id', 'desc')` para obtener la última, lo cual es parcialmente defensivo pero no correcto.

```php
// CORRECCIÓN — verificar al inicio de aperturarBoveda
if (DB::table('boveda')->exists()) {
    return response()->json(['message' => 'Ya existe una bóveda registrada'], 422);
}
```

También se recomienda agregar un constraint `UNIQUE` en la migración o usar un campo `activa` para controlar el estado.

---

#### BUG-06 — Validación de saldo en frontend usa comparación sin `parseFloat()`
**Archivo:** `frmBoveda.vue` → `validarRetiro()`

```javascript
// ACTUAL — puede fallar con strings decimales
if ((this.boveda.saldo_actual - this.montoRetiro) < 0) {

// CORRECCIÓN
if ((parseFloat(this.boveda.saldo_actual) - parseFloat(this.montoRetiro)) < 0) {
```

`saldo_actual` llega de la API como string (ej: `"1500.00"`) y `montoRetiro` es string del `v-model`. JavaScript hace conversión implícita que generalmente funciona, pero es comportamiento no garantizado con todos los formatos decimales.

---

#### BUG-07 — `filtrarIngresos/Retiros` asigna la descripción seleccionada en cada keystroke
**Archivo:** `frmBoveda.vue` → `filtrarIngresos()`, `filtrarRetiros()`

```javascript
filtrarIngresos() {
    this.descripcionIngresoSeleccionada = this.busquedaIngreso; // ← asigna texto parcial
    ...
}
```

Mientras el usuario escribe (ej: "Aporta"), `descripcionIngresoSeleccionada` queda como texto incompleto. La validación `descripcionIngresoSeleccionada === 'Aporte de capital'` (case-sensitive) no detectaría este caso correctamente. La descripción debería asignarse únicamente en `seleccionarIngreso()` / `seleccionarRetiro()`.

Además, el input tiene `class="text-uppercase"` (CSS), pero el `v-model` guarda el valor en minúsculas tal como el usuario lo escribe — la comparación `=== 'Aporte de capital'` fallaría si el usuario escribe en minúsculas.

---

#### BUG-08 — `getMovimientosBoveda` tiene dos llamadas `select()` encadenadas
**Archivo:** `BovedaController.php` → `getMovimientosBoveda()`

```php
->select('movimientos_boveda.*', 'users.personal')      // línea 38 — SOBREESCRITA
->leftJoin('socios', ...)
->select(                                                // línea 40 — esta prevalece
    'movimientos_boveda.*',
    'users.personal',
    'socios.nombres as socio_nombres',
    'socios.apellidos as socio_apellidos'
)
```

La primera llamada `->select()` es código muerto. No produce error porque la segunda la reemplaza, pero debe limpiarse.

---

### 🟢 Menores

---

#### BUG-09 — Variable muerta `fecha_fin` en `data()`
**Archivo:** `frmBoveda.vue`

```javascript
fecha_fin: moment().format('YYYY-MM-DD'),  // línea 385 — nunca usada
```

La variable correcta es `fechaFin` (línea 341). `fecha_fin` es código muerto.

---

#### BUG-10 — Monto y fecha sin formatear en la tabla de movimientos
**Archivo:** `frmBoveda.vue`

```html
{{ movimiento.monto }}  <!-- muestra "1500.00" crudo -->
{{ movimiento.fecha }}  <!-- muestra "2024-09-26T15:15:37.000000Z" -->
```

En otros módulos se usan funciones de formateo. Aquí no se aplican, resultando en un display inconsistente.

---

#### BUG-11 — `per_page` inicializado a 10, backend pagina de 40
**Archivo:** `frmBoveda.vue` → `data()`

```javascript
per_page: 10,   // valor inicial incorrecto
```

El backend usa `->paginate(40)`. El valor se corrige al recibir la primera respuesta, pero el valor inicial es engañoso.

---

#### BUG-12 — `fecha_apertura` retorna `0` en lugar de `null` cuando no hay bóveda
**Archivo:** `BovedaController.php` → `getBoveda()`

```php
'fecha_apertura' => empty($boveda) ? 0 : $boveda->fecha_apertura,
```

Debería retornar `null` o `''` — `0` es un valor confuso para un campo de fecha.

---

#### BUG-13 — Sin diálogo de confirmación al aperturar
**Archivo:** `frmBoveda.vue` → `aperturarBoveda()`

La apertura de bóveda es una operación que crea el fondo central del sistema. No solicita confirmación antes de ejecutar.

---

## 5. Resumen de Issues por Severidad

| # | Severidad | Ubicación | Descripción |
|---|---|---|---|
| BUG-01 | 🔴 Crítico | BovedaController | Sin validación de saldo en backend al retirar |
| BUG-02 | 🔴 Crítico | BovedaController / CajaController | `DB::raw` con input sin validar (riesgo SQL) |
| BUG-03 | 🔴 Crítico | BovedaController | `Exception` sin `\` → rollback puede no ejecutarse |
| BUG-04 | 🔴 Crítico | BovedaController | Operaciones sin respuesta HTTP → errores silenciosos |
| BUG-05 | 🟡 Importante | BovedaController | Sin control de unicidad — múltiples bóvedas posibles |
| BUG-06 | 🟡 Importante | frmBoveda.vue | Comparación de saldo sin `parseFloat()` |
| BUG-07 | 🟡 Importante | frmBoveda.vue | Descripción asignada en cada keystroke (fragile UX) |
| BUG-08 | 🟡 Importante | BovedaController | Doble `->select()` — primer select es código muerto |
| BUG-09 | 🟢 Menor | frmBoveda.vue | Variable `fecha_fin` muerta en `data()` |
| BUG-10 | 🟢 Menor | frmBoveda.vue | Monto y fecha sin formatear en tabla |
| BUG-11 | 🟢 Menor | frmBoveda.vue | `per_page` inicial desincronizado con backend |
| BUG-12 | 🟢 Menor | BovedaController | `fecha_apertura` retorna `0` en lugar de `null` |
| BUG-13 | 🟢 Menor | frmBoveda.vue | Sin confirmación al aperturar |

---

## 6. Notas de Diseño y Observaciones

### Modelo de una sola bóveda activa
El sistema está diseñado conceptualmente para una sola bóveda, pero la base de datos y el código no aplican esta restricción. Todos los métodos usan `orderBy('id', 'desc')` para tomar siempre el último registro, lo cual es una solución frágil. Se recomienda aplicar la restricción de unicidad tanto en base de datos como en el controller.

### Totales filtrados vs. absolutos
Los badges de "Ingresos" y "Salidas" en la pantalla muestran los totales **del período y tipo filtrado activo**, no los totales históricos globales. Esto es coherente con los datos mostrados en la tabla, pero puede confundir al usuario que espera ver los totales absolutos de la bóveda. El comportamiento está documentado internamente en el controller con un comentario.

### Movimientos automáticos desde Caja
`CajaController@actualizarDesembolso` crea movimientos de salida en bóveda sin pasar por `BovedaController`. Estos movimientos son invisibles para el usuario de bóveda a menos que los vea en el historial. El mismo patrón de riesgo SQL (BUG-02) aplica en ese método.

### `TransferenciaCajaBoveda` — sin uso visible
El modelo `TransferenciaCajaBoveda` y su tabla `transferencia_caja_boveda` están creados y migrados, pero no se encontró ningún uso activo en el código actual del controlador ni en el frontend. Parece ser una funcionalidad planificada pero no implementada.

---

## 7. Endpoints — Referencia Rápida

| Método | Ruta | Parámetros | Respuesta |
|---|---|---|---|
| GET | `/get_boveda` | — | `{ saldo_actual, fecha_apertura, id_boveda, usuario_apertura }` |
| GET | `/get_movimientos_boveda` | `tipo`, `fecha_inicio`, `fecha_fin`, `page` | `{ movimientos: paginado, totales: { ingresos, salidas } }` |
| POST | `/ingresar_boveda` | `monto`, `descripcion`, `id_socio?` | void (sin respuesta estructurada — ver BUG-04) |
| POST | `/retirar_boveda` | `monto`, `descripcion`, `id_socio?` | void (sin respuesta estructurada — ver BUG-04) |
| POST | `/aperturar_boveda` | — | `{ message }` 200 / 500 |
| GET | `/verificar-boveda` | — | `{ aperturada: bool }` |
