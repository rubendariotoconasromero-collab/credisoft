> Actualización fase 3: el seed ahora agrega un cobro mixto de Bs100 para Elena (40 efectivo, 30 depósito, 30 transferencia/QR). Son 8 pagos y 19 permisos. Los saldos vigentes del seed son: bóveda38.257, cajaA2.100, cajaB1.500; físico41.857 y total registrado41.917 Bs. Los motivos se generan activos (estado0). Esta actualización sustituye las cifras anteriores de este documento. Ver `fase-3-pruebas-manuales.md`.

# Población inicial para pruebas manuales

La población es ficticia, determinista y está conectada a `DatabaseSeeder`. Se reconstruye al ejecutar `php artisan migrate:fresh --seed`. No utiliza factories aleatorias ni los antiguos ClienteSeeder/CodeudorSeeder/SolicitudSeeder/SocioSeeder.

## Cargar los datos

En el `.env` de tu entorno de desarrollo:

```dotenv
APP_ENV=local
SEED_DEMO_DATA=true
```

Comprueba que `DB_DATABASE` sea la base de desarrollo que quieras reiniciar. **migrate:fresh elimina todas sus tablas y registros actuales.** Guarda un respaldo si necesitas conservarlos.

```powershell
# Solo si modificaste .env y tenías configuración cacheada:
php artisan config:clear

php artisan migrate:fresh --seed
```

Luego cierra la sesión anterior y vuelve a ingresar. Si mantienes la aplicación abierta durante el reinicio, recarga la página para renovar los datos de sesión y permisos.

Sin la variable, el modo DEMO se activa por defecto únicamente para APP_ENV=local. Con `SEED_DEMO_DATA=false` se cargan solo catálogos, roles/permisos, empresa y el administrador. El modo DEMO se rechaza en entornos distintos de local/testing aunque la variable esté en true. Esta población no es una configuración lista para producción.

`php artisan db:seed` sobre tablas de negocio ya pobladas se rechaza antes de modificar datos; no se mezcla la población inicial con operaciones manuales. Para volver al estado inicial usa el reinicio explícito anterior.

## Accesos de desarrollo

Contraseña inicial de todas estas cuentas: **`admin123`**. Se conserva el acceso administrador conocido del proyecto. Son credenciales de prueba.

| Usuario | Rol | Uso |
|---|---|---|
| administrador | Administrador | Todos los módulos y conciliación |
| cajero_user | Cajero | Caja A abierta, cobros y desembolsos |
| cajero2_user | Cajero | Caja B abierta, pruebas de propiedad y operación simultánea |
| oficial_user | Oficial de Credito | Clientes, codeudores, solicitudes y cartera asignada |
| oficial2_user | Oficial de Credito | Otra cartera para comprobar filtros por oficial |
| encargado_user | Encargado de Agencia | Reportes, consultas financieras, cartera, bóveda y socios |

Los permisos se asignan por nombre. Se corrigió la colisión entre los IDs fijos del antiguo PermisoSeeder y los permisos que la migración agrega antes del seed. La configuración de roles corresponde a la población de desarrollo, no a una ratificación de todas las autorizaciones pendientes de fase 2.

## Qué encontrarás

- 16 clientes (15 activos y uno inactivo), con nombres que comienzan en DEMO y código de escenario.
- 8 registros de garantes/codeudores: cuatro de cada tipo.
- 24 direcciones y 24 teléfonos ficticios no operativos; coordenadas próximas a **-17.338440, -63.246961**.
- 2 socios ficticios, empresa Credisoft DEMO y catálogo de actividades/motivos.
- 13 solicitudes, cada una con garante/codeudor vinculado y descripción de garantía.
- 11 planes, 42 cuotas, 10 desembolsos, 10 cobros administrativos y 7 pagos.
- 9 cajas históricas cerradas y 2 abiertas hoy, una por cajero.
- Bóveda, movimientos de caja/bóveda y vínculos de transferencias que concilian.

No hay fotografías ni archivos falsamente vinculados a documentos inexistentes. Las fichas permiten que cargues tus propios archivos de prueba. Los nombres/correos/teléfonos no corresponden a personas reales.

## Escenarios

`D` significa el día en que ejecutas el seed. Las fechas se recalculan en cada reinicio; los importes y relaciones permanecen iguales. Todos los préstamos son de **1.000 Bs**, tasa mensual fija de ejemplo del **10%**, con cuatro cuotas salvo el cancelado, que tiene dos. El cronograma de ejemplo usa intervalos de 7, 15 y 30 días; no redefine las políticas financieras pendientes.

