<template>
  <section
    :class="[
      'rounded-2xl shadow p-6 w-full transition-colors duration-300',
      darkMode ? 'bg-gray-800 text-gray-100' : 'bg-white text-gray-800'
    ]"
  >
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
      <div>
        <h1 class="text-2xl font-bold text-blue-600">
          🧾 Solicitudes de compra
        </h1>
        <p
          class="text-sm"
          :class="darkMode ? 'text-gray-300' : 'text-gray-600'"
        >
          Aquí se muestran las compras <b>pendientes</b> que el propietario
          registró a tu nombre. Solo puedes modificar el
          <b>estado</b>, el <b>método de pago</b> y el <b>total</b>.
        </p>
      </div>

      <div class="flex gap-2">
        <button
          class="px-3 py-2 rounded-lg text-sm font-semibold"
          :class="darkMode ? 'bg-gray-700 hover:bg-gray-600' : 'bg-gray-200 hover:bg-gray-300'"
          @click="volverPanel"
        >
          ⬅ Volver al panel
        </button>
        <button
          class="px-3 py-2 rounded-lg text-sm font-semibold text-white"
          :class="darkMode ? 'bg-blue-600 hover:bg-blue-500' : 'bg-blue-600 hover:bg-blue-700'"
          @click="cargarSolicitudes"
        >
          🔄 Actualizar
        </button>
      </div>
    </div>

    <div v-if="cargando" class="text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-500'">
      Cargando solicitudes...
    </div>

    <div v-else>
      <table class="w-full text-sm border rounded-lg overflow-hidden"
             :class="darkMode ? 'border-gray-700' : 'border-gray-200'">
        <thead :class="darkMode ? 'bg-gray-700 text-gray-100' : 'bg-blue-600 text-white'">
          <tr>
            <th class="px-2 py-2">ID</th>
            <th class="px-2 py-2">Fecha</th>
            <th class="px-2 py-2">Proyecto</th>
            <th class="px-2 py-2">Observación</th>
            <th class="px-2 py-2">Total</th>
            <th class="px-2 py-2">Método de pago</th>
            <th class="px-2 py-2">Estado</th>
            <th class="px-2 py-2">Acción</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="c in solicitudes"
            :key="c.id_compra"
            class="align-top"
            :class="darkMode ? 'border-b border-gray-700 hover:bg-gray-700/40' : 'border-b border-gray-200 hover:bg-gray-50'"
          >
            <td class="px-2 py-2 text-center">
              {{ c.id_compra }}
            </td>

            <td class="px-2 py-2 text-center">
              {{ c.fecha_compra }}
            </td>

            <td class="px-2 py-2">
              {{ c.proyecto ? c.proyecto.nombre_pro : 'Sin proyecto' }}
            </td>

            <!-- OBSERVACIÓN con scroll -->
            <td class="px-2 py-2 max-w-xs">
              <div
                class="max-h-24 overflow-y-auto text-xs whitespace-pre-line rounded-md px-2 py-1"
                :class="darkMode ? 'border border-gray-600 bg-gray-900' : 'border border-gray-200 bg-gray-50'"
              >
                {{ c.observacion_comp || 'Sin observación.' }}
              </div>
            </td>

            <!-- TOTAL editable -->
            <td class="px-2 py-2 text-right whitespace-nowrap">
              <input
                type="number"
                step="0.01"
                v-model.number="c.total_compra"
                class="input text-right text-xs w-24 inline-block"
              />
            </td>

            <!-- MÉTODO DE PAGO: solo EFECTIVO / QR -->
            <td class="px-2 py-2">
              <select
                v-model="c.metodo_pago_comp"
                class="input text-xs"
              >
                <option disabled value="">Seleccione</option>
                <option value="EFECTIVO">Efectivo</option>
                <option value="QR">QR</option>
              </select>
            </td>

            <!-- ESTADO: Pendiente / Completada / Anulada -->
            <td class="px-2 py-2">
              <select
                v-model="c.estado_compra"
                class="input text-xs"
              >
                <option value="Pendiente">Pendiente</option>
                <option value="Completada">Completada</option>
                <option value="Anulada">Anulada</option>
              </select>
            </td>

            <td class="px-2 py-2">
              <div class="flex gap-1 justify-center">
                <button
                  class="px-3 py-1 rounded-lg text-xs font-semibold text-white"
                  :class="darkMode ? 'bg-green-600 hover:bg-green-500' : 'bg-green-600 hover:bg-green-700'"
                  @click="guardarSolicitud(c)"
                >
                  💾 Guardar
                </button>

                <!-- 🔍 BOTÓN VER UBICACIÓN -->
                <button
                  class="px-3 py-1 rounded-lg text-xs font-semibold"
                  :class="darkMode ? 'bg-blue-500 hover:bg-blue-400 text-white' : 'bg-blue-100 hover:bg-blue-200 text-blue-800'"
                  @click="verUbicacion(c)"
                >
                  Ver
                </button>
              </div>
            </td>
          </tr>

          <tr v-if="!solicitudes.length">
            <td colspan="8" class="px-3 py-4 text-center"
                :class="darkMode ? 'text-gray-300' : 'text-gray-500'">
              No tienes compras pendientes por el momento.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
  darkMode: { type: Boolean, default: false }
})

// evento que el padre escuchará
const emit = defineEmits(['ver-proyecto'])

const solicitudes = ref([])
const cargando = ref(false)

async function cargarSolicitudes () {
  try {
    cargando.value = true
    const { data } = await axios.get('proveedor/compras/pendientes')
    solicitudes.value = data?.compras || []
  } catch (e) {
    console.error(e)
    alert('Error al cargar solicitudes de compra')
  } finally {
    cargando.value = false
  }
}

async function guardarSolicitud (compra) {
  try {
    await axios.put(`proveedor/compras/${compra.id_compra}`, {
      estado_compra: compra.estado_compra,
      metodo_pago_comp: compra.metodo_pago_comp,
      total_compra: compra.total_compra
    })
    alert('Solicitud actualizada correctamente')
    await cargarSolicitudes()
  } catch (e) {
    console.error(e)
    alert('Error al actualizar la solicitud')
  }
}

// 👉 cuando se hace clic en VER
function verUbicacion (compra) {
  if (!compra.proyecto) {
    alert('Esta compra no tiene proyecto asociado.')
    return
  }
  emit('ver-proyecto', compra.proyecto)
}

function volverPanel () {
  router.visit('proveedor/dashboard')
}

onMounted(() => {
  cargarSolicitudes()
})
</script>

<style scoped>
.input {
  @apply border border-gray-300 rounded-lg p-2 w-full focus:ring-2 focus:ring-blue-400 focus:outline-none;
}
button {
  transition: all 0.2s ease;
}
</style>
