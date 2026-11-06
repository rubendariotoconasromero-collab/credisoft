> El perfil `demo` conserva los escenarios financieros, pero los registros se presentan como una cartera verosímil: nombres completos, documentos, direcciones, usuarios y referencias sin etiquetas de demostración. Incluye un cobro mixto de 100 Bs para Elena (40 en efectivo, 30 en depósito y 30 por transferencia/QR).

# Población inicial para pruebas manuales

La población es ficticia, determinista y está conectada a `DatabaseSeeder`. Se reconstruye al ejecutar `php artisan migrate:fresh --seed`. No utiliza factories aleatorias ni los antiguos ClienteSeeder/CodeudorSeeder/SolicitudSeeder/SocioSeeder.

## Cargar los datos

En el `.env` de tu entorno de desarrollo:

```dotenv
APP_ENV=local
SEED_PROFILE=demo
```

Para una base inicial sin clientes ni operaciones y con identidades simuladas verosímiles, usa `SEED_PROFILE=operativo`; consulta [poblacion-operativa.md](poblacion-operativa.md). `SEED_DEMO_DATA` permanece solo como compatibilidad para instalaciones que aún no definieron `SEED_PROFILE`.

Comprueba que `DB_DATABASE` sea la base de desarrollo que quieras reiniciar. **migrate:fresh elimina todas sus tablas y registros actuales.** Guarda un respaldo si necesitas conservarlos.

```powershell
# Solo si modificaste .env y tenías configuración cacheada:
php artisan config:clear

php artisan migrate:fresh --seed
```

Luego cierra la sesión anterior y vuelve a ingresar. Si mantienes la aplicación abierta durante el reinicio, recarga la página para renovar los datos de sesión y permisos.

Sin `SEED_PROFILE`, el perfil de escenarios se activa por defecto únicamente para `APP_ENV=local`, de acuerdo con el indicador antiguo `SEED_DEMO_DATA`. El perfil `demo` explícito se rechaza fuera de `local/testing`. Esta población no es una configuración lista para producción.

`php artisan db:seed` sobre tablas de negocio ya pobladas se rechaza antes de modificar datos; no se mezcla la población inicial con operaciones manuales. Para volver al estado inicial usa el reinicio explícito anterior.

## Accesos de desarrollo

Contraseña inicial de todas estas cuentas: **`admin123`**. Se conserva el acceso administrador conocido del proyecto. Son credenciales de prueba.

| Usuario | Rol | Uso |
|---|---|---|
| administrador | Administrador | Todos los módulos y conciliación |
| lucia.rivera | Cajero | Caja A abierta, cobros y desembolsos |
| andres.flores | Cajero | Caja B abierta, pruebas de propiedad y operación simultánea |
| camila.ortiz | Oficial de Credito | Clientes, codeudores, solicitudes y cartera asignada |
| rodrigo.mendez | Oficial de Credito | Otra cartera para comprobar filtros por oficial |
| patricia.suarez | Encargado de Agencia | Reportes, consultas financieras, cartera, bóveda y socios |

Los permisos se asignan por nombre. Se corrigió la colisión entre los IDs fijos del antiguo PermisoSeeder y los permisos que la migración agrega antes del seed. La configuración de roles corresponde a la población de desarrollo, no a una ratificación de todas las autorizaciones pendientes de fase 2.

## Qué encontrarás

- 16 clientes (15 activos y uno inactivo), con nombres completos y documentos numéricos distintos.
- 8 registros de garantes/codeudores: cuatro de cada tipo.
- 24 direcciones y 24 teléfonos simulados; coordenadas próximas a **-17.338440, -63.246961**.
- 2 socios, empresa Financiera Horizonte Andino S.R.L. y catálogo de actividades/motivos.
- 13 solicitudes, cada una con garante/codeudor vinculado y descripción de garantía.
- 11 planes, 42 cuotas, 10 desembolsos, 10 cobros administrativos y 8 pagos.
- 9 cajas históricas cerradas y 2 abiertas hoy, una por cajero.
- Bóveda, movimientos de caja/bóveda y vínculos de transferencias que concilian.

No hay fotografías ni archivos vinculados a documentos inexistentes. Las fichas permiten cargar archivos de prueba. Las identidades son simuladas y los correos usan el dominio reservado `.test`; no uses esta población para contactar personas.

## Escenarios

`D` significa el día en que ejecutas el seed. Las fechas se recalculan en cada reinicio; los importes y relaciones permanecen iguales. Todos los préstamos son de **1.000 Bs**, tasa mensual fija de ejemplo del **10%**, con cuatro cuotas salvo el cancelado, que tiene dos. El cronograma de ejemplo usa intervalos de 7, 15 y 30 días; no redefine las políticas financieras pendientes.

