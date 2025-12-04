<template>
  <section
    :class="[
      'rounded-2xl shadow p-6 space-y-6',
      darkMode ? 'bg-gray-800 text-gray-100' : 'bg-white text-gray-800'
    ]"
  >
    <h3 class="text-xl font-bold text-blue-500 mb-2">Pagos recibidos</h3>

    <!-- Tabla de pagos -->
    <div class="overflow-x-auto">
      <table class="w-full text-sm border border-gray-300">
        <thead :class="darkMode ? 'bg-gray-700' : 'bg-gray-100'">
          <tr>
            <th class="p-2 border">Fecha</th>
            <th class="p-2 border">Monto</th>
            <th class="p-2 border">Método</th>
            <th class="p-2 border">Tipo</th>
            <th class="p-2 border">Concepto</th>
            <th class="p-2 border">Estado</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(p, i) in pagosRecibidos" :key="i">
            <td class="p-2 border">{{ p.fecha_pago }}</td>
            <td class="p-2 border">{{ currency(p.monto_total_pago) }}</td>
            <td class="p-2 border capitalize">{{ p.metodo_pago }}</td>
            <td class="p-2 border">
              {{ p.cuotas_pago && p.cuotas_pago > 0 ? 'En cuotas' : 'Directo' }}
            </td>
            <td class="p-2 border">{{ p.concepto_pago }}</td>
            <td class="p-2 border">
              <span
                :class="[
                  'px-2 py-1 rounded text-xs font-semibold',
                  p.estado_pago
                    ? 'bg-green-100 text-green-700'
                    : 'bg-yellow-100 text-yellow-700'
                ]"
              >
                {{ p.estado_pago ? 'Pagado' : 'Pendiente' }}
              </span>
            </td>
          </tr>
          <tr v-if="!pagosRecibidos.length">
            <td colspan="6" class="text-center py-3 text-gray-500">
              Aún no se registran pagos a tu nombre.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- QR del trabajador -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
      <div>
        <h4 class="text-lg font-semibold mb-2">Tu QR de pago</h4>
        <p
          class="text-sm mb-2"
          :class="darkMode ? 'text-gray-300' : 'text-gray-600'"
        >
          Este código QR se usa cuando el propietario te paga por QR. Puedes actualizarlo si el banco
          te genera uno nuevo.
        </p>

        <form @submit.prevent="subirQr" class="space-y-3">
          <input
            ref="fileInput"
            type="file"
            accept="image/*"
            class="block text-sm"
            @change="onFileChange"
          />

          <div class="flex flex-wrap gap-2">
            <button
              type="submit"
              class="px-4 py-2 rounded-lg text-sm font-semibold text-white"
              :class="
                darkMode
                  ? 'bg-blue-600 hover:bg-blue-500'
                  : 'bg-blue-600 hover:bg-blue-700'
              "
            >
              Guardar nuevo QR
            </button>
          </div>

          <p v-if="msg" class="text-sm mt-1 text-green-500">{{ msg }}</p>
          <p v-if="err" class="text-sm mt-1 text-red-500">{{ err }}</p>
        </form>
      </div>

      <div class="flex justify-center">
        <div class="flex flex-col items-center">
          <div
            class="w-56 h-56 flex items-center justify-center rounded-xl border bg-white overflow-hidden"
          >
            <img
              v-if="qrSrc"
              :src="qrSrc"
              alt="QR trabajador"
              class="max-w-full max-h-full object-contain"
            />
            <span v-else class="text-xs text-gray-500 px-4 text-center">
              Aún no tienes un QR registrado.
            </span>
          </div>
          <p class="mt-2 text-xs text-gray-400">
            {{ trabajador?.nombre_trabajador }} {{ trabajador?.apellido_trabajador }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, ref } from 'vue'
import axios from 'axios'

const baseUrl = import.meta.env.VITE_APP_URL.replace(/\/$/, '')

const storageUrl = (path) => {
  if (!path) return null
  if (path.startsWith('http')) return path   // ya es una URL completa

  const projectRoot = baseUrl.replace('/public', '') // .../metaluvcamiri

  return `${projectRoot}/storage/app/public/${path}`
}



const props = defineProps({
  darkMode: Boolean,
  trabajador: { type: Object, default: () => ({}) },
  pagosRecibidos: { type: Array, default: () => [] },
  // En BD guardas algo como: "trabajadores/qr/miqr.png"
  qrTrabajador: { type: [String, null], default: null }
})

const currency = n =>
  Number(n || 0).toLocaleString('es-BO', { style: 'currency', currency: 'BOB' })

// URL que viene de la BD
const storedQrUrl = computed(() => {
  if (!props.qrTrabajador) return null
  return storageUrl(props.qrTrabajador)
})


// preview local cuando se selecciona una nueva imagen
const preview = ref(null)

// prioridad: si hay preview mostrarla; si no, mostrar la guardada
const qrSrc = computed(() => preview.value || storedQrUrl.value)

const file = ref(null)
const fileInput = ref(null)
const msg = ref('')
const err = ref('')

function onFileChange (e) {
  msg.value = ''
  err.value = ''
  const f = e.target.files[0] || null
  file.value = f

  if (file.value) {
    preview.value = URL.createObjectURL(file.value)
  } else {
    preview.value = null
  }
}

async function subirQr () {
  msg.value = ''
  err.value = ''

  if (!file.value) {
    err.value = 'Selecciona una imagen primero.'
    return
  }

  const formData = new FormData()
  formData.append('qr', file.value)

  try {
    const res = await axios.post('trabajador/qr-actualizar', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    msg.value = res.data.message || 'QR actualizado correctamente.'
    err.value = ''

    // limpiamos input y archivo seleccionados
    file.value = null
    if (fileInput.value) fileInput.value.value = ''
    preview.value = null

    // recargar para traer la nueva ruta desde el backend
    window.location.reload()
  } catch (e) {
    console.error(e)
    err.value = e.response?.data?.message || 'Error al subir el QR.'
  }
}


</script>

