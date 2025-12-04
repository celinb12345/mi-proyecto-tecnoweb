<template>
  <div
    :class="[
      'p-6 md:p-8 rounded-2xl shadow-md transition-colors duration-200',
      isDark ? 'bg-gray-900 text-gray-100' : 'bg-white text-gray-900'
    ]"
  >
    <!-- CABECERA -->
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2
          class="text-xl md:text-2xl font-bold"
          :class="isDark ? 'text-blue-400' : 'text-blue-700'"
        >
          Tablero de Estadísticas
        </h2>
        <p
          class="text-xs md:text-sm"
          :class="isDark ? 'text-gray-300' : 'text-gray-600'"
        >
          Vista rápida de tu negocio de melamina: personas, inventario, pagos,
          cuotas y proyectos en curso.
        </p>
      </div>
    </div>

    <!-- GRID: 2 columnas en desktop, varias filas -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- 1) Torta personas -->
      <div
        class="rounded-2xl p-3 md:p-4 border relative min-h-[220px]"
        :class="isDark ? 'bg-gray-800 border-gray-700' : 'bg-gray-50 border-gray-200'"
      >
        <h3 class="text-sm md:text-base font-semibold mb-2">
          Personas vinculadas al negocio
        </h3>
        <p class="text-[11px] md:text-xs mb-2" :class="isDark ? 'text-gray-300' : 'text-gray-600'">
          Distribución de trabajadores, clientes y proveedores.
        </p>

        <div class="relative h-[160px]">
          <canvas ref="personasCanvas" class="w-full h-full"></canvas>

          <div
            v-if="loadingPersonas"
            class="absolute inset-0 flex items-center justify-center text-xs"
          >
            Cargando...
          </div>
          <div
            v-else-if="!hasPersonas"
            class="absolute inset-0 flex items-center justify-center text-[11px] md:text-xs text-gray-400"
          >
            No hay personas registradas para este propietario.
          </div>
        </div>

        <div
          v-if="!loadingPersonas && hasPersonas"
          class="mt-2 text-[11px] md:text-xs flex flex-wrap gap-2"
        >
          <span v-for="(label, idx) in personas.labels" :key="label">
            <strong>{{ label }}:</strong>
            {{ personas.cantidades[idx] }} ({{ personas.porcentajes[idx] }}%)
          </span>
          <span class="ml-auto font-semibold">
            Total: {{ personas.total }}
          </span>
        </div>
      </div>

      <!-- 2) Barras inventario mensual -->
      <div
        class="rounded-2xl p-3 md:p-4 border relative min-h-[220px]"
        :class="isDark ? 'bg-gray-800 border-gray-700' : 'bg-gray-50 border-gray-200'"
      >
        <h3 class="text-sm md:text-base font-semibold mb-2">
          Inventario: entradas y salidas por mes
        </h3>
        <p class="text-[11px] md:text-xs mb-2" :class="isDark ? 'text-gray-300' : 'text-gray-600'">
          Cantidad de materiales que entran y salen del inventario (por año-mes).
        </p>

        <div class="relative h-[160px]">
          <canvas ref="inventarioCanvas" class="w-full h-full"></canvas>

          <div
            v-if="loadingInventario"
            class="absolute inset-0 flex items-center justify-center text-xs"
          >
            Cargando...
          </div>
          <div
            v-else-if="!hasInventario"
            class="absolute inset-0 flex items-center justify-center text-[11px] md:text-xs text-gray-400"
          >
            No hay movimientos de inventario registrados.
          </div>
        </div>
      </div>

      <!-- 3) Barras pagos pendientes -->
      <div
        class="rounded-2xl p-3 md:p-4 border relative min-h-[220px]"
        :class="isDark ? 'bg-gray-800 border-gray-700' : 'bg-gray-50 border-gray-200'"
      >
        <h3 class="text-sm md:text-base font-semibold mb-2">
          Pagos pendientes
        </h3>
        <p class="text-[11px] md:text-xs mb-2" :class="isDark ? 'text-gray-300' : 'text-gray-600'">
          Monto total pendiente a trabajadores, proveedores y clientes.
        </p>

        <div class="relative h-[160px]">
          <canvas ref="pagosCanvas" class="w-full h-full"></canvas>

          <div
            v-if="loadingPagos"
            class="absolute inset-0 flex items-center justify-center text-xs"
          >
            Cargando...
          </div>
          <div
            v-else-if="!hasPagos"
            class="absolute inset-0 flex items-center justify-center text-[11px] md:text-xs text-gray-400"
          >
            No hay pagos pendientes registrados.
          </div>
        </div>

        <div
          v-if="!loadingPagos && hasPagos"
          class="mt-2 text-[11px] md:text-xs text-right"
        >
          Total pendiente:
          <strong>Bs {{ pagos.total_monto.toFixed(2) }}</strong>
        </div>
      </div>

      <!-- 4) Cuotas pendientes -->
      <div
        class="rounded-2xl p-3 md:p-4 border relative min-h-[220px]"
        :class="isDark ? 'bg-gray-800 border-gray-700' : 'bg-gray-50 border-gray-200'"
      >
        <h3 class="text-sm md:text-base font-semibold mb-2">
          Cuotas pendientes
        </h3>
        <p class="text-[11px] md:text-xs mb-2" :class="isDark ? 'text-gray-300' : 'text-gray-600'">
          Número de cuotas y monto total pendiente por tipo de destino.
        </p>

        <div class="relative h-[160px]">
          <canvas ref="cuotasCanvas" class="w-full h-full"></canvas>

          <div
            v-if="loadingCuotas"
            class="absolute inset-0 flex items-center justify-center text-xs"
          >
            Cargando...
          </div>
          <div
            v-else-if="!hasCuotas"
            class="absolute inset-0 flex items-center justify-center text-[11px] md:text-xs text-gray-400"
          >
            No hay cuotas pendientes registradas.
          </div>
        </div>

        <div
          v-if="!loadingCuotas && hasCuotas"
          class="mt-2 text-[11px] md:text-xs text-right"
        >
          Total cuotas:
          <strong>{{ cuotas.total_cuotas }}</strong>
          · Monto total:
          <strong>Bs {{ cuotas.total_monto.toFixed(2) }}</strong>
        </div>
      </div>

      <!-- 5) Proyectos cuenta regresiva -->
      <div
        class="rounded-2xl p-3 md:p-4 border relative min-h-[220px]"
        :class="isDark ? 'bg-gray-800 border-gray-700' : 'bg-gray-50 border-gray-200'"
      >
        <h3 class="text-sm md:text-base font-semibold mb-2">
          Proyectos en cuenta regresiva
        </h3>
        <p class="text-[11px] md:text-xs mb-2" :class="isDark ? 'text-gray-300' : 'text-gray-600'">
          Días restantes para la fecha de fin de cada proyecto.
        </p>

        <div class="relative h-[160px]">
          <canvas ref="proyectosCanvas" class="w-full h-full"></canvas>

          <div
            v-if="loadingProyectos"
            class="absolute inset-0 flex items-center justify-center text-xs"
          >
            Cargando...
          </div>
          <div
            v-else-if="!hasProyectos"
            class="absolute inset-0 flex items-center justify-center text-[11px] md:text-xs text-gray-400"
          >
            No hay proyectos registrados para este propietario.
          </div>
        </div>

        <div
          v-if="!loadingProyectos && hasProyectos"
          class="mt-2 text-[11px] md:text-xs space-y-1 max-h-20 overflow-y-auto pr-1"
        >
          <div
            v-for="p in proyectos.detalles"
            :key="p.nombre"
            class="flex justify-between gap-2"
          >
            <span class="truncate">
              {{ p.nombre || 'Proyecto' }} ({{ p.estado }})
            </span>
            <span>
              {{ p.dias_restantes }} día(s) restantes
            </span>
          </div>
        </div>
      </div>

      <!-- 6) Pagos de clientes por proyecto (NUEVA GRÁFICA) -->
      <div
        class="rounded-2xl p-3 md:p-4 border relative min-h-[260px]"
        :class="isDark ? 'bg-gray-800 border-gray-700' : 'bg-gray-50 border-gray-200'"
      >
        <h3 class="text-sm md:text-base font-semibold mb-2">
          Pagos de clientes por proyecto
        </h3>
        <p class="text-[11px] md:text-xs mb-2" :class="isDark ? 'text-gray-300' : 'text-gray-600'">
          Precio total vs monto pagado por cada proyecto (cuotas de clientes).
        </p>

        <div class="relative h-[160px]">
          <canvas ref="proyectosPagosCanvas" class="w-full h-full"></canvas>

          <div
            v-if="loadingProyectosPagos"
            class="absolute inset-0 flex items-center justify-center text-xs"
          >
            Cargando...
          </div>
          <div
            v-else-if="!hasProyectosPagos"
            class="absolute inset-0 flex items-center justify-center text-[11px] md:text-xs text-gray-400"
          >
            No hay proyectos con cuotas registradas.
          </div>
        </div>

        <!-- Resumen por proyecto y detalle de cuotas -->
        <div
          v-if="!loadingProyectosPagos && hasProyectosPagos"
          class="mt-2 text-[11px] md:text-xs max-h-28 overflow-y-auto space-y-2 pr-1"
        >
          <div
            v-for="proj in proyectosPagos.detalles"
            :key="proj.id_proyecto"
            class="border-b border-dashed pb-1 last:border-b-0"
          >
            <div class="flex justify-between gap-2">
              <span class="font-semibold truncate">
                {{ proj.nombre || ('Proyecto #' + proj.id_proyecto) }}
              </span>
              <span>
                Cuotas: {{ proj.total_cuotas }}
              </span>
            </div>
            <div class="flex justify-between gap-2">
              <span>
                Precio total: Bs {{ proj.precio_total.toFixed(2) }}
              </span>
              <span>
                Pagado: Bs {{ proj.monto_pagado.toFixed(2) }}
              </span>
            </div>
            <div class="mt-1">
              <span class="font-semibold">Cuotas:</span>
              <span
                v-for="c in proj.cuotas"
                :key="c.numero_cuota"
                class="block"
              >
                #{{ c.numero_cuota }} · Bs {{ c.monto_cuota.toFixed(2) }} ·
                {{ c.fecha_vencimiento }} ·
                <span :class="c.estado_cuota ? 'text-green-600' : 'text-red-600'">
                  {{ c.estado_cuota ? 'Pagada' : 'Pendiente' }}
                </span>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue'
