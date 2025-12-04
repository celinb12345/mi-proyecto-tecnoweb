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
      Gestión de Trabajadores
    </h2>

    <!-- BUSCADOR -->
    <div class="flex gap-4 mb-6">
      <input
        type="text"
        v-model="buscar"
        placeholder="🔍 Buscar por nombre, apellido o ID..."
        class="input flex-1"
        :class="inputClass"
      />
      <button @click="buscarTrabajadores" class="btn-blue text-xs md:text-sm">
        Buscar
      </button>
      <button @click="toggleLista" class="btn-gray text-xs md:text-sm">
        {{ mostrarLista ? 'Ocultar Lista' : 'Listar' }}
      </button>
    </div>

    <!-- FORMULARIO -->
    <form @submit.prevent="guardar" class="grid grid-cols-2 gap-4 mb-6">
      <!-- USUARIO -->
      <div>
        <label class="block text-sm font-semibold mb-1">Usuario</label>
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
          Primero crea el usuario en la sección Usuarios (prefijo
          <b>trab_m</b>), luego selecciónalo aquí.
        </p>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Nombre</label>
        <input
          type="text"
          v-model="form.nombre_trabajador"
          class="input"
          :class="inputClass"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Apellido</label>
        <input
          type="text"
          v-model="form.apellido_trabajador"
          class="input"
          :class="inputClass"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Cargo</label>
        <select
          v-model="form.cargo_trabajador"
          class="input"
          :class="inputClass"
        >
          <option disabled value="">Selecciona</option>
          <option>Ayudante</option>
          <option>Maestro</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Sueldo</label>
        <input
          type="number"
          v-model="form.sueldo_trabajador"
          class="input"
          :class="inputClass"
          step="0.01"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Teléfono</label>
        <input
          type="text"
          v-model="form.telefono_trabajador"
          class="input"
          :class="inputClass"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Tipo de Contrato</label>
        <select
          v-model="form.tipo_contrato_trab"
          class="input"
          :class="inputClass"
        >
          <option disabled value="">Selecciona</option>
          <option value="Eventual">Eventual</option>
          <option value="Contrato">Contrato</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Saldo Pendiente</label>
        <input
          type="number"
          v-model="form.saldo_pendiente_trab"
          class="input"
          :class="inputClass"
        />
      </div>

      <!-- FOTO -->
      <div>
        <label class="block text-sm font-semibold mb-1">Foto</label>
        <input
          type="file"
          ref="fotoInput"
          @change="onFileChange($event, 'foto')"
          class="input"
          :class="inputClass"
        />
        <div v-if="previewFoto" class="mt-2 flex items-center gap-2">
          <img
            :src="previewFoto"
            class="w-24 h-24 rounded-full object-cover border"
          />
          <button
            type="button"
            @click="quitarFoto"
            class="text-red-400 text-sm font-semibold hover:text-red-300"
          >
            ✖ Quitar
          </button>
        </div>
      </div>

      <!-- QR -->
      <div>
        <label class="block text-sm font-semibold mb-1">Código QR</label>
        <input
          type="file"
          ref="qrInput"
          @change="onFileChange($event, 'codigoqr')"
          class="input"
          :class="inputClass"
        />
        <div v-if="previewQR" class="mt-2 flex items-center gap-2">
          <img
            :src="previewQR"
            class="w-24 h-24 rounded-lg object-cover border"
          />
          <button
            type="button"
            @click="quitarQR"
            class="text-red-400 text-sm font-semibold hover:text-red-300"
          >
            ✖ Quitar
          </button>
        </div>
      </div>

      <div class="col-span-2 flex justify-end gap-2">
        <button type="submit" class="btn-green text-xs md:text-sm">
          {{ editando ? 'Actualizar' : 'Registrar' }}
        </button>
        <button
          v-if="editando"
          type="button"
          @click="cancelarEdicion"
          class="btn-gray text-xs md:text-sm"
        >
          Cancelar
        </button>
      </div>
    </form>

    <!-- LISTA -->
    <div v-if="mostrarLista" class="overflow-x-auto">
      <table
        :class="[
          'w-full rounded-lg text-sm',
          darkMode ? 'border border-gray-700' : 'border border-gray-200'
        ]"
      >
        <thead class="bg-blue-600 text-white">
          <tr>
            <!-- Ya NO mostramos ID ni ID Usuario -->
            <th class="px-2 py-2">Nombre</th>
            <th class="px-2 py-2">Apellido</th>
            <th class="px-2 py-2">Cargo</th>
            <th class="px-2 py-2">Contrato</th>
            <th class="px-2 py-2">Sueldo</th>
            <th class="px-2 py-2">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="t in paginatedTrabajadores"
            :key="t.id_trabajador"
            :class="[
              'border-b',
              darkMode
                ? 'border-gray-700 hover:bg-gray-800'
                : 'border-gray-200 hover:bg-gray-100'
            ]"
          >
            <!-- celdas visibles sin ID -->
            <td class="px-2 py-2">{{ t.nombre_trabajador }}</td>
            <td class="px-2 py-2">{{ t.apellido_trabajador }}</td>
            <td class="px-2 py-2">{{ t.cargo_trabajador }}</td>
            <td class="px-2 py-2">{{ t.tipo_contrato_trab }}</td>
            <td class="px-2 py-2">
              S/ {{ Number(t.sueldo_trabajador || 0).toFixed(2) }}
            </td>
            <td class="px-2 py-2 flex justify-center gap-3">
              <button
                @click="editar(t)"
                class="text-blue-400 hover:text-blue-300 text-xs font-semibold"
              >
                ✏️ Editar
              </button>
              <button
                @click="eliminar(t.id_trabajador)"
                class="text-red-400 hover:text-red-300 text-xs font-semibold"
              >
                🗑 Eliminar
              </button>
            </td>
          </tr>
          <tr v-if="!trabajadores.length">
            <td
              colspan="6"
              class="text-center py-3"
              :class="darkMode ? 'text-gray-300' : 'text-gray-500'"
            >
              No hay trabajadores registrados
            </td>
          </tr>
        </tbody>
      </table>

      <!-- PAGINACIÓN (a partir de 5 filas) -->
      <div
        v-if="trabajadores.length > perPage"
        class="flex flex-col md:flex-row justify-between items-center gap-3 mt-4 text-xs md:text-sm"
      >
        <span :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
          Mostrando
          <b>{{ startItem }}</b>
          -
          <b>{{ endItem }}</b>
          de
          <b>{{ trabajadores.length }}</b>
          trabajadores
        </span>

        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="prevPage"
            :disabled="currentPage === 1"
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
            Página {{ currentPage }} de {{ totalPages }}
          </span>

          <button
            type="button"
            @click="nextPage"
            :disabled="currentPage === totalPages || totalPages === 0"
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
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'
const baseUrl = import.meta.env.VITE_APP_URL.replace(/\/$/, '')

