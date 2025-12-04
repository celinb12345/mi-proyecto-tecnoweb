<template> 
  <div
    :class="[
      'p-8 rounded-2xl shadow-md transition-colors duration-300',
      darkMode ? 'bg-gray-800 text-gray-100' : 'bg-white text-gray-900',
      fontClass
    ]"
  >
    <h2
      :class="[
        'text-2xl font-bold mb-6',
        darkMode ? 'text-blue-400' : 'text-blue-700'
      ]"
    >
      Gestión de Servicios
    </h2>

    <!-- BUSCADOR + BOTONES -->
    <div class="grid grid-cols-3 gap-4 mb-4">
      <div class="col-span-2">
        <label class="block text-sm font-semibold mb-1">Buscar por nombre</label>
        <input
          v-model="buscarNombre"
          class="input"
          :class="inputClass"
          placeholder="Nombre del servicio..."
        />
      </div>
      <div class="flex items-end gap-2 justify-end">
        <button @click="buscarServicios" class="btn-blue text-xs md:text-sm">
          Buscar
        </button>
        <button @click="toggleLista" class="btn-gray text-xs md:text-sm">
          {{ mostrarLista ? 'Ocultar lista' : 'Listar todos' }}
        </button>
        <button @click="toggleDetalle" class="btn-indigo text-xs md:text-sm">
          {{ mostrarDetalle ? 'Ocultar detalle' : 'Detalle proyecto-servicio' }}
        </button>
      </div>
    </div>

    <!-- FORM SERVICIO -->
    <form @submit.prevent="guardarServicio" class="grid grid-cols-2 gap-4 mb-8">
      <div>
        <label class="block text-sm font-semibold mb-1">Nombre servicio</label>
        <input v-model="formServ.nombre_servicio" class="input" :class="inputClass" />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Precio</label>
        <input
          type="number"
          step="0.01"
          v-model="formServ.precio_serv"
          class="input"
          :class="inputClass"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Tiempo estimado</label>
        <input
          type="date"
          v-model="formServ.tiempo_estimado_serv"
          class="input"
          :class="inputClass"
        />
      </div>

      <div class="col-span-2">
        <label class="block text-sm font-semibold mb-1">Descripción</label>
        <textarea
          v-model="formServ.descripcion_serv"
          class="input"
          :class="inputClass"
          rows="2"
        ></textarea>
      </div>

      <div class="col-span-2 flex justify-end gap-2">
        <button type="submit" class="btn-green text-xs md:text-sm">
          {{ editandoServ ? 'Actualizar' : 'Registrar' }}
        </button>
        <button
          v-if="editandoServ"
          type="button"
          @click="cancelarEdicionServicio"
          class="btn-gray text-xs md:text-sm"
        >
          Cancelar
        </button>
      </div>
    </form>

    <!-- LISTA SERVICIOS -->
    <div v-if="mostrarLista" class="overflow-x-auto mb-8">
      <table
        :class="[
          'w-full rounded-lg text-sm',
          darkMode ? 'border border-gray-700' : 'border border-gray-200'
        ]"
      >
        <thead class="bg-blue-600 text-white">
          <tr>
            <th class="px-2 py-2">ID</th>
            <th class="px-2 py-2">Nombre</th>
            <th class="px-2 py-2">Precio</th>
            <th class="px-2 py-2">Tiempo estimado</th>
            <th class="px-2 py-2">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="s in paginatedServicios"
            :key="s.id_servicio"
            :class="[
              'border-b',
              darkMode
                ? 'border-gray-700 hover:bg-gray-800'
                : 'border-gray-200 hover:bg-gray-50'
            ]"
          >
            <td class="px-2 py-2">{{ s.id_servicio }}</td>
            <td class="px-2 py-2">{{ s.nombre_servicio }}</td>
            <td class="px-2 py-2">S/ {{ Number(s.precio_serv || 0).toFixed(2) }}</td>
            <td class="px-2 py-2">{{ s.tiempo_estimado_serv }}</td>
            <td class="px-2 py-2 space-x-2">
              <button
                @click="editarServicio(s)"
                class="text-blue-400 hover:text-blue-300 text-xs font-semibold"
              >
                ✏️
              </button>
              <button
                @click="eliminarServicio(s.id_servicio)"
                class="text-red-400 hover:text-red-300 text-xs font-semibold"
              >
                🗑
              </button>
            </td>
          </tr>
          <tr v-if="!servicios.length">
            <td
              colspan="5"
              class="text-center py-3"
              :class="darkMode ? 'text-gray-300' : 'text-gray-500'"
            >
              No hay servicios registrados con los filtros actuales
            </td>
          </tr>
        </tbody>
      </table>

      <!-- PAGINACIÓN SERVICIOS (a partir de 5 filas) -->
      <div
        v-if="servicios.length > perPageServ"
        class="flex flex-col md:flex-row justify-between items-center gap-3 mt-4 text-xs md:text-sm"
      >
        <span :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
          Mostrando
          <b>{{ startItemServ }}</b>
          -
          <b>{{ endItemServ }}</b>
          de
          <b>{{ servicios.length }}</b>
          servicios
        </span>

        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="prevPageServ"
            :disabled="currentPageServ === 1"
            class="px-3 py-1 rounded-lg border font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
            :class="
              darkMode
                ? 'border-gray-600 text-gray-100 hover:bg-gray-700'
                : 'border-gray-300 text-gray-800 hover:bg-gray-100'
            "
          >
            ◀ Anterior
          </button>

          <span :class="darkMode ? 'text-gray-200' : 'text-gray-700'">
            Página {{ currentPageServ }} de {{ totalPagesServ }}
          </span>

          <button
            type="button"
            @click="nextPageServ"
            :disabled="currentPageServ === totalPagesServ || totalPagesServ === 0"
            class="px-3 py-1 rounded-lg border font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
            :class="
              darkMode
                ? 'border-gray-600 text-gray-100 hover:bg-gray-700'
                : 'border-gray-300 text-gray-800 hover:bg-gray-100'
            "
          >
            Siguiente ▶
          </button>
        </div>
      </div>
    </div>

    <!-- DETALLE PROYECTO - SERVICIO -->
    <div v-if="mostrarDetalle" class="border-t pt-6 mt-4">
      <div class="flex justify-between items-center mb-4">
        <h3
          class="text-xl font-bold"
          :class="darkMode ? 'text-blue-400' : 'text-blue-700'"
        >
          Detalle de servicios por proyecto
        </h3>
        <button @click="toggleListaDetalle" class="btn-gray text-xs md:text-sm">
          {{ mostrarListaDetalle ? 'Ocultar lista detalle' : 'Listar detalle' }}
        </button>
      </div>

      <!-- FORM DETALLE -->
      <form
        @submit.prevent="guardarDetalle"
        class="grid grid-cols-2 gap-4 mb-6"
      >
        <div>
          <label class="block text-sm font-semibold mb-1">Proyecto</label>
          <select v-model="formDet.id_proyecto" class="input" :class="inputClass">
            <option disabled value="">Seleccione proyecto</option>
            <option
              v-for="p in proyectos"
              :key="p.id_proyecto"
              :value="p.id_proyecto"
            >
              {{ p.nombre_pro }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">Servicio</label>
          <select v-model="formDet.id_servicio" class="input" :class="inputClass">
            <option disabled value="">Seleccione servicio</option>
            <option
              v-for="s in serviciosDetalle"
              :key="s.id_servicio"
              :value="s.id_servicio"
            >
              {{ s.nombre_servicio }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">Precio unitario</label>
          <input
            type="number"
            step="0.01"
            v-model="formDet.precio_unitario"
            class="input"
            :class="inputClass"
          />
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">Sub total</label>
          <input
            type="number"
            step="0.01"
            v-model="formDet.sub_total"
            class="input"
            :class="inputClass"
          />
        </div>

        <div class="col-span-2 flex justify-end gap-2">
          <button type="submit" class="btn-indigo text-xs md:text-sm">
            {{ editandoDet ? 'Actualizar detalle' : 'Registrar detalle' }}
          </button>
          <button
            v-if="editandoDet"
            type="button"
            @click="cancelarEdicionDetalle"
            class="btn-gray text-xs md:text-sm"
          >
            Cancelar
          </button>
        </div>
      </form>

      <!-- LISTA DETALLE -->
      <div v-if="mostrarListaDetalle" class="overflow-x-auto">
        <table
          :class="[
            'w-full rounded-lg text-sm',
            darkMode ? 'border border-gray-700' : 'border border-gray-200'
          ]"
        >
          <thead class="bg-indigo-600 text-white">
            <tr>
              <th class="px-2 py-2">Proyecto</th>
              <th class="px-2 py-2">Servicio</th>
              <th class="px-2 py-2">Precio unitario</th>
              <th class="px-2 py-2">Sub total</th>
              <th class="px-2 py-2">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="d in paginatedDetalles"
              :key="d.id_proyecto_servicio"
              :class="[
                'border-b',
                darkMode
                  ? 'border-gray-700 hover:bg-gray-800'
                  : 'border-gray-200 hover:bg-gray-50'
              ]"
            >
              <td class="px-2 py-2">
                {{ d.proyecto ? d.proyecto.nombre_pro : '' }}
              </td>
              <td class="px-2 py-2">
                {{ d.servicio ? d.servicio.nombre_servicio : '' }}
              </td>
              <td class="px-2 py-2">
                S/ {{ Number(d.precio_unitario || 0).toFixed(2) }}
              </td>
              <td class="px-2 py-2">
                S/ {{ Number(d.sub_total || 0).toFixed(2) }}
              </td>
              <td class="px-2 py-2 space-x-2">
                <button
                  @click="editarDetalle(d)"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded text-xs font-semibold"
                >
                  ✏️ Editar
                </button>
                <button
                  @click="eliminarDetalle(d.id_proyecto_servicio)"
                  class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs font-semibold"
                >
                  🗑 Eliminar
                </button>
              </td>
            </tr>
            <tr v-if="!detalles.length">
              <td
                colspan="5"
                class="text-center py-3"
                :class="darkMode ? 'text-gray-300' : 'text-gray-500'"
              >
                No hay registros de detalle
              </td>
            </tr>
          </tbody>
        </table>

        <!-- PAGINACIÓN DETALLES (a partir de 5 filas) -->
        <div
          v-if="detalles.length > perPageDet"
          class="flex flex-col md:flex-row justify-between items-center gap-3 mt-4 text-xs md:text-sm"
        >
          <span :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
            Mostrando
            <b>{{ startItemDet }}</b>
            -
            <b>{{ endItemDet }}</b>
            de
            <b>{{ detalles.length }}</b>
            registros
          </span>

          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="prevPageDet"
              :disabled="currentPageDet === 1"
              class="px-3 py-1 rounded-lg border font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
              :class="
                darkMode
                  ? 'border-gray-600 text-gray-100 hover:bg-gray-700'
                  : 'border-gray-300 text-gray-800 hover:bg-gray-100'
              "
            >
              ◀ Anterior
            </button>

            <span :class="darkMode ? 'text-gray-200' : 'text-gray-700'">
              Página {{ currentPageDet }} de {{ totalPagesDet }}
            </span>

            <button
              type="button"
              @click="nextPageDet"
              :disabled="currentPageDet === totalPagesDet || totalPagesDet === 0"
              class="px-3 py-1 rounded-lg border font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
              :class="
                darkMode
                  ? 'border-gray-600 text-gray-100 hover:bg-gray-700'
                  : 'border-gray-300 text-gray-800 hover:bg-gray-100'
              "
            >
              Siguiente ▶
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import axios from 'axios'

