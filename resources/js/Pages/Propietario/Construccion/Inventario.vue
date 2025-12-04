<template>
  <div
    :class="[
      'p-8 rounded-2xl shadow-md transition-colors duration-300',
      props.darkMode ? 'bg-gray-800 text-gray-100' : 'bg-white text-gray-900',
      props.fontClass
    ]"
  >
    <h2 class="text-2xl font-bold text-blue-500 mb-6">Gestión de Inventario</h2>

    <!-- FILTRO + BOTONES -->
    <div class="grid grid-cols-3 gap-4 mb-6">
      <div>
        <label class="block text-sm font-semibold mb-1">Tipo de movimiento</label>
        <select
          v-model="tipoFiltro"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-900'
          ]"
        >
          <option value="">Todos</option>
          <option value="entrada">Entradas</option>
          <option value="salida">Salidas</option>
        </select>
      </div>
      <div class="flex items-end gap-2">
        <button @click="buscarPorTipo" class="btn-blue w-full">Buscar</button>
        <button @click="toggleLista" class="btn-gray w-full">
          {{ mostrarLista ? 'Ocultar lista' : 'Listar movimientos' }}
        </button>
      </div>
      <div class="flex items-end justify-end">
        <button @click="toggleUnidades" class="btn-indigo">
          {{ mostrarUnidades ? 'Ocultar unidades' : 'Añadir unidad de medida' }}
        </button>
      </div>
    </div>

    <!-- FORM INVENTARIO -->
    <form @submit.prevent="guardarInventario" class="grid grid-cols-2 gap-4 mb-8">
      <div>
        <label class="block text-sm font-semibold mb-1">Fecha</label>
        <input
          type="date"
          v-model="formInv.fecha_inv"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-900'
          ]"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Tipo de movimiento</label>
        <select
          v-model="formInv.tipo_movimiento"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-900'
          ]"
        >
          <option disabled value="">Seleccione</option>
          <option value="entrada">Entrada</option>
          <option value="salida">Salida</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Cantidad</label>
        <input
          type="number"
          step="0.01"
          v-model="formInv.cantidad_inv"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-900'
          ]"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Producto</label>
        <select
          v-model="formInv.id_producto"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-900'
          ]"
        >
          <option disabled value="">Seleccione producto</option>
          <option
            v-for="p in productos"
            :key="p.id_producto"
            :value="p.id_producto"
          >
            {{ p.nombre_prod }}
          </option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Unidad de medida</label>
        <select
          v-model="formInv.id_unidad"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-900'
          ]"
        >
          <option disabled value="">Seleccione unidad</option>
          <option
            v-for="u in unidades"
            :key="u.id_unidad"
            :value="u.id_unidad"
          >
            {{ u.nombre_unidad }} ({{ u.abreviatura_unidad }})
          </option>
        </select>
      </div>

      <div class="col-span-2 flex justify-end gap-2">
        <button type="submit" class="btn-green">
          {{ editandoInv ? 'Actualizar' : 'Registrar' }}
        </button>
        <button
          v-if="editandoInv"
          type="button"
          @click="cancelarEdicionInventario"
          class="btn-gray"
        >
          Cancelar
        </button>
      </div>
    </form>

    <!-- LISTA INVENTARIO -->
    <div v-if="mostrarLista" class="overflow-x-auto mb-6">
      <table class="w-full border border-gray-200 rounded-lg text-sm text-center">
        <thead class="bg-blue-600 text-white">
          <tr>
            <th class="px-2 py-2 border border-blue-500/60">Fecha</th>
            <th class="px-2 py-2 border border-blue-500/60">Tipo</th>
            <th class="px-2 py-2 border border-blue-500/60">Cantidad</th>
            <th class="px-2 py-2 border border-blue-500/60">Producto</th>
            <th class="px-2 py-2 border border-blue-500/60">Unidad</th>
            <th class="px-2 py-2 border border-blue-500/60">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="inv in paginatedInventarios"
            :key="inv.id_inventario"
            :class="props.darkMode
              ? 'border-b border-gray-700 hover:bg-gray-700'
              : 'border-b hover:bg-gray-50'"
          >
            <td class="px-2 py-2">{{ formatearFecha(inv.fecha_inv) }}</td>
            <td class="px-2 py-2">
              <span
                :class="inv.tipo_movimiento ? 'text-green-400 font-semibold' : 'text-red-400 font-semibold'"
              >
                {{ inv.tipo_movimiento ? 'Entrada' : 'Salida' }}
              </span>
            </td>
            <td class="px-2 py-2">{{ inv.cantidad_inv }}</td>
            <td class="px-2 py-2">
              {{ inv.producto ? inv.producto.nombre_prod : '' }}
            </td>
            <td class="px-2 py-2">
              <span v-if="inv.unidad">
                {{ inv.unidad.nombre_unidad }}
                ({{ inv.unidad.abreviatura_unidad }})
              </span>
            </td>
            <td class="px-2 py-2 space-x-2 text-center">
              <button
                @click="editarInventario(inv)"
                class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs font-semibold"
              >
                ✏️ Editar
              </button>
              <button
                @click="eliminarInventario(inv.id_inventario)"
                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs font-semibold"
              >
                🗑 Eliminar
              </button>
              <button
                @click="verDetalles(inv)"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded text-xs font-semibold"
              >
                📄 Detalles
              </button>
            </td>
          </tr>
          <tr v-if="!inventarios.length">
            <td
              colspan="6"
              class="text-center py-3"
              :class="props.darkMode ? 'text-gray-300' : 'text-gray-500'"
            >
              No hay movimientos registrados con el filtro actual
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
          <b>{{ inventarios.length }}</b>
          movimientos
        </div>

        <div class="flex flex-wrap items-center gap-1">
          <button
            class="px-2 py-1 rounded border"
            :class="props.darkMode ? 'bg-gray-800 border-gray-600 text-gray-100' : 'bg-white border-gray-300 text-gray-700'"
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
            :class="props.darkMode ? 'bg-gray-800 border-gray-600 text-gray-100' : 'bg-white border-gray-300 text-gray-700'"
            @click="nextPage"
            :disabled="currentPage === totalPages"
          >
            ➡
          </button>
        </div>
      </div>
    </div>

    <!-- DETALLES: UNIDAD DE MEDIDA DEL MOVIMIENTO -->
    <div v-if="inventarioSeleccionado" class="border-t pt-4 mt-4 mb-6">
      <h3 class="text-lg font-bold text-blue-500 mb-2">
        Detalles de unidad de medida del movimiento #
        {{ inventarioSeleccionado.id_inventario }}
      </h3>

      <div v-if="unidadDetalle" class="overflow-x-auto">
        <table class="w-full border border-gray-200 rounded-lg text-sm text-center">
          <thead class="bg-indigo-600 text-white">
            <tr>
              <th class="px-2 py-2 border border-indigo-500/60">Nombre</th>
              <th class="px-2 py-2 border border-indigo-500/60">Abreviatura</th>
              <th class="px-2 py-2 border border-indigo-500/60">Descripción</th>
              <th class="px-2 py-2 border border-indigo-500/60">Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr :class="props.darkMode ? 'border-b border-gray-700' : 'border-b'">
              <td class="px-2 py-2">{{ unidadDetalle.nombre_unidad }}</td>
              <td class="px-2 py-2">{{ unidadDetalle.abreviatura_unidad }}</td>
              <td class="px-2 py-2">{{ unidadDetalle.descripcion_unidad }}</td>
              <td class="px-2 py-2">
                <span
                  :class="unidadDetalle.estado_unidad ? 'text-green-400 font-semibold' : 'text-red-400 font-semibold'"
                >
                  {{ unidadDetalle.estado_unidad ? 'Activo' : 'Inactivo' }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-else :class="props.darkMode ? 'text-gray-300' : 'text-gray-500'">
        No se encontró información de la unidad de medida.
      </p>
    </div>

    <!-- PANEL UNIDADES DE MEDIDA -->
    <div v-if="mostrarUnidades" class="border-t pt-6 mt-4">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold text-blue-500">Unidades de medida</h3>
        <button @click="toggleListaUnidades" class="btn-gray">
          {{ mostrarListaUnidades ? 'Ocultar lista' : 'Listar unidades' }}
        </button>
      </div>

      <!-- FORM UNIDAD -->
      <form @submit.prevent="guardarUnidad" class="grid grid-cols-2 gap-4 mb-6">
        <div>
          <label class="block text-sm font-semibold mb-1">Nombre unidad</label>
          <input
            v-model="formUni.nombre_unidad"
            :class="[
              inputBaseClass,
              props.darkMode
                ? 'bg-gray-900 border-gray-600 text-gray-100'
                : 'bg-white border-gray-300 text-gray-900'
            ]"
          />
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">Abreviatura</label>
          <input
            v-model="formUni.abreviatura_unidad"
            :class="[
              inputBaseClass,
              props.darkMode
                ? 'bg-gray-900 border-gray-600 text-gray-100'
                : 'bg-white border-gray-300 text-gray-900'
            ]"
          />
        </div>

        <div class="col-span-2">
          <label class="block text-sm font-semibold mb-1">Descripción</label>
          <textarea
            v-model="formUni.descripcion_unidad"
            rows="2"
            :class="[
              inputBaseClass,
              props.darkMode
                ? 'bg-gray-900 border-gray-600 text-gray-100 placeholder-gray-400'
                : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400'
            ]"
          ></textarea>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">Estado</label>
          <select
            v-model="formUni.estado_unidad"
            :class="[
              inputBaseClass,
              props.darkMode
                ? 'bg-gray-900 border-gray-600 text-gray-100'
                : 'bg-white border-gray-300 text-gray-900'
            ]"
          >
            <option :value="true">Activo</option>
            <option :value="false">Inactivo</option>
          </select>
        </div>

        <div class="col-span-2 flex justify-end gap-2">
          <button type="submit" class="btn-indigo">
            {{ editandoUni ? 'Actualizar unidad' : 'Registrar unidad' }}
          </button>
          <button
            v-if="editandoUni"
            type="button"
            @click="cancelarEdicionUnidad"
            class="btn-gray"
          >
            Cancelar
          </button>
        </div>
      </form>

      <!-- LISTA UNIDADES -->
      <div v-if="mostrarListaUnidades" class="overflow-x-auto">
        <table class="w-full border border-gray-200 rounded-lg text-sm text-center">
          <thead class="bg-gray-700 text-white">
            <tr>
              <th class="px-2 py-2 border border-gray-600">Nombre</th>
              <th class="px-2 py-2 border border-gray-600">Abreviatura</th>
              <th class="px-2 py-2 border border-gray-600">Descripción</th>
              <th class="px-2 py-2 border border-gray-600">Estado</th>
              <th class="px-2 py-2 border border-gray-600">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="u in unidades"
              :key="u.id_unidad"
              :class="props.darkMode
                ? 'border-b border-gray-700 hover:bg-gray-700'
                : 'border-b hover:bg-gray-50'"
            >
              <td class="px-2 py-2">{{ u.nombre_unidad }}</td>
              <td class="px-2 py-2">{{ u.abreviatura_unidad }}</td>
              <td class="px-2 py-2">{{ u.descripcion_unidad }}</td>
              <td class="px-2 py-2">
                <span
                  :class="u.estado_unidad ? 'text-green-400 font-semibold' : 'text-red-400 font-semibold'"
                >
                  {{ u.estado_unidad ? 'Activo' : 'Inactivo' }}
                </span>
              </td>
              <td class="px-2 py-2 space-x-2">
                <button
                  @click="editarUnidad(u)"
                  class="text-blue-500 text-sm font-semibold"
                >
                  ✏️
                </button>
                <button
                  @click="eliminarUnidad(u.id_unidad)"
                  class="text-red-500 text-sm font-semibold"
                >
                  🗑
                </button>
              </td>
            </tr>
            <tr v-if="!unidades.length">
              <td
                colspan="5"
                class="text-center py-3"
                :class="props.darkMode ? 'text-gray-300' : 'text-gray-500'"
              >
                No hay unidades registradas
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'

