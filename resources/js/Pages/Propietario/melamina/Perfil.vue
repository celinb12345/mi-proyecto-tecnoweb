<template>
  <div
    :class="[
      'p-8 rounded-2xl shadow-md max-w-4xl mx-auto transition-colors duration-300',
      darkMode ? 'bg-gray-800 text-gray-100' : 'bg-white text-gray-900',
      fontClass
    ]"
  >
    <h2 class="text-2xl font-bold text-blue-500 mb-6">Mi Perfil (Melamina)</h2>

    <p v-if="mensaje" :class="['mb-4 font-semibold', mensajeColor]">
      {{ mensaje }}
    </p>

    <!-- BOTÓN EDITAR / CANCELAR -->
    <div class="flex justify-end mb-4">
      <button
        v-if="!editando"
        @click="activarEdicion"
        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold"
      >
        Editar
      </button>
      <button
        v-else
        @click="cancelarEdicion"
        class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded-lg font-semibold"
      >
        Cancelar
      </button>
    </div>

    <form
      @submit.prevent="guardarPerfil"
      class="grid grid-cols-1 md:grid-cols-2 gap-6"
    >
      <!-- IDs -->
      <div>
        <label class="block text-sm font-semibold mb-1">ID Propietario</label>
        <input
          type="text"
          :value="form.id_propietario"
          readonly
          :class="[
            'input',
            darkMode
              ? 'bg-gray-900 border-gray-700 text-gray-200'
              : 'bg-gray-100 border-gray-300 text-gray-800',
            'cursor-not-allowed'
          ]"
        />
      </div>
      <div>
        <label class="block text-sm font-semibold mb-1">ID Usuario</label>
        <input
          type="text"
          :value="form.id_usuario"
          readonly
          :class="[
            'input',
            darkMode
              ? 'bg-gray-900 border-gray-700 text-gray-200'
              : 'bg-gray-100 border-gray-300 text-gray-800',
            'cursor-not-allowed'
          ]"
        />
      </div>

      <!-- Nombre / Apellido -->
      <div>
        <label class="block text-sm font-semibold mb-1">Nombre</label>
        <input
          type="text"
          v-model="form.nombre_propietario"
          :readonly="!editando"
          :class="[
            'input',
            darkMode
              ? 'border-gray-700 text-gray-100 placeholder-gray-400'
              : 'border-gray-300 text-gray-900 placeholder-gray-400',
            !editando ? (darkMode ? 'bg-gray-900 cursor-not-allowed' : 'bg-gray-100 cursor-not-allowed') : (darkMode ? 'bg-gray-900' : 'bg-white')
          ]"
        />
      </div>
      <div>
        <label class="block text-sm font-semibold mb-1">Apellido</label>
        <input
          type="text"
          v-model="form.apellido_propietario"
          :readonly="!editando"
          :class="[
            'input',
            darkMode
              ? 'border-gray-700 text-gray-100 placeholder-gray-400'
              : 'border-gray-300 text-gray-900 placeholder-gray-400',
            !editando ? (darkMode ? 'bg-gray-900 cursor-not-allowed' : 'bg-gray-100 cursor-not-allowed') : (darkMode ? 'bg-gray-900' : 'bg-white')
          ]"
        />
      </div>

      <!-- Correo / Teléfono -->
      <div>
        <label class="block text-sm font-semibold mb-1">Correo</label>
        <input
          type="email"
          v-model="form.correo_propietario"
          :readonly="!editando"
          :class="[
            'input',
            darkMode
              ? 'border-gray-700 text-gray-100 placeholder-gray-400'
              : 'border-gray-300 text-gray-900 placeholder-gray-400',
            !editando ? (darkMode ? 'bg-gray-900 cursor-not-allowed' : 'bg-gray-100 cursor-not-allowed') : (darkMode ? 'bg-gray-900' : 'bg-white')
          ]"
        />
      </div>
      <div>
        <label class="block text-sm font-semibold mb-1">Teléfono</label>
        <input
          type="text"
          v-model="form.telefono_propietario"
          :readonly="!editando"
          :class="[
            'input',
            darkMode
              ? 'border-gray-700 text-gray-100 placeholder-gray-400'
              : 'border-gray-300 text-gray-900 placeholder-gray-400',
            !editando ? (darkMode ? 'bg-gray-900 cursor-not-allowed' : 'bg-gray-100 cursor-not-allowed') : (darkMode ? 'bg-gray-900' : 'bg-white')
          ]"
        />
      </div>

      <!-- Dirección / Especialidad -->
      <div>
        <label class="block text-sm font-semibold mb-1">Dirección</label>
        <input
          type="text"
          v-model="form.direccion_propietario"
          :readonly="!editando"
          :class="[
            'input',
            darkMode
              ? 'border-gray-700 text-gray-100 placeholder-gray-400'
              : 'border-gray-300 text-gray-900 placeholder-gray-400',
            !editando ? (darkMode ? 'bg-gray-900 cursor-not-allowed' : 'bg-gray-100 cursor-not-allowed') : (darkMode ? 'bg-gray-900' : 'bg-white')
          ]"
        />
      </div>
      <div>
        <label class="block text-sm font-semibold mb-1">Especialidad</label>
        <input
          type="text"
          v-model="form.especialidad_propietario"
          readonly
          :class="[
            'input',
            darkMode
              ? 'bg-gray-900 border-gray-700 text-gray-200'
              : 'bg-gray-100 border-gray-300 text-gray-800',
            'cursor-not-allowed'
          ]"
        />
      </div>

      <!-- FOTO -->
      <div class="md:col-span-2">
        <label class="block text-sm font-semibold mb-2">Foto</label>
        <div class="flex flex-col md:flex-row gap-6 items-start">
          <!-- PREVIEW -->
          <div
            :class="[
              'w-32 h-32 rounded-full overflow-hidden border flex items-center justify-center',
              darkMode ? 'bg-gray-900 border-gray-700' : 'bg-gray-100 border-gray-300'
            ]"
          >
            <img
              v-if="previewUrl"
              :src="previewUrl"
              alt="Foto del propietario"
              class="w-full h-full object-cover"
            />
            <span
              v-else
              :class="[
                'text-xs text-center px-2',
                darkMode ? 'text-gray-400' : 'text-gray-400'
              ]"
            >
              Sin foto
            </span>
          </div>

          <!-- INPUT FILE (solo en edición) -->
          <div v-if="editando" class="flex-1">
            <input
              type="file"
              accept="image/*"
              @change="onFileChange"
              :class="[
                'block w-full text-sm',
                darkMode ? 'text-gray-200' : 'text-gray-700'
              ]"
            />
            <p
              class="text-xs mt-1"
              :class="darkMode ? 'text-gray-300' : 'text-gray-500'"
            >
              Selecciona una nueva foto (máx. 2MB)
            </p>
          </div>
        </div>
      </div>

      <!-- BOTÓN GUARDAR (solo en edición) -->
      <div v-if="editando" class="md:col-span-2 flex justify-end">
        <button
          type="submit"
          class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold"
        >
          Guardar cambios
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
const baseUrl = import.meta.env.VITE_APP_URL.replace(/\/$/, '')