/* ===== PROPS PARA MODO OSCURO Y FUENTE ===== */
const props = defineProps({
  darkMode: { type: Boolean, default: false },
  fontClass: { type: String, default: 'font-sans' }
})

const darkMode = computed(() => props.darkMode)
const fontClass = computed(() => props.fontClass || 'font-sans')

const inputClass = computed(() =>
  darkMode.value
    ? 'bg-gray-900 border-gray-700 text-gray-100 placeholder-gray-400'
    : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400'
)

// ===== SERVICIOS =====
const servicios = ref([])
const buscarNombre = ref('')
const mostrarLista = ref(false)

const editandoServ = ref(false)
const idEditarServ = ref(null)

const formServ = ref({
  nombre_servicio: '',
  descripcion_serv: '',
  precio_serv: '',
  tiempo_estimado_serv: ''
})

/* PAGINACIÓN SERVICIOS (5 por página) */
const currentPageServ = ref(1)
const perPageServ = 5

const totalPagesServ = computed(() =>
  servicios.value.length ? Math.ceil(servicios.value.length / perPageServ) : 0
)

const paginatedServicios = computed(() => {
  const start = (currentPageServ.value - 1) * perPageServ
  return servicios.value.slice(start, start + perPageServ)
})

const startItemServ = computed(() => {
  if (!servicios.value.length) return 0
  return (currentPageServ.value - 1) * perPageServ + 1
})

