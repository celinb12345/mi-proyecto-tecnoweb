<template>
  <div
    :class="[
      'p-8 rounded-2xl shadow-md transition-colors duration-300',
      props.darkMode ? 'bg-gray-800 text-gray-100' : 'bg-white text-gray-900',
      props.fontClass
    ]"
  >
    <h2 class="text-2xl font-bold text-blue-500 mb-4">Gestión de Clientes</h2>

    <!-- 🔍 BUSCADOR -->
    <div class="flex mb-4 gap-2">
      <input
        type="text"
        v-model="buscarTexto"
        placeholder="🔍 Buscar por nombre, apellido o ID usuario..."
        :class="[
          'flex-grow',
          inputBaseClass,
          props.darkMode
            ? 'bg-gray-900 border-gray-600 text-gray-100 placeholder-gray-400'
            : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400'
        ]"
      />
      <button
        @click="buscarCliente"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-sm"
      >
        Buscar
      </button>
    </div>

    <!-- 🧾 FORMULARIO -->
    <form @submit.prevent="guardarCliente" class="grid grid-cols-2 gap-4">
      <div>
        <label class="text-sm font-semibold mb-1 block">ID Usuario (cliente)</label>
        <select
          v-model="form.id_usuario"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-900'
          ]"
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
        <p class="text-xs text-gray-400 mt-1">
          Se muestran solo usuarios del tipo <b>cli_m</b> que aún no están asignados a un cliente.
        </p>
      </div>

      <div>
        <label class="text-sm font-semibold mb-1 block">Nombre</label>
        <input
          type="text"
          v-model="form.nombre_cli"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100 placeholder-gray-400'
              : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400'
          ]"
        />
      </div>

      <div>
        <label class="text-sm font-semibold mb-1 block">Apellido</label>
        <input
          type="text"
          v-model="form.apellido_cli"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100 placeholder-gray-400'
              : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400'
          ]"
        />
      </div>

      <div>
        <label class="text-sm font-semibold mb-1 block">Teléfono</label>
        <input
          type="text"
          v-model="form.telefono_cli"
          placeholder="+591 ..."
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100 placeholder-gray-400'
              : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400'
          ]"
        />
      </div>

      <div>
        <label class="text-sm font-semibold mb-1 block">Correo</label>
        <input
          type="email"
          v-model="form.correo_cli"
          placeholder="correo@ejemplo.com"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100 placeholder-gray-400'
              : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400'
          ]"
        />
      </div>

      <div class="col-span-2 mt-2 flex flex-wrap justify-end gap-2">
        <button
          type="submit"
          class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow-sm"
        >
          {{ editando ? 'Actualizar' : 'Registrar' }}
        </button>

        <button
          type="button"
          @click="toggleLista"
          class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow-sm"
        >
          {{ mostrarLista ? 'Ocultar lista' : 'Listar' }}
        </button>

        <button
          type="button"
          @click="limpiarFormulario"
          class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg shadow-sm"
        >
          Limpiar
        </button>

        <button
          v-if="editando"
          type="button"
          @click="cancelarEdicion"
          class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg shadow-sm"
        >
          Cancelar edición
        </button>
      </div>
    </form>

    <!-- 🧠 MENSAJE -->
    <p
      v-if="mensaje"
      :class="['mt-4 font-semibold text-sm', mensajeColor]"
    >
      {{ mensaje }}
    </p>

    <!-- 📋 TABLA -->
    <div v-if="mostrarLista && clientes.length" class="mt-6 overflow-x-auto">
      <h3 class="font-bold mb-2">Clientes Registrados</h3>

      <table class="min-w-full border border-gray-300 text-center text-sm">
        <!-- 🔵 Encabezado azul -->
        <thead :class="props.darkMode ? 'bg-blue-800 text-white' : 'bg-blue-600 text-white'">
          <tr>
            <th class="border border-blue-500/50 p-2">Nombre</th>
            <th class="border border-blue-500/50 p-2">Apellido</th>
            <th class="border border-blue-500/50 p-2">Teléfono</th>
            <th class="border border-blue-500/50 p-2">Correo</th>
            <th class="border border-blue-500/50 p-2">Acciones</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="c in paginatedClientes"
            :key="c.id_cliente"
            :class="props.darkMode ? 'hover:bg-gray-700' : 'hover:bg-gray-50'"
          >
            <td class="border p-2">{{ c.nombre_cli }}</td>
            <td class="border p-2">{{ c.apellido_cli }}</td>
            <td class="border p-2">{{ c.telefono_cli || '-' }}</td>
            <td class="border p-2">{{ c.correo_cli }}</td>
            <td class="border p-2 space-x-2">
              <button
                @click="editarCliente(c)"
                class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs"
              >
                Editar
              </button>
              <button
                @click="eliminarCliente(c)"
                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs"
              >
                Eliminar
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- 📄 PAGINACIÓN -->
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
          <b>{{ clientes.length }}</b>
          clientes
        </div>

        <div class="flex flex-wrap items-center gap-1">
          <button
            class="px-2 py-1 rounded border"
            :class="props.darkMode ? 'bg-gray-800 border-gray-600' : 'bg-white border-gray-300'"
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
            :class="props.darkMode ? 'bg-gray-800 border-gray-600' : 'bg-white border-gray-300'"
            @click="nextPage"
            :disabled="currentPage === totalPages"
          >
            ➡
          </button>
        </div>
      </div>
    </div>

    <!-- Mensaje cuando no hay clientes -->
    <div
      v-else-if="mostrarLista && !clientes.length"
      class="mt-6 text-sm"
      :class="props.darkMode ? 'text-gray-300' : 'text-gray-600'"
    >
      No hay clientes registrados.
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'

