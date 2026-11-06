# Fase 3 — guía de pruebas manuales

Usar exclusivamente la población de escenarios (`SEED_PROFILE=demo`), cuyos registros tienen identidades simuladas sin etiquetas de demostración. `php artisan migrate:fresh --seed` elimina la base seleccionada: ejecútalo solo cuando decidas reiniciar tu desarrollo. Para probar sobre tu base actual no hace falta ese comando; las migraciones incrementales ya están aplicadas.

Las cuentas y escenarios están en `poblacion-desarrollo.md`. En un seed nuevo, `lucia.rivera` mantiene caja A y `andres.flores` caja B. Las operaciones requieren caja propia. El administrador asigna por rol condonar/anular_finanzas/pagos_retroactivos según lo que quieras probar; sus permisos no autorizan operar la caja del cajero.

1. Entrar como `lucia.rivera`. Comprobar caja A: 2.160 Bs disponibles, de los que 2.100 Bs corresponden a efectivo; historial de Elena Patricia Guzmán Vera con operación mixta de 100 Bs y tres formas de pago.
2. Cobrar una cuota parcialmente: importe aplicado 150, distribuir efectivo entregado 120 y transferencia 50 con referencia. Esperado: aplicado150, cambio20, aumento físico100. Recibo único, deuda reducida según cascada. Intentar no efectivo160 contra aplicado150: rechazo.
3. Omitir referencia de depósito/transferencia: no permite registrar. Adjuntar imagen PNG/JPG/GIF: abrirla desde detalle del historial. Si la subida falla, la operación ya confirmada permanece registrada y se puede adjuntar desde el movimiento de caja.
4. Adelanto: elegir crédito vigente, fecha actual y capital adelantado. Cobrar interés devengado a la fecha, no el futuro. Consultar el plan después; el principal residual y el interés futuro deben reducirse. Anotar antes/después para revisar con ejemplos de tu operación real.
5. Crédito con varias cuotas vencidas: verificar una multa por crédito de Bs3 por día de atraso, no Bs3 por cuota/día.
6. Sin condonar: el panel no ofrece condonación y el servidor la rechaza. Con permiso: condonar interés/multa pendientes indicando motivo; no cambia efectivo por el importe condonado. No debe permitir capital ni un importe superior a la deuda.
7. Con pagos_retroactivos: cambiar fecha, volver a seleccionar cuotas y registrar. Debe pertenecer a la caja actual. No permite fecha anterior al crédito, futura ni anterior a pagos ya existentes. Sin permiso: fecha bloqueada.
8. Con anular_finanzas, desde movimiento de caja o historial: anular indicando motivo. Debe revertir todos los componentes y condonaciones juntos; repetir no altera saldo otra vez. Caja ajena/cerrada, pago posterior o efectivo insuficiente bloquean la reversión.
9. Desembolsar el crédito de Diego Armando Cabrera López con una combinación de efectivo y transferencia. Importe igual al aprobado, una sola salida; no se vuelve a descontar de bóveda. Repetir no debe producir otro desembolso. Probar administrativo con el importe aprobado, no un porcentaje calculado sobre el total del plan.
10. Registrar ingreso y gasto con varias formas. Comprobar ingreso/egreso conjunto y físico solo por el componente efectivo. Las transferencias internas se registran por sus flujos específicos y no admiten componentes no efectivos.
11. Abrir detalle/cierre: registrar efectivo contado distinto del esperado debe impedir cierre. Con importe correcto, se cierra una vez, vuelve a bóveda el saldo disponible total, incluyendo depósitos y transferencias y el resumen conserva todas las formas. Consultar cierre desde bóveda y verificar que el libro no cuenta el retorno como ingreso nuevo.
12. Cambiar a otro cajero/supervisor: consulta autorizada, sin operación ni cierre de caja ajena. Oficial: solo créditos asignados. Ejecutar `php artisan finanzas:diagnosticar` y revisar diferencias sin aplicar ajustes automáticos.

Acceso web: servir el directorio `public` como raíz del sitio (virtual host de Laragon o `php artisan serve`). Abrir `localhost/credisoft/public` no es equivalente: las rutas absolutas apuntan a la raíz de localhost.

Registrar para cada caso usuario, operación/código, esperado, resultado y captura/recibo si hay diferencia. La fase obtiene aceptación operativa cuando estos recorridos sean aprobados con tus reglas reales.