const storageUrl = (path) => {
  if (!path) return null
  const p = String(path)

  
  if (p.startsWith('http')) return p

 
  const appUrl = baseUrl  
  
  
  return `${appUrl}/storage/${p}`
}


/* === props para modo oscuro y fuente global === */
const { props: pageProps } = usePage()

const user = pageProps.user || {}
const prop = user.propietario || {}

const componentProps = defineProps({
  darkMode: { type: Boolean, default: false },
  fontClass: { type: String, default: 'font-sans' }
})

const darkMode = componentProps.darkMode
const fontClass = componentProps.fontClass

// FORMULARIO
const form = ref({
  id_propietario: prop.id_propietario || '',
  id_usuario: user.id_usuario || prop.id_usuario || '',
  nombre_propietario: prop.nombre_propietario || '',
  apellido_propietario: prop.apellido_propietario || '',
  correo_propietario: prop.correo_propietario || '',
  telefono_propietario: prop.telefono_propietario || '',
  direccion_propietario: prop.direccion_propietario || '',
  especialidad_propietario: prop.especialidad_propietario || '',
  foto_propietario: prop.foto_propietario || null
})

const editando = ref(false)
const fotoFile = ref(null)
const mensaje = ref('')
const mensajeColor = ref('text-green-400')

const initialFotoUrl = computed(() => storageUrl(form.value.foto_propietario))


const previewUrl = ref(initialFotoUrl.value)

// Cambiar archivo de foto
function onFileChange(e) {
  const file = e.target.files[0]
  if (!file) return
  fotoFile.value = file
  previewUrl.value = URL.createObjectURL(file)
}

// Activar edición
function activarEdicion() {
  editando.value = true
  mensaje.value = ''
}

// Cancelar edición
function cancelarEdicion() {
  editando.value = false
  mensaje.value = ''
  // resetear preview
  previewUrl.value = initialFotoUrl.value
}

// Guardar perfil
async function guardarPerfil() {
  mensaje.value = ''
  mensajeColor.value = darkMode ? 'text-green-300' : 'text-green-600'

  const fd = new FormData()
  fd.append('nombre_propietario', form.value.nombre_propietario)
  fd.append('apellido_propietario', form.value.apellido_propietario)
  fd.append('correo_propietario', form.value.correo_propietario || '')
  fd.append('telefono_propietario', form.value.telefono_propietario || '')
  fd.append('direccion_propietario', form.value.direccion_propietario || '')
  fd.append('especialidad_propietario', form.value.especialidad_propietario || '')

  if (fotoFile.value) fd.append('foto', fotoFile.value)

  try {
    const res = await axios.post('propietario/melamina/perfil', fd, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    if (res.data.success) {
      mensaje.value = res.data.message
      mensajeColor.value = darkMode ? 'text-green-300' : 'text-green-600'
            const updated = res.data.propietario
      Object.assign(form.value, updated)

      // usar la misma lógica centralizada
      previewUrl.value = storageUrl(updated.foto_propietario)


      editando.value = false
    } else {
      mensaje.value = 'Error al actualizar el perfil'
      mensajeColor.value = darkMode ? 'text-red-300' : 'text-red-600'
    }
  } catch (error) {
    console.error(error)
    mensaje.value = 'Error al actualizar el perfil'
    mensajeColor.value = darkMode ? 'text-red-300' : 'text-red-600'
  }
}
</script>

<style scoped>
.input {
  @apply rounded-lg p-2 w-full focus:ring-2 focus:ring-blue-400 focus:outline-none transition-colors duration-200;
}
</style>
