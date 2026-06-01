<template>
    <div class="ls-wrapper position-relative" ref="wrapper">

        <!-- Control row -->
        <div class="ls-control d-flex align-items-center border bg-white"
             :class="isOpen ? `border-${color} border-opacity-75` : 'border-secondary border-opacity-25'">

            <span class="ls-icon-search ps-2 text-muted opacity-50">
                <i class="fas fa-search" style="font-size:0.65rem;"></i>
            </span>

            <input
                ref="inputEl"
                type="text"
                class="ls-input form-control border-0 shadow-none fw-semibold text-uppercase"
                :class="`text-${color === 'danger' ? 'danger' : 'dark'}`"
                :placeholder="placeholder"
                :value="modelValue"
                :required="required"
                autocomplete="off"
                @input="onInput"
                @focus="onFocus"
                @keydown.down.prevent="moveDown"
                @keydown.up.prevent="moveUp"
                @keydown.enter.prevent="confirmHighlight"
                @keydown.escape="close"
            />

            <!-- Clear -->
            <button v-if="modelValue"
                    type="button"
                    class="ls-btn btn btn-link p-0 px-1 text-muted border-0"
                    tabindex="-1"
                    @mousedown.prevent="clear">
                <i class="fas fa-times-circle" style="font-size:0.7rem;"></i>
            </button>

            <!-- Chevron toggle -->
            <button type="button"
                    class="ls-btn btn btn-link p-0 px-2 border-0 border-start"
                    :class="`text-${color}`"
                    tabindex="-1"
                    @mousedown.prevent="toggle">
                <i class="fas" :class="isOpen ? 'fa-chevron-up' : 'fa-chevron-down'" style="font-size:0.65rem;"></i>
            </button>
        </div>

        <!-- Dropdown -->
        <transition name="ls-slide">
            <ul v-if="isOpen"
                class="ls-dropdown list-unstyled mb-0 position-absolute w-100 bg-white border"
                :class="`border-${color} border-opacity-25`"
                style="z-index:1060; max-height:220px; overflow-y:auto; top:calc(100% + 1px);">

                <!-- Loading -->
                <li v-if="loading" class="ls-state px-3 py-2 text-center text-muted">
                    <span class="spinner-border me-1" style="width:0.7rem;height:0.7rem;border-width:0.1em;"></span>
                    <small>Cargando opciones...</small>
                </li>

                <!-- Empty -->
                <li v-else-if="filtered.length === 0" class="ls-state px-3 py-2 text-center text-muted">
                    <i class="fas fa-search-minus me-1 opacity-50"></i>
                    <small class="fst-italic">Sin coincidencias</small>
                </li>

                <!-- Options -->
                <li v-else
                    v-for="(opt, i) in filtered"
                    :key="i"
                    class="ls-option px-3 py-2 fw-semibold text-uppercase"
                    :class="i === highlighted ? `ls-active-${color}` : ''"
                    @mousedown.prevent="select(opt)"
                    @mouseover="highlighted = i">
                    <i class="fas fa-tag me-2 opacity-25" style="font-size:0.55rem;"></i>{{ opt }}
                </li>
            </ul>
        </transition>

    </div>
</template>

<script>
export default {
    name: 'LiveSearch',
    props: {
        modelValue:  { type: String,  default: '' },
        options:     { type: Array,   default: () => [] },
        placeholder: { type: String,  default: 'Buscar o seleccionar...' },
        color:       { type: String,  default: 'primary' }, // success | danger | primary | warning
        required:    { type: Boolean, default: false },
        loading:     { type: Boolean, default: false },
    },
    emits: ['update:modelValue', 'selected'],
    data() {
        return {
            isOpen: false,
            highlighted: -1,
        };
    },
    computed: {
        filtered() {
            if (!this.modelValue) return this.options;
            const term = this.modelValue.toLowerCase();
            return this.options.filter(o => o.toLowerCase().includes(term));
        },
    },
    methods: {
        onInput(e) {
            this.$emit('update:modelValue', e.target.value);
            this.isOpen = true;
            this.highlighted = -1;
        },
        onFocus() {
            this.isOpen = true;
        },
        open() {
            this.isOpen = true;
        },
        close() {
            this.isOpen = false;
            this.highlighted = -1;
        },
        toggle() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) this.$nextTick(() => this.$refs.inputEl?.focus());
        },
        clear() {
            this.$emit('update:modelValue', '');
            this.$emit('selected', '');
            this.isOpen = true;
            this.$nextTick(() => this.$refs.inputEl?.focus());
        },
        select(opt) {
            this.$emit('update:modelValue', opt);
            this.$emit('selected', opt);
            this.close();
        },
        moveDown() {
            if (!this.isOpen) { this.isOpen = true; return; }
            this.highlighted = Math.min(this.highlighted + 1, this.filtered.length - 1);
            this.scrollToHighlighted();
        },
        moveUp() {
            this.highlighted = Math.max(this.highlighted - 1, -1);
            this.scrollToHighlighted();
        },
        confirmHighlight() {
            if (this.highlighted >= 0 && this.filtered[this.highlighted]) {
                this.select(this.filtered[this.highlighted]);
            }
        },
        scrollToHighlighted() {
            this.$nextTick(() => {
                const list = this.$el.querySelector('.ls-dropdown');
                const item = list?.querySelectorAll('.ls-option')[this.highlighted];
                item?.scrollIntoView({ block: 'nearest' });
            });
        },
        handleClickOutside(e) {
            if (!this.$refs.wrapper?.contains(e.target)) this.close();
        },
    },
    mounted() {
        document.addEventListener('mousedown', this.handleClickOutside);
    },
    beforeUnmount() {
        document.removeEventListener('mousedown', this.handleClickOutside);
    },
};
</script>

<style scoped>
.ls-control {
    min-height: 32px;
    border-radius: 0 !important;
    transition: border-color 0.15s ease;
}
.ls-input {
    font-size: 0.8rem;
    padding: 3px 6px;
    min-height: 0;
}
.ls-btn { line-height: 1; flex-shrink: 0; }
.ls-dropdown {
    border-radius: 0 !important;
    border-top: none !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.ls-dropdown::-webkit-scrollbar { width: 4px; }
.ls-dropdown::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 2px; }
.ls-state { font-size: 0.78rem; }
.ls-option {
    font-size: 0.78rem;
    cursor: pointer;
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.08s;
}
.ls-option:last-child { border-bottom: none; }
.ls-option:not(.ls-active-success):not(.ls-active-danger):not(.ls-active-primary):hover {
    background: #f9fafb;
}

/* Active state por color */
.ls-active-success { background: #f0fdf4 !important; color: #198754 !important; }
.ls-active-danger  { background: #fef2f2 !important; color: #dc3545 !important; }
.ls-active-primary { background: #eff6ff !important; color: #0d6efd !important; }
.ls-active-warning { background: #fffbeb !important; color: #b45309 !important; }

/* Dropdown animation */
.ls-slide-enter-active,
.ls-slide-leave-active { transition: opacity 0.1s ease, transform 0.1s ease; }
.ls-slide-enter-from,
.ls-slide-leave-to  { opacity: 0; transform: translateY(-4px); }
</style>