// 🌓 props desde el Dashboard
const props = defineProps({
  darkMode: { type: Boolean, default: false },
  fontClass: { type: String, default: 'font-sans' }
})

const inputBaseClass =
  'rounded-lg p-2 w-full focus:ring-2 focus:ring-blue-400 focus:outline-none transition-colors duration-200'

const form = ref({
  id_cliente: '',
  id_usuario: '',
  nombre_cli: '',
  apellido_cli: '',
  telefono_cli: '',
  correo_cli: '',
})

const clientes = ref([])
const usuarios = ref([])          // usuarios del propietario
const buscarTexto = ref('')
const mensaje = ref('')
const mensajeColor = ref('text-green-600')
const mostrarLista = ref(false)
const editando = ref(false)

// 🔎 usuarios disponibles para clientes: cli_m* que no estén usados
const usuariosDisponibles = computed(() => {
  const usados = new Set(
    clientes.value
      .map(c => c.id_usuario)
      .filter(id => id)
  )
  const actual = form.value.id_usuario

  return usuarios.value.filter(u => {
    const esCliente = u.id_usuario.startsWith('cli_m')
    if (!esCliente) return false
    if (editando.value && u.id_usuario === actual) return true
    return !usados.has(u.id_usuario)
  })
})

/* ---------- PAGINACIÓN ---------- */
const currentPage = ref(1)
const pageSize = ref(5)

const totalPages = computed(() =>
  clientes.value.length
    ? Math.ceil(clientes.value.length / pageSize.value)
    : 1
)

const paginatedClientes = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return clientes.value.slice(start, start + pageSize.value)
})

const pageInfo = computed(() => {
  if (!clientes.value.length) return { from: 0, to: 0 }
  const from = (currentPage.value - 1) * pageSize.value + 1
  const to = Math.min(currentPage.value * pageSize.value, clientes.value.length)
  return { from, to }
})

watch(clientes, () => {
  if (currentPage.value > totalPages.value) {
    currentPage.value = totalPages.value
  }
})

function goToPage(page) {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
  }
}
function prevPage() {
  if (currentPage.value > 1) currentPage.value--
}
function nextPage() {
  if (currentPage.value < totalPages.value) currentPage.value++
}

/* ---------- CRUD / BÚSQUEDA ---------- */

// 📋 Listar clientes
async function listarClientes(params = {}) {
  try {
    const res = await axios.get('propietario/melamina/clientes', { params })
    clientes.value = res.data.clientes || []
    currentPage.value = 1
    mensaje.value = 'Lista de clientes actualizada'
    mensajeColor.value = 'text-green-600'
  } catch (error) {
    console.error(error)
    mensaje.value = 'Error al conectar con el servidor'
    mensajeColor.value = 'text-red-600'
  }
}

// 📋 Listar / ocultar tabla
async function toggleLista() {
  if (!mostrarLista.value) {
    await listarClientes()
    mostrarLista.value = true
  } else {
    mostrarLista.value = false
  }
}

