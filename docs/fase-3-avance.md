# Fase 3 — implementación y validación

Actualizado: 24/09/2026. Implementación del circuito de caja, bóveda, cobros y anulaciones disponible; pendiente la aceptación mediante las pruebas manuales del usuario. Esto no certifica los demás módulos ni una puesta en producción.

## Comportamiento entregado

- Cálculo de deuda en servidor compartido con la consulta de cuotas; el navegador no decide capital, interés ni multa. Centavos enteros y BCMath para prorrateos; requiere extensión PHP BCMath.
- Multa de Bs 3 diarios por crédito, sin multiplicarla por cuotas vencidas ni días de gracia. Conserva interés devengado y moratorio; el adelanto reduce el interés futuro del capital pagado, proporcionalmente si es parcial.
- Cobros consecutivos desde la primera cuota pendiente, modalidades completo/parcial/solo interés/solo multa/interés y multa, capital residual y estado del plan actualizados.
- Efectivo, Depósito y Transferencia / QR combinables en una operación y recibo. Referencia obligatoria por componente no efectivo; imagen privada opcional por componente. Aplica a cobros, administrativos, ingresos, egresos y desembolsos.
- Cambio exclusivamente contra efectivo entregado; excedentes no efectivos rechazados, sin saldos a favor automáticos.
- Permisos independientes `condonar`, `anular_finanzas` y `pagos_retroactivos`. Se asignan inicialmente al Administrador; los cajeros necesitan asignación explícita del permiso por rol. Tener permiso no permite operar cajas ajenas.
- Condonación solo de interés/multa pendientes, con motivo. Anulación completa, con motivo, autor y fecha; restaura cuotas, condonaciones y estados. Rechaza caja cerrada/ajena, operaciones posteriores o efectivo insuficiente. Conserva fuentes y auditoría.
- Fechas anteriores dentro de la caja propia abierta actual, sin reescribir cierres históricos. No se permite insertar un pago anterior a otro pago vigente del mismo crédito.
- Transacciones y bloqueo financiero común antes de validar saldos. UUID, huella de petición y respuesta persistida para reintentos. Los formularios conservan la clave mientras hay respuesta incierta, incluso al recargar la pestaña; reutilizarla con otros datos devuelve 409. Clientes legados sin clave reciben una identidad nueva y no tienen garantía de deduplicación de reintentos.
- Unicidad en MySQL: una caja abierta por usuario, un desembolso activo por plan, una bóveda. Precisión decimal en cuotas y tasa del plan. Las migraciones bloquean inconsistencias previas en vez de corregirlas silenciosamente.
- Arqueo integrado por forma, ingreso/egreso conjunto y efectivo esperado. El cierre exige efectivo contado igual al esperado, conserva resumen y devuelve solo efectivo a bóveda; no crea ajustes automáticos de diferencias.
- Libro financiero sin inflar ingresos por retornos/transferencias internas. Total registrado incluye medios no efectivos; no equivale al disponible físico. Misma consulta base para pantalla y exportación financiera.
- Historial agrupado, recibo PDF, referencia e imagen por componente. Los históricos sin código se consultan individualmente. Anular un histórico exige poder reconstruirlo; transferencias sin vínculo verificable se bloquean para revisión.
- Las rutas antiguas de amortización/liquidación sin este control devuelven 410 y remiten a Caja > Cobros; no se usan por los componentes activos.
- Catálogo de motivos corregido: activo es estado 0. La semilla anterior lo generaba inactivo.

## Verificación

- Suite Laravel: **55 pruebas, 825 comprobaciones**, aprobadas; cubre regresiones de permisos, deuda, cambios, adelantos, anulaciones, PDF, comprobantes privados y rollback.
- Compilación Vite aprobada.
- MySQL 8.4.3 aislado: migración incremental preserva importes; procesos independientes comprueban aperturas duplicadas, cierre contra transferencia, retiros concurrentes, cobros y desembolsos simultáneos. Evidencia: `fase-3-concurrencia.json`.
- Dos `migrate:fresh --seed` en MySQL desechable reproducen población, saldos, consultas de cuotas y permisos. Evidencia: `poblacion-desarrollo-verificacion.json`.
- Navegador: login DEMO, panel de cajero, listado de cajas, catálogo de motivos, diálogo mixto, edición por teclado y cancelación sin registrar dinero comprobados. Las pruebas financieras que escriben se ejecutaron en bases aisladas; la aceptación completa de recorridos queda al usuario.
- `php artisan finanzas:diagnosticar`: solo lectura, duplicados, medios históricos, resúmenes y diferencias entre operación/desglose/fuente/estado. No certifica por sí solo toda la contabilidad histórica.

