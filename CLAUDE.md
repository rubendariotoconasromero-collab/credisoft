# CLAUDE.md — Contexto del proyecto Credisoft

Este archivo provee contexto esencial para asistentes de IA trabajando en este repositorio.
Léelo antes de modificar cualquier archivo relacionado con caja, bóveda, pagos o contabilidad.

---

## Stack tecnológico

- **Backend**: Laravel 10 (PHP 8)
- **Frontend**: Vue 2 (SPA embebida via `resources/js/app.js`)
- **BD**: MySQL (via Laragon en desarrollo)
- **PDF**: mPDF (reportes) + DomPDF (tickets de pago)
- **UI**: Bootstrap 5, FontAwesome

---

## Descripción del sistema

Credisoft es un sistema de gestión para una institución de microcrédito. Administra:
- Solicitudes y desembolsos de créditos
- Planes de pago con cuotas (semanal, quincenal, mensual)
- Cobro de cuotas con cascada mora → interés → capital
- Caja diaria y bóveda (efectivo físico)
- Consulta financiera contable (Flujo de Caja, Libro Diario)

---

## Modelo de Bóveda Física (CRÍTICO)

El sistema usa un **Modelo de Bóveda Física**: `boveda.saldo_actual` representa
el efectivo FÍSICAMENTE en la bóveda, no un total virtual.

### Invariante fundamental

```
Total Capital = boveda.saldo_actual + calcularSaldoCaja(id_caja)
```

El total nunca cambia por transferencias internas, solo por operaciones reales.

### Operaciones y su efecto en saldos

| Operación | Bóveda | Caja | Total |
|-----------|--------|------|-------|
| Apertura bóveda (+N) | +N | — | +N |
| Apertura caja (monto_inicial) | −monto | +monto | sin cambio |
| Transferencia bóveda→caja | −monto | +monto | sin cambio |
| Transferencia caja→bóveda | +monto | −monto | sin cambio |
| Desembolso crédito | — | −monto | −monto |
| Cobro de cuota (pago) | — | +monto | +monto |
| Gasto operativo | — | −monto | −monto |
| Cierre de caja (retorno) | +saldo_caja | −saldo_caja | sin cambio |

### Flujo al cerrar caja

Al cerrar, `CajaController::closeCaja()` calcula el saldo remanente con
`calcularSaldoCaja()` e inserta en `movimientos_boveda` un registro
"Retorno de Caja al Cierre" y suma ese monto a `boveda.saldo_actual`.

---

## Tablas de base de datos clave

| Tabla | Descripción |
|-------|-------------|
| `boveda` | Saldo actual del efectivo en bóveda física. Un solo registro activo. |
| `movimientos_boveda` | Historial de ingresos/egresos de bóveda. |
| `caja` | Sesión de caja diaria (estado 1=abierta, 0=cerrada). |
| `movimientos_caja` | Historial de movimientos dentro de la caja. |
| `egreso` | Gastos de caja (incluyendo transferencias a bóveda). |
| `ingreso` | Ingresos manuales en caja. |
| `plan_pago` | Plan de amortización de un crédito. |
| `cuota` | Cuotas individuales de un plan. estado: 1=pendiente, 2=pagada, 3=parcial. |
| `pago` | Registro de cada pago realizado. Incluye `pago_capital`, `pago_interes`, `pago_mora`, `tipo_pago`. |
| `desembolso` | Desembolsos de crédito (restan de caja, no de bóveda). |
| `pago_administrativo` | Gastos administrativos cobrados al cliente. |
| `solicitud` | Solicitud de crédito. |
| `cliente` | Datos del cliente. |

### Columna `pago.tipo_pago` (VARCHAR 20)

Agregada en migración `2026_05_31_000001`. Valores posibles:

| Valor | Significado |
|-------|-------------|
| `completo` | Cuota completa: capital + interés + mora |
| `solo_interes` | Solo interés devengado/moratorio |
| `solo_mora` | Solo multa fija |
| `interes_mora` | Interés + mora, sin capital |
| `parcial` | Monto libre con cascada estándar |

