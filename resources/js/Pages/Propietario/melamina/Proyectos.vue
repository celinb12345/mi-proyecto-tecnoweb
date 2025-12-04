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
      Gestión de Proyectos
    </h2>

    <!-- FILTRO + BOTONES -->
    <div class="grid grid-cols-4 gap-4 mb-6">
      <div class="col-span-2">
        <label class="block text-sm font-semibold mb-1">Buscar por nombre</label>
        <input
          v-model="filtroNombre"
          class="input"
          :class="inputClass"
          placeholder="Nombre del proyecto..."
        />
      </div>
      <div>
        <label class="block text-sm font-semibold mb-1">Desde</label>
        <input type="date" v-model="fechaDesde" class="input" :class="inputClass" />
      </div>
      <div>
        <label class="block text-sm font-semibold mb-1">Hasta</label>
        <input type="date" v-model="fechaHasta" class="input" :class="inputClass" />
      </div>
    </div>

    <div class="flex gap-2 mb-6">
      <button @click="buscarProyectos" class="btn-blue">Buscar</button>
      <button @click="toggleLista" class="btn-gray">
        {{ mostrarLista ? 'Ocultar lista' : 'Listar todos' }}
      </button>
      <button @click="toggleAsignacion" class="btn-indigo">
        {{ mostrarAsignacion ? 'Ocultar asignación' : 'Asignar trabajador' }}
      </button>
    </div>

    <!-- FORMULARIO PROYECTO -->
    <form @submit.prevent="guardarProyecto" class="grid grid-cols-2 gap-4 mb-8">
      <div>
        <label class="block text-sm font-semibold mb-1">Nombre proyecto</label>
        <input v-model="formProy.nombre_pro" class="input" :class="inputClass" />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Cliente</label>
        <select v-model="formProy.id_cliente" class="input" :class="inputClass">
          <option disabled value="">Seleccione cliente</option>
          <option
            v-for="c in clientes"
            :key="c.id_cliente"
            :value="c.id_cliente"
          >
            {{ c.nombre_cli }} {{ c.apellido_cli }}
          </option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Fecha inicio</label>
        <input
          type="date"
          v-model="formProy.fecha_inicio_pro"
          class="input"
          :class="inputClass"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Fecha fin</label>
        <input
          type="date"
          v-model="formProy.fecha_fin_pro"
          class="input"
          :class="inputClass"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Monto total</label>
        <input
          type="number"
          step="0.01"
          v-model="formProy.monto_total_pro"
          class="input"
          :class="inputClass"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Estado</label>
        <select v-model="formProy.estado_proyecto" class="input" :class="inputClass">
          <option disabled value="">Seleccione</option>
          <option value="En planificación">En planificación</option>
          <option value="En ejecución">En ejecución</option>
          <option value="Finalizado">Finalizado</option>
          <option value="Cancelado">Cancelado</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Obra completa</label>
        <select v-model="formProy.obra_completa" class="input" :class="inputClass">
          <option :value="true">Sí</option>
          <option :value="false">No</option>
        </select>
      </div>

      <!-- 🗺 Dirección con mapa -->
      <div class="col-span-2">
        <label class="block text-sm font-semibold mb-1">Dirección</label>

        <div class="flex gap-2 mb-2">
          <input
            v-model="formProy.direccion_pro"
            class="input flex-1"
            :class="inputClass"
            placeholder="Escribe una dirección..."
          />
          <button type="button" @click="buscarEnMapa" class="btn-gray">
            Buscar en mapa
          </button>
        </div>

        <div
          ref="mapRef"
          class="w-full h-64 rounded-xl overflow-hidden"
          :class="darkMode ? 'border border-gray-700' : 'border border-gray-300'"
        ></div>

        <p
          class="mt-1 text-xs"
          :class="darkMode ? 'text-gray-300' : 'text-gray-500'"
        >
          Puedes buscar una dirección o hacer clic en el mapa.
        </p>
      </div>

      <div class="col-span-2">
        <label class="block text-sm font-semibold mb-1">Descripción</label>
        <textarea
          v-model="formProy.descripcion_pro"
          class="input"
          :class="inputClass"
          rows="2"
        ></textarea>
      </div>

      <div class="col-span-2 flex justify-end gap-2">
        <button type="submit" class="btn-green">
          {{ editandoProy ? 'Actualizar' : 'Registrar' }}
        </button>
        <button
          v-if="editandoProy"
          type="button"
          @click="cancelarEdicionProyecto"
          class="btn-gray"
        >
          Cancelar
        </button>
      </div>
    </form>

    <!-- LISTA PROYECTOS -->
    <div v-if="mostrarLista" class="overflow-x-auto mb-8">
      <table
        :class="[
          'w-full rounded-lg',
          darkMode ? 'border border-gray-700' : 'border border-gray-200'
        ]"
      >
        <thead class="bg-blue-600 text-white">
          <tr>
            <th class="px-2 py-2">ID</th>
            <th class="px-2 py-2">Nombre</th>
            <th class="px-2 py-2">Cliente</th>
            <th class="px-2 py-2">Inicio</th>
            <th class="px-2 py-2">Fin</th>
            <th class="px-2 py-2">Monto</th>
            <th class="px-2 py-2">Estado</th>
            <th class="px-2 py-2">Obra completa</th>
            <th class="px-2 py-2">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="p in paginatedProyectos"
            :key="p.id_proyecto"
            :class="[
              'border-b',
              darkMode
                ? 'border-gray-700 hover:bg-gray-800'
                : 'border-gray-200 hover:bg-gray-50'
            ]"
          >
            <td class="px-2 py-2">{{ p.id_proyecto }}</td>
            <td class="px-2 py-2">{{ p.nombre_pro }}</td>
            <td class="px-2 py-2">
              {{
                p.cliente
                  ? p.cliente.nombre_cli + ' ' + p.cliente.apellido_cli
                  : ''
              }}
            </td>
            <td class="px-2 py-2">{{ p.fecha_inicio_pro }}</td>
            <td class="px-2 py-2">{{ p.fecha_fin_pro }}</td>
            <td class="px-2 py-2">
              S/ {{ Number(p.monto_total_pro || 0).toFixed(2) }}
            </td>
            <td class="px-2 py-2">
              <span :class="estadoBadgeClass(p.estado_proyecto)">
                {{ p.estado_proyecto }}
              </span>
            </td>
            <td class="px-2 py-2">{{ p.obra_completa ? 'Sí' : 'No' }}</td>
            <td class="px-2 py-2 space-x-2">
              <button
                @click="editarProyecto(p)"
                class="text-blue-500 text-sm font-semibold hover:underline"
              >
                ✏️
              </button>
              <button
                @click="eliminarProyecto(p.id_proyecto)"
                class="text-red-500 text-sm font-semibold hover:underline"
              >
                🗑
              </button>
            </td>
          </tr>
          <tr v-if="!proyectos.length">
            <td
              colspan="9"
              class="text-center py-3"
              :class="darkMode ? 'text-gray-300' : 'text-gray-500'"
            >
              No hay proyectos registrados con los filtros actuales
            </td>
          </tr>
        </tbody>
      </table>

      <!-- PAGINACIÓN PROYECTOS (a partir de 5 filas) -->
      <div
        v-if="proyectos.length > perPageProy"
        class="flex flex-col md:flex-row justify-between items-center gap-3 mt-4 text-sm"
      >
        <span :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
          Mostrando
          <b>{{ startItemProy }}</b>
          -
          <b>{{ endItemProy }}</b>
          de
          <b>{{ proyectos.length }}</b>
          proyectos
        </span>

        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="prevPageProy"
            :disabled="currentPageProy === 1"
            class="px-3 py-1 rounded-lg border text-xs font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
            :class="
              darkMode
                ? 'border-gray-600 text-gray-100 hover:bg-gray-700'
                : 'border-gray-300 text-gray-800 hover:bg-gray-100'
            "
          >
            ◀ Anterior
          </button>

          <span :class="darkMode ? 'text-gray-200' : 'text-gray-700'">
            Página {{ currentPageProy }} de {{ totalPagesProy }}
          </span>

          <button
            type="button"
            @click="nextPageProy"
            :disabled="currentPageProy === totalPagesProy || totalPagesProy === 0"
            class="px-3 py-1 rounded-lg border text-xs font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
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

    <!-- SECCIÓN ASIGNACIÓN DE TRABAJADORES -->
    <div v-if="mostrarAsignacion" class="border-t pt-6 mt-4">
      <div class="flex justify-between items-center mb-4">
        <h3
          class="text-xl font-bold"
          :class="darkMode ? 'text-blue-400' : 'text-blue-700'"
        >
          Asignación de trabajadores a proyectos
        </h3>
        <button @click="toggleListaAsign" class="btn-gray">
          {{ mostrarListaAsign ? 'Ocultar lista asignaciones' : 'Listar asignaciones' }}
        </button>
      </div>

      <!-- FORM ASIGNACION -->
      <form @submit.prevent="guardarAsignacion" class="grid grid-cols-3 gap-4 mb-6">
        <div>
          <label class="block text-sm font-semibold mb-1">Fecha asignación</label>
          <input
            type="date"
            v-model="formAsig.fecha_asignacion"
            class="input"
            :class="inputClass"
          />
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">Trabajador</label>
          <select v-model="formAsig.id_trabajador" class="input" :class="inputClass">
            <option disabled value="">Seleccione trabajador</option>
            <option
              v-for="t in trabajadores"
              :key="t.id_trabajador"
              :value="t.id_trabajador"
            >
              {{ t.nombre_trabajador }} {{ t.apellido_trabajador }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">Proyecto</label>
          <select v-model="formAsig.id_proyecto" class="input" :class="inputClass">
            <option disabled value="">Seleccione proyecto</option>
            <option
              v-for="p in proyectosParaAsignar"
              :key="p.id_proyecto"
              :value="p.id_proyecto"
            >
              {{ p.nombre_pro }}
            </option>
          </select>
        </div>

        <div class="col-span-3 flex justify-end gap-2">
          <button type="submit" class="btn-indigo">
            {{ editandoAsig ? 'Actualizar asignación' : 'Registrar asignación' }}
          </button>
          <button
            v-if="editandoAsig"
            type="button"
            @click="cancelarEdicionAsignacion"
            class="btn-gray"
          >
            Cancelar
          </button>
        </div>
      </form>

      <!-- LISTA ASIGNACIONES -->
      <div v-if="mostrarListaAsign" class="overflow-x-auto">
        <table
          :class="[
            'w-full rounded-lg',
            darkMode ? 'border border-gray-700' : 'border border-gray-200'
          ]"
        >
          <thead class="bg-indigo-600 text-white">
            <tr>
              <th class="px-2 py-2">Fecha</th>
              <th class="px-2 py-2">Trabajador</th>
              <th class="px-2 py-2">Proyecto</th>
              <th class="px-2 py-2">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="a in paginatedAsignaciones"
              :key="a.id_asignacion"
              :class="[
                'border-b',
                darkMode
                  ? 'border-gray-700 hover:bg-gray-800'
                  : 'border-gray-200 hover:bg-gray-50'
              ]"
            >
              <td class="px-2 py-2">{{ a.fecha_asignacion }}</td>
              <td class="px-2 py-2">
                {{
                  a.trabajador
                    ? a.trabajador.nombre_trabajador +
                      ' ' +
                      a.trabajador.apellido_trabajador
                    : ''
                }}
              </td>
              <td class="px-2 py-2">
                {{ a.proyecto ? a.proyecto.nombre_pro : '' }}
              </td>
              <td class="px-2 py-2 space-x-2">
                <button
                  @click="editarAsignacion(a)"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded text-xs font-semibold"
                >
                  ✏️ Editar
                </button>
                <button
                  @click="eliminarAsignacion(a.id_asignacion)"
                  class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs font-semibold"
                >
                  🗑 Eliminar
                </button>
              </td>
            </tr>
            <tr v-if="!asignaciones.length">
              <td
                colspan="4"
                class="text-center py-3"
                :class="darkMode ? 'text-gray-300' : 'text-gray-500'"
              >
                No hay asignaciones registradas
              </td>
            </tr>
          </tbody>
        </table>

        <!-- PAGINACIÓN ASIGNACIONES (a partir de 5 filas) -->
        <div
          v-if="asignaciones.length > perPageAsign"
          class="flex flex-col md:flex-row justify-between items-center gap-3 mt-4 text-sm"
        >
          <span :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
            Mostrando
            <b>{{ startItemAsign }}</b>
            -
            <b>{{ endItemAsign }}</b>
            de
            <b>{{ asignaciones.length }}</b>
            asignaciones
          </span>

          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="prevPageAsign"
              :disabled="currentPageAsign === 1"
              class="px-3 py-1 rounded-lg border text-xs font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
              :class="
                darkMode
                  ? 'border-gray-600 text-gray-100 hover:bg-gray-700'
                  : 'border-gray-300 text-gray-800 hover:bg-gray-100'
              "
            >
              ◀ Anterior
            </button>

            <span :class="darkMode ? 'text-gray-200' : 'text-gray-700'">
              Página {{ currentPageAsign }} de {{ totalPagesAsign }}
            </span>

            <button
              type="button"
              @click="nextPageAsign"
              :disabled="
                currentPageAsign === totalPagesAsign || totalPagesAsign === 0
              "
              class="px-3 py-1 rounded-lg border text-xs font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
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
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import L from 'leaflet'

