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
      Gestión de Proveedores
    </h2>

    <!-- BUSCADOR -->
    <div class="flex gap-4 mb-6">
      <input
        v-model="buscar"
        type="text"
        placeholder="🔍 Buscar por nombre, tipo, teléfono o correo..."
        class="input flex-1"
        :class="inputClass"
      />
      <button @click="buscarProveedores" class="btn-blue">Buscar</button>
      <button @click="toggleLista" class="btn-gray">
        {{ mostrarLista ? 'Ocultar Lista' : 'Listar' }}
      </button>
    </div>

    <!-- FORMULARIO -->
    <form @submit.prevent="guardar" class="grid grid-cols-2 gap-4 mb-6">
      <!-- id_proveedor oculto (solo en data) -->

      <div>
        <label class="block text-sm font-semibold mb-1">ID Usuario (proveedor)</label>
        <select
          v-model="form.id_usuario"
          class="input"
          :class="inputClass"
          :disabled="editando"
        >
          <option disabled value="">Seleccione usuario</option>
          <option
            v-for="u in usuariosDisponibles"
            :key="u.id_usuario"
            :value="u.id_usuario"
          >
            {{ u.id_usuario }}
          </option>
        </select>
        <p
          class="text-xs mt-1"
          :class="darkMode ? 'text-gray-300' : 'text-gray-500'"
        >
          Se muestran solo usuarios <b>prov_m</b> que aún no están asignados a un proveedor.
        </p>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Nombre Empresa</label>
        <input
          type="text"
          v-model="form.nombre_empres_prov"
          class="input"
          :class="inputClass"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Tipo de Materiales</label>
        <input
          type="text"
          v-model="form.tipo_materiales_prov"
          class="input"
          :class="inputClass"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Teléfono</label>
        <input
          type="text"
          v-model="form.telefono_prov"
          class="input"
          :class="inputClass"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Correo</label>
        <input
          type="email"
          v-model="form.correo_prov"
          class="input"
          :class="inputClass"
        />
      </div>

      <div class="col-span-2">
        <label class="block text-sm font-semibold mb-1">Dirección</label>
        <input
          type="text"
          v-model="form.direccion_prov"
          class="input"
          :class="inputClass"
        />
      </div>

      <!-- CÓDIGO QR -->
      <div class="col-span-2">
        <label class="block text-sm font-semibold mb-1">Código QR</label>
        <input
          type="file"
          ref="qrInput"
          @change="onFileChange"
          class="input"
          :class="[
            darkMode
              ? 'bg-gray-900 border-gray-700 text-gray-100 file:bg-blue-600 file:text-white'
              : 'bg-white border-gray-300 text-gray-900 file:bg-blue-600 file:text-white'
          ]"
        />
        <div v-if="previewQR" class="mt-2 flex items-center gap-2">
          <img
            :src="previewQR"
            class="w-24 h-24 rounded-lg object-cover"
            :class="darkMode ? 'border border-gray-600' : 'border border-gray-300'"
          />
          <button
            type="button"
            @click="quitarQR"
            class="text-red-500 text-sm font-semibold"
          >
            ✖ Quitar
          </button>
        </div>
      </div>

      <div class="col-span-2 flex justify-end gap-2 mt-2">
        <button type="submit" class="btn-green">
          {{ editando ? 'Actualizar' : 'Registrar' }}
        </button>
        <button
          v-if="editando"
          type="button"
          @click="cancelarEdicion"
          class="btn-gray"
        >
          Cancelar
        </button>
      </div>
    </form>

    <!-- LISTA -->
    <div v-if="mostrarLista && proveedores.length" class="overflow-x-auto">
      <table
        :class="[
          'w-full rounded-lg text-sm text-center',
          darkMode ? 'border border-gray-700' : 'border border-gray-200'
        ]"
      >
        <thead class="bg-blue-600 text-white">
          <tr>
            <!-- ya NO mostramos ID -->
            <th class="px-2 py-2">Empresa</th>
            <th class="px-2 py-2">Tipo Materiales</th>
            <th class="px-2 py-2">Teléfono</th>
            <th class="px-2 py-2">Correo</th>
            <th class="px-2 py-2">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="p in paginatedProveedores"
            :key="p.id_proveedor"
            :class="[
              'border-b',
              darkMode
                ? 'border-gray-700 hover:bg-gray-800'
                : 'border-gray-200 hover:bg-gray-50'
            ]"
          >
            <td class="px-2 py-2">{{ p.nombre_empres_prov }}</td>
            <td class="px-2 py-2">{{ p.tipo_materiales_prov }}</td>
            <td class="px-2 py-2">{{ p.telefono_prov }}</td>
            <td class="px-2 py-2">{{ p.correo_prov }}</td>
            <td class="px-2 py-2 space-x-2">
              <button
                @click="editar(p)"
                class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs"
              >
                ✏️ Editar
              </button>
              <button
                @click="eliminar(p.id_proveedor)"
                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs"
              >
                🗑 Eliminar
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- PAGINACIÓN -->
      <div
        v-if="proveedores.length"
        class="flex flex-col sm:flex-row justify-between items-center gap-3 mt-4 text-sm"
      >
        <span :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
          Mostrando
          <b>{{ startItem }}</b>
          -
          <b>{{ endItem }}</b>
          de
          <b>{{ proveedores.length }}</b>
          proveedores
        </span>

        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="prevPage"
            :disabled="currentPage === 1"
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
            Página {{ currentPage }} de {{ totalPages }}
          </span>

          <button
            type="button"
            @click="nextPage"
            :disabled="currentPage === totalPages || totalPages === 0"
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

    <div
      v-else-if="mostrarLista && !proveedores.length"
      class="mt-4 text-center text-sm"
      :class="darkMode ? 'text-gray-300' : 'text-gray-500'"
    >
      No hay proveedores con el criterio actual
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'