### Columna `cuota.estado`

- `1` = Pendiente
- `2` = Pagada completamente
- `3` = Pago parcial (aún tiene deuda residual)

---

## Traits importantes

### `CalculaSaldoCaja` (`app/Traits/CalculaSaldoCaja.php`)

Calcula el saldo actual de una caja:

```
saldo = monto_inicial + pagos_cuotas + ingresos_caja + pagos_adm − egresos − desembolsos
```

Usado por: `CajaController`, `GastoController`.

### `ActualizaSaldoBoveda` (`app/Traits/ActualizaSaldoBoveda.php`)

**TRAIT OBSOLETO — NO USAR.** Existía para actualizar `boveda.saldo_actual`
directamente desde PagoController, IngresoController, etc. Fue removido de todos
los controladores porque duplicaba actualizaciones y causaba saldos incorrectos.
El trait sigue existiendo como archivo pero no está en uso.

---

## Controladores clave

### `CajaController`

- `save()` — Apertura de caja: descuenta `monto_inicial` de `boveda.saldo_actual`.
- `closeCaja()` — Cierre: transfiere saldo remanente de caja a bóveda.
- `actualizarDesembolso()` — Registra desembolso: resta de caja únicamente (NO de bóveda).
- `pago_adm()` — Gasto administrativo: solo afecta caja.

### `BovedaController`

- `ingresarBoveda()` — Suma a `boveda.saldo_actual`. Para "Transferencia a Caja": también crea ingreso en `ingreso` (tabla).
- `retirarBoveda()` — Resta de `boveda.saldo_actual`. Valida saldo suficiente. Para "Transferencia a Caja": crea movimiento ingreso en caja.

### `GastoController`

- `save()` — Egreso de caja. Para "Transferencia a Bóveda": auto-crea `movimientos_boveda` ingreso y suma a `boveda.saldo_actual`.
- `anularGasto()` — Revierte. Para "Transferencia a Bóveda": resta de `boveda.saldo_actual`.

### `PagoController`

- `pagarCuotas()` — Método principal usado por GestionCobros.vue. Recibe `modalidad_pago` y aplica la cascada correspondiente (mora → interés → capital según modalidad). Guarda `tipo_pago` en tabla `pago`.
- `save()` — Método legado simple (pago de cuota individual, no multimodalidad).
- `generarTicketPago()` — Genera PDF de recibo via DomPDF.

### `ConsultaFinancieraController`

- `getLibroMayor()` — Devuelve movimientos paginados + ingresos/egresos + saldo total.
- `obtenerConsultaSQLBase()` — Construye el UNION de todos los tipos de movimiento.
- `procesarMovimientosYCalcularSaldos()` — Calcula saldo acumulado y totales.

**Tipos de movimiento en el Flujo de Caja:**

| Tipo | Origen | Debe/Haber |
|------|--------|-----------|
| `INTERES` | tabla `pago` (pago_interes > 0) | Debe |
| `MORA` | tabla `pago` (pago_mora > 0) | Debe |
| `GASTOSADM` | tabla `pago_administrativo` | Debe |
| `INGRESO_CAJA` | tabla `ingreso` (excluye desde bóveda) | Debe |
| `EGRESO_CAJA` | tabla `egreso` (excluye transferencia a bóveda) | Haber |
| `CAPITAL` | tabla `pago` (pago_capital > 0) | Debe (solo Libro General) |
| `DESEMBOLSO` | tabla `desembolso` | Haber (solo Libro General) |
| `BOVEDA_INGRESO` | tabla `movimientos_boveda` ingreso real | Debe (solo Libro General) |
| `BOVEDA_EGRESO` | tabla `movimientos_boveda` egreso real | Haber (solo Libro General) |
| `TRANSFER_INTERNO` | transferencias bóveda↔caja y retornos | Debe=Haber (neto cero) |