// ➕ Registrar o actualizar según modo
async function guardarCliente() {
  if (editando.value && form.value.id_cliente) {
    return actualizarCliente()
  }
  return registrarCliente()
}

// ➕ Registrar
async function registrarCliente() {
  try {
    const res = await axios.post('propietario/melamina/clientes', form.value)
    if (res.data.success) {
      mensaje.value = res.data.message
      mensajeColor.value = 'text-green-600'
      limpiarFormulario()
      if (mostrarLista.value) await listarClientes()
    } else {
      mensaje.value = res.data.message || 'Error al registrar cliente'
      mensajeColor.value = 'text-red-600'
    }
  } catch (error) {
    console.error(error)
    mensaje.value = 'Error al conectar con el servidor'
    mensajeColor.value = 'text-red-600'
  }
}

// ✏️ Actualizar
async function actualizarCliente() {
  try {
    const id = form.value.id_cliente
    const payload = {
      nombre_cli: form.value.nombre_cli,
      apellido_cli: form.value.apellido_cli,
      telefono_cli: form.value.telefono_cli,
      correo_cli: form.value.correo_cli,
    }

    const res = await axios.put(`propietario/melamina/clientes/${id}`, payload)

    if (res.data.success) {
      mensaje.value = res.data.message
      mensajeColor.value = 'text-green-600'
      editando.value = false
      limpiarFormulario()
      if (mostrarLista.value) await listarClientes()
    } else {
      mensaje.value = res.data.message || 'Error al actualizar cliente'
      mensajeColor.value = 'text-red-600'
    }
  } catch (error) {
    console.error(error)
    mensaje.value = 'Error al conectar con el servidor'
    mensajeColor.value = 'text-red-600'
  }
}

// ✏️ Poner cliente en modo edición
function editarCliente(c) {
  form.value = { ...c }
  editando.value = true
  mensaje.value = `Editando cliente: ${c.nombre_cli} ${c.apellido_cli}`
  mensajeColor.value = 'text-blue-600'
}

// ❌ Cancelar edición
function cancelarEdicion() {
  editando.value = false
  limpiarFormulario()
  mensaje.value = 'Edición cancelada'
  mensajeColor.value = 'text-gray-500'
}

// 🔍 Buscar
async function buscarCliente() {
  if (!buscarTexto.value.trim()) {
    mensaje.value = 'Ingrese texto para buscar'
    mensajeColor.value = 'text-red-600'
    return
  }
  await listarClientes({ buscar: buscarTexto.value })
  mostrarLista.value = true
  if (clientes.value.length) {
    mensaje.value = `${clientes.value.length} resultado(s) encontrado(s)`
    mensajeColor.value = 'text-green-600'
  } else {
    mensaje.value = 'No se encontraron coincidencias'
    mensajeColor.value = 'text-red-600'
  }
}

// 🧹 Limpiar formulario
function limpiarFormulario() {
  form.value = {
    id_cliente: '',
    id_usuario: '',
    nombre_cli: '',
    apellido_cli: '',
    telefono_cli: '',
    correo_cli: '',
  }
}

// 🗑️ Eliminar cliente
async function eliminarCliente(c) {
  if (!confirm(`¿Eliminar al cliente "${c.nombre_cli} ${c.apellido_cli}"?`)) {
    return
  }
  try {
    const res = await axios.delete(`propietario/melamina/clientes/${c.id_cliente}`)
    if (res.data.success) {
      mensaje.value = res.data.message
      mensajeColor.value = 'text-green-600'
      if (mostrarLista.value) await listarClientes()
      if (editando.value && form.value.id_cliente === c.id_cliente) {
        cancelarEdicion()
      }
    }
  } catch (error) {
    console.error(error)
    mensaje.value = 'Error al eliminar cliente'
    mensajeColor.value = 'text-red-600'
  }
}

// 🔄 cargar usuarios del propietario
async function cargarUsuarios() {
  try {
    const res = await axios.get('propietario/melamina/usuarios')
    usuarios.value = res.data.usuarios || []
  } catch (error) {
    console.error('Error al cargar usuarios:', error)
  }
}

onMounted(async () => {
  await Promise.all([listarClientes(), cargarUsuarios()])
})
</script>

<style scoped>
/* nada especial aquí, todo el estilo está con clases Tailwind */
</style>
