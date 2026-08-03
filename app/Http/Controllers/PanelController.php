<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\CalculaSaldoCaja;

/**
 * Paneles de inicio (post-login) específicos por rol operativo.
 *
 * El panel general (frmAdministracion.vue / AdministracionController) sigue
 * siendo el que ven Administrador y Encargado de Agencia. Este controlador
 * cubre los paneles de Cajero y Oficial de Crédito, con datos acotados a lo
 * que cada uno necesita para su trabajo diario (no son reportes gerenciales).
 */
class PanelController extends Controller
{
    use CalculaSaldoCaja;

    /**
     * Panel del Cajero: estado de la caja (abierta/cerrada, saldo actual) y
     * resumen de movimientos del día. Solo hay una caja abierta a la vez en
     * todo el sistema (ver CajaController::cajaAbierta), por eso no se
     * filtra por usuario: se muestra la caja abierta actual, sea de quien sea.
     */
    public function getPanelCajero()
    {
        $hoy = now()->toDateString();

        $cajaAbierta = DB::table('caja')
            ->join('users', 'users.id', '=', 'caja.id_usuario')
            ->where('caja.estado', 1)
            ->select('caja.id', 'caja.monto_inicial', 'caja.fechahora_apertura', 'users.name as usuario', 'users.id as id_usuario')
            ->first();

        $data = [
            'caja_abierta' => false,
            'es_mi_caja' => false,
            'usuario_caja' => null,
            'monto_inicial' => 0,
            'fecha_apertura' => null,
            'saldo_actual' => 0,
            'cobros_hoy_cantidad' => 0,
            'cobros_hoy_monto' => 0,
            'desembolsos_hoy_cantidad' => 0,
            'desembolsos_hoy_monto' => 0,
            'ingresos_hoy_monto' => 0,
            'gastos_hoy_monto' => 0,
        ];

        if ($cajaAbierta) {
            $idCaja = $cajaAbierta->id;

            $data['caja_abierta'] = true;
            $data['es_mi_caja'] = ((int) $cajaAbierta->id_usuario === (int) Auth::id());
            $data['usuario_caja'] = $cajaAbierta->usuario;
            $data['monto_inicial'] = (float) $cajaAbierta->monto_inicial;
            $data['fecha_apertura'] = $cajaAbierta->fechahora_apertura;
            $data['saldo_actual'] = round($this->calcularSaldoCaja($idCaja), 2);

            $data['cobros_hoy_cantidad'] = (int) DB::table('pago')
                ->where('id_caja', $idCaja)->where('estado', 1)
                ->whereDate('fecha_pago', $hoy)->count();

            $data['cobros_hoy_monto'] = round((float) (DB::table('pago')
                ->where('id_caja', $idCaja)->where('estado', 1)
                ->whereDate('fecha_pago', $hoy)->sum('monto_pago') ?? 0), 2);

            $data['desembolsos_hoy_cantidad'] = (int) DB::table('desembolso')
                ->where('id_caja', $idCaja)->where('estado', 0)
                ->whereDate('fecha', $hoy)->count();

            $data['desembolsos_hoy_monto'] = round((float) (DB::table('desembolso')
                ->where('id_caja', $idCaja)->where('estado', 0)
                ->whereDate('fecha', $hoy)->sum('monto') ?? 0), 2);

            $data['ingresos_hoy_monto'] = round((float) (DB::table('ingreso')
                ->where('id_caja', $idCaja)->where('estado', 1)
                ->whereDate('fecha', $hoy)->sum('monto') ?? 0), 2);

            $data['gastos_hoy_monto'] = round((float) (DB::table('egreso')
                ->where('id_caja', $idCaja)->where('estado', 1)
                ->whereDate('fecha', $hoy)->sum('monto') ?? 0), 2);
        }

        // Informativo, independiente de si hay caja abierta: volumen de trabajo del día.
        $data['cuotas_vencen_hoy'] = (int) DB::table('cuota')
            ->whereIn('estado', [1, 3])
            ->whereDate('fecha', $hoy)
            ->count();

        return response()->json($data);
    }