`TRANSFER_INTERNO` se excluye de sumas de totales (neto cero, no infla ingresos ni egresos).

### `PlanPagoController`

- `listarAmortizaciones()` — Calcula en tiempo real para cada cuota: interés devengado, moratorio, mora fija, capital neto, porcentajes pagados.
  - Campos adicionales en cada cuota: `capital_pagado_total`, `porcentaje_capital_pagado`, `interes_pagado_total`, `porcentaje_interes_pagado`, `mora_pagada_total`, `mora_bruta`, `porcentaje_mora_pagada`, `mora_fija_neta`, `interes_acumulado_neto`.

---

## Componentes Vue clave

| Componente | Descripción |
|------------|-------------|
| `frmCaja.vue` | Panel principal de caja: apertura, desembolsos, movimientos. |
| `frmBoveda.vue` | Panel de bóveda: ingresos, retiros, transferencias. |
| `Caja/GestionCobros.vue` | Modal de cobro de cuotas con selector de modalidad de pago. |
| `Caja/GestionDesembolsos.vue` | Modal para registrar desembolsos. |
| `Caja/ModalAperturaCaja.vue` | Modal para apertura de caja diaria. |
| `Caja/ModalMovimientoCaja.vue` | Modal para ingresos/egresos manuales de caja. |
| `frmConsultaFinanciera.vue` | Consulta contable: Flujo de Caja y Libro Diario. |
| `frmPlanPago.vue` | Plan de amortización y detalle de crédito. |

---

## Lógica de cobro de cuotas (`pagarCuotas`)

La cascada de pago sigue este orden según la modalidad:

```
modalidad = 'completo' | 'parcial':
    1. Condonaciones mora + interés
    2. Pago mora con efectivo
    3. Pago interés con efectivo
    4. Pago capital con efectivo

modalidad = 'solo_mora':
    1. Condonación mora
    2. Pago mora únicamente

modalidad = 'solo_interes':
    1. Condonación interés
    2. Pago interés únicamente

modalidad = 'interes_mora':
    1. Condonaciones mora + interés
    2. Pago mora
    3. Pago interés
```

- `cuota.estado = 2` solo si deuda_mora=0 AND deuda_interes=0 AND deuda_capital=0
- `cuota.estado = 3` en cualquier otro caso (pago parcial de algún componente)
- `plan_pago.fecha_ultima_amortizacion` se actualiza solo cuando alguna cuota queda completamente pagada

---

## Reglas críticas — NO romper

1. **Nunca usar `ActualizaSaldoBoveda` trait** en ningún controlador nuevo.
2. **Los desembolsos NO restan de bóveda** — solo restan de caja (ya fueron extraídos al abrir caja).
3. **Las transferencias internas son neto cero** — no deben sumarse a totales de ingresos/egresos.
4. **Al abrir caja** siempre restar `monto_inicial` de `boveda.saldo_actual`.
5. **Al cerrar caja** siempre retornar el saldo remanente a bóveda.
6. **`pago.tipo_pago`** debe grabarse en todo INSERT a tabla `pago` hecho desde `pagarCuotas()`.
7. **TRANSFER_INTERNO** en ConsultaFinancieraController debe excluirse de `suma_ingresos` y `suma_egresos`.

---

## Convenciones de desarrollo

- Toda operación de caja/bóveda que toca dinero debe estar dentro de `DB::beginTransaction()`.
- Las validaciones de saldo (caja o bóveda) se hacen antes del INSERT, dentro de la transacción.
- Los movimientos en `movimientos_caja` y `movimientos_boveda` son inmutables (no se borran, se anulan lógicamente).
- Las fechas se manejan con `Carbon` en backend. En frontend con `moment.js`.
- El frontend usa `axios` para todas las llamadas a la API. Las rutas están en `routes/web.php`.
- Los componentes Vue se registran en `resources/js/app.js`.