const endItemServ = computed(() => {
  if (!servicios.value.length) return 0
  return Math.min(currentPageServ.value * perPageServ, servicios.value.length)
})

function nextPageServ () {
  if (currentPageServ.value < totalPagesServ.value) currentPageServ.value++
}

function prevPageServ () {
  if (currentPageServ.value > 1) currentPageServ.value--
}

watch(servicios, () => {
  currentPageServ.value = 1
})

// ===== DETALLE PROYECTO_SERVICIO =====
const mostrarDetalle = ref(false)
const mostrarListaDetalle = ref(false)

const detalles = ref([])
const proyectos = ref([])
const serviciosDetalle = ref([])

const editandoDet = ref(false)
const idEditarDet = ref(null)

const formDet = ref({
  id_proyecto: '',
  id_servicio: '',
  precio_unitario: '',
  sub_total: ''
})

/* PAGINACIÓN DETALLES (5 por página) */
const currentPageDet = ref(1)
const perPageDet = 5

const totalPagesDet = computed(() =>
  detalles.value.length ? Math.ceil(detalles.value.length / perPageDet) : 0
)

const paginatedDetalles = computed(() => {
  const start = (currentPageDet.value - 1) * perPageDet
  return detalles.value.slice(start, start + perPageDet)
})

const startItemDet = computed(() => {
  if (!detalles.value.length) return 0
  return (currentPageDet.value - 1) * perPageDet + 1
})