import axios from 'axios'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const props = defineProps({
  darkMode: {
    type: Boolean,
    default: false
  }
})

const isDark = computed(() => props.darkMode)

// canvases
const personasCanvas = ref(null)
const inventarioCanvas = ref(null)
const pagosCanvas = ref(null)
const cuotasCanvas = ref(null)
const proyectosCanvas = ref(null)
const proyectosPagosCanvas = ref(null)

// chart instances
let personasChart = null
let inventarioChart = null
let pagosChart = null
let cuotasChart = null
let proyectosChart = null
let proyectosPagosChart = null

// data state
const personas = ref({ labels: [], cantidades: [], porcentajes: [], total: 0 })
const inventario = ref({ labels: [], entradas: [], salidas: [] })
const pagos = ref({ labels: [], montos: [], total_monto: 0 })
const cuotas = ref({
  labels: [],
  cantidades: [],
  montos: [],
  total_cuotas: 0,
  total_monto: 0
})
const proyectos = ref({ labels: [], dias_restantes: [], detalles: [] })
const proyectosPagos = ref({
  labels: [],
  cuotas_totales: [],
  precios_totales: [],
  montos_pagados: [],
  detalles: []
})

// loading / hasData
const loadingPersonas = ref(false)
const hasPersonas = ref(true)

