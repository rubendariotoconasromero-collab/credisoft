> Ejecución actualizada el 24/09/2026: ver `fase-3-avance.md` y `fase-3-pruebas-manuales.md`. Las decisiones D-01 a D-05 están implementadas; este documento conserva el plan de referencia.

# Plan de desarrollo — Fase 3: integridad financiera

Fecha: 24 de septiembre de 2026. Estado: **implementación parcial en curso**. Ver [avance y evidencia](fase-3-avance.md). Las tres consultas sobre cobros, adelantos, condonación, anulación y medios de pago están respondidas. Estas respuestas definen requisitos; no significan que su implementación ya esté terminada.

## Objetivo y alcance

Conseguir que cada operación financiera registre una sola vez el importe correcto, quede vinculada a su caja y a la trazabilidad institucional de bóveda, y pueda conciliarse con cuotas, arqueos y reportes por forma de pago. Las pantallas, recibos y exportaciones deben mostrar los mismos resultados para la misma fecha de corte.

Esta fase aborda B-03 a B-07 del [backlog](diagnostico-etapa-1/backlog.md), más los contratos de historial/anulación de B-08 necesarios para operar esos flujos. La reprogramación/refinanciamiento completa, las funcionalidades restantes de clientes y la optimización general de rendimiento se mantienen fuera de este alcance. No se incluye un módulo bancario independiente, administración de saldos por cuenta, conciliación con extractos bancarios ni un nuevo sistema de contabilidad completo.

La fase 2 ya implementó permisos, propietario de caja, cartera asignada y protección de archivos. No se deben volver a considerar pendientes todos los hallazgos del diagnóstico original: primero se reclasifican en corregidos, parcialmente resueltos o pendientes mediante pruebas del código actual. Su aceptación manual por el usuario sigue separada del cierre técnico.

## Aclaración de alcance del usuario

El control será integrado en caja y bóveda para efectivo y operaciones no efectivas. La distinción por forma de pago sirve para mostrar los ingresos de cada medio y el total de la operación diaria; no supone separar la gestión en un módulo bancario.

El arqueo incluirá ingresos en efectivo, depósito, transferencia y las demás formas habilitadas, además del total recibido. El conteo físico y su diferencia se calculan sobre el efectivo. Los importes no efectivos forman parte del resumen y cierre, con su trazabilidad, sin exigir un conteo físico o conciliación bancaria independiente.

En bóveda se podrá consultar el resumen por caja/cierre y forma de pago, incluido lo no efectivo. Esa relación es de registro y trazabilidad: `boveda.saldo_actual` mantiene su significado de efectivo físico disponible. Vincular un cobro no efectivo o su cierre a bóveda no debe generar un segundo ingreso ni un retorno físico ficticio.

Ejemplo: una caja cobra 100 Bs en efectivo, 200 Bs por depósito y 50 Bs por transferencia. Su arqueo muestra **350 Bs de ingresos**, desglosados en 100/200/50. El efectivo esperado se calcula con la apertura y demás movimientos físicos; no equivale necesariamente a 100 Bs. Al cerrar, se conserva el resumen completo de 350 Bs y solo se retorna el efectivo realmente disponible. No confundir ingresos del período con saldo disponible ni sumar la apertura como un ingreso externo.

## Condiciones que deben conservarse

- Una caja abierta por usuario; varios usuarios pueden trabajar simultáneamente.
- Solo el propietario opera y cierra su caja. Administradores y encargados consultan las ajenas.
- Oficiales limitados a solicitudes y créditos asignados.
- Efectivo institucional = bóveda física + suma de saldos físicos de todas las cajas abiertas, por moneda. No sumar cajas cerradas ni bancos a ese indicador, ni mezclar monedas sin conversión definida.
- Aperturas, transferencias internas y retorno al cierre conservan el efectivo institucional. Un desembolso en efectivo resta de caja, sin un segundo descuento de bóveda.
- Depósitos y transferencias reducen deuda cuando corresponda y se registran en la caja del usuario; integran el arqueo, el cierre y la consulta vinculada a bóveda por forma de pago. No incrementan el efectivo físico disponible.
- Cobro completo/parcial: mora → interés → capital. Las otras modalidades limitan los componentes aplicables.
- Condonación reduce deuda y no representa dinero recibido.
- Cuota pagada solo cuando capital, interés y mora pendientes sean cero; conservar el registro de la modalidad y recalcular coherentemente las fechas del plan.
- Operaciones monetarias atómicas, auditables y sin borrado de movimientos. No utilizar `ActualizaSaldoBoveda`.

