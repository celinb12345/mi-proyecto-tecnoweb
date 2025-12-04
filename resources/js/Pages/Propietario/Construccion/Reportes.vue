<template>
  <div
    :class="[
      'p-8 rounded-2xl shadow-md transition-colors duration-300',
      darkMode ? 'bg-gray-900 text-gray-100' : 'bg-white text-gray-900',
      fontClass
    ]"
  >
    <!-- Título -->
    <div
      class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4"
    >
      <h2
        class="text-2xl font-bold"
        :class="darkMode ? 'text-blue-400' : 'text-blue-700'"
      >
        Reporte de pagos
      </h2>

      <div class="flex gap-2">
        <button
          type="button"
          class="px-4 py-2 rounded-lg text-sm font-semibold border"
          :class="
            darkMode
              ? 'border-gray-600 text-gray-100 hover:bg-gray-800'
              : 'border-gray-300 text-gray-800 hover:bg-gray-100'
          "
          @click="limpiarFiltros"
        >
          Limpiar filtros
        </button>
      </div>
    </div>

    <!-- BUSCADOR RÁPIDO -->
    <input
      type="text"
      v-model="buscar"
      placeholder="🔍 Buscar en los pagos (concepto, tipo, método, comprobante)..."
      class="input mb-4"
      :class="inputClass"
    />

    <!-- FORMULARIO FILTROS (se envía al backend) -->
    <form
      class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6"
      @submit.prevent="cargarReportes"
    >
      <!-- Sujeto -->
      <div>
        <label class="block text-xs font-semibold mb-1">Sujeto</label>
        <select
          v-model="filtros.sujeto"
          class="input text-sm"
          :class="inputClass"
        >
          <option value="todos">Todos</option>
          <option value="proveedor">Proveedores</option>
          <option value="trabajador">Trabajadores</option>
        </select>
      </div>

      <!-- Método de pago -->
      <div>
        <label class="block text-xs font-semibold mb-1">Método</label>
        <select
          v-model="filtros.metodo"
          class="input text-sm"
          :class="inputClass"
        >
          <option value="">Todos</option>
          <option value="efectivo">Efectivo</option>
          <option value="qr">QR</option>
        </select>
      </div>

      <!-- Forma de pago -->
      <div>
        <label class="block text-xs font-semibold mb-1">Forma</label>
        <select
          v-model="filtros.forma"
          class="input text-sm"
          :class="inputClass"
        >
          <option value="">Todas</option>
          <option value="directo">Directo</option>
          <option value="cuotas">En cuotas</option>
        </select>
      </div>

      <!-- Rango de fechas -->
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-xs font-semibold mb-1">Desde</label>
          <input
            type="date"
            v-model="filtros.desde"
            class="input text-sm"
            :class="inputClass"
          />
        </div>
        <div>
          <label class="block text-xs font-semibold mb-1">Hasta</label>
          <input
            type="date"
            v-model="filtros.hasta"
            class="input text-sm"
            :class="inputClass"
          />
        </div>
      </div>

      <!-- Botón aplicar -->
      <div class="md:col-span-4 flex justify-end">
        <button
          type="submit"
          class="px-4 py-2 rounded-lg text-sm font-semibold text-white"
          :class="
            darkMode
              ? 'bg-green-600 hover:bg-green-500'
              : 'bg-green-600 hover:bg-green-700'
          "
        >
          🔄 Aplicar filtros
        </button>
      </div>
    </form>

    <!-- 🟦 CUADRO DE ACCIONES (NO SE IMPRIME/PDF) -->
    <div class="flex justify-end mb-4">
      <div
        class="px-4 py-3 rounded-xl flex flex-wrap items-center gap-3 text-xs md:text-sm"
        :class="darkMode ? 'bg-gray-800' : 'bg-gray-100'"
      >
        <span :class="darkMode ? 'text-gray-200' : 'text-gray-700'">
          Acciones del reporte:
        </span>

        <button
          type="button"
          class="px-3 py-1 rounded-lg font-semibold text-white text-xs md:text-sm"
          :class="
            darkMode
              ? 'bg-blue-600 hover:bg-blue-500'
              : 'bg-blue-600 hover:bg-blue-700'
          "
          @click="imprimir"
        >
          🖨 Imprimir
        </button>

        <button
          type="button"
          class="px-3 py-1 rounded-lg font-semibold text-white text-xs md:text-sm"
          :class="
            darkMode
              ? 'bg-indigo-600 hover:bg-indigo-500'
              : 'bg-indigo-600 hover:bg-indigo-700'
          "
          @click="descargarPdf"
        >
          📄 Descargar PDF
        </button>
      </div>
    </div>

    <!-- 🔽 ZONA IMPRIMIBLE / PDF 🔽 -->
    <div ref="printArea" id="reporte-pagos-area" class="space-y-4">
      <!-- RESUMEN -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs md:text-sm">
        <div
          class="p-3 rounded-xl"
          :class="darkMode ? 'bg-gray-800' : 'bg-gray-100'"
        >
          <h4 class="font-semibold mb-1">Totales generales</h4>
          <p>Total general: <b>{{ money(resumen.total_general) }}</b></p>
          <p>Proveedores: <b>{{ money(resumen.total_proveedores) }}</b></p>
          <p>Trabajadores: <b>{{ money(resumen.total_trabajadores) }}</b></p>
        </div>

        <div
          class="p-3 rounded-xl"
          :class="darkMode ? 'bg-gray-800' : 'bg-gray-100'"
        >
          <h4 class="font-semibold mb-1">Por método</h4>
          <p>Efectivo: <b>{{ money(resumen.total_efectivo) }}</b></p>
          <p>QR: <b>{{ money(resumen.total_qr) }}</b></p>
        </div>

        <div
          class="p-3 rounded-xl"
          :class="darkMode ? 'bg-gray-800' : 'bg-gray-100'"
        >
          <h4 class="font-semibold mb-1">Por forma</h4>
          <p>Directo: <b>{{ money(resumen.total_directo) }}</b></p>
          <p>En cuotas: <b>{{ money(resumen.total_en_cuotas) }}</b></p>
        </div>
      </div>

      <!-- TABLA DE PAGOS (SIN PAGINACIÓN) -->
      <div class="mt-4 overflow-x-auto">
        <table
          class="w-full text-xs md:text-sm rounded-lg overflow-hidden"
          :class="darkMode ? 'border border-gray-700' : 'border border-gray-300'"
        >
          <thead
            :class="darkMode ? 'bg-blue-700 text-white' : 'bg-blue-600 text-white'"
          >
            <tr>
              <th class="border p-2" :class="darkBorder">Fecha</th>
              <th class="border p-2" :class="darkBorder">Tipo</th>
              <th class="border p-2" :class="darkBorder">Método</th>
              <th class="border p-2" :class="darkBorder">Forma</th>
              <th class="border p-2" :class="darkBorder">Concepto</th>
              <th class="border p-2" :class="darkBorder">Comprobante</th>
              <th class="border p-2 text-right" :class="darkBorder">Monto</th>
              <th class="border p-2 text-center" :class="darkBorder">Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="p in filteredPagos"
              :key="p.id_pago"
              :class="darkMode ? 'hover:bg-gray-800' : 'hover:bg-gray-50'"
            >
              <td class="border p-2" :class="darkBorder">
                {{ p.fecha_pago }}
              </td>
              <td class="border p-2 capitalize" :class="darkBorder">
                {{ p.tipo_pago }}
              </td>
              <td class="border p-2 capitalize" :class="darkBorder">
                {{ (p.metodo_pago || '').toLowerCase() || '—' }}
              </td>
              <td class="border p-2" :class="darkBorder">
                <span v-if="(p.cuotas_pago || 0) > 0">
                  En cuotas ({{ p.cuotas_pago }})
                </span>
                <span v-else>Directo</span>
              </td>
              <td class="border p-2" :class="darkBorder">
                {{ p.concepto_pago || '—' }}
              </td>
              <td class="border p-2 text-center" :class="darkBorder">
                {{ p.numero_comprobante || '—' }}
              </td>
              <td class="border p-2 text-right" :class="darkBorder">
                {{ money(p.monto_total_pago) }}
              </td>
              <td class="border p-2 text-center" :class="darkBorder">
                <span
                  class="px-2 py-1 rounded-full text-[11px] font-semibold"
                  :class="
                    p.estado_pago
                      ? darkMode
                        ? 'bg-green-900 text-green-300'
                        : 'bg-green-100 text-green-700'
                      : darkMode
                        ? 'bg-yellow-900 text-yellow-300'
                        : 'bg-yellow-100 text-yellow-700'
                  "
                >
                  {{ p.estado_pago ? 'Pagado' : 'Pendiente' }}
                </span>
              </td>
            </tr>

            <tr v-if="!filteredPagos.length">
              <td
                colspan="8"
                class="text-center py-3"
                :class="darkMode ? 'text-gray-300' : 'text-gray-500'"
              >
                No hay pagos con los filtros actuales
              </td>
            </tr>
          </tbody>

          <!-- TOTAL PAGADO (todos los filtrados, solo estado=true) -->
          <tfoot v-if="filteredPagos.length">
            <tr
              :class="darkMode ? 'bg-gray-800 text-gray-100' : 'bg-gray-100 text-gray-800'"
            >
              <td
                class="border p-2 text-right font-semibold"
                :class="darkBorder"
                colspan="6"
              >
                Total pagado:
              </td>
              <td
                class="border p-2 text-right font-bold"
                :class="darkBorder"
              >
                {{ money(totalPagado) }}
              </td>
              <td class="border p-2" :class="darkBorder"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    <!-- 🔼 FIN ZONA IMPRIMIBLE / PDF 🔼 -->
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import { usePage } from '@inertiajs/vue3'
import html2canvas from 'html2canvas'
import jsPDF from 'jspdf'