    /**
     * Panel del Oficial de Crédito: su cartera propia, acotada siempre por
     * solicitud.id_usuario = el oficial autenticado (mismo criterio que ya
     * usan los reportes "por oficial" existentes).
     */
    public function getPanelOficial()
    {
        $idUsuario = Auth::id();
        $hoy = now()->startOfDay();
        $limiteProximos = now()->addDays(7)->endOfDay();

        $misClientes = (int) DB::table('solicitud')
            ->where('id_usuario', $idUsuario)
            ->distinct()
            ->count('id_cliente');

        $creditosActivos = (int) DB::table('solicitud')
            ->join('plan_pago', 'plan_pago.id_solicitud', '=', 'solicitud.id')
            ->where('solicitud.id_usuario', $idUsuario)
            ->where('plan_pago.estado', 1)
            ->count();

        $saldoCarteraPendiente = round((float) (DB::table('solicitud')
            ->join('plan_pago', 'plan_pago.id_solicitud', '=', 'solicitud.id')
            ->where('solicitud.id_usuario', $idUsuario)
            ->where('plan_pago.estado', 1)
            ->sum('plan_pago.saldo_pendiente') ?? 0), 2);

        // Solicitudes activas del oficial que todavía no generaron un plan de pago.
        $solicitudesPendientes = (int) DB::table('solicitud')
            ->where('id_usuario', $idUsuario)
            ->where('estado', 1)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('plan_pago')
                  ->whereColumn('plan_pago.id_solicitud', 'solicitud.id');
            })
            ->count();

        $creditosEnMora = (int) DB::table('solicitud')
            ->join('plan_pago', 'plan_pago.id_solicitud', '=', 'solicitud.id')
            ->join('cuota', 'cuota.id_plan_pago', '=', 'plan_pago.id')
            ->where('solicitud.id_usuario', $idUsuario)
            ->whereIn('cuota.estado', [1, 3])
            ->whereDate('cuota.fecha', '<', $hoy)
            ->distinct()
            ->count('solicitud.id');

        $montoEnMora = round((float) (DB::table('solicitud')
            ->join('plan_pago', 'plan_pago.id_solicitud', '=', 'solicitud.id')
            ->join('cuota', 'cuota.id_plan_pago', '=', 'plan_pago.id')
            ->where('solicitud.id_usuario', $idUsuario)
            ->whereIn('cuota.estado', [1, 3])
            ->whereDate('cuota.fecha', '<', $hoy)
            ->sum(DB::raw('cuota.total - (cuota.capital_pagado + cuota.interes_pagado)')) ?? 0), 2);

        $proximosVencimientos = (int) DB::table('solicitud')
            ->join('plan_pago', 'plan_pago.id_solicitud', '=', 'solicitud.id')
            ->join('cuota', 'cuota.id_plan_pago', '=', 'plan_pago.id')
            ->where('solicitud.id_usuario', $idUsuario)
            ->whereIn('cuota.estado', [1, 3])
            ->whereBetween('cuota.fecha', [$hoy, $limiteProximos])
            ->count();

        $ultimasSolicitudes = DB::table('solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->where('solicitud.id_usuario', $idUsuario)
            ->orderBy('solicitud.created_at', 'desc')
            ->limit(5)
            ->select(
                'solicitud.id',
                'cliente.nombre as cliente',
                'solicitud.importe_solicitud',
                'solicitud.moneda',
                'solicitud.estado',
                'solicitud.created_at'
            )
            ->get();

        return response()->json([
            'mis_clientes' => $misClientes,
            'creditos_activos' => $creditosActivos,
            'saldo_cartera_pendiente' => $saldoCarteraPendiente,
            'solicitudes_pendientes' => $solicitudesPendientes,
            'creditos_en_mora' => $creditosEnMora,
            'monto_en_mora' => $montoEnMora,
            'proximos_vencimientos' => $proximosVencimientos,
            'ultimas_solicitudes' => $ultimasSolicitudes,
        ]);
    }
}