/* ===== PROPS (modo oscuro + fuente) ===== */
const props = defineProps({
  darkMode: { type: Boolean, default: false },
  fontClass: { type: String, default: 'font-sans' },
})

/* ===== clase base de inputs ===== */
const inputBaseClass =
  'rounded-lg p-2 w-full focus:ring-2 focus:ring-blue-400 focus:outline-none transition-colors duration-200'

// INVENTARIO
const inventarios = ref([])
const productos = ref([])
const unidades = ref([])

const tipoFiltro = ref('')
const mostrarLista = ref(false)

const editandoInv = ref(false)
const idEditarInv = ref(null)

const formInv = ref({
  id_inventario: '',
  tipo_movimiento: '',
  cantidad_inv: '',
  fecha_inv: '',
  id_producto: '',
  id_unidad: '',
})

const inventarioSeleccionado = ref(null)
const unidadDetalle = ref(null)

// UNIDADES PANEL
const mostrarUnidades = ref(false)
const mostrarListaUnidades = ref(false)

const editandoUni = ref(false)
const idEditarUni = ref(null)

const formUni = ref({
  nombre_unidad: '',
  abreviatura_unidad: '',
  descripcion_unidad: '',
  estado_unidad: true,
})

/* ===== PAGINACIÓN INVENTARIO ===== */
const currentPage = ref(1)
const pageSize = ref(5)