const props = defineProps({
  darkMode: { type: Boolean, default: false },
  fontClass: { type: String, default: 'font-sans' },
  pagos: { type: Array, default: () => [] },
  resumen: { type: Object, default: () => ({}) },
  filtrosIniciales: {
    type: Object,
    default: () => ({
      sujeto: 'todos',
      metodo: '',
      forma: '',
      desde: null,
      hasta: null
    })
  }
})

const page = usePage()
const reportesUrl = page?.url?.startsWith(
  'propietario/Construccion/reportes/pagos'
)
  ? page.url
  : 'propietario/Construccion/reportes/pagos'

const darkMode = computed(() => props.darkMode)
const fontClass = computed(() => props.fontClass || 'font-sans')

const inputClass = computed(() =>
  darkMode.value
    ? 'bg-gray-900 border-gray-700 text-gray-100 placeholder-gray-400'
    : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400'
)

const darkBorder = computed(() =>
  darkMode.value ? 'border border-gray-700' : 'border border-gray-300'
)

// estado principal
const pagos = ref(props.pagos || [])
const resumen = ref({
  total_general: 0,
  total_proveedores: 0,
  total_trabajadores: 0,
  total_efectivo: 0,
  total_qr: 0,
  total_directo: 0,
  total_en_cuotas: 0,
  ...props.resumen
})