const storageUrl = (path) => {
  if (!path) return null
  // Si ya viene con http/https, la dejamos tal cual
  if (path.startsWith('http')) return path

  const appUrl = import.meta.env.VITE_APP_URL.replace(/\/$/, '') 
  const projectRoot = appUrl.replace('/public', '')             

  return `${projectRoot}/storage/app/public/${path}`
}



/* Props para modo oscuro y fuente desde el dashboard */
const props = defineProps({
  darkMode: { type: Boolean, default: false },
  fontClass: { type: String, default: 'font-sans' }
})

const darkMode = computed(() => props.darkMode)
const fontClass = computed(() => props.fontClass)

/* STATE */
const proveedores = ref([])
const usuarios = ref([]) // usuarios del propietario
const mostrarLista = ref(false)
const buscar = ref('')
const editando = ref(false)
const idEditar = ref(null)

const form = ref({
  id_proveedor: '',
  id_usuario: '',
  nombre_empres_prov: '',
  tipo_materiales_prov: '',
  telefono_prov: '',
  correo_prov: '',
  direccion_prov: '',
  codigoqr: null // archivo
})

const previewQR = ref(null)
const qrInput = ref(null)

/* PAGINACIÓN */
const currentPage = ref(1)
const perPage = ref(10)

const totalPages = computed(() =>
  proveedores.value.length
    ? Math.ceil(proveedores.value.length / perPage.value)
    : 0
)

const paginatedProveedores = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  return proveedores.value.slice(start, start + perPage.value)
})

const startItem = computed(() => {
  if (!proveedores.value.length) return 0
  return (currentPage.value - 1) * perPage.value + 1
})

const endItem = computed(() => {
  if (!proveedores.value.length) return 0
  return Math.min(currentPage.value * perPage.value, proveedores.value.length)
})

function nextPage () {
  if (currentPage.value < totalPages.value) currentPage.value++
}

function prevPage () {
  if (currentPage.value > 1) currentPage.value--
}

/* Reset página cuando cambia la lista */
watch(proveedores, () => {
  currentPage.value = 1
})

