<template>
  <section
    :class="[
      'rounded-2xl shadow p-6',
      darkMode ? 'bg-gray-800 text-gray-100' : 'bg-white text-gray-800'
    ]"
  >
    <h3 class="text-xl font-bold text-blue-500 mb-4">
      Horas trabajadas – {{ tituloMes }}
    </h3>

    <!-- Filtros Año / Mes -->
    <div class="flex flex-wrap items-end gap-4 mb-4">
      <div>
        <label class="block text-sm mb-1">Año</label>
        <select v-model.number="selectedYear" class="input w-28">
          <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
        </select>
      </div>
      <div>
        <label class="block text-sm mb-1">Mes</label>
        <select v-model.number="selectedMonth" class="input w-36">
          <option v-for="m in monthOptions" :key="m.value" :value="m.value">
            {{ m.label }}
          </option>
        </select>
      </div>
    </div>

    <!-- Resumen -->
    <div class="mb-4">
      <p><b>Días trabajados:</b> {{ resumen.diasTrabajados }}</p>
      <p><b>Total horas:</b> {{ resumen.totalHoras }}</p>
    </div>

    <!-- Calendario -->
    <div class="overflow-x-auto">
      <div class="inline-grid grid-cols-7 gap-1 text-center text-xs min-w-[280px]">
        <div
          v-for="d in diasSemanaCorto"
          :key="d"
          class="font-semibold py-1"
          :class="darkMode ? 'text-gray-300' : 'text-gray-600'"
        >
          {{ d }}
        </div>

        <div
          v-for="(dia, idx) in calendario"
          :key="idx"
          :class="[
            'flex flex-col items-center justify-center rounded-lg border text-[10px] px-1 py-1 min-w-[40px] min-h-[40px]',
            dia.esVacio
              ? 'bg-transparent border-transparent'
              : dia.worked
                ? 'bg-green-500 text-white border-green-600'
                : darkMode
                  ? 'bg-red-900/40 text-red-200 border-red-700'
                  : 'bg-red-100 text-red-700 border-red-300'
          ]"
        >
          <span v-if="!dia.esVacio" class="font-bold text-sm">
            {{ dia.numero }}
          </span>
          <span v-if="!dia.esVacio">
            {{ dia.worked ? 'Asistió' : 'Falta' }}
          </span>
        </div>
      </div>
    </div>

    <p class="mt-3 text-xs" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">
      * Los días en verde tienen asistencia registrada; los días en rojo no tienen registro en ese
      mes.
    </p>
  </section>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  darkMode: Boolean,
  asistencias: { type: Array, default: () => [] },
  mesActual: { type: String, default: null }
})

const diasSemanaCorto = ['L', 'M', 'X', 'J', 'V', 'S', 'D']
const hoy = new Date()
let defaultYear = hoy.getFullYear()
let defaultMonth = hoy.getMonth() + 1

if (props.mesActual && props.mesActual.includes('-')) {
  const [y, m] = props.mesActual.split('-').map(Number)
  if (!Number.isNaN(y) && !Number.isNaN(m)) {
    defaultYear = y
    defaultMonth = m
  }
}

const selectedYear = ref(defaultYear)
const selectedMonth = ref(defaultMonth)

const monthOptions = [
  { value: 1, label: 'Enero' }, { value: 2, label: 'Febrero' }, { value: 3, label: 'Marzo' },
  { value: 4, label: 'Abril' }, { value: 5, label: 'Mayo' },    { value: 6, label: 'Junio' },
  { value: 7, label: 'Julio' }, { value: 8, label: 'Agosto' },  { value: 9, label: 'Septiembre' },
  { value: 10, label: 'Octubre' }, { value: 11, label: 'Noviembre' }, { value: 12, label: 'Diciembre' }
]

const yearOptions = computed(() => {
  const years = []
  const start = defaultYear - 3
  const end = defaultYear + 1
  for (let y = start; y <= end; y++) years.push(y)
  return years
})

const asistenciasFiltradas = computed(() =>
  (props.asistencias || []).filter(a => {
    if (!a.fecha) return false
    const [y, m] = a.fecha.split('-').map(Number)
    return y === selectedYear.value && m === selectedMonth.value
  })
)

const mapaAsistencia = computed(() => {
  const m = new Map()
  ;(asistenciasFiltradas.value || []).forEach(a => {
    m.set(a.fecha, Number(a.horas || 0))
  })
  return m
})

const calendario = computed(() => {
  const year = selectedYear.value
  const month = selectedMonth.value
  const firstDay = new Date(year, month - 1, 1)
  const lastDay = new Date(year, month, 0)

  const result = []
  const startWeekday = (firstDay.getDay() + 6) % 7

  for (let i = 0; i < startWeekday; i++) result.push({ esVacio: true })

  for (let d = 1; d <= lastDay.getDate(); d++) {
    const dateObj = new Date(year, month - 1, d)
    const iso = dateObj.toISOString().slice(0, 10)
    const horas = mapaAsistencia.value.get(iso) || 0
    const worked = horas >= 8
    result.push({ esVacio: false, numero: d, fecha: iso, horas, worked })
  }
  return result
})

const resumen = computed(() => {
  const diasTrabajados = (asistenciasFiltradas.value || []).filter(a => Number(a.horas || 0) > 0).length
  const totalHoras = (asistenciasFiltradas.value || []).reduce(
    (acc, a) => acc + Number(a.horas || 0),
    0
  )
  return { diasTrabajados, totalHoras }
})

const tituloMes = computed(() => {
  const m = monthOptions.find(x => x.value === selectedMonth.value)
  return `${m ? m.label : ''} ${selectedYear.value}`
})
</script>

<style scoped>
.input {
  @apply border border-gray-300 rounded-lg p-2 w-full focus:ring-2 focus:ring-blue-400 focus:outline-none;
}
</style>
