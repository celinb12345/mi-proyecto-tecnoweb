<template>
  <section
    :class="[
      'rounded-2xl shadow p-6 max-w-lg transition-colors duration-300',
      darkMode ? 'bg-gray-800 text-gray-100' : 'bg-white text-gray-800'
    ]"
  >
    <h3 class="text-xl font-bold text-blue-500 mb-4">👤 Mi perfil</h3>

    <!-- Datos básicos -->
    <ul class="space-y-2 mb-4">
      <li>
        <b>Empresa:</b>
        {{ proveedor?.nombre_empres_prov || '—' }}
      </li>
      <li>
        <b>Teléfono:</b>
        {{ proveedor?.telefono_prov || '—' }}
      </li>
      <li>
        <b>Correo:</b>
        {{ proveedor?.correo_prov || user.email || '—' }}
      </li>
    </ul>

    <!-- Botón para mostrar / ocultar formulario -->
    <button
      class="px-4 py-2 rounded-lg text-sm font-semibold mb-4"
      :class="
        darkMode
          ? 'bg-indigo-600 hover:bg-indigo-500 text-white'
          : 'bg-indigo-600 hover:bg-indigo-700 text-white'
      "
      @click="mostrarCambio = !mostrarCambio"
    >
      {{ mostrarCambio ? 'Cancelar cambio de contraseña' : 'Cambiar contraseña' }}
    </button>

    <!-- Formulario cambio de contraseña -->
    <div v-if="mostrarCambio" class="border-t pt-4 mt-2">
      <h4 class="font-semibold mb-3 text-sm">Actualizar contraseña</h4>

      <form @submit.prevent="cambiarPassword" class="space-y-3">
        <div>
          <label class="block text-sm mb-1">Contraseña actual</label>
          <input
            type="password"
            v-model="passForm.password_actual"
            class="input"
            required
          />
        </div>

        <div>
          <label class="block text-sm mb-1">Nueva contraseña</label>
          <input
            type="password"
            v-model="passForm.password_nueva"
            class="input"
            required
          />
        </div>

        <div>
          <label class="block text-sm mb-1">Confirmar nueva contraseña</label>
          <input
            type="password"
            v-model="passForm.password_nueva_confirmation"
            class="input"
            required
          />
        </div>

        <div class="flex justify-end gap-2 mt-2">
          <button
            type="button"
            class="px-4 py-2 rounded-lg text-sm"
            :class="
              darkMode
                ? 'bg-gray-600 hover:bg-gray-500 text-white'
                : 'bg-gray-300 hover:bg-gray-400 text-gray-800'
            "
            @click="cancelarCambio"
          >
            Cancelar
          </button>

          <button
            type="submit"
            class="px-4 py-2 rounded-lg text-sm font-semibold text-white"
            :class="
              darkMode
                ? 'bg-green-600 hover:bg-green-500'
                : 'bg-green-600 hover:bg-green-700'
            "
          >
            Guardar nueva contraseña
          </button>
        </div>
      </form>

      <p v-if="passMsg" class="mt-3 text-sm text-green-500">
        {{ passMsg }}
      </p>
      <p v-if="passError" class="mt-3 text-sm text-red-500">
        {{ passError }}
      </p>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
  darkMode: { type: Boolean, default: false },
  proveedor: { type: Object, default: () => ({}) },
  user: { type: Object, default: () => ({}) }
})

const mostrarCambio = ref(false)
const passMsg = ref('')
const passError = ref('')

const passForm = ref({
  password_actual: '',
  password_nueva: '',
  password_nueva_confirmation: ''
})

async function cambiarPassword () {
  passMsg.value = ''
  passError.value = ''

  try {
    await axios.post('proveedor/cambiar-password', passForm.value)

    passMsg.value = 'Contraseña actualizada correctamente.'
    passError.value = ''

    // limpiar formulario
    passForm.value = {
      password_actual: '',
      password_nueva: '',
      password_nueva_confirmation: ''
    }
    mostrarCambio.value = false
  } catch (e) {
    console.error(e)
    if (e.response?.status === 422) {
      passError.value = e.response.data?.message || 'La contraseña actual no es correcta.'
    } else if (e.response?.data?.message) {
      passError.value = e.response.data.message
    } else {
      passError.value = 'Error al actualizar la contraseña.'
    }
  }
}

function cancelarCambio () {
  mostrarCambio.value = false
  passMsg.value = ''
  passError.value = ''
  passForm.value = {
    password_actual: '',
    password_nueva: '',
    password_nueva_confirmation: ''
  }
}
</script>

<style scoped>
.input {
  @apply border border-gray-300 rounded-lg p-2 w-full focus:ring-2 focus:ring-blue-400 focus:outline-none;
}
button {
  transition: all 0.2s ease;
}
</style>
