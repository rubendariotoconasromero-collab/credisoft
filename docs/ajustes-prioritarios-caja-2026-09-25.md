# Ajustes prioritarios de caja — 25/09/2026

## Trabajo realizado

### Interés moratorio de cuotas vencidas

`Debt` calcula el moratorio de cada cuota pendiente o parcial con su interés programado, días vencidos y divisor del período. Antes lo asignaba únicamente a la primera cuota pendiente; al liquidarla, la siguiente adquiría un importe que no figuraba al cobrarlas juntas. Consulta y cobro siguen usando el mismo servicio.

El escenario reproducido tenía dos cuotas de capital Bs 1.000 e interés Bs 100, vencidas el 10 y 17 de septiembre, con corte el 24 de septiembre. Antes: Bs 2.288,67 juntas y Bs 2.312,00 separadas. Ahora ambos recorridos cobran Bs 2.312,00: capital 2.000, interés ordinario 200, moratorio 70 y multa fija 42. La multa conserva Bs 3 diarios por crédito, sin multiplicarse por cuota.

Esto modifica el importe calculado para cuotas pendientes posteriores que ya estén vencidas. No modifica pagos ni cuotas liquidadas previamente, ni reconstruye históricos. El moratorio sigue usando la fórmula y el divisor existentes; el cambio elimina su dependencia de la posición de primera cuota pendiente.

### Reintentos de pago administrativo y desembolso

`CashMovements` envuelve el proceso conjunto en `Operations`, que conserva clave, huella y respuesta dentro de una transacción con bloqueo financiero. Recupera un resultado previo antes de validar el saldo de una operación nueva.

La operación principal guarda el resultado y la caja, sin importe ni dirección contable: solamente sus dos operaciones hijas aportan ingresos y egresos. Reutilizar la misma clave con otros datos devuelve 409. El reintento de una operación nueva completada también funciona después de cerrar su caja cuando se especifica el mismo id_caja.

Se recuperan operaciones del formato anterior con ambas hijas y la misma clave en caja abierta; las huellas de las hijas validan los datos. Una única hija provoca rechazo y revisión. El formato anterior sin registro principal sigue sujeto a la restricción de caja cerrada del middleware; no se reconstruyen retrospectivamente operaciones en cajas cerradas.

## Verificación

- `php artisan test`: 79 pruebas, 1087 comprobaciones, todas aprobadas en SQLite en memoria.
- Cuatro pruebas nuevas en `tests/Feature/CashFlowPriorityTest.php`: igualdad de cobro conjunto/separado; interés pagado que no reaparece al avanzar de cuota; reintento con saldo consumido, conflicto de datos y caja cerrada; recuperación del formato anterior en caja abierta.
- Las pruebas existentes de insuficiencia de saldo y fallo en la segunda escritura siguen comprobando la reversión completa del pago administrativo y desembolso.
- El diagnóstico temporal anterior se sustituyó por pruebas de regresión dentro de la suite.
- No se ejecutaron migraciones ni escrituras monetarias en la base habitual. No hubo cambios de frontend que requirieran recompilación.

## Pendientes

Actualización posterior del 25/09/2026: ver [resolución de pendientes](pendientes-caja-resueltos-2026-09-25.md). La lista siguiente conserva el estado al terminar los ajustes prioritarios.

1. Sincronizar fecha, revisión de deuda e importe cuando se cancela y reabre un cobro retroactivo.
2. Implementar integralmente el cobro de órdenes de reprogramación, o deshabilitar la acción hasta que esté disponible; su ruta de cobro sigue ausente.
3. Mejorar el listado para que el filtro inicial de tres meses no oculte créditos antiguos pendientes.
4. Actualizar la documentación anterior de fase 3 que aún dice que el cierre retorna únicamente efectivo; la regla vigente retorna el saldo operativo total y conserva el arqueo físico separado.
5. Realizar aceptación manual con datos DEMO y comprobar estos cambios en MySQL aislado con concurrencia. Esta ejecución verificó SQLite; no constituye validación completa de navegador, concurrencia ni datos históricos reales.