## Actualización local realizada

Migraciones 150000, 160000 y 170000 aplicadas. Las dos últimas se aplicaron con `tools/diagnostics/upgrade-finance.php`, después de respaldo MySQL y comparación de importes de las fuentes. No se ejecutó fresh ni se reemplazó la población financiera existente.

Respaldo previo a 160000/170000: `storage/app/diagnostics/finance-upgrade-20260924-160651/before.sql`, con SHA-256 y snapshot de importes. El catálogo de motivos fue repoblado por separado, sin alterar dinero.

Para otra instalación local: respaldar, detener escrituras, ejecutar migraciones incrementales y diagnóstico. El helper solo acepta MySQL local en APP_ENV=local. Ante un fallo parcial de DDL, revisar `migrate:status` antes de reintentar. No retroceder solamente el código después de registrar operaciones nuevas. Restaurar el respaldo junto con el código compatible en una base aparte y verificar saldos antes de cambiar la conexión; una restauración eliminaría operaciones posteriores al respaldo y requiere decisión explícita.

## Población para pruebas

El próximo fresh/seed incorpora un cobro mixto de Bs 100 para DEMO 05 Elena: efectivo 40, depósito 30 y transferencia/QR 30. El seed reintenta la misma clave para comprobar que se registra una sola vez. La base habitual conserva sus cifras anteriores hasta que el usuario decida reiniciarla.

Saldos de una instalación nueva: bóveda 38.257; caja A 2.100; caja B 1.500; efectivo institucional 41.857; saldo total registrado 41.917 Bs. Los Bs 60 de diferencia son medios no efectivos, registrados dentro de caja.

## Límites explícitos

- Caja en bolivianos; se rechazan operaciones en otra moneda, sin conversión implícita.
- Bloqueo global conservador: prioriza consistencia y debe medirse antes de reemplazarlo por bloqueos más finos.
- Cierres e históricos anteriores no reciben comprobantes o vínculos inventados. Amortizaciones legadas o formas desconocidas quedan señaladas para revisión.
- No incluye cuentas bancarias, conciliación bancaria, reprogramación/refinanciamiento ni reparación masiva de históricos.
- La cobertura automatizada reduce riesgos, pero la aceptación manual y el diagnóstico de datos reales siguen siendo necesarios.

Ver `fase-3-pruebas-manuales.md` para los recorridos de aceptación.


## Ajuste del historial de ingresos y gastos

- Las cuatro pestañas reutilizan el detalle de la operación y sus comprobantes por componente no efectivo. Los históricos sin vínculo muestran importe, fecha, estado y caja, sin reconstruir pagos.
- Acciones sujetas al único permiso `anular_finanzas`, caja propia abierta y operación vigente. Motivo obligatorio, bloqueo de solicitudes repetidas mientras se procesa y mensajes 401/403/422 del servidor.
- Tras confirmar la anulación se actualizan las cuatro listas conservando filtros/paginación. Un fallo de actualización se informa como tal, sin presentarlo como fallo de la anulación.
- Totales con la misma búsqueda y rango que el listado, solo movimientos vigentes y todas las páginas. Las pestañas de cajas abiertas mantienen ese ámbito; se corrigió el título anterior «Caja actual».
- Verificado: 59 pruebas y 911 comprobaciones; compilación Vite; consulta de ambos historiales en MySQL local sin escrituras; navegador con desglose mixto, vínculo de imagen y ocultación de anulación al cajero sin permiso.
- No se añadieron permisos ni migraciones ni se alteraron movimientos existentes.


## Presentación de condonaciones y atraso histórico

El porcentaje de cobertura se calcula sobre el importe cubierto más el residual de la cuota. Una cuota liquidada no se compara contra el interés ordinario recalculado hoy. Los campos históricos de acumulados se conservan; los nuevos campos `*_cobrado_total` y `*_condonado_total` separan dinero y condonación a partir de pagos vigentes.