## Secuencia de implementación

| Etapa | Trabajo | Entregable y criterio de salida | Dependencia |
|---|---|---|---|
| 3.1 Diagnóstico actualizado y contratos | Reproducir los casos financieros sobre el código posterior a fase 2; inventariar rutas, variantes de cobro, estados y columnas; acordar ejemplos numéricos y decisiones pendientes | Matriz actualizada, pruebas que reproducen los defectos vigentes y especificación de cada operación con entrada, resultado, error y efecto en saldos | Base de fase 2 |
| 3.2 Precisión, medios de pago y trazabilidad | Definir representación monetaria exacta, formas de pago, vínculos con caja/bóveda, claves de reintento y migraciones incrementales | Modelo y servicios comunes probados; un cobro no efectivo aparece en caja, arqueo y resumen de bóveda sin aumentar efectivo físico; informe de históricos ambiguos | 3.1; detalles de formas de pago y redondeo |
| 3.3 Caja, bóveda y transferencias | Unificar apertura, transferencias y cierre bajo transacciones, bloqueos y restricciones de unicidad; registrar ambas contrapartidas y retorno único | Dos usuarios operan sin cruzar cajas; no hay doble apertura/cierre ni transferencias incompletas; conservación del efectivo | 3.2 |
| 3.4 Deuda, cobros y desembolsos | Centralizar cálculo en servidor; aplicar cinco modalidades, condonaciones autorizadas y medios de pago; validar desembolso contra crédito aprobado | Manipular los importes enviados por Vue no modifica la deuda real; cobros concurrentes no sobrepagan; un desembolso no se duplica | 3.2–3.3; reglas de interés/mora, fechas y excedentes |
| 3.5 Anulaciones y auditoría | Revertir transacciones completas y transferencias vinculadas; reconstruir acumulados y estados; conectar los historiales pendientes | Anulación exacta una sola vez, con autor/motivo y comprobante; tratamiento definido de cajas cerradas y operaciones posteriores | 3.3–3.4; política de anulación |
| 3.6 Conciliación, pantallas y reportes | Integrar arqueo y resúmenes por forma de pago en caja/bóveda; clasificar retornos y transferencias por tipo explícito; alinear saldos iniciales, movimientos y saldos finales | Pantalla/PDF/Excel coinciden; transferencias internas no inflan ingresos/egresos; diferencias históricas quedan identificadas | 3.3–3.5 |
| 3.7 Validación integral y entrega | Pruebas de concurrencia en MySQL, fallos intermedios, migración desde esquema anterior, población reproducible y recorrido manual | Evidencia de pruebas y conciliación, guía de actualización/recuperación y aceptación manual por separado | Todas las anteriores |

Cada etapa se entrega con sus pruebas y documentación. No esperar al final para probar dinero o permisos. La etapa siguiente comienza cuando sus dependencias técnicas y operativas estén resueltas.

## Desarrollo por etapa

### 3.1 — Base de trabajo

1. Contrastar FIN-01 a FIN-09, REP-01 y RISK-01/02/03 con el código actual. Mantener como regresiones las protecciones de fase 2, aunque parte del defecto original ya no sea reproducible por HTTP.
2. Inventariar todos los puntos de escritura: cobro individual, cobro múltiple, amortización, liquidación, administrativo, desembolso, ingreso/egreso, transferencia y anulación. Ninguna ruta antigua debe permitir eludir las reglas nuevas.
3. Documentar los distintos significados de `estado` por tabla; no asumir que 1 significa activo en todas.
4. Fijar ejemplos esperados independientes del código, con fecha congelada, pagos parciales y tasa fraccionaria. La semilla actual es un escenario reproducible, no la validación institucional de una fórmula.
5. Elaborar un diagnóstico de históricos de solo lectura: duplicados, cajas abiertas por usuario, medios de pago, vínculos y diferencias de saldo.