const endItemDet = computed(() => {
  if (!detalles.value.length) return 0
  return Math.min(currentPageDet.value * perPageDet, detalles.value.length)
})

function nextPageDet () {
  if (currentPageDet.value < totalPagesDet.value) currentPageDet.value++
}

function prevPageDet () {
  if (currentPageDet.value > 1) currentPageDet.value--
}

watch(detalles, () => {
  currentPageDet.value = 1
})

// cuando cambia el servicio, copiar precio_serv al precio_unitario y sub_total
watch(
  () => formDet.value.id_servicio,
  (nuevo) => {
    if (!nuevo) return
    const s = serviciosDetalle.value.find(
      (srv) => srv.id_servicio === nuevo
    )
    if (s) {
      formDet.value.precio_unitario = s.precio_serv
      formDet.value.sub_total = s.precio_serv
    }
  }
)

onMounted(() => {
  cargarServicios()
})

// ------- SERVICIOS -------
function cargarServicios (params = {}) {
  return axios
    .get('propietario/melamina/servicios', { params })
    .then((res) => {
      servicios.value = res.data.servicios || []
      // para el detalle usamos la misma lista
      serviciosDetalle.value = res.data.servicios || []
    })
    .catch((err) => {
      console.error('Error al obtener servicios:', err)
      alert('Error al obtener servicios')
    })
}

function buscarServicios () {
  const params = {}
  if (buscarNombre.value) params.buscar = buscarNombre.value
  cargarServicios(params).then(() => {
    mostrarLista.value = true
  })
}

function toggleLista () {
  mostrarLista.value = !mostrarLista.value
  if (mostrarLista.value) {
    buscarServicios()
  }
}

function guardarServicio () {
  const url = editandoServ.value
    ? `propietario/melamina/servicios/${idEditarServ.value}`
    : 'propietario/melamina/servicios'

  axios
    .post(url, formServ.value)
    .then((res) => {
      if (res.data.success) {
        alert(editandoServ.value ? 'Servicio actualizado' : 'Servicio registrado')
        limpiarServicio()
        buscarServicios()
      }
    })
    .catch((err) => {
      console.error('Error al guardar servicio:', err)
      alert('Error al guardar servicio')
    })
}

