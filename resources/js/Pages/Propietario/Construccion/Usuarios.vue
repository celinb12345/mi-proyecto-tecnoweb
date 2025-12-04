<template>
  <div
    :class="[
      'p-8 rounded-2xl shadow-md transition-colors duration-300',
      darkMode ? 'bg-gray-900 text-gray-100' : 'bg-white text-gray-900',
      fontClass
    ]"
  >
    <h2
      class="text-2xl font-bold mb-6"
      :class="darkMode ? 'text-blue-400' : 'text-blue-700'"
    >
      Asignar acceso al sistema
    </h2>

    <!-- FORMULARIO -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
      <!-- ===== ID USUARIO + BOTONES ===== -->
      <div>
        <div class="flex items-center justify-between mb-1">
          <label class="block text-sm font-semibold">ID Usuario</label>

          <div class="flex gap-2">
            <button
              type="button"
              class="px-3 py-1 rounded-lg text-[11px] md:text-xs bg-blue-500 text-white hover:bg-emerald-600"
              @click="generarId('cli_c')"
              :disabled="editMode"
            >
              Crear ID Cliente
            </button>
            <button
              type="button"
              class="px-3 py-1 rounded-lg text-[11px] md:text-xs bg-blue-500 text-white hover:bg-blue-600"
              @click="generarId('trab_c')"
              :disabled="editMode"
            >
              Crear ID Trabajador
            </button>
            <button
              type="button"
              class="px-3 py-1 rounded-lg text-[11px] md:text-xs bg-blue-500 text-white hover:bg-amber-600"
              @click="generarId('prov_c')"
              :disabled="editMode"
            >
              Crear ID Proveedor
            </button>
          </div>
        </div>

        <input
          type="text"
          v-model="form.id_usuario"
          readonly
          class="input cursor-not-allowed"
          :class="[
            inputClass,
            darkMode
              ? 'bg-gray-800 text-gray-300 border-gray-700'
              : 'bg-gray-100 text-gray-700 border-gray-300'
          ]"
          placeholder="Ej: cli_c1, trab_c2, prov_c3..."
        />
        <p
          class="text-xs mt-1"
          :class="darkMode ? 'text-gray-300' : 'text-gray-500'"
        >
          El ID se genera con los botones según el tipo. Solo escribe la contraseña.
        </p>

        <p v-if="formErrors.id_usuario" class="text-red-500 text-xs mt-1">
          {{ formErrors.id_usuario }}
        </p>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Contraseña</label>
        <input
          type="password"
          v-model="form.contrasenia"
          class="input"
          :class="inputClass"
          placeholder="Escribe la contraseña"
        />
        <p v-if="formErrors.contrasenia" class="text-red-500 text-xs mt-1">
          {{ formErrors.contrasenia }}
        </p>
        <p
          v-if="editMode"
          class="text-xs mt-1"
          :class="darkMode ? 'text-gray-300' : 'text-gray-500'"
        >
          Deja la contraseña en blanco si no deseas cambiarla.
        </p>
      </div>
    </div>

    <!-- ESTADO -->
    <div class="flex items-center gap-3 mb-6">
      <span class="font-semibold">Estado:</span>
      <button
        type="button"
        @click="form.estado_usuario = !form.estado_usuario"
        class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors"
        :class="form.estado_usuario ? 'bg-green-500' : 'bg-gray-400'"
      >
        <span
          class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform"
          :class="form.estado_usuario ? 'translate-x-6' : 'translate-x-1'"
        />
      </button>
      <span :class="form.estado_usuario ? 'text-green-500' : 'text-gray-400'">
        {{ form.estado_usuario ? 'Activo' : 'Inactivo' }}
      </span>
    </div>

    <!-- BOTONES -->
    <div class="flex flex-wrap gap-2 mb-4">
      <button
        v-if="!editMode"
        @click="registrarUsuario"
        class="bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded-lg font-semibold text-xs md:text-sm"
      >
        Registrar
      </button>

      <button
        v-else
        @click="actualizarUsuario"
        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-1.5 rounded-lg font-semibold text-xs md:text-sm"
      >
        Actualizar
      </button>

      <button
        type="button"
        @click="toggleLista"
        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1.5 rounded-lg font-semibold text-xs md:text-sm"
      >
        {{ showLista ? 'Ocultar lista' : 'Listar' }}
      </button>

      <button
        type="button"
        @click="limpiarFormulario"
        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-1.5 rounded-lg font-semibold text-xs md:text-sm"
      >
        Limpiar
      </button>

      <button
        v-if="editMode"
        type="button"
        @click="cancelarEdicion"
        class="bg-red-500 hover:bg-red-600 text-white px-4 py-1.5 rounded-lg font-semibold text-xs md:text-sm"
      >
        Cancelar edición
      </button>
    </div>

    <!-- MENSAJE -->
    <p v-if="mensaje" :class="mensajeColor" class="mt-2 font-semibold">
      {{ mensaje }}
    </p>

    <!-- TABLA -->
    <div v-if="showLista && usuarios.length" class="mt-6 overflow-x-auto">
      <h3 class="font-bold mb-2">Usuarios con acceso al sistema</h3>
      <table
        class="w-full text-center text-sm rounded-lg overflow-hidden"
        :class="darkMode ? 'border border-gray-700' : 'border border-gray-300'"
      >
        <thead :class="darkMode ? 'bg-gray-800 text-gray-100' : 'bg-gray-100'">
          <tr>
            <th class="border p-2" :class="darkBorder">ID Usuario</th>
            <th class="border p-2" :class="darkBorder">Estado</th>
            <th class="border p-2" :class="darkBorder">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="u in paginatedUsuarios"
            :key="u.id_usuario"
            :class="[
              darkMode
                ? 'hover:bg-gray-800'
                : 'hover:bg-gray-50',
              darkBorder
            ]"
          >
            <td class="border p-2" :class="darkBorder">{{ u.id_usuario }}</td>
            <td class="border p-2" :class="darkBorder">
              <!-- switch de estado -->
              <button
                type="button"
                @click="toggleEstado(u)"
                class="relative inline-flex items-center h-5 rounded-full w-10 transition-colors"
                :class="u.estado_usuario ? 'bg-green-500' : 'bg-gray-400'"
              >
                <span
                  class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform"
                  :class="u.estado_usuario ? 'translate-x-5' : 'translate-x-1'"
                />
              </button>
              <span
                class="ml-2 text-xs md:text-sm"
                :class="u.estado_usuario ? 'text-green-500' : 'text-gray-400'"
              >
                {{ u.estado_usuario ? 'Activo' : 'Inactivo' }}
              </span>
            </td>
            <td class="border p-2 space-x-2" :class="darkBorder">
              <button
                @click="editarUsuario(u)"
                class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-[11px] md:text-xs"
              >
                Editar
              </button>
              <button
                @click="eliminarUsuario(u)"
                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-[11px] md:text-xs"
              >
                Eliminar
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- PAGINACIÓN (a partir de 5 filas) -->
      <div
        v-if="usuarios.length > perPage"
        class="flex flex-col md:flex-row justify-between items-center gap-3 mt-4 text-xs md:text-sm"
      >
        <span :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
          Mostrando
          <b>{{ startItem }}</b>
          -
          <b>{{ endItem }}</b>
          de
          <b>{{ usuarios.length }}</b>
          usuarios
        </span>

        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="prevPage"
            :disabled="currentPage === 1"
            class="px-3 py-1 rounded-lg border font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
            :class="
              darkMode
                ? 'border-gray-600 text-gray-100 hover:bg-gray-800'
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
            class="px-3 py-1 rounded-lg border font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
            :class="
              darkMode
                ? 'border-gray-600 text-gray-100 hover:bg-gray-800'
                : 'border-gray-300 text-gray-800 hover:bg-gray-100'
            "
          >
            Siguiente ▶
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
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

