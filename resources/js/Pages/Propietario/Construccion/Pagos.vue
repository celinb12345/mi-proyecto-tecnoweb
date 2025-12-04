<template>
  <div
    :class="[
      'p-6 rounded-2xl shadow-md transition-colors duration-300',
      props.darkMode ? 'bg-gray-800 text-gray-100' : 'bg-white text-gray-900',
      props.fontClass
    ]"
  >
    <h2 class="text-2xl font-bold text-blue-500 mb-4">Gestión de Pagos</h2>

    <!-- Filtros -->
    <div class="flex gap-2 mb-6 flex-wrap">
      <input
        type="date"
        v-model="filtro.desde"
        :class="[
          inputBaseClass,
          props.darkMode
            ? 'bg-gray-900 border-gray-600 text-gray-100'
            : 'bg-white border-gray-300 text-gray-900'
        ]"
      />
      <input
        type="date"
        v-model="filtro.hasta"
        :class="[
          inputBaseClass,
          props.darkMode
            ? 'bg-gray-900 border-gray-600 text-gray-100'
            : 'bg-white border-gray-300 text-gray-900'
        ]"
      />
      <button @click="buscarPagos" class="btn-primary">Buscar</button>
      <button @click="listarPagos" class="btn-gray">Listar Todo</button>

      <button @click="toggleCuotasPendientes" class="btn-warning">
        {{ showCuotasPendientes ? 'Ocultar cuotas pendientes' : 'Cuotas pendientes' }}
      </button>
    </div>

    <!-- CUOTAS PENDIENTES -->
    <div v-if="showCuotasPendientes" class="mb-8">
      <h3 class="font-semibold mb-2">Cuotas Pendientes</h3>

      <table
        class="w-full text-sm border"
        :class="props.darkMode ? 'border-gray-700' : 'border-gray-300'"
      >
        <thead :class="props.darkMode ? 'bg-yellow-600 text-white' : 'bg-yellow-100'">
          <tr>
            <th class="p-2 border">#</th>
            <th class="p-2 border">Destino</th>
            <th class="p-2 border">Tipo</th>
            <th class="p-2 border">Método</th>
            <th class="p-2 border">N° cuota</th>
            <th class="p-2 border">Monto</th>
            <th class="p-2 border">Vencimiento</th>
            <th class="p-2 border">Concepto</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="c in cuotasPendientes"
            :key="c.id_cuota"
            @click="seleccionarCuota(c)"
            :class="[
              'cursor-pointer',
              cuotaSeleccionada && cuotaSeleccionada.id_cuota === c.id_cuota
                ? (props.darkMode ? 'bg-yellow-900 text-white' : 'bg-yellow-100')
                : (props.darkMode
                    ? 'border-b border-gray-700 hover:bg-gray-700'
                    : 'border-b border-gray-200 hover:bg-gray-50')
            ]"
          >
            <td class="p-2 border">{{ c.id_cuota }}</td>
            <td class="p-2 border">{{ c.destino }}</td>
            <td class="p-2 border">{{ c.tipo_pago }}</td>
            <td class="p-2 border">{{ c.metodo_pago }}</td>
            <td class="p-2 border">{{ c.numero_cuota }}</td>
            <td class="p-2 border">{{ currency(c.monto_cuota) }}</td>
            <td class="p-2 border">{{ c.fecha_vencimiento }}</td>
            <td class="p-2 border">{{ c.concepto_pago || '-' }}</td>
          </tr>

          <tr v-if="!cuotasPendientes.length">
            <td
              colspan="8"
              class="text-center py-3"
              :class="props.darkMode ? 'text-gray-300' : 'text-gray-500'"
            >
              No hay cuotas pendientes
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- FORMULARIO -->
    <form @submit.prevent="registrarPago" class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-semibold mb-1">Tipo de Pago</label>
        <select
          v-model="pago.tipo_pago"
          class="w-full"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-900'
          ]"
          @change="onTipoChange"
        >
          <option disabled value="">Seleccione</option>
          <option value="trabajador">Trabajador</option>
          <option value="proveedor">Proveedor</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Método de Pago</label>
        <select
          v-model="pago.metodo_pago"
          class="w-full"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-900'
          ]"
        >
          <option disabled value="">Seleccione</option>
          <option value="efectivo">Efectivo</option>
          <option value="qr">QR</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Número Comprobante</label>
        <input
          type="number"
          v-model.number="pago.numero_comprobante"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100 placeholder-gray-400'
              : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400'
          ]"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Estado</label>
        <select
          v-model="pago.estado_pago"
          class="w-full"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-900'
          ]"
        >
          <option value="Pagado">Pagado</option>
          <option value="Pendiente">Pendiente</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">
          Monto total / Monto del pago
        </label>
        <input
          type="number"
          min="0"
          step="0.01"
          inputmode="decimal"
          v-model.number="pago.monto_total_pago"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100 placeholder-gray-400'
              : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400'
          ]"
        />
        <p
          v-if="pago.metodo_pago === 'qr' && enCuotas === 'si' && planCuotas.length"
          class="text-xs mt-1"
          :class="props.darkMode ? 'text-gray-300' : 'text-gray-500'"
        >
          * Para QR en cuotas se cobrará el monto de la
          <b>primera cuota ({{ currency(planCuotas[0].monto) }})</b>.
        </p>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Concepto</label>
        <input
          type="text"
          v-model="pago.concepto_pago"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100 placeholder-gray-400'
              : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400'
          ]"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Fecha Pago</label>
        <input
          type="date"
          v-model="pago.fecha_pago"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-900'
          ]"
        />
      </div>

      <!-- TRABAJADOR -->
      <div v-if="pago.tipo_pago === 'trabajador'">
        <label class="block text-sm font-semibold mb-1">Trabajador</label>
        <select
          v-model="pago.id_trabajador"
          class="w-full"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-900'
          ]"
          @change="onTrabajadorChange"
        >
          <option disabled value="">Seleccione</option>
          <option
            v-for="t in trabajadores"
            :key="t.id_trabajador"
            :value="t.id_trabajador"
          >
            {{ t.nombre_trabajador }} {{ t.apellido_trabajador }}
          </option>
        </select>
        <p
          v-if="sueldoDia"
          class="text-sm mt-1"
          :class="props.darkMode ? 'text-gray-300' : 'text-gray-500'"
        >
          Sueldo por día: <b>{{ currency(sueldoDia) }}</b>
        </p>
      </div>

      <!-- PROVEEDOR -->
      <div v-if="pago.tipo_pago === 'proveedor'">
        <label class="block text-sm font-semibold mb-1">Proveedor</label>
        <select
          v-model="pago.id_proveedor"
          class="w-full"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-900'
          ]"
        >
          <option disabled value="">Seleccione</option>
          <option
            v-for="p in proveedores"
            :key="p.id_proveedor"
            :value="p.id_proveedor"
          >
            {{ p.nombre_empres_prov }}
          </option>
        </select>
      </div>

      <!-- DÍAS TRABAJADOS (SEMANA)  -->
      <div
              v-if="pago.tipo_pago === 'trabajador'"
                 class="col-span-2 mt-2"
                 >

        <label class="block mb-1 font-semibold">Días trabajados (semana)</label>

        <div class="flex items-center gap-3 mb-3 flex-wrap">
          <span
            class="text-sm w-44"
            :class="props.darkMode ? 'text-gray-300' : 'text-gray-600'"
          >
            Semana que inicia:
          </span>
          <input
            type="date"
            v-model="semanaInicio"
            class="w-48"
            :class="[
              inputBaseClass,
              props.darkMode
                ? 'bg-gray-900 border-gray-600 text-gray-100'
                : 'bg-white border-gray-300 text-gray-900'
            ]"
          />
          <button @click="armarSemana" type="button" class="btn-gray">
            Armar Semana
          </button>
        </div>

        <div class="flex flex-wrap gap-3">
          <button
            v-for="(d, i) in diasSemana"
            :key="i"
            type="button"
            :class="[
              'px-4 py-2 rounded-lg border text-sm font-medium',
              d.sel
                ? 'bg-green-500 text-white border-green-600'
                : props.darkMode
                  ? 'bg-gray-700 text-gray-100 border-gray-600'
                  : 'bg-red-100 text-gray-800 border-red-200'
            ]"
            @click="toggleDia(i)"
          >
            {{ d.label }}
          </button>
        </div>

        <div class="flex items-center gap-3 mt-3 flex-wrap">
          <span
            class="text-sm"
            :class="props.darkMode ? 'text-gray-300' : 'text-gray-600'"
          >
            Seleccionados: <b>{{ diasSeleccionados }}</b>
          </span>
          <button
            type="button"
            class="btn-primary"
            @click="usarMontoSugerido"
          >
            Usar monto sugerido
            ({{ currency(sueldoDia * diasSeleccionados) }})
          </button>
        </div>
      </div>

      <!-- Cuotas -->
      <div>
        <label class="block text-sm font-semibold mb-1">¿En Cuotas?</label>
        <select
          v-model="enCuotas"
          :disabled="enCuotasBloqueado"
          class="w-full"
          :class="[
            inputBaseClass,
            enCuotasBloqueado ? 'bg-gray-200 cursor-not-allowed' : '',
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-900'
          ]"
        >
          <option value="no">Pago directo</option>
          <option value="si">En cuotas</option>
        </select>
      </div>

      <div v-if="enCuotas === 'si'" class="col-span-2">
        <fieldset
          :class="[
            'border rounded-xl p-4',
            props.darkMode ? 'border-gray-600' : 'border-gray-300'
          ]"
        >
          <legend
            class="px-2 text-sm"
            :class="props.darkMode ? 'text-gray-300' : 'text-gray-600'"
          >
            Plan de Cuotas
          </legend>

          <div class="grid grid-cols-3 gap-4 mb-3">
            <div>
              <label class="block text-sm font-semibold mb-1">
                Número de cuotas
              </label>
              <input
                type="number"
                min="1"
                v-model.number="cuotas.cantidad"
                :class="[
                  inputBaseClass,
                  props.darkMode
                    ? 'bg-gray-900 border-gray-600 text-gray-100'
                    : 'bg-white border-gray-300 text-gray-900'
                ]"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold mb-1">
                Fecha 1ª Cuota
              </label>
              <input
                type="date"
                v-model="cuotas.primerVenc"
                :class="[
                  inputBaseClass,
                  props.darkMode
                    ? 'bg-gray-900 border-gray-600 text-gray-100'
                    : 'bg-white border-gray-300 text-gray-900'
                ]"
              />
            </div>
            <div class="flex items-end">
              <button
                class="btn-gray w-full"
                type="button"
                @click="generarPlan"
              >
                Generar
              </button>
            </div>
          </div>

          <table
            v-if="planCuotas.length"
            class="w-full text-sm border mt-2"
            :class="props.darkMode ? 'border-gray-700' : 'border-gray-300'"
          >
            <thead :class="props.darkMode ? 'bg-gray-700 text-white' : 'bg-gray-100'">
              <tr>
                <th class="p-2 border">#</th>
                <th class="p-2 border">Monto</th>
                <th class="p-2 border">Fecha</th>
                <th class="p-2 border">Estado</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(c, i) in planCuotas"
                :key="i"
                :class="props.darkMode
                  ? 'border-b border-gray-700'
                  : 'border-b border-gray-200'"
              >
                <td class="p-2 border">{{ c.numero }}</td>
                <td class="p-2 border">{{ currency(c.monto) }}</td>
                <td class="p-2 border">
                  <span v-if="i === 0">
                    {{ c.fecha }}
                  </span>
                  <input
                    v-else
                    type="date"
                    v-model="c.fecha"
                    :class="[
                      inputBaseClass,
                      'text-xs',
                      props.darkMode
                        ? 'bg-gray-900 border-gray-600 text-gray-100'
                        : 'bg-white border-gray-300 text-gray-900'
                    ]"
                  />
                </td>
                <td class="p-2 border">{{ c.estado }}</td>
              </tr>
            </tbody>
          </table>
        </fieldset>
      </div>

      <!-- BOTON -->
      <div class="col-span-2 flex justify-end mt-4">
        <button type="submit" class="btn-success">Registrar Pago</button>
      </div>
    </form>

    <!-- QR (desde trabajador/proveedor) -->
    <div v-if="qrImage" class="mt-10 text-center">
      <h3 class="text-lg font-semibold mb-2">Código QR del destino seleccionado</h3>
      <img
        :src="qrImage"
        class="w-64 mx-auto shadow-lg rounded-xl p-3 bg-white"
        alt="QR"
      />
    </div>

    <!-- LISTADO -->
    <div class="mt-10">
      <h3 class="font-semibold mb-2">Pagos Registrados</h3>

      <table
        class="w-full text-sm border"
        :class="props.darkMode ? 'border-gray-700' : 'border-gray-300'"
      >
        <thead :class="props.darkMode ? 'bg-blue-600 text-white' : 'bg-gray-100'">
          <tr>
            <th class="p-2 border">#</th>
            <th class="p-2 border">Tipo</th>
            <th class="p-2 border">Método</th>
            <th class="p-2 border">Estado</th>
            <th class="p-2 border">Fecha</th>
            <th class="p-2 border">Monto</th>
            <th class="p-2 border">Destino</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="p in paginatedPagos"
            :key="p.id_pago"
            :class="props.darkMode
              ? 'border-b border-gray-700 hover:bg-gray-700'
              : 'border-b border-gray-200 hover:bg-gray-50'"
          >
            <td class="p-2 border">{{ p.id_pago }}</td>
            <td class="p-2 border">{{ p.tipo_pago }}</td>
            <td class="p-2 border">{{ p.metodo_pago }}</td>
            <td class="p-2 border">
              {{ p.estado_pago ? 'Pagado' : 'Pendiente' }}
            </td>
            <td class="p-2 border">{{ p.fecha_pago }}</td>
            <td class="p-2 border">{{ currency(p.monto_total_pago) }}</td>
            <td class="p-2 border">
              <span v-if="p.trabajador">{{ p.trabajador.nombre_trabajador }}</span>
              <span v-else-if="p.proveedor">{{ p.proveedor.nombre_empres_prov }}</span>
              <span v-else>-</span>
            </td>
          </tr>

          <tr v-if="!pagosTrabProv.length">
            <td
              colspan="7"
              class="text-center py-3"
              :class="props.darkMode ? 'text-gray-300' : 'text-gray-500'"
            >
              No hay pagos registrados de trabajadores o proveedores
            </td>
          </tr>
        </tbody>
      </table>

      <!-- PAGINACIÓN -->
      <div
        v-if="totalPages > 1"
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mt-3 text-xs md:text-sm"
      >
        <div>
          Mostrando
          <b>{{ pageInfo.from }}</b>
          -
          <b>{{ pageInfo.to }}</b>
          de
          <b>{{ pagosTrabProv.length }}</b>
          pagos
        </div>

        <div class="flex flex-wrap items-center gap-1">
          <button
            class="px-2 py-1 rounded border"
            :class="props.darkMode
              ? 'bg-gray-800 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-700'"
            @click="prevPage"
            :disabled="currentPage === 1"
          >
            ⬅
          </button>

          <button
            v-for="page in totalPages"
            :key="page"
            class="px-2 py-1 rounded border text-xs"
            :class="[
              page === currentPage
                ? 'bg-blue-600 text-white border-blue-500'
                : (props.darkMode
                    ? 'bg-gray-800 text-gray-100 border-gray-600 hover:bg-gray-700'
                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100')
            ]"
            @click="goToPage(page)"
          >
            {{ page }}
          </button>

          <button
            class="px-2 py-1 rounded border"
            :class="props.darkMode
              ? 'bg-gray-800 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-700'"
            @click="nextPage"
            :disabled="currentPage === totalPages"
          >
            ➡
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'
const baseUrl = import.meta.env.VITE_APP_URL.replace(/\/$/, '')