/* ==== PROPS PARA MODO OSCURO Y FUENTE ==== */
const props = defineProps({
  darkMode: { type: Boolean, default: false },
  fontClass: { type: String, default: 'font-sans' }
})

const darkMode = computed(() => props.darkMode)
const fontClass = computed(() => props.fontClass)

/* Clase base para inputs */
const inputClass = computed(() =>
  darkMode.value
    ? 'bg-gray-900 border-gray-700 text-gray-100 placeholder-gray-400'
    : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400'
)

/* =======================
   PROYECTOS
======================= */
const proyectos = ref([])
const clientes = ref([])

const filtroNombre = ref('')
const fechaDesde = ref('')
const fechaHasta = ref('')
const mostrarLista = ref(false)

const editandoProy = ref(false)
const idEditarProy = ref(null)

const formProy = ref({
  nombre_pro: '',
  descripcion_pro: '',
  fecha_inicio_pro: '',
  fecha_fin_pro: '',
  direccion_pro: '',
  monto_total_pro: '',
  estado_proyecto: '',
  obra_completa: true,
  id_cliente: ''
})

/* ===== PAGINACIÓN PROYECTOS (5 por página) ===== */
const currentPageProy = ref(1)
const perPageProy = 5

const totalPagesProy = computed(() =>
  proyectos.value.length ? Math.ceil(proyectos.value.length / perPageProy) : 0
)