const darkBorder = computed(() =>
  darkMode.value ? 'border border-gray-700' : 'border border-gray-300'
)

const form = ref({
  id_usuario: '',
  contrasenia: '',
  estado_usuario: true
})

const formErrors = ref({})
const usuarios = ref([])
const mensaje = ref('')
const mensajeColor = ref('text-green-600')
const showLista = ref(false)
const editMode = ref(false) // false = registrar, true = editar

/* ===== PAGINACIÓN (5 filas) ===== */
const currentPage = ref(1)
const perPage = 5

const totalPages = computed(() =>
  usuarios.value.length
    ? Math.ceil(usuarios.value.length / perPage)
    : 0
)

const paginatedUsuarios = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return usuarios.value.slice(start, start + perPage)
})

const startItem = computed(() => {
  if (!usuarios.value.length) return 0
  return (currentPage.value - 1) * perPage + 1
})

const endItem = computed(() => {
  if (!usuarios.value.length) return 0
  return Math.min(currentPage.value * perPage, usuarios.value.length)
})

function nextPage () {
  if (currentPage.value < totalPages.value) currentPage.value++
}

function prevPage () {
  if (currentPage.value > 1) currentPage.value--
}

watch(usuarios, () => {
  currentPage.value = 1
})

/* 🔢 Generar ID automático según prefijo */
function generarId (prefix) {
  // prefix: 'cli_m', 'trab_m', 'prov_m'
  const regex = new RegExp('^' + prefix + '(\\d+)$')
  let max = 0

  usuarios.value.forEach(u => {
    const match = u.id_usuario.match(regex)
    if (match) {
      const num = parseInt(match[1], 10)
      if (num > max) max = num
    }
  })

  const next = max + 1
  form.value.id_usuario = `${prefix}${next}`
}

