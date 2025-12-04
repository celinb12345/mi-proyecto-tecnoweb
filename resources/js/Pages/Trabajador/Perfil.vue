<template>
  <section
    :class="[
      'rounded-2xl shadow p-6 max-w-lg',
      darkMode ? 'bg-gray-800 text-gray-100' : 'bg-white text-gray-800'
    ]"
  >
    <h3 class="text-xl font-bold text-blue-500 mb-4">Mi perfil</h3>

    <ul class="space-y-2 mb-4">
      <li>
        <b>Nombre:</b>
        {{ trabajador?.nombre_trabajador }} {{ trabajador?.apellido_trabajador }}
      </li>
      <li><b>Cargo:</b> {{ trabajador?.cargo_trabajador }}</li>
      <li><b>Teléfono:</b> {{ trabajador?.telefono_trabajador }}</li>
      <li><b>Tipo de contrato:</b> {{ trabajador?.tipo_contrato_trab }}</li>
    </ul>

    <button
      class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold"
      @click="mostrarCambio = !mostrarCambio"
    >
      {{ mostrarCambio ? 'Cancelar cambio de contraseña' : 'Cambiar contraseña' }}
    </button>

    <div v-if="mostrarCambio" class="mt-4 border-t pt-4">
      <h4 class="font-semibold mb-2 text-sm">Actualizar contraseña</h4>

      <form @submit.prevent="cambiarPassword" class="space-y-3">
        <div>
          <label class="block text-sm mb-1">Contraseña actual</label>
          <input type="password" v-model="form.password_actual" class="input" required />
        </div>
        <div>
          <label class="block text-sm mb-1">Nueva contraseña</label>
          <input type="password" v-model="form.password_nueva" class="input" required />
        </div>
        <div>
          <label class="block text-sm mb-1">Confirmar nueva contraseña</label>
          <input
            type="password"
            v-model="form.password_nueva_confirmation"
            class="input"
            required
          />
        </div>

        <div class="flex justify-end gap-2">
          <button
            type="button"
            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded-lg text-sm"
            @click="cancelar"
          >
            Cancelar
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm"
          >
            Guardar nueva contraseña
          </button>
        </div>
      </form>

      <p v-if="msg" class="mt-2 text-sm text-green-500">{{ msg }}</p>
      <p v-if="err" class="mt-2 text-sm text-red-500">{{ err }}</p>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
  darkMode: Boolean,
  trabajador: { type: Object, default: () => ({}) }
})

const mostrarCambio = ref(false)
const msg = ref('')
const err = ref('')

const form = ref({
  password_actual: '',
  password_nueva: '',
  password_nueva_confirmation: ''
})

async function cambiarPassword () {
  msg.value = ''
  err.value = ''
  try {
    // Ruta según tu web.php (prefijo Trabajador)
    await axios.post('trabajador/cambiar-password', form.value)
    msg.value = 'Contraseña actualizada correctamente.'
    form.value = { password_actual: '', password_nueva: '', password_nueva_confirmation: '' }
    mostrarCambio.value = false
  } catch (e) {
    if (e.response && e.response.status === 422) {
      err.value = e.response.data.message || 'La contraseña actual no es correcta.'
    } else if (e.response?.data?.message) {
      err.value = e.response.data.message
    } else {
      err.value = 'Error al actualizar la contraseña.'
    }
  }
}

function cancelar () {
  mostrarCambio.value = false
  msg.value = ''
  err.value = ''
  form.value = { password_actual: '', password_nueva: '', password_nueva_confirmation: '' }
}
</script>

<style scoped>
.input {
  @apply border border-gray-300 rounded-lg p-2 w-full focus:ring-2 focus:ring-blue-400 focus:outline-none;
}
</style>