### 3.2 — Modelo de dinero

- Proponer `DECIMAL` en persistencia y aritmética decimal o unidades mínimas en servicios; evitar operaciones financieras con `float`. Definir precisión de tasas/intermedios y cuándo redondear, sin truncar tasas fraccionarias ni perder centavos en la última cuota.
- Normalizar las formas de pago y conservar importe, moneda, fecha, referencia/respaldo cuando corresponda y vínculo a operación, usuario y caja. Relacionar estos registros con el resumen de cierre consultable desde bóveda, sin crear un catálogo de cuentas con saldos bancarios ni exigir conciliación con extractos.
- Formas aprobadas: Efectivo, Depósito y Transferencia / QR. Cada componente no efectivo exige su referencia; adjuntar imagen es opcional y mantiene las protecciones de archivos de fase 2.
- Permitir pagos mixtos dentro de una sola operación, con desglose de importes y referencias por componente. Aplicarlo a cuotas, administrativos, ingresos, gastos y desembolsos. No representar un pago mixto únicamente con una etiqueta sin desglose ni obligar a crear operaciones independientes.
- Validar en centavos que la suma de importes aplicados por forma coincide con el total de la operación. En cobros, distinguir efectivo entregado, efectivo aplicado y cambio; solo el efectivo aplicado aumenta el saldo físico. No generar cambio ni saldo a favor a partir de un excedente no efectivo.
- Todas las formas de pago mantienen la relación con la sesión de caja y su propietario. Adaptar `CalculaSaldoCaja` para efectivo disponible y añadir agregados explícitos de ingresos por forma de pago y total registrado. Reutilizar esos agregados en arqueo, cierre y resumen de bóveda.
- Usar tipos explícitos y referencias entre operaciones/contrapartidas. Evitar clasificar nuevos movimientos por palabras de una descripción libre.
- Diseñar claves de idempotencia: el mismo intento devuelve el resultado ya registrado; la misma clave con contenido diferente se rechaza. Cubrir también reintentos después de perder la respuesta HTTP y operaciones realizadas desde dos pestañas.
- La clave de reintento y su hash abarcan toda la operación mixta, incluido el desglose. Todos los componentes se confirman o revierten juntos; nunca se deja un componente confirmado si falla otro.
- Añadir índices y restricciones solo después de diagnosticar duplicados. Para una sola caja abierta por usuario, definir una solución compatible con MySQL y probarla también bajo concurrencia.
- Crear migraciones nuevas; no reescribir migraciones históricas como sustituto de actualizar instalaciones existentes. Ensayar rango, nulos, valores anteriores y conservación de importes.
- No inventar formas de pago, relaciones con cajas o saldos históricos cuando no exista evidencia suficiente. Entregar clasificación y propuesta por registro para revisión.

### 3.3 — Caja y bóveda

- Extraer servicios de apertura, transferencia y cierre que sean usados por todos sus puntos de entrada.
- Bloquear los registros necesarios en orden uniforme para evitar sobreventa de saldo y reducir interbloqueos. Cuando aún no exista caja abierta, la exclusión debe apoyarse en un registro estable y/o restricción de BD, no en bloquear una consulta vacía.
- Validar propietario, permisos, estado, moneda e importe dentro de la transacción; cerrar correctamente cualquier salida por error.
- Registrar transferencia y contrapartida de forma atómica. Saldo insuficiente o destino inválido debe dejar ambos lados sin cambios.
- El cierre incluye todas las formas de pago y conserva sus totales en el arqueo y resumen vinculado a bóveda. El retorno físico mueve únicamente el efectivo disponible, una sola vez; los registros no efectivos permanecen vinculados, sin simular una entrega de billetes ni duplicar ingresos.
- Probar cierre simultáneo con cobro o egreso: la operación se registra antes del cierre o se rechaza; nunca queda dinero fuera del retorno.