| Buscar cliente | Estado inicial | Prueba manual |
|---|---|---|
| DEMO 01 - Ana | Cliente sin solicitud | Editar ficha, mapa y crear una solicitud |
| DEMO 02 - Bruno | Solicitud pendiente, sin plan | Revisar, simular y aprobar |
| DEMO 03 - Carla | Solicitud inactiva | Verificar filtros y activación |
| DEMO 04 - Diego | Aprobado, pendiente administrativo y desembolso | Cobrar administrativo 20 Bs y desembolsar 1.000 Bs una vez |
| DEMO 05 - Elena | Semanal vigente; primera cuota D+4 | Consultar cuota futura y cobro anticipado |
| DEMO 06 - Fabian | Quincenal vigente; primera cuota D+10 | Comparar cronograma y fechas |
| DEMO 07 - Gabriela | Mensual vigente; primera cuota D+20 | Consultar cronograma mensual |
| DEMO 08 - Hugo | Crédito vencido; última cuota D−7 | Revisar mora y reportes de vencidos |
| DEMO 09 - Irene | Primera cuota parcial: capital 100 + interés 25 pagados en D−7 | Ver residual de capital 150 y nuevos intereses/mora |
| DEMO 10 - Jorge | Solo interés 25 pagado en D−7 | Ver capital de primera cuota todavía 250 |
| DEMO 11 - Karla | Solo mora 6 pagada en D−1 | Ver que capital e interés siguen pendientes |
| DEMO 12 - Luis | Interés 50 + mora 21 pagados en D−1 | Ver capital de primera cuota todavía 250 |
| DEMO 13 - Marta | Dos cuotas pagadas en sus vencimientos; plan cancelado | Ver capital pendiente cero y recibos históricos |
| DEMO 14 - Nicolas | Primera cuota completa: efectivo 280 + condonación 41 en D−1 | Revisar desglose y siguiente cuota pendiente |
| DEMO 15 - Olga | Cliente inactivo, sin crédito | Revisar activación/desactivación |
| DEMO 16 - Pablo | Cliente activo sin crédito | Crear solicitud y probar un recorrido desde cero |

En Nicolas: efectivo = capital 250 + interés 30; condonación = interés 20 + mora 21. La condonación reduce deuda, pero no incrementa caja. Las cuotas vencidas siguen devengando según el código actual al pasar los días: los importes a cobrar hoy pueden cambiar aunque los pagos históricos permanezcan iguales.

## Saldos exactos después de reiniciar

| Concepto | Bs |
|---|---:|
| Aporte externo inicial a bóveda | 50.000,00 |
| Desembolsos en efectivo | −10.000,00 |
| Pagos de cuotas en efectivo | +1.557,00 |
| Administrativos cobrados | +200,00 |
| Ingreso manual en caja A | +100,00 |
| Papelería en caja A | −40,00 |
| **Efectivo total institucional** | **41.817,00** |
| Bóveda física | 38.257,00 |
| Caja A: apertura 2.000 + 100 − 40 | 2.060,00 |
| Caja B: apertura | 1.500,00 |

Las transferencias internas y retornos históricos son neto cero. Cada sesión histórica abrió con 5.000 Bs y devolvió su remanente el mismo día; los desembolsos y pagos tienen una caja y un usuario coherentes. Los retornos sembrados incluyen la clasificación «Transferencia desde Caja» para que el reporte los identifique correctamente como internos. No se invoca el controlador de cierre que tiene el defecto registrado en fase 1.

El Libro General debe indicar capital total **41.817 Bs** al corte D. Para ver todo el origen, selecciona un rango desde D−60 hasta D. Otros filtros pueden cambiar el saldo de la lista y los totales visibles.

## Recorrido recomendado

1. Ingresar como administrador; comprobar los saldos anteriores en bóveda, cajas y libro financiero.
2. Buscar DEMO 01, ver/editar su ficha y ubicación.
3. Abrir DEMO 02 como oficial y revisar su solicitud; aprobar con el rol autorizado.
4. Ingresar como cajero_user y trabajar con DEMO 04: administrativo y desembolso.
5. Consultar los casos DEMO 08 a 14 antes de cobrar, anotando los valores esperados de cada componente.
6. Probar los cinco tipos de pago y contrastar recibo, cuota, caja y reportes después de cada acción.
7. Usar cajero2_user para verificar la separación entre cajas; reiniciar los datos entre escenarios que requieran el mismo punto de partida.

## Limitaciones del sistema actual

La población es consistente, pero **no corrige los defectos de la aplicación identificados en fase 1**. El sistema todavía puede seleccionar la primera caja abierta, impedir operar con la segunda desde la interfaz, duplicar cierres/desembolsos o anular incorrectamente. Esos comportamientos deben registrarse como fallos, no arreglarse alterando a mano los datos iniciales.

No se precargan pagos bancarios: el esquema/cálculo actual todavía los mezcla con efectivo, contrario a la regla acordada. Tampoco se inventan reprogramaciones ni anulaciones históricas incompletas. Estos escenarios se añadirán cuando sus flujos y reglas estén implementados.

## Verificación automatizada

```powershell
php artisan test --filter=DevelopmentSeederTest
powershell -NoProfile -ExecutionPolicy Bypass -File tools/diagnostics/run-mysql.ps1 -SeedDevelopment
```

La segunda comprobación ejecuta **dos ciclos completos de migrate:fresh --seed en una instancia MySQL independiente**. Compara inventario y saldos, consulta el Libro General y calcula las 42 cuotas mediante el código real. No reinicia tu base habitual. Resultado: [poblacion-desarrollo-verificacion.json](poblacion-desarrollo-verificacion.json).