// mismo helper que en Trabajadores.vue
const storageUrl = (path) => {
  if (!path) return null
  // si ya viene con http/https la dejamos igual
  if (path.startsWith('http')) return path

  const appUrl = baseUrl
  const projectRoot = appUrl.replace('/public', '')

  // aquí es donde realmente están tus archivos en el server
  return `${projectRoot}/storage/app/public/${path}`
}


const props = defineProps({
  darkMode: { type: Boolean, default: false },
  fontClass: { type: String, default: 'font-sans' },
})

const inputBaseClass =
  'rounded-lg p-2 w-full focus:ring-2 focus:ring-blue-400 focus:outline-none transition-colors duration-200'

const filtro = ref({ desde: '', hasta: '' })

const pago = ref({
  tipo_pago: '',
  metodo_pago: '',
  estado_pago: 'Pendiente',
  monto_total_pago: 0,
  concepto_pago: '',
  fecha_pago: '',
  numero_comprobante: null,
  id_trabajador: '',
  id_proveedor: '',
})

const trabajadores = ref([])
const proveedores = ref([])
const pagos = ref([])

// cuotas pendientes
const cuotasPendientes = ref([])
const showCuotasPendientes = ref(false)
const cuotaSeleccionada = ref(null)

const sueldoDia = ref(0)