### 3.4 — Cobros y desembolsos

- Crear un servicio de cálculo compartido para consulta de deuda, confirmación de cobro y recibo. El cliente envía intención, cuotas, medio e importe; el servidor obtiene deuda, tasas, fechas y acumulados de sus propios registros.
- Recalcular bajo bloqueo al confirmar. Si la vista quedó desactualizada por otro pago, devolver un conflicto explicable y los datos necesarios para refrescarla.
- Validar orden de cuotas, pertenencia al plan, cantidades positivas, moneda, límites y distribución. Aplicar condonaciones separadas del dinero recibido y dentro de la facultad aprobada.
- Cubrir `completo`, `parcial`, `solo_interes`, `solo_mora` e `interes_mora`, incluidos pagos que atraviesen más de una cuota y cierre de deuda.
- Guardar el desglose aplicado y la fecha efectiva para que un recibo histórico no cambie sus importes cobrados al volver a imprimirse. Cualquier saldo consultado posteriormente debe distinguir su fecha de corte.
- Validar que el crédito esté aprobado y pendiente de desembolso, con importe y origen de fondos correctos. Proteger la unicidad tanto con bloqueos como con una restricción compatible con el ciclo de anulaciones definido.
- Integrar los cobros administrativos y rutas legadas con las mismas reglas de precisión, medio, permisos y reintentos. Si una ruta se retira, actualizar sus consumidores y devolver una respuesta explícita.

### 3.5 — Anulaciones

- Registrar autor, motivo, fecha y vínculo al original; conservar movimientos originales y asientos/contrapartidas de reversión.
- Revertir capital, interés, mora, condonaciones, registros de caja/bóveda por forma de pago y estados de cuota/plan dentro de una sola transacción. La repetición no debe restar ni devolver dinero otra vez.
- En operaciones mixtas, revertir todos los componentes conservando las formas e importes originales; no transformar una transferencia o depósito en devolución de efectivo.
- Para transferencias, revertir ambos lados juntos, verificando disponibilidad y estados de las cajas implicadas.
- Definir el comportamiento cuando existen pagos posteriores, crédito cerrado o una caja ya cerrada: bloquear o aplicar un procedimiento compensatorio autorizado, sin reabrir ni modificar silenciosamente el cierre.
- Completar los contratos financieros FUN-02b/c/d/e/f/g: listados generales, historial agrupado y anulación por transacción. No activar botones mediante simples alias antes de corregir el efecto financiero.

### 3.6 — Conciliación y presentación

- Presentar en caja, arqueo, cierre y consulta de bóveda los ingresos por forma de pago y su total conjunto, con acceso al movimiento original. Mostrar también apertura, egresos y efectivo esperado/contado/diferencia donde corresponda. Las formas no efectivas son totales registrados, sin control de saldo bancario independiente. Distinguir ingresos del período, efectivo disponible y capital de cartera.
- Mostrar en recibos, pantallas y exportaciones el desglose de cada operación mixta, sus referencias y el cambio cuando corresponda. Sumar sus componentes una sola vez en los totales por forma y en el total conjunto.
- Corregir REP-01: un retorno de caja conserva fondos y no es un ingreso externo.
- Establecer para cada reporte el saldo anterior al rango, movimientos dentro del rango y saldo final. La paginación no debe modificar los totales.
- Reutilizar la misma consulta/regla de agregación en pantalla, PDF y Excel; comprobar también anulaciones, filtros, fechas de corte y permisos de cartera.
- Entregar una consulta/comando de conciliación de solo lectura con diferencias por operación. No compensar diferencias creando movimientos automáticos sin revisión.
- Optimizar consultas del alcance cuando sea necesario para obtener resultados correctos; la optimización general del historial masivo se mantiene en B-10.

### 3.7 — Pruebas, población y actualización

