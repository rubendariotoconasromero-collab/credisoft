-- permisos

/*
•	Tiene acceso a todo el sistema
•	Acceso a roles
•	Acceso a Información
•	Acceso a usuarios
•	Acceso a panel administración
•	Acceso a plan pagos
•	Acceso a listado de pagos
•	Acceso a reportes
•	Acceso a Control de caja
•	Acceso a clientes
•	Acceso a solicitud de prestamos
•	Acceso a reportes
*/

insert into permiso (id, nombre, descripcion, estado) values(1, 'roles', 'Gestión de roles de usuarios', 1);
insert into permiso (id, nombre, descripcion, estado) values(2, 'informacion', 'Gestión información de la empresa', 1);
insert into permiso (id, nombre, descripcion, estado) values(3, 'usuarios', 'Gestión de usuarios', 1);
insert into permiso (id, nombre, descripcion, estado) values(4, 'paneladministracion', 'Vista panel administración', 1);
insert into permiso (id, nombre, descripcion, estado) values(5, 'planpagos', ' Gestión de plan de pagos', 1);
insert into permiso (id, nombre, descripcion, estado) values(6, 'listadopagos', 'Gestión de pagos - cuotas',1);
insert into permiso (id, nombre, descripcion, estado) values(7, 'controlcaja', 'Gestión de control de caja',1);
insert into permiso (id, nombre, descripcion, estado) values(8, 'cliente', 'Gestión de clientes',1);
insert into permiso (id, nombre, descripcion, estado) values(9, 'solicitudprestamos', 'Gestión de solicitudes de prestamos', 1);
insert into permiso (id, nombre, descripcion, estado) values(10, 'reportes', 'Reportes',1);
insert into permiso (id, nombre, descripcion, estado) values(11, 'estado_resultados', 'Gestion de Estado de resultados',1);
insert into permiso (id, nombre, descripcion, estado) values(12, 'codeudores', 'Gestion de codeudores',1);


-- rol
insert into rol (id, nombre, estado) values(1, 'administrador', 1);

-- permiso_rol
insert into permiso_rol (id_permiso, id_rol) values(1, 1);
insert into permiso_rol (id_permiso, id_rol) values(2, 1);
insert into permiso_rol (id_permiso, id_rol) values(3, 1);
insert into permiso_rol (id_permiso, id_rol) values(4, 1);
insert into permiso_rol (id_permiso, id_rol) values(5, 1);
insert into permiso_rol (id_permiso, id_rol) values(6, 1);
insert into permiso_rol (id_permiso, id_rol) values(7, 1);
insert into permiso_rol (id_permiso, id_rol) values(8, 1);
insert into permiso_rol (id_permiso, id_rol) values(9, 1);
insert into permiso_rol (id_permiso, id_rol) values(10, 1);

insert into permiso_rol (id_permiso, id_rol) values(11, 1);

insert into permiso_rol (id_permiso, id_rol) values(12, 1);

-- usuario
insert into users(id, name, personal, password, id_rol)
values(1, 'administrador', 'Cleidy Rocha', '$2a$12$/vZCxf6X9CJULLvHHAtMBOWwgEHY7KNq6Fv9O8VsJSXqTpAWxD6Im', 1);


-- aumento de atributo en cliente para imagen
-- alter table cliente add imagen varchar(255);

insert into mi_empresa (nombre, nit, telefono, direccion, email, logo)
values ('credi san juan', 1, 1, 'san juan', 'sanjuan@gmail.com', 'logo')

-- COLUMNAS NUEVAS AÑADIDAS
ALTER TABLE `pago_amortizacion`
ADD COLUMN `total_seguro` FLOAT DEFAULT 0;

ALTER TABLE `plan_pago`
ADD COLUMN `id_plan_aux` BIGINT(20) DEFAULT 0;

ALTER TABLE `solicitud`
ADD COLUMN `monto_pago_adm` DECIMAL(10,2) DEFAULT 0.00;

ALTER TABLE `telefono`
ADD COLUMN `nombre` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
ADD COLUMN `apellidos` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
ADD COLUMN `relacion` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL;


ALTER TABLE `users`
ADD COLUMN `ci` VARCHAR(30) COLLATE utf8mb4_unicode_ci DEFAULT '0',
ADD COLUMN `telefono` VARCHAR(20) COLLATE utf8mb4_unicode_ci DEFAULT '0';


ALTER TABLE solicitud add tipo_tasa varchar(50) default 'amortizable';

ALTER TABLE pago_amortizacion add id_plan_ligado int;


ALTER TABLE pago_amortizacion
ADD COLUMN estado INT DEFAULT 0,
ADD COLUMN monto_desembolso DECIMAL(10, 2) DEFAULT 0,
ADD COLUMN tipo VARCHAR(255) DEFAULT 'amortizacion',
ADD COLUMN fecha_desembolso DATE NULL;