const semanaInicio = ref('')
const diasSemana = ref([])

const diasSeleccionados = computed(
  () => diasSemana.value.filter((d) => d.sel).length
)

function armarSemana() {
  if (!semanaInicio.value) return
  const base = new Date(semanaInicio.value)
  const labels = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']
  diasSemana.value = []

  for (let i = 0; i < 7; i++) {
    const d = new Date(base)
    d.setDate(base.getDate() + i)
    diasSemana.value.push({
      date: d.toISOString().slice(0, 10),
      label: labels[i] + ' ' + d.getDate(),
      sel: i < 5,
    })
  }
}

function toggleDia(i) {
  diasSemana.value[i].sel = !diasSemana.value[i].sel
}

function usarMontoSugerido() {
  pago.value.monto_total_pago = sueldoDia.value * diasSeleccionados.value
}

/* ===== Cuotas ===== */
const enCuotas = ref('no')
const enCuotasBloqueado = ref(false)
const cuotas = ref({ cantidad: 1, primerVenc: '' })
const planCuotas = ref([])

function generarPlan() {
  if (!pago.value.monto_total_pago || !cuotas.value.cantidad || !cuotas.value.primerVenc) {
    alert('Debe ingresar monto total, número de cuotas y la fecha de la primera cuota.')
    return
  }

  planCuotas.value = []
  const n = cuotas.value.cantidad || 1
  const base = pago.value.monto_total_pago / n
  const fechaBase = new Date(cuotas.value.primerVenc)

  for (let i = 1; i <= n; i++) {
    const f = new Date(fechaBase)
    f.setMonth(f.getMonth() + (i - 1))
    planCuotas.value.push({
      numero: i,
      monto: Number(base.toFixed(2)),
      fecha: f.toISOString().slice(0, 10),
      estado: 'Pendiente',
    })
  }

  if (planCuotas.value.length) {
    pago.value.monto_total_pago = planCuotas.value[0].monto
  }
}