- Mantener la suite de fase 2 como regresión y ampliar pruebas de servicios, HTTP y persistencia.
- Utilizar MySQL aislado con conexiones/procesos independientes para bloqueos, unicidad y concurrencia. SQLite puede cubrir lógica rápida, pero no sustituye estas pruebas.
- Probar tanto instalación limpia (`migrate:fresh --seed` únicamente en BD desechable) como actualización incremental desde el esquema previo con datos representativos.
- Ampliar la población DEMO con pagos por depósito/transferencia vinculados a caja y reflejados en arqueo y resumen de bóveda, reintentos y anulaciones válidas una vez aprobadas e implementadas sus políticas. Publicar los nuevos saldos esperados; no conservar cifras anteriores artificialmente si se amplían los escenarios.
- Mantener el seed reproducible y las cuentas de los distintos perfiles para que el usuario pueda repetir las pruebas manuales.
- Documentar respaldo/restauración, compatibilidad de código con las migraciones y estrategia de recuperación. No prometer que un rollback de código puede deshacer operaciones financieras nuevas.
- En la BD habitual, comenzar con diagnóstico de solo lectura. Las reparaciones de históricos requieren propuesta concreta, respaldo y autorización específica; no ejecutar `migrate:fresh` para aplicar esta fase.

## Decisiones operativas necesarias

Estas decisiones no impiden preparar pruebas ni diseñar los servicios, pero sí cerrar los comportamientos que dependen de ellas. Las propuestas no se implementarán por silencio. El usuario respondió la consulta 1: conservar fórmulas, multa de 3 Bs diarios por crédito sin acumular por cuota ni días de gracia; permitir fechas anteriores con permiso específico; cambio en efectivo y rechazo de excedentes no efectivos; adelantos de cuotas/capital sin cobrar interés pendiente. Aclaración confirmada: cobrar el interés ya devengado hasta la fecha efectiva del pago y excluir solo el interés futuro del capital adelantado; en adelantos parciales recalcular sobre el capital restante. Ver [decisiones actualizadas](fase-3-avance.md).

| Decisión | Definición necesaria | Momento |
|---|---|---|
| D-01 Condonaciones | Confirmado permiso específico, hasta el total del interés y multa pendientes, sin capital, motivo obligatorio y trazabilidad. Se conserva caja propia; el beneficio por adelanto no exige este permiso | Regla aprobada; implementar en 3.4 |
| D-02 Anulaciones | Confirmado permiso específico independiente, caja propia abierta y motivo obligatorio. Bloquear caja cerrada, pagos posteriores en el mismo crédito y reversión con saldo insuficiente. Revertir cobro y condonación asociada una sola vez, conservando el original | Regla aprobada; implementar en 3.5 |
| D-03 Interés, mora y fechas | Confirmado conservar fórmulas y multa diaria de 3 Bs por crédito, sin multiplicar por cuotas ni días de gracia. Fechas anteriores con permiso específico. Adelantos: cobrar interés devengado y excluir el futuro del capital adelantado; recalcular sobre el capital restante. Falta resolver la interacción de fechas retroactivas con operaciones posteriores | Antes del cálculo definitivo de 3.4 |
| D-04 Excedentes | Confirmado cambio en efectivo, rechazo de excedentes por depósito/transferencia y adelantos de cuotas/capital. No crear saldos a favor automáticamente; distinguir entrega, aplicación y cambio | Antes de cerrar 3.4 |
| D-05 Formas de pago | Confirmado: Efectivo, Depósito y Transferencia / QR; referencia obligatoria e imagen opcional para cada componente no efectivo; pagos mixtos permitidos. Aplica a cuotas, administrativos, ingresos, gastos y desembolsos. Registro integrado en caja/bóveda, sin módulo bancario independiente | Regla aprobada; implementar desde 3.2 |

D-06 ya fue resuelta: solo el propietario opera; supervisores consultan. D-07 (reprogramación) y D-08 (identidad/homónimos) corresponden a otros paquetes y no deben bloquear las correcciones independientes de esta fase.

## Matriz mínima de aceptación

