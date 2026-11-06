# Población operativa inicial

`DatabaseSeeder` ofrece dos perfiles seleccionables en `.env` antes de ejecutar `php artisan migrate:fresh --seed`:

| `SEED_PROFILE` | Resultado |
|---|---|
| `demo` | Conserva los escenarios de desarrollo existentes, con clientes, créditos, cajas y pagos. Solo se admite con `APP_ENV=local` o `testing`. |
| `operativo` | Carga únicamente actividades, motivos, roles, permisos, una empresa y una cuenta administradora. Las tablas de clientes, créditos, caja, bóveda y movimientos quedan vacías. |

Para ensayar una instalación limpia con aspecto operativo:

```dotenv
APP_ENV=local
SEED_PROFILE=operativo
BOOTSTRAP_ADMIN_PASSWORD=
SEED_COMPANY_NAME="Financiera Valle Verde S.R.L."
SEED_COMPANY_NIT=4762158019
SEED_COMPANY_ADDRESS="Av. Beni 421, Santa Cruz de la Sierra"
SEED_COMPANY_PHONE=33789012
SEED_COMPANY_EMAIL=contacto@valleverde.test
SEED_ADMIN_NAME="Mariana Torres Salvatierra"
SEED_ADMIN_EMAIL=mariana.torres@valleverde.test
SEED_ADMIN_CI=6897342
SEED_ADMIN_PHONE=72168403
```

Los nombres y datos de contacto anteriores son ejemplos simulados, sin marcas visibles de demostración; el dominio `.test` evita enviar correo a terceros. **Antes de una puesta en marcha real, sustitúyelos por la identidad legal y los datos del administrador de la institución.** En `APP_ENV=production` no se aplican los valores simulados por defecto: los campos `SEED_COMPANY_*` y `SEED_ADMIN_*` deben estar configurados. La contraseña inicial es obligatoria incluso en `local` y debe tener al menos 12 caracteres. El usuario de acceso inicial es `administrador`; el seeder no imprime la contraseña.

Comprueba primero `DB_DATABASE`: `migrate:fresh` elimina todas las tablas y registros de la base seleccionada. No lo ejecutes sobre una base con datos que deban conservarse.

```powershell
php artisan config:clear
php artisan migrate:fresh --seed
```

El perfil operativo no inventa saldos ni operaciones. Después del primer acceso, configura la empresa con sus datos definitivos, crea los usuarios de caja y crédito, abre la bóveda desde el módulo correspondiente y registra el aporte de capital mediante el flujo normal. El seeder rechaza una segunda ejecución de `db:seed` si ya existen usuarios, empresa o datos de negocio, para evitar mezclar poblaciones.

Para volver a los escenarios actuales en una base de desarrollo desechable, usa `SEED_PROFILE=demo`. La variable antigua `SEED_DEMO_DATA` se respeta únicamente si `SEED_PROFILE` no está definido; el selector nuevo tiene prioridad.