const totalPages = computed(() =>
  inventarios.value.length
    ? Math.ceil(inventarios.value.length / pageSize.value)
    : 1
)

const paginatedInventarios = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return inventarios.value.slice(start, start + pageSize.value)
})

const pageInfo = computed(() => {
  if (!inventarios.value.length) return { from: 0, to: 0 }
  const from = (currentPage.value - 1) * pageSize.value + 1
  const to = Math.min(currentPage.value * pageSize.value, inventarios.value.length)
  return { from, to }
})

watch(inventarios, () => {
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

/* ======== helpers ======== */
function formatearFecha(valor) {
  if (!valor) return ''
  return String(valor).split('T')[0]
}

/* ========= CARGA PRINCIPAL ========= */
onMounted(() => {
  cargarInventario()
})

function cargarInventario(params = {}) {
  return axios
    .get('propietario/Construccion/inventario', { params })
    .then((res) => {
      inventarios.value = res.data.inventarios || []
      productos.value = res.data.productos || []
      unidades.value = res.data.unidades || []
      currentPage.value = 1
    })
    .catch((err) => {
      console.error('Error al obtener inventario:', err)
      alert('Error al obtener movimientos de inventario')
    })
}

function buscarPorTipo() {
  const params = {}
  if (tipoFiltro.value) params.tipo = tipoFiltro.value
  cargarInventario(params).then(() => {
    mostrarLista.value = true
  })
}

function toggleLista() {
  mostrarLista.value = !mostrarLista.value
  if (mostrarLista.value) {
    buscarPorTipo()
  }
}

/* ========= CRUD INVENTARIO ========= */
function guardarInventario() {
  const payload = { ...formInv.value }

  const url = editandoInv.value
    ? `propietario/Construccion/inventario/${idEditarInv.value}`
    : 'propietario/Construccion/inventario'

  axios
    .post(url, payload)
    .then((res) => {
      if (res.data.success) {
        alert(editandoInv.value ? 'Movimiento actualizado' : 'Movimiento registrado')
        limpiarInventario()
        buscarPorTipo()
      }
    })
    .catch((err) => {
      console.error('Error al guardar inventario:', err)
      alert('Error al guardar movimiento de inventario')
    })
}

function editarInventario(inv) {
  editandoInv.value = true
  idEditarInv.value = inv.id_inventario

  formInv.value = {
    id_inventario: inv.id_inventario,
    tipo_movimiento: inv.tipo_movimiento ? 'entrada' : 'salida',
    cantidad_inv: inv.cantidad_inv,
    fecha_inv: formatearFecha(inv.fecha_inv),
    id_producto: inv.id_producto,
    id_unidad: inv.id_unidad,
  }
}

function eliminarInventario(id) {
  if (!confirm('¿Eliminar este movimiento de inventario?')) return

  axios
    .delete(`propietario/Construccion/inventario/${id}`)
    .then(() => {
      alert('Movimiento eliminado')
      buscarPorTipo()
      if (inventarioSeleccionado.value && inventarioSeleccionado.value.id_inventario === id) {
        inventarioSeleccionado.value = null
        unidadDetalle.value = null
      }
    })
    .catch((err) => {
      console.error('Error al eliminar inventario:', err)
      alert('Error al eliminar movimiento')
    })
}

function limpiarInventario() {
  editandoInv.value = false
  idEditarInv.value = null
  formInv.value = {
    id_inventario: '',
    tipo_movimiento: '',
    cantidad_inv: '',
    fecha_inv: '',
    id_producto: '',
    id_unidad: '',
  }
}

function cancelarEdicionInventario() {
  limpiarInventario()
}

function verDetalles(inv) {
  inventarioSeleccionado.value = inv
  if (!inv || !inv.id_unidad) {
    unidadDetalle.value = null
    return
  }
  const u = unidades.value.find((x) => x.id_unidad === inv.id_unidad)
  unidadDetalle.value = u || null
}

/* ========= PANEL UNIDADES ========= */
function toggleUnidades() {
  mostrarUnidades.value = !mostrarUnidades.value
  if (mostrarUnidades.value && !unidades.value.length) {
    cargarUnidades()
  }
}

function toggleListaUnidades() {
  mostrarListaUnidades.value = !mostrarListaUnidades.value
  if (mostrarListaUnidades.value && !unidades.value.length) {
    cargarUnidades()
  }
}

function cargarUnidades(params = {}) {
  return axios
    .get('propietario/Construccion/unidad-medida', { params })
    .then((res) => {
      unidades.value = res.data.unidades || []
    })
    .catch((err) => {
      console.error('Error al obtener unidades:', err)
      alert('Error al obtener unidades de medida')
    })
}

function guardarUnidad() {
  const payload = { ...formUni.value }

  const url = editandoUni.value
    ? `propietario/Construccion/unidad-medida/${idEditarUni.value}`
    : 'propietario/Construccion/unidad-medida'

  axios
    .post(url, payload)
    .then((res) => {
      if (res.data.success) {
        alert(editandoUni.value ? 'Unidad actualizada' : 'Unidad registrada')
        limpiarUnidad()
        cargarUnidades()
      }
    })
    .catch((err) => {
      console.error('Error al guardar unidad:', err)
      alert('Error al guardar unidad de medida')
    })
}

function editarUnidad(u) {
  editandoUni.value = true
  idEditarUni.value = u.id_unidad

  formUni.value = {
    nombre_unidad: u.nombre_unidad,
    abreviatura_unidad: u.abreviatura_unidad,
    descripcion_unidad: u.descripcion_unidad,
    estado_unidad: !!u.estado_unidad,
  }
}

function eliminarUnidad(id) {
  if (!confirm('¿Eliminar esta unidad de medida?')) return

  axios
    .delete(`propietario/Construccion/unidad-medida/${id}`)
    .then(() => {
      alert('Unidad eliminada')
      cargarUnidades()
    })
    .catch((err) => {
      console.error('Error al eliminar unidad:', err)
      alert('Error al eliminar unidad de medida')
    })
}

function limpiarUnidad() {
  editandoUni.value = false
  idEditarUni.value = null
  formUni.value = {
    nombre_unidad: '',
    abreviatura_unidad: '',
    descripcion_unidad: '',
    estado_unidad: true,
  }
}

function cancelarEdicionUnidad() {
  limpiarUnidad()
}
</script>

<style scoped>
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
