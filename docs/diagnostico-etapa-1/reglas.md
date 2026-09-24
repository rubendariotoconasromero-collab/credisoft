# Reglas operativas y aceptación

## Confirmadas por el usuario el 2026-09-24

| ID | Regla | Aceptación futura |
|---|---|---|
| R-01 | Una caja abierta por usuario; distintos usuarios pueden operar simultáneamente | A y B tienen cajas diferentes; cobros, egresos y cierres de A afectan únicamente la caja autorizada de A; dos aperturas simultáneas del mismo usuario no crean dos cajas |
| R-02 | Depósitos y transferencias bancarias separados del efectivo físico | Un cobro bancario reduce la deuda y queda conciliable con su cuenta bancaria, pero no incrementa efectivo de caja o bóveda |

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
| D-05 | Cuentas bancarias, referencia, fecha de abono y pagos mixtos | R-02 confirma separación; falta definir cómo registrar/conciliar bancos | Modelo bancario y UI |
| D-06 | Facultades del supervisor sobre caja ajena y transferencias entre usuarios | R-01 establece propiedad; faltan excepciones autorizadas | Política de acceso y cierres |
| D-07 | Tratamiento de reprogramación, refinanciamiento y crédito original | La aceptación exige estados y trazabilidad precisos | Completar estos flujos |
| D-08 | Homónimos y unicidad de CI/documentos | El alta bloquea mismo nombre además de CI | Validación de clientes/codeudores |

No se asumieron respuestas ni se implementaron esas políticas.

## Escenarios de aceptación transversales

1. Bóveda 1.000; abrir caja A con 100 y B con 200 → bóveda 700, cajas 300, total 1.000.
2. Transferir 50 de bóveda a A → bóveda 650, A 150, B 200, total 1.000. Reintentar con la misma identidad no cambia nada.
3. Cobro efectivo 30 en B → B 230, total físico 1.030. Cobro bancario 40 → saldo físico idéntico; banco +40; deuda reducida según modalidad.
4. Cerrar A retorna una vez sus 150; B permanece abierta y disponible. Segundo cierre no registra otro retorno.
5. Anular transferencia abierta restituye ambos lados una sola vez y conserva auditoría.
6. Peticiones sin permiso o de otro usuario no modifican saldos; un fallo intermedio no deja movimientos parciales.
7. Libro financiero conserva capital total al transferir/cerrar y coincide con pantalla/PDF/Excel para el mismo corte.