const storageUrl = (path) => {
  if (!path) return null
  // si ya viene con http/https la dejamos
  if (path.startsWith('http')) return path

  const appUrl = baseUrl                
  const projectRoot = appUrl.replace('/public', '')

  // aquí es donde realmente están tus archivos en el server
  return `${projectRoot}/storage/app/public/${path}`
}

/* ==== PROPS PARA MODO OSCURO Y FUENTE ==== */
const props = defineProps({
  darkMode: { type: Boolean, default: false },
  fontClass: { type: String, default: 'font-sans' }
})

const darkMode = computed(() => props.darkMode)
const fontClass = computed(() => props.fontClass || 'font-sans')

const inputClass = computed(() =>
  darkMode.value
    ? 'bg-gray-900 border border-gray-700 text-gray-100 placeholder-gray-400'
    : 'bg-white border border-gray-300 text-gray-900 placeholder-gray-400'
)

const trabajadores = ref([])
const usuarios = ref([]) // todos los usuarios del propietario
const mostrarLista = ref(false)
const buscar = ref('')
const editando = ref(false)
const idEditar = ref(null)

const form = ref({
  id_usuario: '',
  nombre_trabajador: '',
  apellido_trabajador: '',
  cargo_trabajador: '',
  sueldo_trabajador: '',
  telefono_trabajador: '',
  tipo_contrato_trab: '',
  saldo_pendiente_trab: '',
  foto: null,
  codigoqr: null
})

const previewFoto = ref(null)
const previewQR = ref(null)
const fotoInput = ref(null)
const qrInput = ref(null)

/* ==== PAGINACIÓN (5 filas por página) ==== */
const currentPage = ref(1)
const perPage = 5

const totalPages = computed(() =>
  trabajadores.value.length
    ? Math.ceil(trabajadores.value.length / perPage)
    : 0
)

const paginatedTrabajadores = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return trabajadores.value.slice(start, start + perPage)
})

const startItem = computed(() => {
  if (!trabajadores.value.length) return 0
  return (currentPage.value - 1) * perPage + 1
})

const endItem = computed(() => {
  if (!trabajadores.value.length) return 0
  return Math.min(currentPage.value * perPage, trabajadores.value.length)
})

function nextPage () {
  if (currentPage.value < totalPages.value) currentPage.value++
}

function prevPage () {
  if (currentPage.value > 1) currentPage.value--
}

watch(trabajadores, () => {
  currentPage.value = 1
})