const filtros = ref({
  sujeto: props.filtrosIniciales.sujeto ?? 'todos',
  metodo: props.filtrosIniciales.metodo ?? '',
  forma: props.filtrosIniciales.forma ?? '',
  desde: props.filtrosIniciales.desde ?? null,
  hasta: props.filtrosIniciales.hasta ?? null
})

const buscar = ref('')
const cargando = ref(false)

// zona imprimible
const printArea = ref(null)

// helper formato moneda
const money = n =>
  Number(n || 0).toLocaleString('es-BO', {
    style: 'currency',
    currency: 'BOB'
  })

async function cargarReportes () {
  try {
    cargando.value = true

    const { data } = await axios.get(reportesUrl, {
      params: {
        sujeto: filtros.value.sujeto,
        metodo: filtros.value.metodo || undefined,
        forma: filtros.value.forma || undefined,
        desde: filtros.value.desde || undefined,
        hasta: filtros.value.hasta || undefined
      }
    })

    pagos.value = data.pagos || []
    resumen.value = { ...resumen.value, ...(data.resumen || {}) }
  } catch (e) {
    console.error(e)
    alert('Error al cargar el reporte de pagos.')
  } finally {
    cargando.value = false
  }
}

// filtro rápido en el front
const filteredPagos = computed(() => {
  const term = buscar.value.toLowerCase().trim()
  if (!term) return pagos.value

  return pagos.value.filter(p => {
    const concepto = (p.concepto_pago || '').toLowerCase()
    const metodo = (p.metodo_pago || '').toLowerCase()
    const tipo = (p.tipo_pago || '').toLowerCase()
    const comp = String(p.numero_comprobante || '')

    return (
      concepto.includes(term) ||
      metodo.includes(term) ||
      tipo.includes(term) ||
      comp.includes(term)
    )
  })
})

// total pagado de TODOS los filtrados (solo estado_pago = true)
const totalPagado = computed(() =>
  filteredPagos.value.reduce((sum, p) => {
    if (p.estado_pago) {
      return sum + Number(p.monto_total_pago || 0)
    }
    return sum
  }, 0)
)

function limpiarFiltros () {
  filtros.value = {
    sujeto: 'todos',
    metodo: '',
    forma: '',
    desde: null,
    hasta: null
  }
  cargarReportes()
}

/* ========= IMPRESIÓN SOLO DE ESA ZONA ========= */
function imprimir () {
  if (!printArea.value) return

  const printContents = printArea.value.innerHTML
  const ventana = window.open('', '_blank', 'width=900,height=650')

  ventana.document.write(`
    <!doctype html>
    <html>
      <head>
        <title>Reporte de pagos</title>
        <style>
          body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont,
              'Segoe UI', sans-serif;
            padding: 16px;
          }
          table {
            border-collapse: collapse;
            width: 100%;
            font-size: 12px;
          }
          th, td {
            border: 1px solid #ccc;
            padding: 4px 6px;
          }
          th {
            background: #2563eb;
            color: #fff;
          }
          tfoot td {
            font-weight: bold;
          }
        </style>
      </head>
      <body>
        ${printContents}
      </body>
    </html>
  `)

  ventana.document.close()
  ventana.focus()
  ventana.print()
  ventana.close()
}

/* ========= DESCARGAR PDF DE ESA ZONA ========= */
async function descargarPdf () {
  if (!printArea.value) return

  try {
    const canvas = await html2canvas(printArea.value, { scale: 2 })
    const imgData = canvas.toDataURL('image/png')

    const pdf = new jsPDF('p', 'mm', 'a4')
    const pdfWidth = pdf.internal.pageSize.getWidth()
    const pdfHeight = (canvas.height * pdfWidth) / canvas.width

    pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight)
    pdf.save('reporte-pagos.pdf')
  } catch (e) {
    console.error(e)
    alert('Error al generar el PDF del reporte.')
  }
}
</script>

<style scoped>
.input {
  @apply border rounded-lg p-2 w-full focus:ring-2 focus:ring-blue-400 focus:outline-none transition-colors duration-200;
}
</style>