function editarServicio (s) {
  editandoServ.value = true
  idEditarServ.value = s.id_servicio

  formServ.value = {
    nombre_servicio: s.nombre_servicio,
    descripcion_serv: s.descripcion_serv,
    precio_serv: s.precio_serv,
    tiempo_estimado_serv: s.tiempo_estimado_serv
  }
}

function eliminarServicio (id) {
  if (!confirm('¿Eliminar este servicio?')) return

  axios
    .delete(`propietario/melamina/servicios/${id}`)
    .then(() => {
      alert('Servicio eliminado')
      buscarServicios()
    })
    .catch((err) => {
      console.error('Error al eliminar servicio:', err)
      alert('Error al eliminar servicio')
    })
}

function limpiarServicio () {
  editandoServ.value = false
  idEditarServ.value = null
  formServ.value = {
    nombre_servicio: '',
    descripcion_serv: '',
    precio_serv: '',
    tiempo_estimado_serv: ''
  }
}

function cancelarEdicionServicio () {
  limpiarServicio()
}

// ------- DETALLE PROYECTO_SERVICIO -------
function toggleDetalle () {
  mostrarDetalle.value = !mostrarDetalle.value
  if (mostrarDetalle.value) {
    cargarDetalle()
  }
}

function toggleListaDetalle () {
  mostrarListaDetalle.value = !mostrarListaDetalle.value
  if (mostrarListaDetalle.value && !detalles.value.length) {
    cargarDetalle()
  }
}

function cargarDetalle () {
  return axios
    .get('propietario/melamina/proyecto-servicios')
    .then((res) => {
      detalles.value = res.data.detalles || []
      proyectos.value = res.data.proyectos || []
      serviciosDetalle.value = res.data.servicios || serviciosDetalle.value
    })
    .catch((err) => {
      console.error('Error al obtener detalle:', err)
      alert('Error al obtener detalle de servicios')
    })
}

function guardarDetalle () {
  const url = editandoDet.value
    ? `propietario/melamina/proyecto-servicios/${idEditarDet.value}`
    : 'propietario/melamina/proyecto-servicios'

  axios
    .post(url, formDet.value)
    .then((res) => {
      if (res.data.success) {
        alert(editandoDet.value ? 'Detalle actualizado' : 'Detalle registrado')
        limpiarDetalle()
        cargarDetalle()
        mostrarListaDetalle.value = true
      }
    })
    .catch((err) => {
      console.error('Error al guardar detalle:', err)
      alert('Error al guardar detalle')
    })
}

function editarDetalle (d) {
  editandoDet.value = true
  idEditarDet.value = d.id_proyecto_servicio

  formDet.value = {
    id_proyecto: d.id_proyecto,
    id_servicio: d.id_servicio,
    precio_unitario: d.precio_unitario,
    sub_total: d.sub_total
  }
}

function eliminarDetalle (id) {
  if (!confirm('¿Eliminar este registro de detalle?')) return

  axios
    .delete(`propietario/melamina/proyecto-servicios/${id}`)
    .then(() => {
      alert('Detalle eliminado')
      cargarDetalle()
    })
    .catch((err) => {
      console.error('Error al eliminar detalle:', err)
      alert('Error al eliminar detalle')
    })
}

function limpiarDetalle () {
  editandoDet.value = false
  idEditarDet.value = null
  formDet.value = {
    id_proyecto: '',
    id_servicio: '',
    precio_unitario: '',
    sub_total: ''
  }
}

function cancelarEdicionDetalle () {
  limpiarDetalle()
}
</script>

<style scoped>
.input {
  @apply rounded-lg p-2 w-full focus:ring-2 focus:ring-blue-400 focus:outline-none transition-colors duration-200;
}
.btn-blue {
  @apply bg-blue-600 text-white px-3 py-1.5 rounded-lg hover:bg-blue-700;
}
.btn-gray {
  @apply bg-gray-500 text-white px-3 py-1.5 rounded-lg hover:bg-gray-600;
}
.btn-green {
  @apply bg-green-600 text-white px-3 py-1.5 rounded-lg hover:bg-green-700;
}
.btn-indigo {
  @apply bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700;
}
</style>