const loadingInventario = ref(false)
const hasInventario = ref(true)

const loadingPagos = ref(false)
const hasPagos = ref(true)

const loadingCuotas = ref(false)
const hasCuotas = ref(true)

const loadingProyectos = ref(false)
const hasProyectos = ref(true)

const loadingProyectosPagos = ref(false)
const hasProyectosPagos = ref(true)

// axis color depending dark
const axisColor = () => (isDark.value ? '#E5E7EB' : '#111827')

// ===== charts builders =====
function buildPersonasChart () {
  if (!personasCanvas.value) return
  if (!personas.value.labels.length || personas.value.cantidades.every(v => v === 0)) {
    hasPersonas.value = false
    if (personasChart) { personasChart.destroy(); personasChart = null }
    return
  }
  hasPersonas.value = true
  if (personasChart) personasChart.destroy()

  personasChart = new Chart(personasCanvas.value, {
    type: 'doughnut',
    data: {
      labels: personas.value.labels,
      datasets: [
        {
          data: personas.value.cantidades,
          backgroundColor: ['#2563EB', '#16A34A', '#F97316']
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: { color: axisColor() }
        }
      }
    }
  })
}

function buildInventarioChart () {
  if (!inventarioCanvas.value) return
  if (!inventario.value.labels.length) {
    hasInventario.value = false
    if (inventarioChart) { inventarioChart.destroy(); inventarioChart = null }
    return
  }
  hasInventario.value = true
  if (inventarioChart) inventarioChart.destroy()

  inventarioChart = new Chart(inventarioCanvas.value, {
    type: 'bar',
    data: {
      labels: inventario.value.labels,
      datasets: [
        { label: 'Entradas', data: inventario.value.entradas },
        { label: 'Salidas', data: inventario.value.salidas }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { labels: { color: axisColor() } }
      },
      scales: {
        x: { ticks: { color: axisColor() } },
        y: { ticks: { color: axisColor() } }
      }
    }
  })
}

