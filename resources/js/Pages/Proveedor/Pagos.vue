<template>
  <section
    :class="[
      'rounded-2xl shadow p-6 max-w-4xl transition-colors duration-300',
      darkMode ? 'bg-gray-800 text-gray-100' : 'bg-white text-gray-800'
    ]"
  >
    <h3 class="text-xl font-bold text-blue-500 mb-4">💰 Pagos recibidos</h3>

    <!-- Tabla de pagos -->
    <div class="overflow-x-auto mb-6">
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
          <tr
            v-for="(p, i) in pagos"
            :key="i"
            class="border-t"
            :class="darkMode ? 'border-gray-700' : 'border-gray-200'"
          >
            <td class="p-2 border">{{ p.fecha_pago }}</td>
            <td class="p-2 border">{{ currency(p.monto_total_pago) }}</td>
            <td class="p-2 border capitalize">{{ p.metodo_pago || '-' }}</td>
            <td class="p-2 border">
              {{ p.en_cuotas ? `En cuotas (${p.cuotas_pago})` : 'Directo' }}
            </td>
            <td class="p-2 border">{{ p.concepto_pago || '-' }}</td>
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

          <tr v-if="!pagos.length">
            <td colspan="6" class="text-center py-3 text-gray-500">
              Aún no se registran pagos a tu nombre.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Detalle de cuotas (si eliges un pago en cuotas, lo podrías mejorar luego) -->
    <div v-if="pagosEnCuotas.length" class="mb-6">
      <h4 class="font-semibold mb-2 text-sm">Pagos con detalle de cuotas</h4>
      <ul class="text-xs space-y-1">
        <li v-for="p in pagosEnCuotas" :key="p.id_pago">
          <b>{{ p.concepto_pago || 'Pago' }}</b> ·
          {{ currency(p.monto_total_pago) }} ·
          {{ p.cuotas?.length || p.cuotas_pago }} cuotas
        </li>
      </ul>
    </div>

    <!-- QR del proveedor (solo mostrar, sin subir por ahora) -->
    <!-- QR del proveedor -->
    <div class="mt-4 flex flex-col md:flex-row gap-6 items-center">
      <div class="flex-1">
        <h4 class="text-lg font-semibold mb-2">Tu QR de cobro</h4>
        <p
          class="text-sm mb-3"
          :class="darkMode ? 'text-gray-300' : 'text-gray-600'"
        >
          Este código QR se usa cuando el propietario te paga por QR. Puedes actualizarlo
          si tu banco te genera uno nuevo.
        </p>

        <form @submit.prevent="subirQr" class="space-y-3">
          <input
            ref="fileInput"
            type="file"
            accept="image/*"
            class="block text-sm"
            @change="onFileChange"
          />

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

          <p v-if="msg" class="text-sm mt-1 text-green-500">{{ msg }}</p>
          <p v-if="err" class="text-sm mt-1 text-red-500">{{ err }}</p>
        </form>
      </div>

      <div class="flex justify-center flex-1">
        <div class="flex flex-col items-center">
          <div
            class="w-56 h-56 flex items-center justify-center rounded-xl border bg-white overflow-hidden"
          >
            <img
              v-if="qrSrc"
              :src="qrSrc"
              alt="QR proveedor"
              class="max-w-full max-h-full object-contain"
            />
            <span v-else class="text-xs text-gray-500 px-4 text-center">
              Aún no tienes un QR registrado.
            </span>
          </div>
          <p class="mt-2 text-xs text-gray-400">
            {{ proveedor?.nombre_empres_prov || 'Proveedor' }}
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

  // baseUrl = .../metaluvcamiri/public  → quitamos /public
  const projectRoot = baseUrl.replace('/public', '') // .../metaluvcamiri

  // aquí es donde realmente están los archivos
  return `${projectRoot}/storage/app/public/${path}`
}


const props = defineProps({
  darkMode: { type: Boolean, default: false },
  pagos: { type: Array, default: () => [] },
  qrProveedor: { type: [String, null], default: null },
  proveedor: { type: Object, default: () => ({}) }
})

const currency = n =>
  Number(n || 0).toLocaleString('es-BO', { style: 'currency', currency: 'BOB' })

// pagos que tienen detalle de cuotas
const pagosEnCuotas = computed(() =>
  (props.pagos || []).filter(p => p.en_cuotas && (p.cuotas_pago || 0) > 0)
)

// URL que viene desde BD (storage)
const storedQrUrl = computed(() => {
  if (!props.qrProveedor) return null
  return storageUrl(props.qrProveedor)
})


// preview local
const preview = ref(null)
const qrSrc = computed(() => preview.value || storedQrUrl.value)

const file = ref(null)
const fileInput = ref(null)
const msg = ref('')
const err = ref('')

function onFileChange (e) {
  msg.value = ''
  err.value = ''
  const f = e.target.files[0]
  file.value = f || null

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
    const res = await axios.post('proveedor/qr-actualizar', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    msg.value = res.data.message || 'QR actualizado correctamente.'
    err.value = ''

    // limpiamos input
    file.value = null
    if (fileInput.value) fileInput.value.value = ''

    // opcional: recargar para traer la nueva ruta desde el back
    window.location.reload()
  } catch (e) {
    console.error(e)
    err.value = e.response?.data?.message || 'Error al subir el QR.'
  }
}
</script>