const paginatedProyectos = computed(() => {
  const start = (currentPageProy.value - 1) * perPageProy
  return proyectos.value.slice(start, start + perPageProy)
})

const startItemProy = computed(() => {
  if (!proyectos.value.length) return 0
  return (currentPageProy.value - 1) * perPageProy + 1
})

const endItemProy = computed(() => {
  if (!proyectos.value.length) return 0
  return Math.min(currentPageProy.value * perPageProy, proyectos.value.length)
})

function nextPageProy () {
  if (currentPageProy.value < totalPagesProy.value) currentPageProy.value++
}

function prevPageProy () {
  if (currentPageProy.value > 1) currentPageProy.value--
}

watch(proyectos, () => {
  currentPageProy.value = 1
})

/* =======================
   MAPA (Leaflet + OSM)
======================= */
const mapRef = ref(null)
let mapInstance = null
let markerInstance = null

const defaultCenter = { lat: -17.8, lng: -63.2 }

function initMap () {
  if (!mapRef.value || mapInstance) return

  mapInstance = L.map(mapRef.value).setView(
    [defaultCenter.lat, defaultCenter.lng],
    14
  )

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(mapInstance)

  markerInstance = L.marker([defaultCenter.lat, defaultCenter.lng], {
    draggable: true
  }).addTo(mapInstance)

  markerInstance.on('dragend', (e) => {
    const { lat, lng } = e.target.getLatLng()
    reverseGeocode(lat, lng)
  })

  mapInstance.on('click', (e) => {
    markerInstance.setLatLng(e.latlng)
    reverseGeocode(e.latlng.lat, e.latlng.lng)
  })

  if (formProy.value.direccion_pro) {
    buscarEnMapa(true)
  }
}
async function buscarEnMapa (silencioso = false) {
  const texto = formProy.value.direccion_pro?.trim()
  if (!texto) {
    if (!silencioso) alert('Escribe una dirección para buscar.')
    return
  }

  try {
    const url = `https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(
      texto
    )}`

    const { data } = await axios.get(url, {
      withCredentials: false,          
      headers: {
        'Accept-Language': 'es'       
        // 'User-Agent': '...'        
      }
    })

    if (!data.length) {
      if (!silencioso) alert('No se encontró esa dirección.')
      return
    }

    const { lat, lon, display_name } = data[0]
    mapInstance.setView([lat, lon], 17)
    markerInstance.setLatLng([lat, lon])
    formProy.value.direccion_pro = display_name
  } catch (error) {
    console.error('Error al buscar en mapa:', error)
    if (!silencioso) alert('Error al buscar en el mapa.')
  }
}