/* usuarios prov_m* que no estén en proveedor (salvo el actual si estamos editando) */
const usuariosDisponibles = computed(() => {
  const usados = new Set(
    proveedores.value
      .map(p => p.id_usuario)
      .filter(id => id)
  )

  const actual = form.value.id_usuario

  return usuarios.value.filter(u => {
    const esProveedor = u.id_usuario.startsWith('prov_m')
    if (!esProveedor) return false

    if (editando.value && u.id_usuario === actual) return true

    return !usados.has(u.id_usuario)
  })
})

/* Clase de inputs para modo claro/oscuro */
const inputClass = computed(() =>
  darkMode.value
    ? 'bg-gray-900 border-gray-700 text-gray-100 placeholder-gray-400'
    : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400'
)

function onFileChange (e) {
  const file = e.target.files[0]
  if (!file) return
  form.value.codigoqr = file
  previewQR.value = URL.createObjectURL(file)
}

function quitarQR () {
  form.value.codigoqr = null
  previewQR.value = null
  if (qrInput.value) qrInput.value.value = null
}

async function guardar () {
  const fd = new FormData()
  fd.append('id_usuario', form.value.id_usuario || '')
  fd.append('nombre_empres_prov', form.value.nombre_empres_prov)
  fd.append('tipo_materiales_prov', form.value.tipo_materiales_prov)
  fd.append('telefono_prov', form.value.telefono_prov || '')
  fd.append('correo_prov', form.value.correo_prov || '')
  fd.append('direccion_prov', form.value.direccion_prov || '')
  if (form.value.codigoqr) fd.append('codigoqr', form.value.codigoqr)

  const url = editando.value
    ? `propietario/melamina/proveedores/${idEditar.value}`
    : 'propietario/melamina/proveedores'

  try {
    const res = await axios.post(url, fd, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    if (res.data.success) {
      alert(editando.value ? 'Proveedor actualizado' : 'Proveedor registrado')
      limpiar()
      await buscarProveedores() 
      await cargarUsuarios()      
    }
  } catch (e) {
    alert('Error al guardar proveedor')
  }
}


function editar (p) {
  editando.value = true
  idEditar.value = p.id_proveedor

  form.value.id_proveedor = p.id_proveedor
  form.value.id_usuario = p.id_usuario
  form.value.nombre_empres_prov = p.nombre_empres_prov
  form.value.tipo_materiales_prov = p.tipo_materiales_prov
  form.value.telefono_prov = p.telefono_prov
  form.value.correo_prov = p.correo_prov
  form.value.direccion_prov = p.direccion_prov
  form.value.codigoqr = null

  previewQR.value = storageUrl(p.codigoqr_prov)
  if (qrInput.value) qrInput.value.value = null
}

function cancelarEdicion () {
  editando.value = false
  limpiar()
}

function eliminar (id) {
  if (!confirm('¿Eliminar este proveedor?')) return
  axios
    .delete(`propietario/melamina/proveedores/${id}`)
    .then((res) => {
      alert(res.data.message || 'Proveedor eliminado')
      buscarProveedores()
    })
    .catch(() => alert('Error al eliminar proveedor'))
}

function limpiar () {
  editando.value = false
  idEditar.value = null
  previewQR.value = null
  if (qrInput.value) qrInput.value.value = null

  form.value = {
    id_proveedor: '',
    id_usuario: '',
    nombre_empres_prov: '',
    tipo_materiales_prov: '',
    telefono_prov: '',
    correo_prov: '',
    direccion_prov: '',
    codigoqr: null
  }
}

function buscarProveedores () {
  axios
    .get('propietario/melamina/proveedores', { params: { buscar: buscar.value } })
    .then((res) => {
      proveedores.value = res.data.proveedores || []
      mostrarLista.value = true
    })
    .catch(() => alert('Error al obtener proveedores'))
}

function toggleLista () {
  mostrarLista.value = !mostrarLista.value
  if (mostrarLista.value) buscarProveedores()
}

async function cargarUsuarios () {
  try {
    const res = await axios.get('propietario/melamina/usuarios')
    usuarios.value = res.data.usuarios || []
  } catch (e) {
    console.error('Error al cargar usuarios', e)
  }
}

onMounted(async () => {
  await Promise.all([cargarUsuarios(), buscarProveedores()])
})
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
</style>