En Caja, «Int. pendiente» identifica el importe residual. «Atraso histórico» muestra días acumulados y el resumen de multa generada, cobrada, condonada y pendiente. El detalle de plan y los PDF consultados separan también los importes condonados del dinero cobrado.

Regresión DEMO Nicolás al 24/09/2026: interés cobrado30, condonado20, pendiente0, cubierto100%; multa histórica24 por8días, cobrada0, condonada21, pendiente3. Prueba de lectura sin alterar pagos. Suite:60pruebas/922comprobaciones; compilación aprobada.


## Detalle del plan: generación, cobro y saldo

La pantalla distingue cuota programada, interés generado ordinario/moratorio, interés cobrado/condonado/pendiente y saldo exigible al corte (capital + interés + multa). El detalle de cada cuota muestra los tres componentes y sus cobros, condonaciones y pendientes. La cobertura usa cobros + condonaciones + saldo residual como base, no la cuota programada.

Los nuevos cobros conservan el cálculo de interés generado en `operaciones_financieras.resultado.detalles`. Al saldar una cuota se consulta ese registro vinculado al último pago vigente; una anulación no aporta datos al historial vigente. Los pagos anteriores sin desglose muestran el total registrado y una advertencia explícita, sin inferir moratorio ni distribuir arbitrariamente el interés cobrado. No se modificaron datos históricos ni las fórmulas de cobro. Esta mejora corresponde a la pantalla; la exportación PDF conserva su formato existente.

Validación: 61 pruebas, 938 comprobaciones; compilación Vite correcta. Regresión de conservación del moratorio tras liquidación y consulta futura, reversión y registros antiguos sin desglose. La sesión de navegador disponible (cajero_user) no tiene acceso a /plan_pago; no se cambiaron sus permisos para la revisión visual.


## Desglose del interés cobrado y condonado

Los nuevos cobros guardan `desglose_interes` en cada detalle de `operaciones_financieras.resultado`: ordinario/moratorio cobrado y condonado. Se conserva la prioridad del cálculo existente (ordinario antes que moratorio, condonación antes del dinero). `pago_interes` y los totales financieros permanecen agregados para compatibilidad; el desglose no suma un segundo ingreso. No requiere migración.

El detalle del plan mantiene sus 11 columnas compactas y muestra generado, cobrado, condonado y pendiente dentro de las columnas de interés. El historial por cuota muestra también el desglose de cada pago. La abreviatura M se reemplaza por Multa. Solo se acumulan pagos vigentes; la anulación conserva el registro original y excluye sus importes de los totales activos. Si algún pago de interés anterior carece de desglose, el acumulado separado se muestra como desconocido, sin asignarlo artificialmente a ordinario o moratorio; el total conocido y el detalle de nuevos pagos permanecen disponibles.

Validación: 63 pruebas / 966 comprobaciones y compilación Vite correcta. Casos: pago parcial, reintento sin duplicación, condonación que atraviesa ambos conceptos, anulación, liquidación con consultas futuras y datos antiguos sin desglose.


## Importe registrado y población de pruebas con desglose

El plan conserva el total registrado antes de condonar (capital + interés + multa), junto a cobros, condonaciones y pendiente. Para cuotas pendientes se identifica como importe al corte, sin confundirlo con el total programado ni con interés futuro. Nicolás: 321 antes de condonar = 280 cobrados + 41 condonados + 0 pendientes.

DevelopmentSeeder crea operaciones vinculadas para los pagos ficticios con el desglose conocido del escenario, sin cambiar sus importes ni duplicar ingresos. Nicolás: ordinario generado25/cobrado5/condonado20; moratorio generado25/cobrado25/condonado0; multa condonada21. Se mantienen importes y saldos de caja/bóveda. Estos cambios se aplican al crear una nueva población; no se modificaron los pagos de la base habitual ni se ejecutó migrate:fresh sobre ella. Los históricos sin desglose conservan sus totales conocidos y sus campos desconocidos.

Validación:64 pruebas/977 comprobaciones y compilación Vite aprobada, incluida regresión de un histórico sin snapshot.