function buildPagosChart () {
  if (!pagosCanvas.value) return
  if (!pagos.value.labels.length || pagos.value.montos.every(v => v === 0)) {
    hasPagos.value = false
    if (pagosChart) { pagosChart.destroy(); pagosChart = null }
    return
  }
  hasPagos.value = true
  if (pagosChart) pagosChart.destroy()

  pagosChart = new Chart(pagosCanvas.value, {
    type: 'bar',
    data: {
      labels: pagos.value.labels,
      datasets: [
        {
          label: 'Monto pendiente',
          data: pagos.value.montos
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { labels: { color: axisColor() } }
      },
      scales: {
        x: { ticks: { color: axisColor() } },
        y: { ticks: { color: axisColor() } }
      }
    }
  })
}

function buildCuotasChart () {
  if (!cuotasCanvas.value) return
  if (!cuotas.value.labels.length || cuotas.value.cantidades.every(v => v === 0)) {
    hasCuotas.value = false
    if (cuotasChart) { cuotasChart.destroy(); cuotasChart = null }
    return
  }
  hasCuotas.value = true
  if (cuotasChart) cuotasChart.destroy()

  cuotasChart = new Chart(cuotasCanvas.value, {
    type: 'bar',
    data: {
      labels: cuotas.value.labels,
      datasets: [
        {
          label: 'Nº de cuotas',
          data: cuotas.value.cantidades
        },
        {
          label: 'Monto total (Bs)',
          data: cuotas.value.montos
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { labels: { color: axisColor() } }
      },
      scales: {
        x: { ticks: { color: axisColor() } },
        y: { ticks: { color: axisColor() } }
      }
    }
  })
}

function buildProyectosChart () {
  if (!proyectosCanvas.value) return
  if (!proyectos.value.labels.length) {
    hasProyectos.value = false
    if (proyectosChart) { proyectosChart.destroy(); proyectosChart = null }
    return
  }
  hasProyectos.value = true
  if (proyectosChart) proyectosChart.destroy()

  proyectosChart = new Chart(proyectosCanvas.value, {
    type: 'bar',
    data: {
      labels: proyectos.value.labels,
      datasets: [
        {
          label: 'Días restantes',
          data: proyectos.value.dias_restantes
        }
      ]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { labels: { color: axisColor() } }
      },
      scales: {
        x: { ticks: { color: axisColor() } },
        y: { ticks: { color: axisColor() } }
      }
    }
  })
}

function buildProyectosPagosChart () {
  if (!proyectosPagosCanvas.value) return
  if (!proyectosPagos.value.labels.length ||
      proyectosPagos.value.precios_totales.every(v => v === 0) &&
      proyectosPagos.value.montos_pagados.every(v => v === 0)) {
    hasProyectosPagos.value = false
    if (proyectosPagosChart) { proyectosPagosChart.destroy(); proyectosPagosChart = null }
    return
  }

  hasProyectosPagos.value = true
  if (proyectosPagosChart) proyectosPagosChart.destroy()

  proyectosPagosChart = new Chart(proyectosPagosCanvas.value, {
    type: 'bar',
    data: {
      labels: proyectosPagos.value.labels,
      datasets: [
        {
          label: 'Precio total (Bs)',
          data: proyectosPagos.value.precios_totales
        },
        {
          label: 'Monto pagado (Bs)',
          data: proyectosPagos.value.montos_pagados
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { labels: { color: axisColor() } }
      },
      scales: {
        x: { ticks: { color: axisColor() } },
        y: { ticks: { color: axisColor() } }
      }
    }
  })
}

// ===== loaders =====
async function loadPersonas () {
  loadingPersonas.value = true
  try {
    const { data } = await axios.get('propietario/Construccion/dashboard/resumen-personas')
    personas.value = data
    buildPersonasChart()
  } catch (e) {
    console.error('Error cargando resumen personas', e)
    hasPersonas.value = false
  } finally {
    loadingPersonas.value = false
  }
}

async function loadInventario () {
  loadingInventario.value = true
  try {
    const { data } = await axios.get('propietario/Construccion/dashboard/inventario-mensual')
    inventario.value = {
      labels: data.labels || [],
      entradas: data.entradas || [],
      salidas: data.salidas || []
    }
    buildInventarioChart()
  } catch (e) {
    console.error('Error cargando inventario mensual', e)
    hasInventario.value = false
  } finally {
    loadingInventario.value = false
  }
}

async function loadPagos () {
  loadingPagos.value = true
  try {
    const { data } = await axios.get('propietario/Construccion/dashboard/pagos-pendientes')
    pagos.value = {
      labels: data.labels || [],
      montos: data.montos || [],
      total_monto: data.total_monto || 0
    }
    buildPagosChart()
  } catch (e) {
    console.error('Error cargando pagos pendientes', e)
    hasPagos.value = false
  } finally {
    loadingPagos.value = false
  }
}

async function loadCuotas () {
  loadingCuotas.value = true
  try {
    const { data } = await axios.get('propietario/Construccion/dashboard/cuotas-pendientes')
    cuotas.value = {
      labels: data.labels || [],
      cantidades: data.cantidades || [],
      montos: data.montos || [],
      total_cuotas: data.total_cuotas || 0,
      total_monto: data.total_monto || 0
    }
    buildCuotasChart()
  } catch (e) {
    console.error('Error cargando cuotas pendientes', e)
    hasCuotas.value = false
  } finally {
    loadingCuotas.value = false
  }
}

async function loadProyectos () {
  loadingProyectos.value = true
  try {
    const { data } = await axios.get('propietario/Construccion/dashboard/proyectos-contador')
    proyectos.value = {
      labels: data.labels || [],
      dias_restantes: data.dias_restantes || [],
      detalles: data.detalles || []
    }
    buildProyectosChart()
  } catch (e) {
    console.error('Error cargando proyectos contador', e)
    hasProyectos.value = false
  } finally {
    loadingProyectos.value = false
  }
}

async function loadProyectosPagos () {
  loadingProyectosPagos.value = true
  try {
    const { data } = await axios.get('propietario/Construccion/dashboard/proyectos-pagos-clientes')
    proyectosPagos.value = {
      labels: data.labels || [],
      cuotas_totales: data.cuotas_totales || [],
      precios_totales: data.precios_totales || [],
      montos_pagados: data.montos_pagados || [],
      detalles: data.detalles || []
    }
    buildProyectosPagosChart()
  } catch (e) {
    console.error('Error cargando pagos de proyectos', e)
    hasProyectosPagos.value = false
  } finally {
    loadingProyectosPagos.value = false
  }
}

// redraw all when cambia darkMode
function redrawAll () {
  buildPersonasChart()
  buildInventarioChart()
  buildPagosChart()
  buildCuotasChart()
  buildProyectosChart()
  buildProyectosPagosChart()
}

onMounted(async () => {
  await Promise.all([
    loadPersonas(),
    loadInventario(),
    loadPagos(),
    loadCuotas(),
    loadProyectos(),
    loadProyectosPagos()
  ])
})

watch(
  () => isDark.value,
  () => {
    redrawAll()
  }
)
</script>
