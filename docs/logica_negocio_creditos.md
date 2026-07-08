# Lógica de Negocio — Gestión de Créditos (Credisoft)

Documento de referencia para reimplementar la lógica de gestión de préstamos en otro sistema.
Cubre el ciclo completo: solicitud → aprobación → desembolso → cobro de cuotas → cierre.

---

## Índice

1. [Tablas y relaciones](#1-tablas-y-relaciones)
2. [Ciclo de vida de un crédito](#2-ciclo-de-vida-de-un-crédito)
3. [Cálculo del plan de pagos (cuotas)](#3-cálculo-del-plan-de-pagos-cuotas)
4. [Estructura de la tabla `cuota`](#4-estructura-de-la-tabla-cuota)
5. [Lógica de cobro de cuotas](#5-lógica-de-cobro-de-cuotas)
6. [Cálculo de mora e interés devengado en el cobro](#6-cálculo-de-mora-e-interés-devengado-en-el-cobro)
7. [Reprogramación y Refinanciamiento](#7-reprogramación-y-refinanciamiento)
8. [Códigos de estado](#8-códigos-de-estado)
9. [Reglas de negocio críticas](#9-reglas-de-negocio-críticas)
10. [Formularios — Funcionamiento detallado](#10-formularios--funcionamiento-detallado)

---

## 1. Tablas y relaciones

### Diagrama de relaciones

```
cliente ──────────────── solicitud ──────── plan_pago ──── cuota ──── pago
                            │                   │
                       [codeudores]         desembolso
                       [garantias]
```

### `cliente`

| Columna          | Tipo         | Descripción                              |
|------------------|--------------|------------------------------------------|
| id               | int PK       |                                          |
| nombre           | string       | Nombre completo                          |
| ci               | string       | Cédula de identidad                      |
| lugar_expedicion | string       | Lugar de expedición del CI               |
| fecha_nacimiento | date         |                                          |
| sexo             | string       |                                          |
| estado_civil     | string       |                                          |
| actividad        | string       | Actividad económica                      |
| vivienda         | string       | Tipo de vivienda                         |
| ingreso_mensual  | float        | Ingreso mensual declarado                |
| imagen           | string null  | Foto del cliente                         |
| estado           | int (1)      | 1 = Activo                               |

### `solicitud`

Representa la solicitud de crédito (aprobada o no).

| Columna                    | Tipo              | Descripción                                                          |
|----------------------------|-------------------|----------------------------------------------------------------------|
| id                         | int PK            |                                                                      |
| id_cliente                 | FK → cliente      |                                                                      |
| id_usuario                 | FK → users        | Oficial que tramitó                                                  |
| importe_solicitud          | double(8,2)       | Monto del crédito                                                    |
| moneda                     | string            | Bs, USD, etc.                                                        |
| lapso_capital              | string            | `Semanal` / `Quincenal` / `Mensual`                                 |
| nro_cuotas                 | int               | Total de cuotas del plan                                             |
| tasa                       | decimal(11,2)     | Tasa de interés **mensual** (%)                                     |
| tipo_tasa                  | string(50)        | `amortizable` (capital fijo) o `fija` (cuota fija / método francés) |
| fecha                      | date              | Fecha de la solicitud                                                |
| fecha_desembolso           | date              | Fecha pactada de desembolso                                          |
| fecha_primera_cuota        | date              | Fecha de la primera cuota                                            |
| destino_prestamo           | string            | Propósito del préstamo                                               |
| tipo_garantia              | string            | Tipo de garantía ofrecida                                            |
| tipo_desembolso            | string            | Forma de entrega del dinero                                          |
| monto_pago_adm             | decimal(10,2)     | Cargo administrativo cobrado al desembolsar                          |
| estado                     | int               | Ver [Códigos de estado](#8-códigos-de-estado)                        |
| desembolso                 | int               | 0 = Pendiente de desembolso, 1 = Ya desembolsado                    |
| tipo_solicitud             | string(250)       | `Nuevo` / `Reprogramacion` / `Refinanciamiento`                     |
| cantidad_reprogramaciones  | int (0)           | Contador de reprogramaciones acumuladas                              |
| cantidad_refinanciamientos | int (0)           | Contador de refinanciamientos acumulados                             |
| monto_refinanciamiento     | int               | Efectivo extra entregado en refinanciamiento                         |
| id_solicitud_origen        | FK null           | Apunta a la solicitud original (para reprogramaciones)              |
| observacion                | string(250) null  |                                                                      |

### `plan_pago`

Una solicitud aprobada genera exactamente **un** `plan_pago`.

| Columna                  | Tipo           | Descripción                                              |
|--------------------------|----------------|----------------------------------------------------------|
| id                       | int PK         |                                                          |
| id_solicitud             | FK → solicitud |                                                          |
| id_plan_aux              | bigint null    | Referencia a plan anterior (en reprogramaciones)        |
| fecha_inicio             | date           | Fecha de inicio del plan                                 |
| fecha_fin                | date           | Fecha de la última cuota                                 |
| total_pagar              | decimal(12,2)  | Suma de capitales (= importe_solicitud)                  |
| moneda                   | string         |                                                          |
| lapso_capital            | string         | Frecuencia de pago                                       |
| nro_cuotas               | int            |                                                          |
| tasa                     | int            | Tasa mensual almacenada como entero                      |
| estado                   | int (1)        | Ver [Códigos de estado](#8-códigos-de-estado)            |
| desembolso               | int (1)        | 0 = Pendiente, 1 = Desembolsado                         |
| pago_administrativo      | int (1)        | Flag de pago del cargo administrativo                    |
| fecha_ultima_amortizacion| date null      | Fecha del último pago **completo** (reinicia reloj mora) |
| saldo_pendiente          | decimal(15,2) null | Capital pendiente de pago                            |
| fecha_registro           | date null      | Fecha de creación del plan                               |

### `cuota`

Cada fila es una cuota del plan de pagos.

| Columna         | Tipo           | Descripción                                                              |
|-----------------|----------------|--------------------------------------------------------------------------|
| id              | int PK         |                                                                          |
| id_plan_pago    | FK → plan_pago |                                                                          |
| numero          | int            | Número de cuota (1, 2, 3 …)                                             |
| fecha           | date           | Fecha de vencimiento de la cuota                                         |
| capital         | double(8,2)    | Porción de capital programada para esta cuota                            |
| interes         | double(8,2)    | Interés programado al momento de generar el plan                         |
| saldo_capital   | double(8,2)    | Capital pendiente **después** de esta cuota                              |
| ahorro          | double(8,2) null | Componente de ahorro (si aplica)                                       |
| seguro          | double(8,2) null | Componente de seguro (si aplica)                                       |
| total           | double(8,2)    | `capital + interes [+ ahorro + seguro]`                                  |
| capital_pagado  | decimal(10,2) (0) | Capital acumulado ya pagado en esta cuota                            |
| interes_pagado  | decimal(10,2) (0) | Interés acumulado ya pagado (incluye condonaciones)                   |
| mora_pagada     | decimal(10,2) (0) | Mora acumulada ya pagada (incluye condonaciones)                      |
| estado          | int (1)        | 0=Cancelada, 1=Pendiente, 2=Pagada, 3=Pago parcial                      |
| amortizado      | int (0)        | 1 = Cuota reemplazada por amortización extraordinaria                    |

### `pago`

Registro de cada transacción de cobro. Un cobro puede generar varios `pago` (uno por cuota).

| Columna                  | Tipo           | Descripción                                            |
|--------------------------|----------------|--------------------------------------------------------|
| id                       | int PK         |                                                        |
| id_cuota                 | FK → cuota     |                                                        |
| id_caja                  | FK → caja      | Caja donde se registró el cobro                        |
| id_usuario               | FK → users     |                                                        |
| codigo_transaccion       | string(50) idx | Agrupa todos los `pago` de una misma operación de cobro|
| fecha_pago               | date           |                                                        |
| monto_pago               | decimal(12,2)  | Total cobrado en esta línea                            |
| pago_capital             | decimal(12,2) (0) | Porción aplicada a capital                          |
| pago_interes             | decimal(12,2) (0) | Porción aplicada a interés                          |
| pago_mora                | decimal(12,2) (0) | Porción aplicada a mora                             |
| monto_condonado          | decimal(12,2) (0) | Total condonado (mora + interés)                    |
| monto_condonado_interes  | decimal(12,2) (0) | Interés condonado                                   |
| monto_condonado_mora     | decimal(12,2) (0) | Mora condonada                                      |
| motivo_condonacion       | string         | Justificación de la condonación                        |
| dias_retrasados          | int null       | Días de retraso al momento del pago                    |
| multa_dia                | decimal(12,2) null | Multa por día de retraso                           |
| multa_total              | decimal(12,2) null | Multa total calculada                              |
| tipo_pago                | string(20)     | `completo` / `parcial` / `solo_interes` / `solo_mora` / `interes_mora` |
| monto_cuota              | decimal(10,2)  | Monto original de la cuota al momento del pago         |
| forma_pago               | string         | Efectivo, transferencia, etc.                          |
| imagen                   | string         | Comprobante adjunto                                    |
| estado                   | int (1)        | 0 = Anulado, 1 = Activo                               |

### `desembolso`

| Columna      | Tipo           | Descripción                            |
|--------------|----------------|----------------------------------------|
| id           | int PK         |                                        |
| id_plan_pago | FK → plan_pago |                                        |
| id_caja      | FK → caja      |                                        |
| id_usuario   | FK → users     |                                        |
| monto        | decimal(12,2)  | Monto desembolsado                     |
| fecha        | datetime       |                                        |
| estado       | int (0)        | 0 = Activo, 1 = Anulado               |

### Tablas de soporte

| Tabla                  | Descripción                                              |
|------------------------|----------------------------------------------------------|
| `solicitud_codeudor`   | Tabla pivote N:M entre solicitud y cliente (codeudores)  |
| `garantia`             | Garantías vinculadas a una solicitud                     |
| `pago_administrativo`  | Cobros administrativos al cliente (cargo de desembolso)  |

---

## 2. Ciclo de vida de un crédito

```
[1] SOLICITUD NUEVA
    solicitud.estado = 1
    solicitud.tipo_solicitud = 'Nuevo'
        ↓
[2] APROBACIÓN Y GENERACIÓN DEL PLAN
    solicitud.estado = 2
    Se crea plan_pago (estado=1, desembolso=1 si es reprogramación, 0 si es nuevo)
    Se insertan cuotas (estado=1) con capital, interes, saldo_capital calculados
        ↓
[3] DESEMBOLSO (solo si plan_pago.desembolso = 0)
    Se registra en tabla desembolso
    plan_pago.desembolso = 1
    El monto sale de caja (no de bóveda)
        ↓
[4] COBRO DE CUOTAS (ver sección 5)
    Se van pagando cuotas (estado: 1→2 o 1→3)
    Cada cobro registra en tabla pago
    plan_pago.fecha_ultima_amortizacion se actualiza solo si alguna cuota queda estado=2
        ↓
[5] CIERRE DEL PLAN
    plan_pago.estado = 2 cuando todas las cuotas tienen estado=2
```

### Transiciones de estado de `solicitud`

```
1 (Nueva) → 2 (Aprobada) → 3 (Anulada/Reprogramada)
                         → 10 (Especial/Interna)
```

### Transiciones de estado de `plan_pago`

```
1 (Activo) → 2 (Completado/Pagado)
           → 5 (En Proceso — bloqueado por reprogramación en curso)
           → 0 (Inactivo — reemplazado por nueva solicitud)
```

---

## 3. Cálculo del plan de pagos (cuotas)

La tasa siempre se expresa como **porcentaje mensual** (ej. `3` = 3% mensual).

Existen dos modalidades de amortización:

---

### 3.1 Tipo `amortizable` — Capital fijo, interés variable

El **capital** por cuota es constante. El **interés** varía porque se calcula sobre el saldo de capital restante.

#### Conversión plazo → número de cuotas

```
Semanal:   nro_cuotas = plazo_meses × 4
Quincenal: nro_cuotas = plazo_meses × 2
Mensual:   nro_cuotas = plazo_meses × 1
```

#### Variables de estado durante el cálculo

```
capital_aux       = importe_solicitud / nro_cuotas   // capital fijo por cuota
saldo_capital_aux = importe_solicitud                 // saldo que decrece
saldo_capital_sq  = importe_solicitud                 // "snapshot" del saldo al inicio de cada mes
                                                      // (solo usado en Semanal y Quincenal)
```

#### Fórmula de interés por lapso

```
MENSUAL:
    interes_diario = (saldo_capital_aux × tasa%) / dias_del_periodo
    interes_cuota  = interes_diario × dias_del_periodo
                   = saldo_capital_aux × tasa%       // efectivamente: capital × tasa% mensual

SEMANAL:
    interes_cuota = (saldo_capital_sq × tasa%) / 4
    // El mismo saldo_capital_sq se usa para las 4 cuotas de cada mes.
    // saldo_capital_sq se actualiza al terminar la cuota número múltiplo de 4:
    //   if (contador % 4 == 0) → saldo_capital_sq = saldo_capital_aux

QUINCENAL:
    interes_cuota = (saldo_capital_sq × tasa%) / 2
    // El mismo saldo_capital_sq se usa para las 2 cuotas de cada mes.
    // saldo_capital_sq se actualiza al terminar la cuota número múltiplo de 2:
    //   if (contador % 2 == 0) → saldo_capital_sq = saldo_capital_aux
```

#### Total de la cuota y saldo capital

```
total_cuota    = capital_aux + interes_cuota
saldo_capital  = saldo_capital_aux - capital_aux   // después de aplicar esta cuota
```

#### Avance de fecha entre cuotas

```
Mensual:   fecha += 1 mes
Quincenal: fecha += 15 días
Semanal:   fecha += 7 días
```

#### Ejemplo simplificado — Semanal con capital fijo

```
Crédito: 10 000 Bs, tasa 3% mensual, 12 cuotas semanales (3 meses)

capital_aux       = 10 000 / 12 = 833.33 Bs
saldo_capital_sq  = 10 000 (snapshot del mes 1)

Cuota 1 (semana 1): interes = 10 000 × 3% / 4 = 75.00  → total = 908.33
Cuota 2 (semana 2): interes = 10 000 × 3% / 4 = 75.00  → total = 908.33
Cuota 3 (semana 3): interes = 10 000 × 3% / 4 = 75.00  → total = 908.33
Cuota 4 (semana 4): interes = 10 000 × 3% / 4 = 75.00  → total = 908.33
                                                            ↑ al final de la cuota 4:
                                                            saldo_capital_aux = 10000 - 4×833.33 = 6666.67
                                                            saldo_capital_sq se actualiza = 6666.67

Cuota 5 (semana 5): interes = 6666.67 × 3% / 4 = 50.00 → total = 883.33
... y así sucesivamente
```

---

### 3.2 Tipo `fija` — Cuota fija (Método Francés / PMT)

La **cuota total** es constante cada período. El capital crece y el interés decrece en cada cuota.

#### Fórmula PMT (cuota mensual fija)

```
cuotas_por_mes:
    Mensual   → 1
    Quincenal → 2
    Semanal   → 4

meses_del_plan = nro_cuotas / cuotas_por_mes

cuota_mensual = PV × [r × (1+r)^n] / [(1+r)^n − 1]

donde:
    PV = importe_solicitud
    r  = tasa / 100           // tasa mensual como decimal (ej. 0.03)
    n  = meses_del_plan
```

#### Descomposición sub-mensual (para Semanal y Quincenal)

Cuando hay más de una cuota por mes, la cuota mensual se divide equitativamente:

```
cuota_periodo = cuota_mensual / cuotas_por_mes
```

El interés se calcula **una sola vez al inicio de cada mes** y se distribuye:

```
Al inicio de cada grupo mensual (cuotas (1,2,3,4) / (5,6,7,8) / ...):
    interes_mensual = saldo_capital × (tasa / 100)

Cada cuota del grupo:
    interes_periodo = interes_mensual / cuotas_por_mes
    capital_periodo = cuota_periodo  − interes_periodo
    saldo_capital  -= capital_periodo

En la última cuota del grupo (o del plan) se ajusta el redondeo:
    interes_ajustado = interes_mensual − interes_acumulado_previo_en_el_grupo
```

#### Ajuste de primera cuota (días reales)

Si la primera cuota no cae exactamente en 30/15/7 días desde el desembolso,
se aplica un ajuste proporcional:

```
dias_diferencia = dias_reales_primer_período − dias_estandar
                  (dias_estandar: Mensual=30, Quincenal=15, Semanal=7)

ajuste = (interes_mensual / cant_dias_referencia) × dias_diferencia
         (cant_dias_referencia: Semanal=28, otros=30)

Si el período real es mayor al estándar: interes_cuota1 += ajuste
Si el período real es menor al estándar: interes_cuota1 -= ajuste
```

#### Ejemplo simplificado — Mensual con cuota fija

```
Crédito: 10 000 Bs, tasa 3% mensual, 12 meses

cuota_mensual = 10 000 × [0.03 × (1.03)^12] / [(1.03)^12 − 1]
              = 10 000 × [0.03 × 1.4258] / [1.4258 − 1]
              = 10 000 × 0.042748 / 0.42576
              ≈ 1005.17 Bs

Cuota 1: interes = 10 000 × 3% = 300  → capital = 705.17 → saldo = 9 294.83
Cuota 2: interes =  9 294.83 × 3% = 278.84 → capital = 726.33 → saldo = 8 568.50
...
Cuota 12: todo el saldo restante se amortiza con el último pago
```

---

## 4. Estructura de la tabla `cuota`

Cuando se genera el plan, cada cuota almacena los valores **proyectados** al momento de la aprobación:

| Campo         | Descripción                                                                      |
|---------------|----------------------------------------------------------------------------------|
| numero        | Orden correlativo (1 a nro_cuotas)                                               |
| fecha         | Fecha de vencimiento calculada desde `fecha_primera_cuota`                       |
| capital       | Capital proyectado para esta cuota (fijo en tipo amortizable)                    |
| interes       | Interés proyectado al momento de crear el plan                                   |
| saldo_capital | Saldo de capital **después** de pagar esta cuota                                 |
| total         | `capital + interes [+ ahorro + seguro]`                                          |
| capital_pagado| Acumula lo que el cliente ha pagado de capital (inicia en 0)                     |
| interes_pagado| Acumula lo que se ha pagado de interés + interés condonado (inicia en 0)         |
| mora_pagada   | Acumula lo que se ha pagado de mora + mora condonada (inicia en 0)               |
| estado        | 1=Pendiente, 2=Pagada completamente, 3=Pago parcial                              |
| amortizado    | 1 si fue reemplazada por una amortización extraordinaria                         |

> **Importante:** El campo `interes` en la tabla es el interés **proyectado** al generar el plan.
> Al momento del cobro real, el interés **devengado** se recalcula dinámicamente sobre los días
> transcurridos (ver sección 6). El interés real puede diferir del proyectado.

---

## 5. Lógica de cobro de cuotas

El método principal es `pagarCuotas()`. Recibe:

- `id_plan_pago`
- `cuotas[]` — array de cuotas a pagar con sus montos adeudados calculados
- `monto_recibido` — efectivo recibido del cliente
- `condonacion_interes` — monto de interés a condonar
- `condonacion_mora` — monto de mora a condonar
- `modalidad_pago` — tipo de pago (ver abajo)

### Validaciones previas

1. Las cuotas deben pagarse **en orden** desde la primera pendiente o parcial.
2. `monto_recibido + condonaciones` no puede exceder `total_deuda` (tolerancia ±0.01 por redondeo).

### Modalidades de pago

| Modalidad       | Mora | Interés | Capital | Descripción                                      |
|-----------------|------|---------|---------|--------------------------------------------------|
| `completo`      | ✔    | ✔       | ✔       | Pago completo con cascada estándar               |
| `parcial`       | ✔    | ✔       | ✔       | Pago libre; acepta pago parcial en cualquier componente |
| `solo_mora`     | ✔    | ✗       | ✗       | Solo se paga la mora (multa fija)                |
| `solo_interes`  | ✗    | ✔       | ✗       | Solo se paga el interés devengado                |
| `interes_mora`  | ✔    | ✔       | ✗       | Se pagan mora e interés, sin tocar capital       |

### Cascada de aplicación del efectivo

Para cada cuota (en orden), el efectivo disponible (`bolsa`) se aplica así:

```
PASO 0 — Condonaciones (si aplica la modalidad):
    deuda_mora     -= condonacion_mora     (hasta agotarla)
    deuda_interes  -= condonacion_interes  (hasta agotarla)

PASO 1 — Pago de mora (si la modalidad incluye mora):
    pago_mora   = min(bolsa, deuda_mora)
    bolsa      -= pago_mora
    deuda_mora -= pago_mora

PASO 2 — Pago de interés (si la modalidad incluye interés):
    pago_interes   = min(bolsa, deuda_interes)
    bolsa         -= pago_interes
    deuda_interes -= pago_interes

PASO 3 — Pago de capital (si la modalidad incluye capital):
    pago_capital   = min(bolsa, deuda_capital)
    bolsa         -= pago_capital
    deuda_capital -= pago_capital
```

### Determinación del estado de la cuota

```
SI deuda_mora <= 0 AND deuda_interes <= 0 AND deuda_capital <= 0:
    cuota.estado = 2  (Pagada completamente)
    → plan_pago.fecha_ultima_amortizacion = HOY()  // reinicia el reloj de mora
SINO:
    cuota.estado = 3  (Pago parcial)
```

### Actualización de campos en `cuota`

```
cuota.capital_pagado += pago_capital
cuota.interes_pagado += pago_interes + condonacion_interes_aplicada
cuota.mora_pagada    += pago_mora    + condonacion_mora_aplicada
cuota.estado          = estado_determinado
```

### Cierre del plan

```
SI todas las cuotas tienen estado = 2:
    plan_pago.estado = 2  (Completado)
```

---

## 6. Cálculo de mora e interés devengado en el cobro

Al momento de cobrar, el interés y la mora se **recalculan dinámicamente** (no se usa el valor proyectado en `cuota.interes`). Esto se hace en `listarAmortizaciones()` y en la pantalla de cobro.

### 6.1 Interés devengado (período normal)

```
diasPeriodoCuota = fecha_cuota_actual − fecha_cuota_anterior
                   (mínimo 1 día)

interesPorDia    = cuota.interes / diasPeriodoCuota

diasTranscurridos = min(días desde inicio del período hasta HOY, diasPeriodoCuota)

interesDevengado  = interesPorDia × diasTranscurridos
```

Si la cuota ya fue pagada completamente (estado=2), `diasTranscurridos` usa la `fecha_pago` real.

### 6.2 Interés moratorio (recargo por retraso)

**Solo aplica a la primera cuota pendiente/parcial del plan** y solo si hay días de retraso.

```
fecha_referencia   = plan_pago.fecha_ultima_amortizacion
                     (fecha del último pago completo, o fecha_primera_cuota al inicio)

diasRetrasoCuota   = max(0, HOY − fecha_cuota_vencimiento)

Divisor por lapso:
    Semanal   → diasDivisor = 7
    Quincenal → diasDivisor = 15
    Mensual   → diasDivisor = 30

interesMoratorio  = (cuota.interes / diasDivisor) × diasRetrasoCuota
```

### 6.3 Multa fija (mora económica)

Independiente del interés moratorio. Se cobra por cada día de atraso:

```
TASA_MULTA_DIA     = 3 Bs/día   // constante del sistema
moraFija           = diasRetrasoCuota × TASA_MULTA_DIA
```

### 6.4 Deuda real al momento del cobro

```
capitalRestante        = max(0, cuota.capital  − cuota.capital_pagado)

interesDevengadoRestante = max(0, interesDevengado − cuota.interes_pagado)

// Si el cliente ya pagó de más en interés devengado, el exceso se aplica al moratorio:
excesoInteres          = max(0, cuota.interes_pagado − interesDevengado)
interesMoratorioRestante = max(0, interesMoratorio − excesoInteres)

interesTotalRestante   = interesDevengadoRestante + interesMoratorioRestante

moraRestante           = max(0, moraFija − cuota.mora_pagada)

totalAdeudado          = capitalRestante + interesTotalRestante + moraRestante
```

### 6.5 Porcentajes de pago (para visualización)

```
porcentajeCapital  = (cuota.capital_pagado   / cuota.capital)     × 100
porcentajeInteres  = (cuota.interes_pagado   / interesTotalBruto)  × 100
porcentajeMora     = (cuota.mora_pagada      / moraFija)           × 100
```

---

## 7. Reprogramación y Refinanciamiento

### Reprogramación

El cliente no puede pagar bajo el plan actual. Se restructura el saldo pendiente en un nuevo plan.

**Flujo:**

```
1. Se crea nueva solicitud:
   - tipo_solicitud = 'Reprogramacion'
   - cantidad_reprogramaciones = original.cantidad_reprogramaciones + 1
   - id_solicitud_origen = original.id
   - importe_solicitud = saldo_capital_pendiente
   - desembolso = 1  (no hay nuevo desembolso)

2. Se bloquea el plan original:
   - plan_pago.estado = 5  (En Proceso)

3. Al aprobar la reprogramación:
   - Nueva solicitud.estado = 2
   - Plan original.estado = 0  (Inactivo)
   - Cuotas pendientes del plan original: estado = 0 (Canceladas)
   - Se crea nuevo plan_pago con nuevas cuotas

4. Si el cliente tenía mora/interés pendiente al reprogramar:
   - Se puede registrar una OrdenPagoReprogramacion
   - Al activar el nuevo plan, la orden queda estado = 'POR_PAGAR'
```

### Refinanciamiento

Similar a reprogramación pero se entrega **efectivo adicional** al cliente.

```
monto_nuevo_plan = saldo_capital_pendiente + monto_refinanciamiento
desembolso       = 0  (hay un nuevo desembolso por el efectivo extra)
```

---

## 8. Códigos de estado

### `solicitud.estado`

| Valor | Significado             |
|-------|-------------------------|
| 0     | Cancelada               |
| 1     | Nueva / Pendiente       |
| 2     | Aprobada                |
| 3     | Anulada / Reprogramada  |
| 10    | Especial / Interna      |

### `plan_pago.estado`

| Valor | Significado                                        |
|-------|----------------------------------------------------|
| 0     | Inactivo (reemplazado por nuevo plan)              |
| 1     | Activo                                             |
| 2     | Completado (todas las cuotas pagadas)              |
| 5     | En Proceso (bloqueado por reprogramación en curso) |

### `cuota.estado`

| Valor | Significado                                    |
|-------|------------------------------------------------|
| 0     | Cancelada (por reprogramación)                 |
| 1     | Pendiente                                      |
| 2     | Pagada completamente                           |
| 3     | Pago parcial (tiene deuda residual)            |

### `pago.estado`

| Valor | Significado |
|-------|-------------|
| 0     | Anulado     |
| 1     | Activo      |

### `pago.tipo_pago`

| Valor          | Significado                           |
|----------------|---------------------------------------|
| `completo`     | Capital + interés + mora              |
| `solo_interes` | Solo interés devengado                |
| `solo_mora`    | Solo multa fija                       |
| `interes_mora` | Interés + mora, sin capital           |
| `parcial`      | Monto libre con cascada estándar      |

### `plan_pago.desembolso`

| Valor | Significado              |
|-------|--------------------------|
| 0     | Pendiente de desembolso  |
| 1     | Ya desembolsado          |

---

## 9. Reglas de negocio críticas

1. **Orden de cuotas:** Las cuotas se deben pagar secuencialmente. No se puede pagar la cuota 3 si la 1 y 2 no están pagadas (estado=2).

2. **Reloj de mora:** `plan_pago.fecha_ultima_amortizacion` solo avanza cuando una cuota queda completamente pagada (`estado=2`). Si solo se paga parcialmente, el reloj no se mueve.

3. **Interés moratorio solo en la primera cuota pendiente:** El recargo por retraso aplica exclusivamente a la cuota más antigua sin pagar. Las demás cuotas futuras no generan mora aunque estén "vencidas".

4. **Exceso de interés se aplica a moratorio:** Si el cliente pagó más interés del devengado, el exceso se descuenta del interés moratorio antes de calcular la deuda de mora.

5. **El capital proyectado y el real pueden diferir:** `cuota.interes` es el valor al momento de generar el plan. El interés **cobrable** se recalcula en tiempo real al momento del cobro.

6. **Saldo `saldo_capital_sq` en Semanal/Quincenal:** Este saldo "snapshot" es el saldo al inicio de cada mes. Dentro de un mismo mes, todos los períodos usan el mismo saldo base para calcular el interés, lo que produce cuotas de interés iguales dentro del mes aunque el saldo real disminuya.

7. **No hay desembolso en reprogramaciones:** Al reprogramar, no sale efectivo nuevo de caja (salvo en refinanciamiento). El nuevo plan solo restructura el saldo pendiente.

8. **Condonaciones se registran en `pago`:** Aunque no representen efectivo recibido, las condonaciones se graban en `monto_condonado_interes` y `monto_condonado_mora`, y se suman a `cuota.interes_pagado` y `cuota.mora_pagada` para el cálculo de deuda restante.

---

## 10. Formularios — Funcionamiento detallado

Esta sección describe el flujo de cada pantalla principal: qué datos maneja, qué lógica ejecuta en el frontend, y qué acciones dispara hacia el backend.

---

### 10.1 Formulario de Solicitud (`frmSolicitud.vue`)

#### Vistas internas (`view`)

| Valor | Pantalla |
|-------|----------|
| `0` | Lista de solicitudes (con filtros y paginación) |
| `1` | Formulario nuevo / editar / ver |
| `2` | Aprobación y previsualización del plan de pagos |
| `'aprobar_reprogramacion'` | Pantalla de auditoría y aprobación de reprogramación/refinanciamiento |

#### Modos del formulario (`solicitud.accion`)

| Valor | Modo | Acciones disponibles |
|-------|------|----------------------|
| `0` | Nuevo | Todos los campos editables, botón Guardar |
| `1` | Editar | Campos editables, botón Modificar |
| `2` | Solo lectura | Campos deshabilitados; el Administrador puede observar |

#### Secciones del formulario (view=1)

**Sección 1 — Datos del Cliente**
- Campo de búsqueda live (por nombre o CI): dispara `filteredItemsClienteMetodo()` con debounce que filtra la lista en memoria.
- Si el cliente ya existe en lista, muestra tarjeta con: foto, CI, lugar expedición, sexo, estado civil, vivienda, ingreso mensual, actividad.
- Botón "NUEVO" abre `ClienteForm` en un modal Bootstrap para crear un cliente al vuelo; al guardarlo, recarga la lista y pre-selecciona el cliente creado.
- Botón "Editar" abre `ClienteForm` en modo edición con los datos del cliente ya seleccionado.
- Botón "Quitar" limpia la selección.
- `solicitud.id_cliente` se setea al seleccionar el cliente.

**Sección 2 — Codeudores/Garantes**
- Toggle switch "Con/Sin Codeudor": si se desmarca, oculta la sección y guarda `sinCodeudor=true`.
- Lista dinámica de codeudores (`lista_codeudores[]`): botón `+` agrega un slot nuevo, botón papelera lo elimina.
- Cada slot tiene su propio buscador live (por nombre o CI) con dropdown autocomplete.
- Un codeudor seleccionado muestra su tarjeta de información (CI, sexo, actividad, estado civil, vivienda, ingreso mensual).
- Se permite múltiples codeudores.

**Sección 3 — Datos de la Solicitud**

| Campo | Tipo | Lógica / Restricciones |
|-------|------|------------------------|
| `importe_solicitud` | number | Monto del crédito |
| `moneda` | select | Lista de monedas configurables |
| `tipo_tasa` | select | `fija` (cuota fija) o `amortizable` (capital fijo); determina el algoritmo de cálculo |
| `lapso_capital` | select | `Semanal` / `Quincenal` / `Mensual`; al cambiar, recalcula `nro_cuotas` |
| `plazo` | number | Plazo en meses (solo edición/nuevo); activa `actualizarCuotasSolicitud()` |
| `nro_cuotas` | number | **Auto-calculado** desde plazo+lapso; editable manualmente si se necesita |
| `tasa` | number decimal | Tasa mensual en % |
| `fecha_desembolso` | date | Fecha de entrega del dinero |
| `fecha_primera_cuota` | date | Punto de inicio del plan |
| `destino_prestamo` | text | Obligatorio |
| `tipo_desembolso` | select | Forma de entrega del dinero |
| `tipo_garantia` | select | Tipo de garantía; si es física, habilita Sección 4 |

**Auto-cálculo de nro_cuotas:**
```
Al cambiar lapso_capital o plazo:
    Semanal   → nro_cuotas = plazo × 4
    Quincenal → nro_cuotas = plazo × 2
    Mensual   → nro_cuotas = plazo × 1
```

**Sección 4 — Garantías** (condicional)
- Solo visible si `tipo_garantia` es uno de los valores físicos:
  `'Empeño de electrodoméstico u Otros'`, `'Custodia de Papeles de Moto'`, etc.
- Lista dinámica de descripciones de garantía con botones agregar/eliminar.

**Sección 5 — Observaciones** (solo lectura)
- Solo visible si `solicitud.observacion != ''`.
- El Administrador puede agregar, modificar o eliminar observaciones vía `ModalObservacion`.
- **Una solicitud observada no puede aprobarse** hasta que la observación sea levantada.

#### Flujo al guardar (accion=0): `guardarSolicitud()`

```
POST /solicitud/save
  body: { solicitud, lista_codeudores, lista_garantias, tipo_tasa, sinCodeudor }

Respuesta exitosa → recarga lista (view=0)
```

#### Flujo de aprobación: `abrirModalSimulacionPlanPago()`

```
1. GET /solicitud/ver/{id} → carga datos de la solicitud y cliente
2. Ejecuta generarPlanPagosGeneral():
   - Si tipo_tasa == 'amortizable' → generarPlanPagos()   (capital fijo)
   - Si tipo_tasa == 'fija'        → generarPlanPagosTasaFija() (método francés)
3. Llena lista_cuotas[] con el plan calculado en el frontend
4. Cambia view = 2 → muestra <AprobacionCredito>
```

#### Flujo de aprobación de reprogramación: `aprobarReprogramacion(item)`

```
1. GET /solicitud/detalle-completo/{id_solicitud_origen} → datos del crédito original
2. GET /get_orden_pago_reprogramacion?id_solicitud= → orden de cobro pendiente (si existe)
3. Ejecuta simularNuevaTabla() para previsualizar el nuevo plan
4. view = 'aprobar_reprogramacion'

Al aprobar definitivamente:
  - Valida: sin observaciones Y tabla simulada generada
  - POST /solicitud/aprobar-reprogramacion-final
      body: { id_solicitud, datos_actualizados, cuotas: lista_cuotas_simuladas }
```

---

### 10.2 Aprobación de Crédito (`AprobacionCredito.vue`)

Componente **de solo lectura**. Recibe todos sus datos vía `props` desde el padre.

#### Props recibidos

| Prop | Descripción |
|------|-------------|
| `solicitud` | Objeto con todos los datos de la solicitud (monto, tasa, lapso, fechas, estado) |
| `cliente` | Objeto con datos del cliente (nombre, ci, lugar_expedicion) |
| `cuotas` | Array con el plan de pagos ya calculado por el padre |
| `procesando` | Boolean que muestra spinner en el botón "Aprobar" |

#### Tabla del plan (columnas)

| Columna | Descripción |
|---------|-------------|
| Nro | Número de cuota |
| Fecha | Fecha de vencimiento |
| Capital | Porción de capital de esta cuota |
| Interés | Interés proyectado |
| Saldo Capital | Capital pendiente después de esta cuota |
| Total Cuota | `capital + interés` |

#### Acciones disponibles

| Evento emitido | Condición | Descripción |
|----------------|-----------|-------------|
| `@aprobar` | `solicitud.estado === 1` | Llama `aprobarSolicitud()` en el padre → `POST /solicitud/aprobar-plan-pagos` |
| `@generar-pdf` | Siempre | Abre `GET /imprimir_cuotas?id_solicitud=` en nueva pestaña |
| `@cerrar` | Siempre | Regresa a view=0 en el padre |

**Nota:** Cuando `solicitud.estado === 2`, el botón "Aprobar" se reemplaza por un badge "Aprobada" deshabilitado, indicando que ya fue procesada.

---

### 10.3 Gestión de Cobros (`Caja/GestionCobros.vue`)

Componente usado dentro del contexto de caja abierta. Tiene dos vistas internas: `'lista'` y `'detalle'`.

#### Vista: lista de planes (`vistaInterna='lista'`)

**Tab "Cuotas Normales":**
- Filtros: criterio (Cod. Crédito / Nombre / CI), rango de fechas (inicio/fin), texto libre.
- Carga inicial al montar: `GET /get_planespago_caja` — devuelve solo planes con `desembolso=1` y `estado=1`.
- Columnas: `# Cred.`, `Cliente`, `CI`, `Desembolso`, `Monto`, `Asesor`, `F. Inicio`, `F. Fin`, `Cuotas (lapso)`, botón Cobrar.

**Tab "Órdenes de Pago":**
- Órdenes de interés/mora generadas al registrar una reprogramación.
- Columnas: `# Orden`, `Fecha`, `Cliente`, `Int. Calc.`, `Int. Cond.`, `Mora Calc.`, `Mora Cond.`, `Total Cobrar`, `Estado`, `Acción`.
- Estados de orden: `1=POR PAGAR`, `2=PAGADO`.
- Botón "Cobrar": `POST /caja/cobrar-orden-reprogramacion { id_orden }`.
- Botón "Recibo": abre PDF en nueva pestaña.

#### Vista: detalle de plan (`vistaInterna='detalle'`)

Se activa al hacer clic en "Cobrar" desde la lista. Carga:
```
GET /listar_amortizaciones_cuotas?id_plan_pago={id}
Respuesta: {
  cuotas: [...],          // con campos calculados dinámicamente
  dias_pasados_mora: N,   // días vencidos de la primera cuota pendiente
  multa_dia: 3            // constante Bs por día de mora
}
```

**Tabla de cuotas (columnas):**

| Columna | Fuente | Descripción |
|---------|--------|-------------|
| # | `cuota.numero` | Número de cuota |
| Fecha | `cuota.fecha` | Fecha de vencimiento |
| Capital | `cuota.capital` + `porcentaje_capital_pagado` | Capital programado; muestra % y monto ya pagado |
| Interés | `cuota.interes` | Interés proyectado original |
| Saldo Cap. | `cuota.saldo_capital` | Saldo proyectado |
| Total Bs | `cuota.total` | Total cuota proyectado |
| Días Trans. | `cuota.dias_transcurridos` | Días transcurridos del período |
| Int. Devengado | `cuota.interes_devengado_neto` | Interés real al día de hoy |
| Int. Moratorio | `cuota.interes_moratorio_neto` | Recargo por retraso (solo 1ra cuota) |
| Mora a Pagar | `cuota.mora_fija_neta` | Multa fija (Bs/día × días vencidos) |
| Total a Pagar | `capital_neto + interes_acumulado_neto + mora_fija_neta` | Deuda real al día |
| Int. Acumulado | `cuota.interes_acumulado_neto` | Devengado + moratorio residual |
| Estado | `cuota.estado` | Badge con color según estado y vencimiento |
| Pagar | checkbox | Selección para incluir en el cobro |

**Regla de selección de checkboxes:**
- Solo se puede seleccionar la primera cuota pendiente (estado=1 o 3).
- Cada cuota siguiente solo se habilita si la anterior ya está seleccionada.
- Desmarcar una cuota **desmarca también todas las siguientes** automáticamente.

#### Modal de cobro (`paymentModal`)

Se abre con el botón "Cobrar Cuota/s" (requiere al menos una cuota seleccionada).

**Selector de modalidad:**

| Modalidad | Etiqueta | Color activo | Efecto |
|-----------|----------|--------------|--------|
| `completo` | Cuota Completa | Verde | Mora → Interés → Capital |
| `solo_interes` | Solo Interés | Azul | Solo interés; capital queda pendiente |
| `solo_mora` | Solo Mora | Rojo | Solo multa fija |
| `interes_mora` | Interés + Mora | Amarillo | Interés + mora; sin capital |
| `parcial` | Pago Parcial (libre) | Cian | Monto libre del operador |

**Panel "Resumen de Deuda"** (computed del estado de cuotas seleccionadas):
```
totalCapital        = Σ cuota.capital_neto
totalInteresDevengado = Σ cuota.interes_devengado_neto
totalInteresMoratorio = Σ cuota.interes_moratorio_neto
totalInteres        = Σ cuota.interes_acumulado_neto   (devengado + moratorio)
totalMulta          = Σ cuota.mora_fija_neta
totalPagar          = totalCapital + totalInteres + totalMulta
```

**Cálculo del Total Líquido** (lo que el cliente realmente paga, después de condonaciones):
```
modalidad = 'solo_interes' → base = totalInteres − condonacion_interes
modalidad = 'solo_mora'    → base = totalMulta   − condonacion_mora
modalidad = 'interes_mora' → base = totalInteres + totalMulta − cond_int − cond_mora
modalidad = 'completo'
         | 'parcial'       → base = totalPagar   − cond_int − cond_mora
totalLiquido = max(0, base)
```

**Condonaciones** (campos visibles según modalidad y si hay deuda positiva):
- `monto_condonado_interes`: máximo = `totalInteres`; aparece en: completo, parcial, solo_interes, interes_mora.
- `monto_condonado_multa`: máximo = `totalMulta`; aparece en: completo, parcial, solo_mora, interes_mora.
- Si hay condonación > 0, se habilita campo de motivo de condonación.

**Campo "Efectivo a Recibir":**
- Para modalidades distintas de `'parcial'`: se **auto-rellena** con `totalLiquido` (solo lectura).
- Para `'parcial'`: editable libremente. Toggle "COBRAR TODO" rellena con `totalLiquido`.
- Validación: `monto_recibido > totalLiquido + 0.01` → error (no se puede cobrar de más).
- Si `monto_recibido < totalLiquido` en modo parcial → aviso visual del saldo pendiente.

**Al cambiar modalidad:**
- Se limpian automáticamente las condonaciones que no aplican a la nueva modalidad.
- Para `'completo'`: activa `cobrarTotal=true` → rellena monto automáticamente.
- Para `'parcial'`: resetea `monto_recibido=0` y `cobrarTotal=false`.

**Envío (`procesarPagoCuotas`):**
```
POST /pagar_cuotas
body: {
  id_plan_pago,
  cuotas: [{ id_cuota, capital_adeudado, interes_adeudado, mora_adeudada }],
  modalidad_pago,
  monto_recibido,
  fecha_pago,
  forma_pago,
  condonacion_interes,
  motivo_condonacion_interes,
  condonacion_mora,
  motivo_condonacion_mora
}

Respuesta exitosa:
  - Cierra modal
  - Recarga detalle del plan (cuotas actualizadas)
  - Ofrece generar recibo PDF: GET /imprimir/recibo/{codigo_transaccion}
```

---

### 10.4 Plan de Pagos y Reprogramación (`frmPlanPago.vue`)

#### Vistas internas (`view`)

| Valor | Pantalla |
|-------|----------|
| `0` | Lista de planes activos con filtros |
| `1` | Detalle del plan (`DetallePlanPago` component) |
| `2` | Formulario de reprogramación / refinanciamiento |

#### Vista 0 — Lista de planes

- Filtros disponibles: código crédito, nombre cliente, CI, asesor, fechas, estado (vigentes/vencidos).
- Opciones por fila: Ver Detalle, Generar Contrato, Reprogramar, Anular/Activar.
- Botón "Reprogramar" llama a `reprogramar(item, 'REPROGRAMACION')`.
- Si el plan tiene `tipo_solicitud='Refinanciamiento'` activo, llama a `reprogramar(item, 'REFINANCIAMIENTO')`.

#### Vista 1 — Detalle del plan (`DetallePlanPago`)

Muestra los datos del plan y las cuotas calculadas dinámicamente (mismos campos que la tabla de GestionCobros).
- Botón "Ver Ficha": abre modal con datos completos del cliente.
- Botón "Ver Original": si el plan es una reprogramación, carga el crédito original para comparar.

#### Vista 2 — Formulario de reprogramación/refinanciamiento

**Flujo de carga:**
```
1. Copia datos del plan a plan_pago (read-only para mostrar)
2. GET /caja/obtener-calculo-reprogramacion/{id_plan_pago}
   → monto_a_reprogramar: saldo capital real calculado en backend
3. GET /get_cuotas_plan/{id_plan_pago}
   → lista_cuotas_plan[] con todos los campos calculados (interes_acumulado_neto, mora_fija_neta, etc.)
```

**Sección: Datos del Crédito (solo lectura)**
Muestra monto, tasa, lapso, nro cuotas, destino, fechas.

**Sección: Selección de Pagos Previos**

Antes de reprogramar, el operador puede seleccionar qué intereses y mora quiere cobrar del plan anterior.
Los checkboxes de la tabla se agrupan en dos conjuntos independientes:

- **Checkbox de Interés** (columna "Int. Devengado/Moratorio"): selección acumulativa desde cuota 1 hasta la marcada.
- **Checkbox de Mora** (columna "Multa"): solo aplica a la primera cuota con mora, mismo modelo acumulativo.

Regla de habilitación:
```
La cuota en índice i es habilitada si:
  - i == 0 (primera cuota siempre habilitada), O
  - la fecha de la cuota anterior ya venció (fecha_anterior < HOY)
```

Al marcar/desmarcar se recalcula `pago_previo`:
```
pago_previo.detalle_interes = Σ interes_acumulado_neto de cuotas 0..idx_sel_interes
pago_previo.detalle_mora    = Σ mora_fija_neta de cuotas 0..idx_sel_mora
pago_previo.total_pagar     = (detalle_interes − cond_int) + (detalle_mora − cond_mora)
```

Opciones de condonación (switches):
- "Condonar Interés": habilita campo de monto (0 .. detalle_interes).
- "Condonar Mora": habilita campo de monto (0 .. detalle_mora).
- Campo motivo (habilitado si alguna condonación > 0).

**Cálculo del saldo base (computed `saldoTotalParaReprogramar`):**
```
Para cada cuota no pagada (estado ≠ 2):
  totalDeuda += capital_neto + interes_acumulado_neto + mora_fija_neta

Si hay pago_previo seleccionado:
  totalDeuda -= pago_previo.detalle_interes
  totalDeuda -= pago_previo.detalle_mora

saldoTotalParaReprogramar = max(0, totalDeuda)
```

Este valor es el `importe_solicitud` de la nueva solicitud de reprogramación.

**Sección: Datos del Nuevo Plan**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| Saldo Deuda Actual | read-only | `saldoTotalParaReprogramar` (auto-actualizado) |
| Monto Adicional | number | Solo para REFINANCIAMIENTO; efectivo extra al cliente |
| Nuevo Monto Total | calculado | `saldo + monto_adicional` (solo para refinanciamiento) |
| Forma de Pago | select | Semanal / Quincenal / Mensual |
| Plazo (Meses) | number | Nuevo plazo; cambia activa recálculo de `numero_cuotas_reprogramacion` |
| N° de Cuotas | read-only | Auto: `plazo × factor_lapso` |

**Auto-cálculo de cuotas (watchers):**
```
watch: plazo_meses, forma_pago_reprogramacion → calcularCuotasReprogramacion()

calcularCuotasReprogramacion():
  factor: Mensual=1, Quincenal=2, Semanal=4, Diario=30
  numero_cuotas_reprogramacion = round(plazo_meses × factor)
```

**Simulación del nuevo plan:**
- Botón "Calcular Nuevo Plan de Pagos": ejecuta `generarPlanPagosGeneral()` usando el mismo algoritmo de `frmSolicitud.vue` (tipo_tasa del plan original).
- La tabla resultante muestra: #, Fecha, Capital, Interés, Total Cuota, Saldo Capital.
- Botón "Generar PDF del Plan": `POST /reportes/plan-pago-reprogramado-pdf` con el plan y cuotas → descarga PDF.

**Envío final (`registrarSolicitudReprogramacion`):**

Validaciones previas:
1. `lista_cuotas.length > 0` (se debe calcular el plan antes de enviar).
2. Para refinanciamiento: `monto_adicional > 0`.

```
POST /solicitud/registrar-especial
body: {
  plan_pago: {
    id_plan_pago,
    lapso_capital (forma_pago_reprogramacion),
    nro_cuotas (numero_cuotas_reprogramacion),
    plazo_meses,
    fecha_primera_cuota,
    importe_solicitud (= saldoTotalParaReprogramar),
    tasa, moneda, tipo_tasa, ...
  },
  tipo_operacion: 'REPROGRAMACION' | 'REFINANCIAMIENTO',
  monto_adicional: N,
  pago_intereses: pago_previo | null
}
```

Resultado: crea nueva `solicitud` con `tipo_solicitud='Reprogramacion'`, bloquea el plan original (`estado=5`), y registra la `orden_pago_reprogramacion` si se seleccionaron pagos previos.