async function reverseGeocode (lat, lon) {
  try {
    const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`
    const { data } = await axios.get(url, {
      withCredentials: false,          
      headers: {
        'Accept-Language': 'es'
      }
    })

    if (data.display_name) {
      formProy.value.direccion_pro = data.display_name
    }
  } catch (error) {
    console.error('Error en reverseGeocode:', error)
  }
}


/* =======================
   ASIGNACIONES
======================= */
const mostrarAsignacion = ref(false)
const mostrarListaAsign = ref(false)
const asignaciones = ref([])
const trabajadores = ref([])
const proyectosParaAsignar = ref([])

const editandoAsig = ref(false)
const idEditarAsig = ref(null)

const formAsig = ref({
  fecha_asignacion: '',
  id_trabajador: '',
  id_proyecto: ''
})

/* PAGINACIÓN ASIGNACIONES (5 por página) */
const currentPageAsign = ref(1)
const perPageAsign = 5

const totalPagesAsign = computed(() =>
  asignaciones.value.length
    ? Math.ceil(asignaciones.value.length / perPageAsign)
    : 0
)

const paginatedAsignaciones = computed(() => {
  const start = (currentPageAsign.value - 1) * perPageAsign
  return asignaciones.value.slice(start, start + perPageAsign)
})

const startItemAsign = computed(() => {
  if (!asignaciones.value.length) return 0
  return (currentPageAsign.value - 1) * perPageAsign + 1
})

const endItemAsign = computed(() => {
  if (!asignaciones.value.length) return 0
  return Math.min(
    currentPageAsign.value * perPageAsign,
    asignaciones.value.length
  )
})

function nextPageAsign () {
  if (currentPageAsign.value < totalPagesAsign.value) currentPageAsign.value++
}

function prevPageAsign () {
  if (currentPageAsign.value > 1) currentPageAsign.value--
}

watch(asignaciones, () => {
  currentPageAsign.value = 1
})

/* =======================
   CARGA INICIAL
======================= */
onMounted(() => {
  cargarProyectos()
  initMap()
})

/* ===== PROYECTOS ===== */
function cargarProyectos (params = {}) {
  return axios
    .get('propietario/melamina/proyectos', { params })
    .then((res) => {
      proyectos.value = res.data.proyectos || []
      clientes.value = res.data.clientes || []
      proyectosParaAsignar.value = proyectos.value
    })
    .catch((err) => {
      console.error('Error al obtener proyectos:', err)
      alert('Error al obtener proyectos')
    })
}

function buscarProyectos () {
  const params = {}
  if (filtroNombre.value) params.buscar = filtroNombre.value
  if (fechaDesde.value) params.desde = fechaDesde.value
  if (fechaHasta.value) params.hasta = fechaHasta.value

  cargarProyectos(params).then(() => {
    mostrarLista.value = true
  })
}

function toggleLista () {
  mostrarLista.value = !mostrarLista.value
  if (mostrarLista.value) buscarProyectos()
}

function guardarProyecto () {
  const payload = { ...formProy.value }
  payload.obra_completa = formProy.value.obra_completa ? 1 : 0

  const url = editandoProy.value
    ? `propietario/melamina/proyectos/${idEditarProy.value}`
    : 'propietario/melamina/proyectos'

  axios
    .post(url, payload)
    .then((res) => {
      if (res.data.success) {
        alert(editandoProy.value ? 'Proyecto actualizado' : 'Proyecto registrado')
        limpiarProyecto()
        buscarProyectos()
      }
    })
    .catch((err) => {
      console.error('Error al guardar proyecto:', err)
      alert('Error al guardar proyecto')
    })
}

function editarProyecto (p) {
  editandoProy.value = true
  idEditarProy.value = p.id_proyecto

  formProy.value = {
    nombre_pro: p.nombre_pro,
    descripcion_pro: p.descripcion_pro,
    fecha_inicio_pro: p.fecha_inicio_pro,
    fecha_fin_pro: p.fecha_fin_pro,
    direccion_pro: p.direccion_pro,
    monto_total_pro: p.monto_total_pro,
    estado_proyecto: p.estado_proyecto,
    obra_completa: !!p.obra_completa,
    id_cliente: p.id_cliente
  }

  if (mapInstance && formProy.value.direccion_pro) {
    buscarEnMapa(true)
  }
}

function eliminarProyecto (id) {
  if (!confirm('¿Eliminar este proyecto?')) return

  axios
    .delete(`propietario/melamina/proyectos/${id}`)
    .then(() => {
      alert('Proyecto eliminado')
      buscarProyectos()
    })
    .catch((err) => {
      console.error('Error al eliminar proyecto:', err)
      alert('Error al eliminar proyecto')
    })
}

function limpiarProyecto () {
  editandoProy.value = false
  idEditarProy.value = null
  formProy.value = {
    nombre_pro: '',
    descripcion_pro: '',
    fecha_inicio_pro: '',
    fecha_fin_pro: '',
    direccion_pro: '',
    monto_total_pro: '',
    estado_proyecto: '',
    obra_completa: true,
    id_cliente: ''
  }
}

function cancelarEdicionProyecto () {
  limpiarProyecto()
}

/* ===== ASIGNACIONES ===== */
function toggleAsignacion () {
  mostrarAsignacion.value = !mostrarAsignacion.value
  if (mostrarAsignacion.value) {
    cargarAsignaciones()
  }
}

function toggleListaAsign () {
  mostrarListaAsign.value = !mostrarListaAsign.value
  if (mostrarListaAsign.value && !asignaciones.value.length) {
    cargarAsignaciones()
  }
}

function cargarAsignaciones () {
  return axios
    .get('propietario/melamina/asignaciones')
    .then((res) => {
      asignaciones.value = res.data.asignaciones || []
      trabajadores.value = res.data.trabajadores || []
      proyectosParaAsignar.value =
        res.data.proyectos || proyectosParaAsignar.value
    })
    .catch((err) => {
      console.error('Error al obtener asignaciones:', err)
      alert('Error al obtener asignaciones')
    })
}

function guardarAsignacion () {
  const payload = { ...formAsig.value }

  const url = editandoAsig.value
    ? `propietario/melamina/asignaciones/${idEditarAsig.value}`
    : 'propietario/melamina/asignaciones'

  axios
    .post(url, payload)
    .then((res) => {
      if (res.data.success) {
        alert(
          editandoAsig.value
            ? 'Asignación actualizada'
            : 'Asignación registrada'
        )
        limpiarAsignacion()
        cargarAsignaciones()
        mostrarListaAsign.value = true
      }
    })
    .catch((err) => {
      console.error('Error al guardar asignación:', err)
      alert('Error al guardar asignación')
    })
}

function editarAsignacion (a) {
  editandoAsig.value = true
  idEditarAsig.value = a.id_asignacion

  formAsig.value = {
    fecha_asignacion: a.fecha_asignacion,
    id_trabajador: a.id_trabajador,
    id_proyecto: a.id_proyecto
  }
}

function eliminarAsignacion (id) {
  if (!confirm('¿Eliminar esta asignación?')) return

  axios
    .delete(`propietario/melamina/asignaciones/${id}`)
    .then(() => {
      alert('Asignación eliminada')
      cargarAsignaciones()
    })
    .catch((err) => {
      console.error('Error al eliminar asignación:', err)
      alert('Error al eliminar asignación')
    })
}

function limpiarAsignacion () {
  editandoAsig.value = false
  idEditarAsig.value = null
  formAsig.value = {
    fecha_asignacion: '',
    id_trabajador: '',
    id_proyecto: ''
  }
}

function cancelarEdicionAsignacion () {
  limpiarAsignacion()
}

/* ===== ESTILO DEL ESTADO ===== */
function estadoBadgeClass (estado) {
  const base = 'px-2 py-1 rounded-full text-xs font-semibold'

  switch (estado) {
    case 'En planificación':
      return base + ' bg-yellow-100 text-yellow-800'
    case 'En ejecución':
      return base + ' bg-blue-100 text-blue-800'
    case 'Finalizado':
      return base + ' bg-green-100 text-green-800'
    case 'Cancelado':
      return base + ' bg-red-100 text-red-800'
    default:
      return base + ' bg-gray-100 text-gray-700'
  }
}
</script>

<style scoped>
.input {
  @apply rounded-lg p-2 w-full focus:ring-2 focus:ring-blue-400 focus:outline-none transition-colors duration-200;
}
.btn-blue {
  @apply bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700;
}
.btn-gray {
  @apply bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600;
}
.btn-green {
  @apply bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700;
}
.btn-indigo {
  @apply bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700;
}
</style>