function limpiarFormulario () {
  form.value = {
    id_usuario: '',
    contrasenia: '',
    estado_usuario: true
  }
  formErrors.value = {}
  editMode.value = false
}

function cancelarEdicion () {
  limpiarFormulario()
  mensaje.value = 'Edición cancelada'
  mensajeColor.value = 'text-gray-500'
}

// 📋 Obtener lista
async function listarUsuarios () {
  try {
    const res = await axios.get('propietario/Construccion/usuarios')
    usuarios.value = res.data.usuarios || []
    mensaje.value = 'Lista de usuarios actualizada'
    mensajeColor.value = 'text-green-600'
  } catch (error) {
    console.error(error)
    mensaje.value = 'Error al conectar con el servidor'
    mensajeColor.value = 'text-red-600'
  }
}

// Mostrar / ocultar lista
async function toggleLista () {
  if (!showLista.value) {
    await listarUsuarios()
    showLista.value = true
  } else {
    showLista.value = false
  }
}

// ➕ Registrar usuario
async function registrarUsuario () {
  formErrors.value = {}
  mensaje.value = ''

  try {
    const payload = {
      id_usuario: form.value.id_usuario,
      contrasenia: form.value.contrasenia,
      estado_usuario: form.value.estado_usuario
    }

    const res = await axios.post('propietario/Construccion/usuarios', payload)

    if (res.data.success) {
      mensaje.value = res.data.message
      mensajeColor.value = 'text-green-600'
      limpiarFormulario()
      await listarUsuarios()
    } else {
      mensaje.value = res.data.message || 'Error al registrar usuario'
      mensajeColor.value = 'text-red-600'
    }
  } catch (error) {
    console.error(error)
    if (error.response?.status === 422) {
      formErrors.value = error.response.data.errors || {}
      mensaje.value = 'Corrige los errores del formulario'
      mensajeColor.value = 'text-red-600'
    } else {
      mensaje.value = 'Error al conectar con el servidor'
      mensajeColor.value = 'text-red-600'
    }
  }
}

// ✏️ Poner usuario en modo edición
function editarUsuario (u) {
  form.value.id_usuario = u.id_usuario
  form.value.contrasenia = '' // se puede cambiar, pero la mostramos vacía
  form.value.estado_usuario = u.estado_usuario
  editMode.value = true
  mensaje.value = `Editando usuario: ${u.id_usuario}`
  mensajeColor.value = 'text-blue-600'
}

// 🔁 Actualizar usuario
async function actualizarUsuario () {
  formErrors.value = {}
  mensaje.value = ''

  try {
    const payload = {
      contrasenia: form.value.contrasenia || null,
      estado_usuario: form.value.estado_usuario
    }

    const res = await axios.put(
      `propietario/Construccion/usuarios/${form.value.id_usuario}`,
      payload
    )

    if (res.data.success) {
      mensaje.value = res.data.message
      mensajeColor.value = 'text-green-600'
      editMode.value = false
      form.value.contrasenia = ''
      await listarUsuarios()
    } else {
      mensaje.value = res.data.message || 'Error al actualizar usuario'
      mensajeColor.value = 'text-red-600'
    }
  } catch (error) {
    console.error(error)
    if (error.response?.status === 422) {
      formErrors.value = error.response.data.errors || {}
      mensaje.value = 'Corrige los errores del formulario'
      mensajeColor.value = 'text-red-600'
    } else {
      mensaje.value = 'Error al conectar con el servidor'
      mensajeColor.value = 'text-red-600'
    }
  }
}

// 🔁 Cambiar estado desde la tabla
async function toggleEstado (u) {
  try {
    const res = await axios.patch(
      `propietario/Construccion/usuarios/${u.id_usuario}/estado`
    )
    if (res.data.success) {
      u.estado_usuario = res.data.usuario.estado_usuario
    }
  } catch (error) {
    console.error(error)
    mensaje.value = 'Error al cambiar estado'
    mensajeColor.value = 'text-red-600'
  }
}

// 🗑️ Eliminar usuario
async function eliminarUsuario (u) {
  if (!confirm(`¿Seguro que deseas eliminar el usuario "${u.id_usuario}"?`)) {
    return
  }

  try {
    const res = await axios.delete(
      `propietario/Construccion/usuarios/${u.id_usuario}`
    )
    if (res.data.success) {
      mensaje.value = res.data.message
      mensajeColor.value = 'text-green-600'
      await listarUsuarios()
      if (editMode.value && form.value.id_usuario === u.id_usuario) {
        limpiarFormulario()
      }
    }
  } catch (error) {
    console.error(error)
    mensaje.value = 'Error al eliminar usuario'
    mensajeColor.value = 'text-red-600'
  }
}

// Cargar usuarios al entrar
onMounted(() => {
  listarUsuarios()
})
</script>

<style scoped>
.input {
  @apply w-full rounded-lg p-2 border focus:ring-2 focus:ring-blue-400 focus:outline-none transition-colors duration-200;
}
</style>