| Buscar cliente | Estado inicial | Prueba manual |
|---|---|---|
| Ana María Vargas Quispe | Cliente sin solicitud | Editar ficha, mapa y crear una solicitud |
| Bruno Alejandro Salazar Rocha | Solicitud pendiente, sin plan | Revisar, simular y aprobar |
| Carla Fernanda Ríos Mendoza | Solicitud inactiva | Verificar filtros y activación |
| Diego Armando Cabrera López | Aprobado, pendiente administrativo y desembolso | Cobrar administrativo 20 Bs y desembolsar 1.000 Bs una vez |
| Elena Patricia Guzmán Vera | Semanal vigente; primera cuota D+4 | Consultar cuota futura y cobro anticipado |
| Fabián Mauricio Herrera Soria | Quincenal vigente; primera cuota D+10 | Comparar cronograma y fechas |
| Gabriela Lucía Flores Arce | Mensual vigente; primera cuota D+20 | Consultar cronograma mensual |
| Hugo Daniel Rojas Méndez | Crédito vencido; última cuota D−7 | Revisar mora y reportes de vencidos |
| Irene Valeria Choque Aguilar | Primera cuota parcial: capital 100 + interés 25 pagados en D−7 | Ver residual de capital 150 y nuevos intereses/mora |
| Jorge Luis Molina Paredes | Solo interés 25 pagado en D−7 | Ver capital de primera cuota todavía 250 |
| Karla Beatriz Sandoval Céspedes | Solo mora 6 pagada en D−1 | Ver que capital e interés siguen pendientes |
| Luis Fernando Suárez Velasco | Interés 50 + mora 21 pagados en D−1 | Ver capital de primera cuota todavía 250 |
| Marta Isabel Quiroga Salinas | Dos cuotas pagadas en sus vencimientos; plan cancelado | Ver capital pendiente cero y recibos históricos |
| Nicolás Adrián Vaca Rivero | Primera cuota completa: efectivo 280 + condonación 41 en D−1 | Revisar desglose y siguiente cuota pendiente |
| Olga Teresa Barrientos Cruz | Cliente inactivo, sin crédito | Revisar activación/desactivación |
| Pablo Andrés Mendoza Ávila | Cliente activo sin crédito | Crear solicitud y probar un recorrido desde cero |

En Nicolás: efectivo = capital 250 + interés 30; condonación = interés 20 + mora 21. La condonación reduce deuda, pero no incrementa caja. Las cuotas vencidas siguen devengando al pasar los días: los importes a cobrar hoy pueden cambiar aunque los pagos históricos permanezcan iguales.

## Saldos exactos después de reiniciar

| Concepto | Bs |
|---|---:|
| Aporte externo inicial a bóveda | 50.000,00 |
| Desembolsos en efectivo | −10.000,00 |
| Pagos de cuotas en efectivo | +1.597,00 |
| Administrativos cobrados | +100,00 |
| Ingreso manual en caja A | +100,00 |
| Papelería en caja A | −40,00 |
| **Efectivo total institucional** | **41.757,00** |
| Depósito y transferencia del cobro mixto | +60,00 |
| **Saldo operativo total** | **41.817,00** |
| Bóveda | 38.157,00 |
| Caja A: saldo disponible | 2.160,00 |
| Caja A: efectivo esperado | 2.100,00 |
| Caja B: apertura | 1.500,00 |

Las transferencias internas y retornos históricos son neto cero. Cada sesión histórica abrió con 5.000 Bs y devolvió su remanente el mismo día; los desembolsos y pagos tienen una caja y un usuario coherentes. Los retornos sembrados incluyen la clasificación «Transferencia desde Caja» para que el reporte los identifique correctamente como internos. No se invoca el controlador de cierre que tiene el defecto registrado en fase 1.

El Libro General debe indicar saldo operativo total **41.817 Bs** al corte D. Para ver todo el origen, selecciona un rango desde D−60 hasta D. Otros filtros pueden cambiar el saldo de la lista y los totales visibles.

## Recorrido recomendado

1. Ingresar como administrador; comprobar los saldos anteriores en bóveda, cajas y libro financiero.
2. Buscar a Ana María Vargas Quispe, ver/editar su ficha y ubicación.
3. Abrir la solicitud de Bruno Alejandro Salazar Rocha como oficial; revisar y aprobar con el rol autorizado.
4. Ingresar como `lucia.rivera` y trabajar con el crédito de Diego Armando Cabrera López: administrativo y desembolso.
5. Consultar los casos de Hugo Daniel Rojas Méndez a Nicolás Adrián Vaca Rivero antes de cobrar, anotando los valores esperados de cada componente.
6. Probar los cinco tipos de pago y contrastar recibo, cuota, caja y reportes después de cada acción.
7. Usar `andres.flores` para verificar la separación entre cajas; reiniciar los datos entre escenarios que requieran el mismo punto de partida.

## Alcance de la población

La población conserva los escenarios de cartera, caja y cobro mixto. No precarga reprogramaciones ni anulaciones históricas incompletas. Usa el perfil `operativo` para comprobar una instalación inicial sin transacciones.

## Verificación automatizada

```powershell
php artisan test --filter=DevelopmentSeederTest
powershell -NoProfile -ExecutionPolicy Bypass -File tools/diagnostics/run-mysql.ps1 -SeedDevelopment
```

La segunda comprobación ejecuta **dos ciclos completos de migrate:fresh --seed en una instancia MySQL independiente**. Compara inventario y saldos, consulta el Libro General y calcula las 42 cuotas mediante el código real. No reinicia tu base habitual. Resultado actualizado: [poblacion-desarrollo-verificacion.json](poblacion-desarrollo-verificacion.json).
