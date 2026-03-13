<template>
    <div class="modal fade" id="modalCalculadoraCredito" tabindex="-1" aria-labelledby="modalCalculadoraLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl" style="max-width:90%; width:90%">
            <div class="modal-content border border-2 border-dark">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title text-dark" id="modalCalculadoraLabel">
                        <i class="fas fa-calculator me-2"></i>Cálculo de Cuotas y Plan de Pago
                    </h5>
                    <button type="button" class="btn-close btn-close-dark" @click="cerrar"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Monto Crédito</label>
                            <input v-model="solicitud_simulacion.importe_solicitud" type="number"
                                class="form-control" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Forma de Pago</label>
                            <select v-model="solicitud_simulacion.lapso_capital" class="form-select"
                                @change="actualizarCuotas()" required>
                                <option value="" disabled>Seleccione</option>
                                <option v-for="item in lapso_capitales" :key="item.nombre" :value="item.nombre">
                                    {{ item.nombre }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Plazo (Meses)</label>
                            <input v-model="solicitud_simulacion.plazo" type="number" @input="actualizarCuotas()"
                                class="form-control" required />
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Nro. Cuotas</label>
                            <input v-model="solicitud_simulacion.nro_cuotas" type="number" class="form-control"
                                disabled required />
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Tasa %</label>
                            <input v-model="solicitud_simulacion.tasa" type="number" class="form-control" required />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Tipo de Cuota</label>
                            <select v-model="tipo_tasa" class="form-select" required>
                                <option value="" disabled>Seleccione</option>
                                <option value="fija">Cuota Fija</option>
                                <option value="amortizable">Cuota Variable</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Fecha Desembolso</label>
                            <input v-model="solicitud_simulacion.fecha_desembolso" type="date" class="form-control" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Fecha Primera Cuota</label>
                            <input v-model="solicitud_simulacion.fecha_primera_cuota" type="date" class="form-control" required />
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <button @click="generarPlanPagoSimulacionGeneral" class="btn btn-success me-2">
                                Generar Plan pago
                            </button>
                            <button @click="listaCuotasPdfSimulacion()" class="btn btn-warning text-dark">
                                <i class="fas fa-print me-1"></i> Imprimir
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive mt-4">
                        <table class="table table-bordered table-sm table-striped">
                            <thead class="table-success">
                                <tr>
                                    <th class="text-uppercase fw-bold">Nro</th>
                                    <th class="text-uppercase fw-bold">Fecha</th>
                                    <th class="text-uppercase fw-bold text-center">Capital</th>
                                    <th class="text-uppercase fw-bold text-center">Interes %</th>
                                    <th class="text-uppercase fw-bold text-center">Saldo Capital</th>
                                    <th class="text-uppercase fw-bold text-center">Total Bs</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in lista_cuotas_simulacion" :key="item.nro">
                                    <td>{{ item.nro }}</td>
                                    <td>{{ formatearFecha(item.fecha) }}</td>
                                    <td class="text-center">{{ item.capital }}</td>
                                    <td class="text-center">{{ item.interes }}</td>
                                    <td class="text-center">{{ item.saldo_capital }}</td>
                                    <td class="text-center">{{ item.total_cuota }}</td>
                                </tr>
                                <tr v-if="lista_cuotas_simulacion.length === 0">
                                    <td colspan="6" class="text-center text-muted fst-italic py-3">
                                        Ingrese los datos y genere el plan para ver la tabla.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="cerrar">
                        <i class="fas fa-times me-1"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import moment from "moment";
import Swal from "sweetalert2";

export default {
    data() {
        return {
            // Datos locales del simulador
            solicitud_simulacion: {
                importe_solicitud: 0,
                lapso_capital: "",
                nro_cuotas: 0,
                cantidad_ahorro: 0,
                cantidad_seguro: 0,
                ahorro: true,
                seguro: true,
                tasa: 0,
                plazo: 0,
                fecha_desembolso: moment().format("YYYY-MM-DD"),
                fecha_primera_cuota: moment().format("YYYY-MM-DD"),
            },
            tipo_tasa: "amortizable",
            lista_cuotas_simulacion: [],
            
            // Listas estáticas necesarias para el select
            lapso_capitales: [
                { nombre: "Semanal" },
                { nombre: "Quincenal" },
                { nombre: "Mensual" },
            ],
        };
    },
    methods: {
        // --- MÉTODOS DE APERTURA/CIERRE ---
        abrir() {
            this.resetearDatos();
            // Usamos jQuery como en el original
            $("#modalCalculadoraCredito").modal("show");
        },
        
        cerrar() {
            $("#modalCalculadoraCredito").modal("hide");
            this.resetearDatos();
            this.$emit('cerrar');
        },

        resetearDatos() {
            this.solicitud_simulacion = {
                importe_solicitud: 0,
                lapso_capital: "",
                nro_cuotas: 0,
                cantidad_ahorro: 0,
                cantidad_seguro: 0,
                ahorro: true,
                seguro: true,
                tasa: 0,
                plazo: 0,
                fecha_desembolso: moment().format("YYYY-MM-DD"),
                fecha_primera_cuota: moment().format("YYYY-MM-DD"),
            };
            this.lista_cuotas_simulacion = [];
            this.tipo_tasa = "amortizable";
        },

        // --- LÓGICA DE NEGOCIO ---
        actualizarCuotas() {
            // Cálculo simplificado respetando tu lógica original
            const plazo = parseFloat(this.solicitud_simulacion.plazo) || 0;
            const lapso = this.solicitud_simulacion.lapso_capital;

            if (lapso == 'Semanal') {
                this.solicitud_simulacion.nro_cuotas = plazo * 4;
            } else if (lapso == 'Quincenal') {
                this.solicitud_simulacion.nro_cuotas = plazo * 2;
            } else if (lapso == 'Mensual') {
                this.solicitud_simulacion.nro_cuotas = plazo * 1;
            } else {
                this.solicitud_simulacion.nro_cuotas = 0;
            }
        },

        async generarPlanPagoSimulacionGeneral() {
            // Validaciones
            if (this.solicitud_simulacion.importe_solicitud == 0 ||
                this.solicitud_simulacion.nro_cuotas == 0 ||
                this.solicitud_simulacion.tasa == 0 ||
                !this.solicitud_simulacion.lapso_capital) {
                
                Swal.fire({
                    position: 'center',
                    icon: 'warning',
                    title: 'Advertencia.!!!',
                    text: 'Faltan datos por ingresar.!!!',
                    showConfirmButton: true,
                    confirmButtonText: 'Aceptar',
                });
                return;
            }

            if (this.tipo_tasa == 'amortizable') {
                await this.generarPlanPagosSimulacion();
            } else if (this.tipo_tasa == 'fija') {
                await this.generarPlanPagosTasaFijaSimulacion();
            }
        },

        // LÓGICA AMORTIZABLE (Variable) - Respetando lógica original
        async generarPlanPagosSimulacion() {
            this.solicitud_simulacion.saldo_capital_sq = 0;
            this.lista_cuotas_simulacion = [];
            
            let contador = 1;
            let fecha_inicio = moment(this.solicitud_simulacion.fecha_desembolso);
            let fecha_final = moment(this.solicitud_simulacion.fecha_primera_cuota);
            
            let capital_aux = this.solicitud_simulacion.importe_solicitud / this.solicitud_simulacion.nro_cuotas;
            let saldo_capital_aux = parseFloat(this.solicitud_simulacion.importe_solicitud);
            this.solicitud_simulacion.saldo_capital_sq = parseFloat(this.solicitud_simulacion.importe_solicitud);

            while (contador <= this.solicitud_simulacion.nro_cuotas) {
                let dias_inicio_fin = fecha_final.diff(fecha_inicio, 'days');
                let interes = 0;

                if (contador == 1) {
                    interes = (saldo_capital_aux * (this.solicitud_simulacion.tasa / 100)) / 30;
                } else {
                    if (this.solicitud_simulacion.lapso_capital == 'Mensual') {
                        interes = (saldo_capital_aux * (this.solicitud_simulacion.tasa / 100)) / dias_inicio_fin;
                    } else if (this.solicitud_simulacion.lapso_capital == 'Semanal') {
                        interes = (this.solicitud_simulacion.saldo_capital_sq * (this.solicitud_simulacion.tasa / 100)) / 4;
                    } else if (this.solicitud_simulacion.lapso_capital == 'Quincenal') {
                        interes = (this.solicitud_simulacion.saldo_capital_sq * (this.solicitud_simulacion.tasa / 100)) / 2;
                    }
                }
                
                saldo_capital_aux -= capital_aux;

                // Lógica de saldo sq para semanales/quincenales
                if (this.solicitud_simulacion.lapso_capital == 'Semanal' && (contador % 4 == 0)) {
                    this.solicitud_simulacion.saldo_capital_sq = saldo_capital_aux;
                }
                if (this.solicitud_simulacion.lapso_capital == 'Quincenal' && (contador % 2 == 0)) {
                    this.solicitud_simulacion.saldo_capital_sq = saldo_capital_aux;
                }

                this.lista_cuotas_simulacion.push({
                    nro: contador,
                    fecha: fecha_final.format('YYYY-MM-DD'),
                    capital: Math.round(parseFloat(capital_aux)), // Removido toFixed intermedio para evitar strings
                    interes: Math.round(parseFloat(interes * (this.solicitud_simulacion.lapso_capital == 'Mensual' || contador == 1 ? dias_inicio_fin : 1))),
                    saldo_capital: Math.round(parseFloat(saldo_capital_aux)),
                    total_cuota: Math.round(parseFloat(capital_aux) + parseFloat(interes * (this.solicitud_simulacion.lapso_capital == 'Mensual' || contador == 1 ? dias_inicio_fin : 1))),
                });

                fecha_inicio = fecha_final.clone(); // Clone para evitar mutación incorrecta
                
                if (this.solicitud_simulacion.lapso_capital == 'Mensual') {
                    fecha_final.add(1, 'month');
                } else if (this.solicitud_simulacion.lapso_capital == 'Quincenal') {
                    fecha_final.add(15, 'days');
                } else if (this.solicitud_simulacion.lapso_capital == 'Semanal') {
                    fecha_final.add(1, 'week');
                } else if (this.solicitud_simulacion.lapso_capital == 'Diario') {
                    fecha_final.add(1, 'day');
                }
                contador++;
            }
        },

        // LÓGICA TASA FIJA
        async generarPlanPagosTasaFijaSimulacion() {
            this.lista_cuotas_simulacion = [];
            
            const monto_total = parseFloat(this.solicitud_simulacion.importe_solicitud);
            let saldo_capital = monto_total;
            let fecha_inicio = moment(this.solicitud_simulacion.fecha_desembolso);
            let fecha_pago = moment(this.solicitud_simulacion.fecha_primera_cuota);
            
            const tasa_mensual = parseFloat(this.solicitud_simulacion.tasa) / 100;
            let cuotas_por_mes = 1;
            let interes_mensual = 0;
            let interes_acumulado = 0;

            switch (this.solicitud_simulacion.lapso_capital) {
                case 'Mensual': cuotas_por_mes = 1; break;
                case 'Quincenal': cuotas_por_mes = 2; break;
                case 'Semanal': cuotas_por_mes = 4; break;
                case 'Diario': cuotas_por_mes = 30; break;
                default: throw new Error('Período de pago no soportado');
            }

            // Método Francés
            const cuota_fija = monto_total *
                (tasa_mensual * Math.pow(1 + tasa_mensual, this.solicitud_simulacion.nro_cuotas / cuotas_por_mes)) /
                (Math.pow(1 + tasa_mensual, this.solicitud_simulacion.nro_cuotas / cuotas_por_mes) - 1);

            const cuota_periodo = cuota_fija / cuotas_por_mes;

            for (let nro_cuota = 1; nro_cuota <= this.solicitud_simulacion.nro_cuotas; nro_cuota++) {
                const dias_periodo = fecha_pago.diff(fecha_inicio, 'days');
                let dias_diferencia = 0;
                let monto_diferencia_dias = 0;
                let mas_menos = true; 

                // Cálculo de interés mensual completo (solo 1ra cuota del mes)
                if ((nro_cuota - 1) % cuotas_por_mes === 0) {
                    interes_mensual = saldo_capital * tasa_mensual;
                    interes_acumulado = 0;

                    if (nro_cuota == 1) {
                        const inicio = moment(fecha_inicio);
                        let fin = null;
                        
                        // Determinar fecha fin teórica del periodo
                        if (this.solicitud_simulacion.lapso_capital == 'Mensual') fin = inicio.clone().add(30, 'days');
                        else if (this.solicitud_simulacion.lapso_capital == 'Quincenal') fin = inicio.clone().add(15, 'days');
                        else if (this.solicitud_simulacion.lapso_capital == 'Semanal') fin = inicio.clone().add(7, 'days');
                        else if (this.solicitud_simulacion.lapso_capital == 'Diario') fin = inicio.clone().add(1, 'days');

                        const dias = fin.diff(inicio, 'days');
                        let cant_dias = (this.solicitud_simulacion.lapso_capital == 'Semanal') ? 28 : 30;

                        if (dias_periodo > dias) {
                            dias_diferencia = dias_periodo - dias;
                            monto_diferencia_dias = (interes_mensual / cant_dias) * dias_diferencia;
                        } else if (dias_periodo < dias) {
                            dias_diferencia = dias - dias_periodo;
                            mas_menos = false;
                            monto_diferencia_dias = (interes_mensual / cant_dias) * dias_diferencia;
                        }
                    }
                }

                // Distribuir interés
                const interes_periodo = interes_mensual / cuotas_por_mes;
                interes_acumulado += interes_periodo;

                // Ajuste de redondeo en última cuota del mes
                const interes_ajustado = (nro_cuota % cuotas_por_mes === 0 || nro_cuota === this.solicitud_simulacion.nro_cuotas)
                    ? interes_mensual - (interes_acumulado - interes_periodo)
                    : interes_periodo;

                const capital_periodo = (cuota_periodo - interes_ajustado);
                saldo_capital -= capital_periodo;

                this.lista_cuotas_simulacion.push({
                    nro: nro_cuota,
                    fecha: fecha_pago.format('YYYY-MM-DD'),
                    capital: Math.round(capital_periodo),
                    saldo_capital: Math.round(saldo_capital),
                    interes: Math.round((mas_menos) ? interes_ajustado + parseFloat(monto_diferencia_dias) : interes_ajustado - parseFloat(monto_diferencia_dias)),
                    total_cuota: Math.round((mas_menos) ? cuota_periodo + parseFloat(monto_diferencia_dias) : cuota_periodo - parseFloat(monto_diferencia_dias)),
                });

                fecha_inicio = fecha_pago.clone();

                if (this.solicitud_simulacion.lapso_capital == 'Mensual') fecha_pago.add(1, 'month');
                else if (this.solicitud_simulacion.lapso_capital == 'Quincenal') fecha_pago.add(15, 'days');
                else if (this.solicitud_simulacion.lapso_capital == 'Semanal') fecha_pago.add(1, 'week');
                else if (this.solicitud_simulacion.lapso_capital == 'Diario') fecha_pago.add(1, 'day');
            }
        },

        listaCuotasPdfSimulacion() {
            if (this.lista_cuotas_simulacion.length === 0) {
                Swal.fire({
                    position: 'center',
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Primero debe generar el plan de pagos',
                    showConfirmButton: true,
                    confirmButtonText: 'Aceptar',
                });
            } else {
                // Preparamos objeto para el reporte
                const simulacionData = {
                    ...this.solicitud_simulacion,
                    tipo_tasa: this.tipo_tasa
                };
                
                const url = '/imprimir_cuotas_simulacion?' + new URLSearchParams({
                    solicitud_simulacion: JSON.stringify(simulacionData),
                    detalles: JSON.stringify(this.lista_cuotas_simulacion)
                }).toString();
                window.open(url, '_blank');
            }
        },

        formatearFecha(fecha) {
            return fecha ? moment(fecha).format("DD/MM/YYYY") : "-";
        },
    },
};
</script>
<style scoped>
@import './../styles/frmSolicitud.css';
</style>