-- tabla motivo ingreso
CREATE TABLE `motivo_ingreso` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);


-- Población para la tabla motivo ingreso
INSERT INTO motivo_ingreso (nombre, estado, created_at, updated_at) VALUES
('Capital inicial de inversión', 0, NOW(), NOW()),
('Otros ingresos', 0, NOW(), NOW()),
('Inyección de capital', 0, NOW(), NOW());


-- Otros gastos

CREATE TABLE `motivo_gasto` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

INSERT INTO motivo_gasto (nombre, estado, created_at, updated_at) VALUES
('Pago de salarios', 0, NOW(), NOW()),
('Pago de servicios públicos', 0, NOW(), NOW()),
('Alquiler de oficina', 0, NOW(), NOW()),
('Gastos administrativos', 0, NOW(), NOW()),
('Compra de suministros de oficina', 0, NOW(), NOW()),
('Publicidad y marketing', 0, NOW(), NOW()),
('Mantenimiento de equipo', 0, NOW(), NOW()),
('Impuestos y tasas', 0, NOW(), NOW()),
('Capacitación de empleados', 0, NOW(), NOW());


-- Para Boveda -->>>> otro

-- Agregando atributo para tabla pago
ALTER TABLE pago
ADD COLUMN monto_cuota DECIMAL(10, 2) DEFAULT 0;


-- Aumentando atributos a la tabla plan de pago
ALTER TABLE `plan_pago`
ADD COLUMN `moneda` VARCHAR(255) NOT NULL DEFAULT '',
ADD COLUMN `lapso_capital` VARCHAR(255) NOT NULL DEFAULT '',
ADD COLUMN `nro_cuotas` INT NOT NULL DEFAULT 0,
ADD COLUMN `tasa` INT NOT NULL DEFAULT 0;

ALTER TABLE `pago_amortizacion`
ADD COLUMN `numero_cuota` INT NOT NULL DEFAULT 0;

-- Se aumento para controlar los dias de interes
ALTER TABLE plan_pago add fecha_ultima_amortizacion date;

-- aumentar atributo en codeudor, para direfenciar entre codeudor y garante
ALTER TABLE codeudor
ADD COLUMN tipo varchar(50) default 'Codeudor';





-- aumentar opcion de obs en solicitud
ALTER TABLE solicitud
ADD COLUMN observacion varchar(250) default '';


-- NUEVOS **************

ALTER TABLE direccion
ADD COLUMN lat DECIMAL(10, 8) NULL AFTER descripcion,
ADD COLUMN lng DECIMAL(11, 8) NULL AFTER lat;

CREATE TABLE `boveda` (
  `id` bigint primary key UNSIGNED NOT NULL,
  `saldo_actual` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fecha_apertura` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

CREATE TABLE `movimientos_boveda` (
  `id` bigint primary key UNSIGNED NOT NULL,
  `tipo_movimiento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` datetime NOT NULL,
  `id_boveda` bigint UNSIGNED NOT NULL,
  `id_usuario` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

CREATE TABLE `movimientos_caja` (
  `id` bigint UNSIGNED NOT NULL,
  `tipo_movimiento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` datetime NOT NULL,
  `id_caja` bigint UNSIGNED NOT NULL,
  `id_usuario` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;


CREATE TABLE `transferencia_caja_boveda` (
  `id` bigint UNSIGNED NOT NULL,
  `tipo_transferencia` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` datetime NOT NULL,
  `id_boveda` bigint UNSIGNED NOT NULL,
  `id_caja` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);




-- aumentar opcion de obs en solicitud
-- ALTER TABLE solicitud
-- ADD COLUMN plazo int default 0;

-- Añadiendo columna de tipo solicitud a solicitud

ALTER TABLE solicitud
ADD COLUMN tipo_solicitud varchar(250) default 'Nuevo';

-- Añadiendo cantidad reprogramaciones
ALTER TABLE solicitud
ADD COLUMN cantidad_reprogramaciones int default 0,
ADD COLUMN cantidad_refinanciamientos int default 0,
ADD COLUMN monto_refinanciamiento int default 0;


ALTER TABLE solicitud
ADD COLUMN desembolso int default 0;

-- Aumentar atributo saldo_pendiente para controlar cuanto se debe en planes de pagos
ALTER TABLE plan_pago
ADD COLUMN saldo_pendiente decimal (15,2);

-- COLUMNAS PARA EL CONTROL DE VIGENCIAS DE CONTRASEÑAS
ALTER TABLE users ADD COLUMN fecha_cambio_password DATE DEFAULT NULL;
ALTER TABLE users ADD COLUMN dias_vigencia int DEFAULT 90;
-- PONER FECHA DEFECTO
UPDATE users SET fecha_cambio_password = NOW() WHERE fecha_cambio_password IS NULL;