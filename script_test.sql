-- Usar la base de datos correcta
USE db_credisoft;

-- Limpiar datos de prueba anteriores para evitar conflictos
DELETE FROM cuota;
DELETE FROM plan_pago;
DELETE FROM solicitud;

-- Reiniciar los contadores para IDs predecibles
ALTER TABLE solicitud AUTO_INCREMENT = 1;
ALTER TABLE plan_pago AUTO_INCREMENT = 1;
ALTER TABLE cuota AUTO_INCREMENT = 1;

-- =================================================================
-- Escenario 1: OSMAR COIMBRA (ID 242) - NO ELEGIBLE (TIENE MORA)
-- =================================================================

-- 1.1. La Solicitud
INSERT INTO `solicitud` 
(
    `id`, `importe_solicitud`, `moneda`, `lapso_capital`, `nro_cuotas`, `tasa`, `fecha`, 
    `fecha_desembolso`, `fecha_primera_cuota`, `destino_prestamo`, `tipo_garantia`, 
    `tipo_desembolso`, `estado`, `id_cliente`, `id_usuario`, `tipo_tasa`, 
    `tipo_solicitud`, `created_at`, `updated_at`
) 
VALUES
(
    1, 6500.00, 'Bolivianos', 'Mensual', 4, 10.00, '2025-08-10', 
    '2025-08-10', '2025-09-10', 'Compra terreno', 'Prendario o Quirografaria', 
    'Efectivo', 1, 242, 10, 'fija', -- 2 = Nuevo (registrado)
    'Nuevo', NOW(), NOW()
);

-- 1.2. El Plan de Pago (Asociado a la Solicitud 1)
-- (Hoy es 14-11-2025)
INSERT INTO `plan_pago` 
(
    `id`, `id_solicitud`, `fecha_inicio`, `fecha_fin`, `total_pagar`, `estado`, 
    `id_plan_aux`, `moneda`, `lapso_capital`, `nro_cuotas`, `tasa`, 
    `fecha_ultima_amortizacion`, `saldo_pendiente`, `fecha_registro`, `created_at`, `updated_at`
) 
VALUES
(
    1, 1, '2025-09-10', '2025-12-10', 7800.00, 1, -- 1=Activo
    0, 'Bolivianos', 'Mensual', 4, 10,
    '2025-10-10', -- Fecha del último pago (Cuota 2)
    3250.00,      -- Saldo pendiente DESPUÉS del último pago
    '2025-08-10', NOW(), NOW()
);

-- 1.3. Las Cuotas (Simulación simple: 1625 Capital + Intereses)
-- (Hoy es 14-11-2025)
INSERT INTO `cuota` 
(`id`, `numero`, `fecha`, `capital`, `interes`, `saldo_capital`, `ahorro`, `seguro`, `total`, `estado`, `amortizado`, `id_plan_pago`)
VALUES
-- Pagadas
(1, 1, '2025-09-10', 1625.00, 650.00, 4875.00, 0.00, 0.00, 2275.00, 0, 1, 1), -- Pagado (estado 0)
(2, 2, '2025-10-10', 1625.00, 487.50, 3250.00, 0.00, 0.00, 2112.50, 0, 1, 1), -- Pagado (estado 0)
-- ¡VENCIDA!
(3, 3, '2025-11-10', 1625.00, 325.00, 1625.00, 0.00, 0.00, 1950.00, 1, 0, 1), -- Pendiente (estado 1) Y VENCIDA
-- Pendiente
(4, 4, '2025-12-10', 1625.00, 162.50, 0.00, 0.00, 0.00, 1787.50, 1, 0, 1); -- Pendiente (estado 1)


-- =================================================================
-- Escenario 2: LILIANA MERCADO (ID 241) - SÍ ELEGIBLE (AL DÍA)
-- =================================================================

-- 2.1. La Solicitud
INSERT INTO `solicitud` 
(
    `id`, `importe_solicitud`, `moneda`, `lapso_capital`, `nro_cuotas`, `tasa`, `fecha`, 
    `fecha_desembolso`, `fecha_primera_cuota`, `destino_prestamo`, `tipo_garantia`, 
    `tipo_desembolso`, `estado`, `id_cliente`, `id_usuario`, `tipo_tasa`, 
    `tipo_solicitud`, `created_at`, `updated_at`
) 
VALUES
(
    2, 8000.00, 'Bolivianos', 'Mensual', 4, 10.00, '2025-08-15', 
    '2025-08-15', '2025-09-15', 'Inversión', 'Prendario o Quirografaria', 
    'Efectivo', 1, 241, 10, 'fija', -- 1 = Nuevo (registrado)
    'Nuevo', NOW(), NOW()
);

-- 2.2. El Plan de Pago (Asociado a la Solicitud 2)
-- (Hoy es 14-11-2025)
INSERT INTO `plan_pago` 
(
    `id`, `id_solicitud`, `fecha_inicio`, `fecha_fin`, `total_pagar`, `estado`, 
    `id_plan_aux`, `moneda`, `lapso_capital`, `nro_cuotas`, `tasa`, 
    `fecha_ultima_amortizacion`, `saldo_pendiente`, `fecha_registro`, `created_at`, `updated_at`
) 
VALUES
(
    2, 2, '2025-09-15', '2025-12-15', 10000.00, 1, -- 1=Activo
    0, 'Bolivianos', 'Mensual', 4, 10,
    '2025-10-15', -- Fecha del último pago (Cuota 2)
    4000.00,      -- Saldo pendiente DESPUÉS del último pago
    '2025-08-15', NOW(), NOW()
);

-- 2.3. Las Cuotas (Capital 2000 por cuota)
-- (Hoy es 14-11-2025)
INSERT INTO `cuota` 
(`id`, `numero`, `fecha`, `capital`, `interes`, `saldo_capital`, `ahorro`, `seguro`, `total`, `estado`, `amortizado`, `id_plan_pago`)
VALUES
-- Pagadas
(5, 1, '2025-09-15', 2000.00, 800.00, 6000.00, 0.00, 0.00, 2800.00, 0, 1, 2), -- Pagado (estado 0)
(6, 2, '2025-10-15', 2000.00, 600.00, 4000.00, 0.00, 0.00, 2600.00, 0, 1, 2), -- Pagado (estado 0)
-- Pendientes (PERO NO VENCIDAS)
(7, 3, '2025-11-15', 2000.00, 400.00, 2000.00, 0.00, 0.00, 2400.00, 1, 0, 2), -- Pendiente. Vence MAÑANA.
(8, 4, '2025-12-15', 2000.00, 200.00, 0.00, 0.00, 0.00, 2200.00, 1, 0, 2); -- Pendiente.

COMMIT;