| Caso | Resultado verificable |
|---|---|
| Bóveda 1.000; apertura A=100 y B=200 | Bóveda 700, cajas 300, efectivo institucional 1.000 |
| Transferir 50 de bóveda a A | Bóveda 650, A=150, B=200, total 1.000; reintento sin efecto adicional |
| Cobro efectivo 30 en B y cobro por depósito 40 en B | Efectivo de B=230; efectivo institucional=1.030. Arqueo de B: ingresos efectivo=30, depósito=40, total=70; ambos cobros vinculados a B y consultables desde bóveda; deuda reducida en 70 |
| Cerrar A después de esos movimientos | Bóveda física=800, efectivo de B=230, total físico=1.030; los ingresos no efectivos de B siguen registrados; segundo cierre de A sin segundo retorno |
| Cerrar una caja con ingresos por efectivo, depósito y transferencia | Arqueo y resumen de bóveda conservan el total y desglose de todas las formas; solo el remanente físico se suma a `boveda.saldo_actual`; no se duplican ingresos |
| Una operación mixta de 150: efectivo 100 y Transferencia / QR 50 | Una operación y recibo con ambos componentes; ingreso total 150, incremento físico 100, referencia obligatoria para los 50 no efectivos |
| Cobro de 150: transferencia 50 y efectivo entregado 120 | Aplicar transferencia 50 y efectivo 100; cambio 20; total aplicado 150, incremento físico 100; conservar entrega y cambio en recibo |
| Cobro de deuda 150 con componente no efectivo de 160 | Rechazar sin escrituras; no devolver los 10 de excedente como efectivo ni crear saldo a favor |
| Desembolso mixto de 1.000: efectivo 300 y transferencia 700 | Una operación vinculada al crédito y caja; salida total 1.000, disminución física 300; referencia para 700; no descontar nuevamente de bóveda |
| Componente no efectivo sin referencia o fallo al guardar un componente mixto | Rechazo o rollback de toda la operación, sin cobro, desembolso o movimiento parcial |
| Reintentar o anular una operación mixta | Reintento sin duplicación; anulación autorizada revierte todos los componentes una sola vez con su desglose original |
| Cuota de capital 1.000 y petición manipulada que declara deuda 1 | El servidor conserva el residual correcto; pagar 1 no liquida el crédito |
| Dos cobros simultáneos sobre la misma cuota | No se cobra más deuda de la disponible ni se duplican componentes |
| Dos aperturas simultáneas del mismo usuario | Exactamente una caja abierta; otros usuarios pueden abrir la suya |
| Dos desembolsos simultáneos/reintentados | Una sola salida válida por el desembolso autorizado |
| Dos retiros que juntos exceden el disponible | Solo se confirman los compatibles con el saldo; no queda saldo negativo por la carrera |
| Fallo tras escribir la primera contrapartida | Rollback completo: sin operación parcial ni registro de éxito reutilizable |
| Anular pago o transferencia dos veces | Una sola reversión, motivo/autor conservados y saldo/estado exactos |
| Condonación de interés/mora | Reduce deuda, no incrementa efectivo ni los totales de dinero recibido por forma de pago |
| Importes con centavos y tasas fraccionarias | Distribución exacta según política aprobada; no aparecen diferencias ocultas |
| Cierre/transferencia en libro, pantalla, PDF y Excel | Mismo saldo por corte; sin ingresos/egresos reales inflados |
| Usuario sin permiso o caja/cartera ajena | Rechazo HTTP sin cambios de dinero, deuda ni auditoría de éxito |

Los primeros cuatro casos forman una secuencia; los demás tendrán fixtures independientes para evitar que una prueba altere los resultados de otra.

## Cierre de fase

La fase se considera implementada cuando las decisiones aplicables estén registradas, los defectos financieros incluidos tengan pruebas de regresión aprobadas, la concurrencia y migración se hayan ensayado en MySQL, las cifras concilien y las pantallas/recibos permitan completar los recorridos definidos. Las incidencias ajenas al alcance deben quedar expresamente documentadas.

La aceptación operativa exige además las pruebas manuales del usuario. Completar esta fase no equivale a terminar todos los módulos ni a certificar la puesta en producción.

Primer bloque de ejecución: **3.1**, seguido de **3.2**. Preparar el informe y las pruebas antes de modificar datos históricos o publicar cambios en las reglas de cobro.