/* ===== Carga de catálogos ===== */
async function onTipoChange() {
  sueldoDia.value = 0
  diasSemana.value = []

  if (pago.value.tipo_pago === 'trabajador') {
    const r = await axios.get('propietario/Construccion/pagos/trabajadores')
    trabajadores.value = r.data
    proveedores.value = []
    pago.value.id_proveedor = ''
  } else if (pago.value.tipo_pago === 'proveedor') {
    const r = await axios.get('propietario/Construccion/pagos/proveedores')
    proveedores.value = r.data
    trabajadores.value = []
    pago.value.id_trabajador = ''
  } else {
    trabajadores.value = []
    proveedores.value = []
  }

  actualizarQrDesdeDestino()
}

function onTrabajadorChange() {
  const t = trabajadores.value.find(
    (x) => x.id_trabajador == pago.value.id_trabajador
  )
  sueldoDia.value = t ? Number(t.sueldo_trabajador) : 0
}

function currency(n) {
  return Number(n || 0).toLocaleString('es-BO', {
    style: 'currency',
    currency: 'BOB',
  })
}

/* ===========================
   QR LOCAL (guardado)
=========================== */
const qrImage = ref(null)

function actualizarQrDesdeDestino() {
  if (pago.value.metodo_pago !== 'qr') {
    qrImage.value = null
    return
  }

  if (pago.value.tipo_pago === 'trabajador' && pago.value.id_trabajador) {
    const t = trabajadores.value.find(
      (x) => x.id_trabajador == pago.value.id_trabajador
    )

    qrImage.value = t && t.codigoqr_trab
      ? storageUrl(t.codigoqr_trab)      
      : null

  } else if (pago.value.tipo_pago === 'proveedor' && pago.value.id_proveedor) {
    const p = proveedores.value.find(
      (x) => x.id_proveedor == pago.value.id_proveedor
    )

    qrImage.value = p && p.codigoqr_prov
      ? storageUrl(p.codigoqr_prov)      
      : null

  } else {
    qrImage.value = null
  }
}


