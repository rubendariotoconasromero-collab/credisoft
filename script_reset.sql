-- NO BORRAR ACTIVIDADES
-- NO BORRAR USUARIOS
-- NO BORRAR CLIENTES
-- NO BORRAR CODEUDORES
-- boveda no se borra inicia en 0

update boveda set saldo_actual=0 where id=1;

delete from desembolso;
delete from direccion;
delete from telefono;
delete from egreso;
delete from ingreso;
delete from imagen;

delete from garantia;
delete from motivo_ingreso;
delete from motivo_gasto;

delete from movimientos_boveda;
delete from movimientos_caja;
delete from pago;

delete from pago_administrativo;
delete from pago_amortizacion;
delete from cuota;
delete from plan_pago;

delete from respaldo;
delete from solicitud_codeudor;
delete from solicitud;
delete from cliente;
delete from codeudor;
delete from transferencia_caja_boveda;
delete from caja;

-- reset
delete from pago;
delete from cuota;
delete from desembolso;
delete from pago_administrativo;
delete from plan_pago;
delete from ingreso;
delete from egreso;
delete from caja;

delete from solicitud_codeudor;
delete from solicitud;
delete from garantia;
--

ALTER TABLE pago AUTO_INCREMENT = 1;
ALTER TABLE cuota AUTO_INCREMENT = 1;
ALTER TABLE desembolso AUTO_INCREMENT = 1;
ALTER TABLE pago_administrativo AUTO_INCREMENT = 1;
ALTER TABLE plan_pago AUTO_INCREMENT = 1;
ALTER TABLE ingreso AUTO_INCREMENT = 1;
ALTER TABLE egreso AUTO_INCREMENT = 1;
ALTER TABLE caja AUTO_INCREMENT = 1;


-- reset truncate
truncate table pago;
truncate table cuota;
truncate table desembolso;
truncate table pago_administrativo;
truncate table plan_pago;
truncate table ingreso;
truncate table egreso;
truncate table caja;
--

update solicitud set estado=1 ;
update solicitud set desembolso=0 ;

-- NOTA: Para establecer una solicitud sin codeudor o garante se necesita agregar el codeudor/garante: 'SIN GARANTE'





