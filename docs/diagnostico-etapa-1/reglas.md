# Reglas operativas y aceptación

## Confirmadas por el usuario el 2026-09-24

| ID | Regla | Aceptación futura |
|---|---|---|
| R-01 | Una caja abierta por usuario; distintos usuarios pueden operar simultáneamente | A y B tienen cajas diferentes; cobros, egresos y cierres de A afectan únicamente la caja autorizada de A; dos aperturas simultáneas del mismo usuario no crean dos cajas |
| R-02 (aclarada posteriormente por el usuario) | Efectivo y no efectivo integrados en caja/bóveda, con ingresos por forma de pago y total en el arqueo | Un depósito reduce deuda, pertenece a la caja y figura en arqueo/cierre y resumen de bóveda; no incrementa efectivo físico ni exige módulo o conciliación bancaria independiente |

## Reglas existentes del proyecto que deben conservarse

- Efectivo físico institucional = saldo de bóveda + suma del saldo físico de todas las cajas abiertas. El saldo histórico de una caja cerrada no se vuelve a sumar.
- Apertura, transferencia y retorno al cierre conservan ese total. Cobro en efectivo lo aumenta; desembolso/gasto en efectivo lo reduce.
- Desembolso no descuenta directamente de bóveda. No usar ActualizaSaldoBoveda.
- Dinero: transacción, validación de saldo dentro de ella y trazabilidad inmutable.
- Pago completo/parcial: mora → interés → capital. Solo mora, solo interés e interés+mora limitan los componentes aplicables.
- tipo_pago debe persistirse en cada registro de pagarCuotas.
- Cuota pagada únicamente si no queda deuda de capital, interés ni mora; fecha_ultima_amortizacion cambia al completar una cuota.
- Transferencias internas excluidas de ingresos y egresos reales.

Estas son condiciones objetivo. FIN-07, FIN-09 y REP-01 muestran divergencias de la implementación actual.

## Decisiones pendientes de validación institucional

| ID | Decisión | Por qué se necesita | Bloquea |
|---|---|---|---|
| D-01 | Quién puede condonar, límites e información obligatoria | El permiso de caja actual no distingue aprobación de condonaciones | Diseño de permisos granulares / etapa 2 y cobros |
| D-02 | Anulación de pagos/gastos de caja cerrada: rechazo o asiento compensatorio autorizado | Reescribir una caja cerrada puede deshacer una conciliación | Corrección de anulaciones históricas |
| D-03 | Fechas retroactivas y reloj de mora; tasa/divisor/multa fija válidos | El código contiene cálculos por lapso y multa diaria fija de 3; no equivale a una regla ratificada | Matriz completa de intereses/mora |
| D-04 | Exceso de efectivo: rechazo, cambio o abono adelantado | No puede perderse dinero recibido por limitar modalidades | Cobros y recibos |
| D-05 | Formas habilitadas, referencia/respaldo y pagos mixtos | R-02 aclara registro integrado y totales por forma en el arqueo; faltan detalles operativos, sin conciliación bancaria independiente | Modelo de formas de pago y UI |
| D-06 | Facultades del supervisor sobre caja ajena y transferencias entre usuarios | R-01 establece propiedad; faltan excepciones autorizadas | Política de acceso y cierres |
| D-07 | Tratamiento de reprogramación, refinanciamiento y crédito original | La aceptación exige estados y trazabilidad precisos | Completar estos flujos |
| D-08 | Homónimos y unicidad de CI/documentos | El alta bloquea mismo nombre además de CI | Validación de clientes/codeudores |

La tabla refleja las decisiones del diagnóstico inicial. Actualización posterior de D-03/D-04: el usuario confirmó conservar las fórmulas, multa de 3 Bs diarios por crédito sin acumulación por cuota ni días de gracia; permitir fechas anteriores con permiso específico; cambio en efectivo, rechazo de excedentes por depósito/transferencia y adelantos de cuotas/capital sin cobrar interés pendiente. Aclaración confirmada: se cobra el interés devengado hasta la fecha efectiva del pago y se excluye únicamente el interés futuro correspondiente al capital adelantado; los adelantos parciales recalculan el interés sobre el capital restante. Ver [avance de fase 3](../fase-3-avance.md). Son reglas confirmadas pendientes de implementación, no cambios ya aplicados a los históricos.

## Escenarios de aceptación transversales

Actualización posterior de D-05: confirmadas las formas Efectivo, Depósito y Transferencia / QR; referencia obligatoria e imagen opcional para cada componente no efectivo; pagos mixtos dentro de una operación, aplicables a cuotas, administrativos, ingresos, gastos y desembolsos. Todos los componentes figuran en caja, arqueo, cierre y trazabilidad de bóveda; únicamente la porción efectiva modifica el saldo físico. La propuesta inicial de limitar a una forma por operación queda sustituida. Implementación pendiente en fase 3.

Actualización posterior de D-01/D-02: confirmados permisos específicos e independientes de condonación y anulación. Condonar hasta el total del interés y multa pendientes, sin capital, con motivo obligatorio. Anular solo desde caja propia abierta, con motivo; bloquear caja cerrada, pagos posteriores en el mismo crédito y reversión con saldo insuficiente. Conservar trazabilidad y revertir una sola vez, incluyendo condonación asociada al cobro. Estas reglas están aprobadas y pendientes de implementación en fase 3.

1. Bóveda 1.000; abrir caja A con 100 y B con 200 → bóveda 700, cajas 300, total 1.000.
2. Transferir 50 de bóveda a A → bóveda 650, A 150, B 200, total 1.000. Reintentar con la misma identidad no cambia nada.
3. Cobro efectivo 30 en B → B 230, total físico 1.030. Cobro por depósito 40 en B → saldo físico idéntico; arqueo de B con efectivo 30, depósito 40 y total de ingresos 70, consultable desde bóveda; deuda reducida según modalidad.
4. Cerrar A retorna una vez sus 150; B permanece abierta y disponible. Segundo cierre no registra otro retorno.
5. Anular transferencia abierta restituye ambos lados una sola vez y conserva auditoría.
6. Peticiones sin permiso o de otro usuario no modifican saldos; un fallo intermedio no deja movimientos parciales.
7. Libro financiero conserva capital total al transferir/cerrar y coincide con pantalla/PDF/Excel para el mismo corte.