watch(
  () => pago.value.metodo_pago,
  () => {
    actualizarQrDesdeDestino()
  }
)

watch(
  () => pago.value.id_trabajador,
  () => {
    onTrabajadorChange()
    actualizarQrDesdeDestino()
  }
)

watch(
  () => pago.value.id_proveedor,
  () => {
    actualizarQrDesdeDestino()
  }
)

/* ===========================
   RESET FORMULARIO
=========================== */
function resetFormulario() {
  pago.value = {
    tipo_pago: '',
    metodo_pago: '',
    estado_pago: 'Pendiente',
    monto_total_pago: 0,
    concepto_pago: '',
    fecha_pago: '',
    numero_comprobante: null,
    id_trabajador: '',
    id_proveedor: '',
  }

  sueldoDia.value = 0
  semanaInicio.value = ''
  diasSemana.value = []

  enCuotas.value = 'no'
  enCuotasBloqueado.value = false
  cuotas.value = { cantidad: 1, primerVenc: '' }
  planCuotas.value = []
  cuotaSeleccionada.value = null
  qrImage.value = null
}

/* ===========================
   SELECCIONAR CUOTA PENDIENTE
=========================== */
async function seleccionarCuota(cuota) {
  cuotaSeleccionada.value = cuota

  // llenar formulario con datos de la cuota
  pago.value.tipo_pago = cuota.tipo_pago
  pago.value.metodo_pago = cuota.metodo_pago
  pago.value.estado_pago = 'Pagado'
  pago.value.monto_total_pago = Number(cuota.monto_cuota)
  pago.value.concepto_pago = cuota.concepto_pago || ''
  pago.value.fecha_pago = new Date().toISOString().slice(0, 10)

  // al pagar una cuota pendiente SIEMPRE es pago directo (no nuevo plan)
  enCuotas.value = 'no'
  enCuotasBloqueado.value = true
  planCuotas.value = []
  cuotas.value = { cantidad: 1, primerVenc: '' }

  const destino = String(cuota.destino || '').trim()

  if (cuota.tipo_pago === 'trabajador') {
    const r = await axios.get('propietario/Construccion/pagos/trabajadores')
    trabajadores.value = r.data
    proveedores.value = []
    pago.value.id_proveedor = ''

    const match = trabajadores.value.find(t =>
      (String(t.nombre_trabajador || '') + ' ' + String(t.apellido_trabajador || '')).trim() === destino
    )

    pago.value.id_trabajador = match ? match.id_trabajador : ''
    if (match) {
      sueldoDia.value = Number(match.sueldo_trabajador || 0)
    }
  } else if (cuota.tipo_pago === 'proveedor') {
    const r = await axios.get('propietario/Construccion/pagos/proveedores')
    proveedores.value = r.data
    trabajadores.value = []
    pago.value.id_trabajador = ''

    const match = proveedores.value.find(p =>
      String(p.nombre_empres_prov || '').trim() === destino
    )
    pago.value.id_proveedor = match ? match.id_proveedor : ''
  }

  actualizarQrDesdeDestino()
}