/* 🔎 usuarios disponibles: solo trab_m* y que no estén en la tabla trabajador,
   pero permitiendo el actual cuando se edita */
const usuariosDisponibles = computed(() => {
  const usados = new Set(
    trabajadores.value
      .map(t => t.id_usuario)
      .filter(id => id)
  )

  const actual = form.value.id_usuario

  return usuarios.value.filter(u => {
    const esTrab = u.id_usuario.startsWith('trab_c')
    if (!esTrab) return false

    if (editando.value && u.id_usuario === actual) return true

    return !usados.has(u.id_usuario)
  })
})

onMounted(() => {
  cargarUsuarios()
  buscarTrabajadores()
})

function cargarUsuarios () {
  axios
    .get('propietario/Construccion/usuarios')
    .then(res => {
      usuarios.value = res.data.usuarios || []
    })
    .catch(err => {
      console.error('Error al obtener usuarios:', err)
    })
}

function onFileChange (e, tipo) {
  const file = e.target.files[0]
  if (!file) return
  if (tipo === 'foto') {
    form.value.foto = file
    previewFoto.value = URL.createObjectURL(file)
  } else {
    form.value.codigoqr = file
    previewQR.value = URL.createObjectURL(file)
  }
}

function quitarFoto () {
  form.value.foto = null
  previewFoto.value = null
  if (fotoInput.value) fotoInput.value.value = null
}

function quitarQR () {
  form.value.codigoqr = null
  previewQR.value = null
  if (qrInput.value) qrInput.value.value = null
}

function guardar () {
  const fd = new FormData()
  for (const key in form.value) {
    if (form.value[key] !== null && form.value[key] !== '') {
      fd.append(key, form.value[key])
    }
  }

  const url = editando.value
    ? `propietario/Construccion/trabajadores/${idEditar.value}`
    : 'propietario/Construccion/trabajadores'

  axios
    .post(url, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    .then(res => {
      if (res.data.success) {
        alert(editando.value ? 'Trabajador actualizado' : 'Trabajador registrado')
        limpiar()
        buscarTrabajadores()
        cargarUsuarios() // actualizar disponibles
      }
    })
    .catch(err => {
      console.error('Error al guardar trabajador:', err)
      alert('Error al guardar trabajador')
    })
}

function editar (t) {
  editando.value = true
  idEditar.value = t.id_trabajador

  form.value.id_usuario = t.id_usuario
  form.value.nombre_trabajador = t.nombre_trabajador
  form.value.apellido_trabajador = t.apellido_trabajador
  form.value.cargo_trabajador = t.cargo_trabajador
  form.value.sueldo_trabajador = t.sueldo_trabajador
  form.value.telefono_trabajador = t.telefono_trabajador
  form.value.tipo_contrato_trab = t.tipo_contrato_trab
  form.value.saldo_pendiente_trab = t.saldo_pendiente_trab

  form.value.foto = null
  form.value.codigoqr = null

  previewFoto.value = storageUrl(t.foto_trabajador)
previewQR.value   = storageUrl(t.codigoqr_trab)

  if (fotoInput.value) fotoInput.value.value = null
  if (qrInput.value) qrInput.value.value = null
}

function cancelarEdicion () {
  editando.value = false
  limpiar()
}

function eliminar (id) {
  if (!confirm('¿Eliminar este trabajador?')) return
  axios
    .delete(`propietario/Construccion/trabajadores/${id}`)
    .then(() => {
      buscarTrabajadores()
      cargarUsuarios() // usuario vuelve a quedar disponible
    })
    .catch(err => {
      console.error('Error al eliminar trabajador:', err)
      alert('Error al eliminar trabajador')
    })
}

function limpiar () {
  editando.value = false
  idEditar.value = null
  previewFoto.value = null
  previewQR.value = null
  if (fotoInput.value) fotoInput.value.value = null
  if (qrInput.value) qrInput.value.value = null

  form.value = {
    id_usuario: '',
    nombre_trabajador: '',
    apellido_trabajador: '',
    cargo_trabajador: '',
    sueldo_trabajador: '',
    telefono_trabajador: '',
    tipo_contrato_trab: '',
    saldo_pendiente_trab: '',
    foto: null,
    codigoqr: null
  }
}

function buscarTrabajadores () {
  axios
    .get('propietario/Construccion/trabajadores', {
      params: { buscar: buscar.value }
    })
    .then(res => {
      trabajadores.value = res.data.trabajadores || []
      mostrarLista.value = true
    })
    .catch(err => {
      console.error('Error al obtener trabajadores:', err)
      alert('Error al obtener trabajadores')
    })
}

function toggleLista () {
  mostrarLista.value = !mostrarLista.value
  if (mostrarLista.value) buscarTrabajadores()
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
</style>
