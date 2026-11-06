<template>
    <div class="desglose-exigible" :title="'Total registrado antes de condonar: ' + formatNumero(registrado) + ' Bs'">
        <div><span>Programado</span><strong>{{ formatNumero(programado) }}</strong></div>
        <div><span>Variación vs. plan</span><strong :class="variacion >= 0 ? 'text-danger' : 'text-success'">{{ variacion >= 0 ? '+' : '−' }}{{ formatNumero(Math.abs(variacion)) }}</strong></div>
        <div><span>Condonado</span><strong>−{{ formatNumero(condonado) }}</strong></div>
        <div class="desglose-exigible__total"><span>Exigible neto</span><strong>{{ formatNumero(exigible) }} Bs</strong></div>
    </div>
</template>

<script>
export default {
    name: 'DesgloseExigibleCuota',
    props: {
        cuota: { type: Object, required: true }
    },
    computed: {
        programado() {
            return Math.round(Number(this.cuota.total || 0) * 100);
        },
        registrado() {
            return Math.round(Number(this.cuota.total_registrado_antes_condonacion || 0) * 100);
        },
        condonado() {
            return Math.round(Number(this.cuota.interes_condonado_total || 0) * 100)
                + Math.round(Number(this.cuota.mora_condonado_total || 0) * 100);
        },
        variacion() {
            return this.registrado - this.programado;
        },
        exigible() {
            return this.registrado - this.condonado;
        }
    },
    methods: {
        formatNumero(centavos) {
            return new Intl.NumberFormat('es-BO', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(centavos / 100);
        }
    }
};
</script>

<style scoped>
.desglose-exigible {
    min-width: 132px;
    max-width: 170px;
    margin: auto;
    font-variant-numeric: tabular-nums;
    text-align: left;
}
.desglose-exigible > div {
    display: flex;
    justify-content: space-between;
    gap: 0.4rem;
    white-space: nowrap;
}
.desglose-exigible > div:not(:last-child) {
    color: #6c757d;
}
.desglose-exigible__total {
    margin-top: 0.18rem;
    padding-top: 0.18rem;
    border-top: 1px solid #d1d9e0;
    color: #0b5ed7;
    font-weight: 700;
}
</style>