/* ===========================
   REGISTRAR PAGO
=========================== */
async function registrarPago() {
  try {
    if (enCuotas.value === 'si' && pago.value.metodo_pago !== 'qr' && planCuotas.value.length) {
      planCuotas.value[0].estado = 'Pagado'
    }

    const payload = {
      ...pago.value,
      detalle_dias:
        pago.value.tipo_pago === 'trabajador'
          ? diasSemana.value.filter((d) => d.sel).map((d) => d.date)
          : [],
      en_cuotas: enCuotas.value === 'si',
      plan: enCuotas.value === 'si' ? planCuotas.value : [],
      id_cuota_origen: cuotaSeleccionada.value
        ? cuotaSeleccionada.value.id_cuota
        : null,
    }

    if (
      payload.metodo_pago === 'qr' &&
      payload.en_cuotas &&
      planCuotas.value.length
    ) {
      payload.monto_total_pago = planCuotas.value[0].monto
    }

    const res = await axios.post('propietario/Construccion/pagos', payload)

    if (!res.data.ok) {
      alert(res.data.message || 'Ocurrió un error al registrar el pago.')
      return
    }

    alert(res.data.message || 'Pago registrado correctamente.')
    resetFormulario()
    await listarPagos()
    if (showCuotasPendientes.value) await cargarCuotasPendientes()
  } catch (err) {
    console.error(err)

    if (err.response && err.response.status === 422) {
      const data = err.response.data
      alert('Error al registrar pago: ' + JSON.stringify(data.errors))
    } else {
      alert('Error inesperado al registrar pago.')
    }
  }
}

/* ===========================
   LISTADO + FILTRO
=========================== */
async function listarPagos() {
  const r = await axios.get('propietario/Construccion/pagos')
  pagos.value = r.data.pagos || []
}

async function buscarPagos() {
  const params = new URLSearchParams(filtro.value).toString()
  const r = await axios.get(`propietario/Construccion/pagos?${params}`)
  pagos.value = r.data.pagos || []
}

/* ===========================
   CUOTAS PENDIENTES
=========================== */
async function cargarCuotasPendientes() {
  try {
    const r = await axios.get('propietario/Construccion/pagos/cuotas-pendientes')
    cuotasPendientes.value = r.data.cuotas || []
  } catch (e) {
    console.error(e)
    alert('No se pudieron cargar las cuotas pendientes.')
  }
}

async function toggleCuotasPendientes() {
  if (!showCuotasPendientes.value) {
    await cargarCuotasPendientes()
    showCuotasPendientes.value = true
  } else {
    showCuotasPendientes.value = false
    cuotaSeleccionada.value = null
    enCuotasBloqueado.value = false
  }
}

/* ===========================
   PAGOS: sólo trabajador / proveedor
=========================== */
const pagosTrabProv = computed(() =>
  pagos.value.filter(
    (p) => p.tipo_pago === 'trabajador' || p.tipo_pago === 'proveedor'
  )
)

/* ===== Pagos: paginación local ===== */
const currentPage = ref(1)
const pageSize = ref(5)

const totalPages = computed(() =>
  pagosTrabProv.value.length
    ? Math.ceil(pagosTrabProv.value.length / pageSize.value)
    : 1
)

const paginatedPagos = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return pagosTrabProv.value.slice(start, start + pageSize.value)
})

const pageInfo = computed(() => {
  if (!pagosTrabProv.value.length) return { from: 0, to: 0 }
  const from = (currentPage.value - 1) * pageSize.value + 1
  const to = Math.min(currentPage.value * pageSize.value, pagosTrabProv.value.length)
  return { from, to }
})

watch(pagosTrabProv, () => {
  if (currentPage.value > totalPages.value) {
    currentPage.value = totalPages.value
  }
})

function goToPage(page) {
  if (page >= 1 && page <= totalPages.value) currentPage.value = page
}
function prevPage() {
  if (currentPage.value > 1) currentPage.value--
}
function nextPage() {
  if (currentPage.value < totalPages.value) currentPage.value++
}

onMounted(async () => {
  await listarPagos()
})
</script>

<style scoped>
.btn-primary {
  @apply bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg;
}
.btn-success {
  @apply bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg;
}
.btn-gray {
  @apply bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg;
}
.btn-warning {
  @apply bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg;
}
</style>
