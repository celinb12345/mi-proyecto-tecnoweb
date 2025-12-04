<template>
  <div :class="['space-y-6', isDark ? 'text-gray-100' : 'text-gray-900']">
    <!-- MENSAJE GLOBAL -->
    <transition name="fade">
      <div
        v-if="mensajeExito || mensajeError"
        class="rounded-2xl px-4 py-3 flex items-center gap-3 shadow"
        :class="mensajeExito
          ? (isDark
              ? 'bg-green-900/40 text-green-200 border border-green-700'
              : 'bg-green-50 text-green-700 border border-green-200')
          : (isDark
              ? 'bg-red-900/40 text-red-200 border border-red-700'
              : 'bg-red-50 text-red-700 border border-red-200')"
      >
        <span class="text-2xl">
          {{ mensajeExito ? '✅' : '⚠️' }}
        </span>
        <div>
          <p class="font-semibold">
            {{ mensajeExito || 'Ocurrió un problema' }}
          </p>
          <p v-if="mensajeError && !mensajeExito" class="text-sm mt-1">
            {{ mensajeError }}
          </p>
        </div>
      </div>
    </transition>

    <!-- CABECERA DEL PROYECTO -->
    <section
      :class="[
        'rounded-2xl shadow p-6',
        isDark ? 'bg-gray-800 text-gray-100' : 'bg-gray-50 text-gray-800'
      ]"
    >
      <h2 class="text-3xl font-semibold text-blue-500 mb-6">
        Pagos del proyecto
      </h2>

      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <!-- Proyecto -->
        <div class="flex-1">
          <p
            class="text-sm font-semibold uppercase tracking-wide"
            :class="isDark ? 'text-gray-300' : 'text-gray-500'"
          >
            Proyecto
          </p>
          <p class="text-xl font-bold mt-1">
            {{ proyecto?.nombre_pro || 'Sin nombre' }}
          </p>
          <p
            class="text-base mt-1"
            :class="isDark ? 'text-gray-300' : 'text-gray-600'"
          >
            {{ proyecto?.descripcion_pro || 'Sin descripción registrada.' }}
          </p>
        </div>

        <!-- Propietario -->
        <div class="flex-1">
          <p
            class="text-sm font-semibold uppercase tracking-wide"
            :class="isDark ? 'text-gray-300' : 'text-gray-500'"
          >
            Propietario
          </p>
          <p class="text-xl font-bold mt-1">
            <span v-if="propietario">
              {{ propietario.nombre_propietario }} {{ propietario.apellido_propietario }}
            </span>
            <span v-else>Sin propietario</span>
          </p>
        </div>

        <!-- Monto + Saldos (SOLO FRONT) -->
        <div class="flex-1 text-right space-y-1">
          <p
            class="text-sm font-semibold uppercase tracking-wide"
            :class="isDark ? 'text-gray-300' : 'text-gray-500'"
          >
            Monto del proyecto
          </p>
          <p class="text-3xl font-extrabold text-green-500 mt-1">
            {{ currency(totalProyecto) }}
          </p>

          <!-- contador de días para la próxima cuota -->
          <p v-if="diasProximaCuota !== null" class="text-sm mt-1">
            Próxima cuota vence en
            <span
              :class="diasProximaCuota <= 3 ? 'text-red-500 font-semibold' : 'font-semibold'"
            >
              {{ diasProximaCuota }} día(s)
            </span>
          </p>

          <div
            class="text-sm mt-2 space-y-1"
            :class="isDark ? 'text-gray-200' : 'text-gray-700'"
          >
            <p>
              Pagado hasta ahora:
              <span class="font-semibold text-blue-400">
                {{ currency(totalPagadoLocal) }}
              </span>
            </p>
            <p>
              Saldo pendiente:
              <span class="font-semibold">
                {{ currency(saldoActual) }}
              </span>
            </p>
            <p v-if="form.monto_total_pago > 0" class="mt-1">
              Saldo <b>después</b> de este pago:
              <span class="font-bold text-red-400">
                {{ currency(saldoDespuesPago) }}
              </span>
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- FORMULARIO DE PAGO -->
    <section
      :class="[
        'rounded-2xl shadow p-6',
        isDark ? 'bg-gray-800 text-gray-100' : 'bg-white text-gray-800'
      ]"
    >
      <h3 class="text-xl font-semibold text-blue-500 mb-4">Registrar un pago</h3>

      <form @submit.prevent="registrarPago" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Tipo fijo -->
        <div>
          <label class="text-sm font-semibold mb-1 block">Tipo de pago</label>
          <input
            disabled
            value="Cliente / Proyecto"
            class="w-full rounded-lg border px-3 py-2 bg-gray-100 text-gray-700"
          />
        </div>

        <!-- Método -->
        <div>
          <label class="text-sm font-semibold mb-1 block">Método de pago</label>
          <select
            v-model="form.metodo_pago"
            class="w-full rounded-lg border px-3 py-2"
            :class="isDark ? 'bg-gray-900 border-gray-600' : 'bg-white border-gray-300'"
          >
            <option disabled value="">Seleccione</option>
            <option value="efectivo">Efectivo</option>
            <option value="qr">QR</option>
          </select>
        </div>

        <!-- Nº comprobante -->
        <div>
          <label class="text-sm font-semibold mb-1 block">Nº comprobante (opcional)</label>
          <input
            v-model="form.numero_comprobante"
            type="number"
            class="w-full rounded-lg border px-3 py-2"
            :class="isDark ? 'bg-gray-900 border-gray-600' : 'bg-white border-gray-300'"
          />
        </div>

        <!-- Fecha -->
        <div>
          <label class="text-sm font-semibold mb-1 block">Fecha de pago</label>
          <input
            type="date"
            v-model="form.fecha_pago"
            class="w-full rounded-lg border px-3 py-2"
            :class="isDark ? 'bg-gray-900 border-gray-600' : 'bg-white border-gray-300'"
          />
        </div>

        <!-- Concepto -->
        <div class="md:col-span-2">
          <label class="text-sm font-semibold mb-1 block">Concepto</label>
          <input
            v-model="form.concepto_pago"
            type="text"
            placeholder="Ej: pago de cuota, pago parcial, etc."
            class="w-full rounded-lg border px-3 py-2"
            :class="isDark ? 'bg-gray-900 border-gray-600' : 'bg-white border-gray-300'"
          />
        </div>

        <!-- Monto -->
        <div>
          <label class="text-sm font-semibold mb-1 block">Monto del pago</label>
          <input
            type="number"
            min="0"
            step="0.01"
            v-model.number="form.monto_total_pago"
            class="w-full rounded-lg border px-3 py-2"
            :class="isDark ? 'bg-gray-900 border-gray-600' : 'bg-white border-gray-300'"
          />
          <p
            class="text-xs mt-1"
            :class="isDark ? 'text-gray-400' : 'text-gray-500'"
          >
            Si usas cuotas nuevas, este será el monto de la <b>primera cuota</b>.
          </p>
        </div>

        <!-- En cuotas -->
        <div>
          <label class="text-sm font-semibold mb-1 block">¿En cuotas?</label>
          <select
            v-model="form.enCuotas"
            :disabled="!!cuotaSeleccionada"
            class="w-full rounded-lg border px-3 py-2"
            :class="isDark ? 'bg-gray-900 border-gray-600' : 'bg-white border-gray-300'"
          >
            <option value="no">Pago directo</option>
            <option value="si">En cuotas</option>
          </select>
          <p
            v-if="cuotaSeleccionada"
            class="text-[11px] mt-1"
            :class="isDark ? 'text-gray-400' : 'text-gray-500'"
          >
            Estás pagando la cuota #{{ cuotaSeleccionada.numero_cuota }} (pago directo).
          </p>
        </div>

        <!-- PLAN DE CUOTAS (solo cuando se está creando un plan nuevo) -->
        <div v-if="form.enCuotas === 'si'" class="md:col-span-2 mt-2">
          <fieldset
            class="border p-4 rounded-xl"
            :class="isDark ? 'border-gray-600' : 'border-gray-300'"
          >
            <legend
              class="px-2 text-sm"
              :class="isDark ? 'text-gray-300' : 'text-gray-600'"
            >
              Plan de cuotas
            </legend>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
              <div>
                <label class="text-sm font-semibold mb-1 block">Número de cuotas</label>
                <input
                  type="number"
                  min="1"
                  v-model.number="form.cantidadCuotas"
                  class="w-full rounded-lg border px-3 py-2"
                  :class="isDark ? 'bg-gray-900 border-gray-600' : 'bg-white border-gray-300'"
                />
              </div>

              <div>
                <label class="text-sm font-semibold mb-1 block">Fecha 1ª cuota</label>
                <input
                  type="date"
                  v-model="form.primeraFecha"
                  class="w-full rounded-lg border px-3 py-2"
                  :class="isDark ? 'bg-gray-900 border-gray-600' : 'bg-white border-gray-300'"
                />
              </div>

              <div class="flex items-end">
                <button
                  type="button"
                  @click="generarPlan"
                  class="w-full text-white px-4 py-2 rounded-lg"
                  :class="isDark ? 'bg-gray-600 hover:bg-gray-500' : 'bg-gray-700 hover:bg-gray-800'"
                >
                  Generar plan
                </button>
              </div>
            </div>

            <table
              v-if="form.planCuotas.length"
              class="w-full text-sm border mt-2"
              :class="isDark ? 'border-gray-600' : 'border-gray-300'"
            >
              <thead :class="isDark ? 'bg-gray-700' : 'bg-gray-100'">
                <tr>
                  <th class="p-2 border" :class="isDark ? 'border-gray-600' : 'border-gray-300'">#</th>
                  <th class="p-2 border" :class="isDark ? 'border-gray-600' : 'border-gray-300'">Monto</th>
                  <th class="p-2 border" :class="isDark ? 'border-gray-600' : 'border-gray-300'">Fecha</th>
                  <th class="p-2 border" :class="isDark ? 'border-gray-600' : 'border-gray-300'">Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(c, i) in form.planCuotas" :key="i">
                  <td class="p-2 border" :class="isDark ? 'border-gray-600' : 'border-gray-300'">
                    {{ c.numero }}
                  </td>
                  <td class="p-2 border" :class="isDark ? 'border-gray-600' : 'border-gray-300'">
                    {{ currency(c.monto) }}
                  </td>
                  <td class="p-2 border" :class="isDark ? 'border-gray-600' : 'border-gray-300'">
                    <input
                      type="date"
                      v-model="c.fecha"
                      class="w-full border rounded-lg px-2 py-1 text-sm"
                      :class="isDark ? 'bg-gray-900 border-gray-600' : 'bg-white border-gray-300'"
                    />
                  </td>
                  <td class="p-2 border" :class="isDark ? 'border-gray-600' : 'border-gray-300'">
                    {{ c.estado }}
                  </td>
                </tr>
              </tbody>
            </table>
          </fieldset>
        </div>

        <!-- Botón -->
        <div class="md:col-span-2 flex justify-end mt-4">
          <button
            type="submit"
            class="px-8 py-3 rounded-lg font-semibold text-base text-white"
            :class="isDark ? 'bg-green-700 hover:bg-green-600' : 'bg-green-600 hover:bg-green-700'"
          >
            Registrar pago
          </button>
        </div>
      </form>
    </section>

    <!-- CUOTAS PENDIENTES -->
    <section
      :class="[
        'rounded-2xl shadow p-6',
        isDark ? 'bg-gray-800 text-gray-100' : 'bg-white text-gray-800'
      ]"
    >
      <div class="flex items-center justify-between mb-2">
        <h3 class="text-xl font-semibold text-blue-500">Cuotas pendientes</h3>
        <button
          class="text-sm text-white px-3 py-1 rounded-lg"
          :class="isDark ? 'bg-yellow-600 hover:bg-yellow-500' : 'bg-yellow-500 hover:bg-yellow-600'"
          @click="cargarCuotasPendientes"
        >
          Actualizar
        </button>
      </div>

      <p
        class="text-[11px] mb-2"
        :class="isDark ? 'text-gray-400' : 'text-gray-500'"
      >
        Haz clic en una fila para seleccionar esa cuota y registrarla como pago directo.
      </p>

      <table
        class="w-full text-sm border"
        :class="isDark ? 'border-gray-600' : 'border-gray-300'"
      >
        <thead :class="isDark ? 'bg-yellow-900/40' : 'bg-yellow-100'">
          <tr>
            <th class="p-2 border" :class="isDark ? 'border-gray-600' : 'border-gray-300'">#</th>
            <th class="p-2 border" :class="isDark ? 'border-gray-600' : 'border-gray-300'">Monto</th>
            <th class="p-2 border" :class="isDark ? 'border-gray-600' : 'border-gray-300'">Vencimiento</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="c in cuotasPendientes"
            :key="c.id_cuota"
            @click="seleccionarCuota(c)"
            :class="[
              'border-b cursor-pointer',
              isDark ? 'border-gray-700' : 'border-gray-200',
              cuotaSeleccionada && cuotaSeleccionada.id_cuota === c.id_cuota
                ? (isDark ? 'bg-blue-900/40' : 'bg-blue-50')
                : (isDark ? 'hover:bg-gray-700' : 'hover:bg-yellow-50')
            ]"
          >
            <td class="p-2 border-0">{{ c.numero_cuota }}</td>
            <td class="p-2 border-0">{{ currency(c.monto_cuota) }}</td>
            <td class="p-2 border-0">{{ c.fecha_vencimiento }}</td>
          </tr>
          <tr v-if="!cuotasPendientes.length">
            <td
              colspan="3"
              class="text-center py-3"
              :class="isDark ? 'text-gray-400' : 'text-gray-500'"
            >
              No tienes cuotas pendientes.
            </td>
          </tr>
        </tbody>
      </table>
    </section>

    <!-- QR -->
    <section
      v-if="qrImage"
      :class="[
        'rounded-2xl shadow p-6 text-center',
        isDark ? 'bg-gray-800 text-gray-100' : 'bg-white text-gray-800'
      ]"
    >
      <h3 class="text-lg font-semibold mb-2 text-blue-500">Escanea el código QR para realizar tu pago</h3>
      <img :src="qrImage" class="w-64 mx-auto shadow-lg rounded-xl p-3 bg-white" />
      <button
        @click="consultarQR"
        class="mt-4 text-white px-4 py-2 rounded-lg"
        :class="isDark ? 'bg-blue-600 hover:bg-blue-500' : 'bg-blue-600 hover:bg-blue-700'"
      >
        Verificar pago
      </button>
    </section>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'

const page = usePage()

const props = defineProps({
  darkMode: { type: Boolean, default: false },
})

const isDark = computed(() => props.darkMode)

const proyecto = computed(() => page.props.proyecto ?? null)
const propietario = computed(() => page.props.propietario ?? null)
const totalPagadoLocal = ref(Number(page.props.totalPagado || 0))

const currency = n =>
  Number(n || 0).toLocaleString('es-BO', {
    style: 'currency',
    currency: 'BOB',
  })

const totalProyecto = computed(() =>
  proyecto.value ? Number(proyecto.value.monto_total_pro ?? 0) : 0
)

const saldoActual = computed(() => totalProyecto.value - totalPagadoLocal.value)

const form = reactive({
  tipo_pago: 'cliente',
  metodo_pago: '',
  numero_comprobante: null,
  concepto_pago: '',
  fecha_pago: '',
  monto_total_pago: 0,
  enCuotas: 'no',
  cantidadCuotas: 1,
  primeraFecha: '',
  planCuotas: [],
})

const saldoDespuesPago = computed(
  () => saldoActual.value - Number(form.monto_total_pago || 0)
)

const cuotasPendientes = ref([])
const cuotaSeleccionada = ref(null)

const qrImage = ref(null)
const qrTransaccion = ref(null)

// mensajes bonitos
const mensajeExito = ref('')
const mensajeError = ref('')

function limpiarMensajes () {
  mensajeExito.value = ''
  mensajeError.value = ''
}

/* ======================
   Próxima cuota - contador de días
====================== */
const diasProximaCuota = computed(() => {
  if (!cuotasPendientes.value.length) return null

  const hoy = new Date()
  hoy.setHours(0, 0, 0, 0)
  const MS_DIA = 1000 * 60 * 60 * 24

  let minimo = null

  for (const c of cuotasPendientes.value) {
    const f = new Date(c.fecha_vencimiento)
    if (isNaN(f)) continue
    f.setHours(0, 0, 0, 0)
    const diff = Math.round((f - hoy) / MS_DIA)
    if (diff < 0) continue
    if (minimo === null || diff < minimo) minimo = diff
  }

  return minimo
})

function verificarAlertaVencimiento() {
  if (!cuotasPendientes.value.length) return

  const hoy = new Date()
  hoy.setHours(0, 0, 0, 0)
  const MS_DIA = 1000 * 60 * 60 * 24

  let hayHoy = false

  for (const c of cuotasPendientes.value) {
    const f = new Date(c.fecha_vencimiento)
    if (isNaN(f)) continue
    f.setHours(0, 0, 0, 0)
    const diff = Math.round((f - hoy) / MS_DIA)
    if (diff === 0) {
      hayHoy = true
      break
    }
  }

  if (hayHoy) {
    alert('Atención: hoy vence al menos una de tus cuotas.')
  }
}

/* Generar plan de cuotas NUEVO */
function generarPlan() {
  limpiarMensajes()

  if (!form.cantidadCuotas || !form.primeraFecha) {
    mensajeError.value = 'Debe completar número de cuotas y fecha inicial.'
    return
  }

  form.planCuotas = []
  const base = totalProyecto.value / form.cantidadCuotas
  const fechaBase = new Date(form.primeraFecha)

  for (let i = 1; i <= form.cantidadCuotas; i++) {
    const f = new Date(fechaBase)
    f.setMonth(f.getMonth() + (i - 1))

    form.planCuotas.push({
      numero: i,
      monto: Number(base.toFixed(2)),
      fecha: f.toISOString().slice(0, 10),
      estado: i === 1 ? 'Pagado' : 'Pendiente',
    })
  }

  if (form.planCuotas.length) {
    form.monto_total_pago = form.planCuotas[0].monto
  }
}

/* Seleccionar cuota de la tabla para pagarla directamente */
function seleccionarCuota(c) {
  limpiarMensajes()

  cuotaSeleccionada.value = c
  form.enCuotas = 'no' // siempre pago directo cuando seleccionas una cuota
  form.monto_total_pago = Number(c.monto_cuota)
  form.concepto_pago = `Pago cuota #${c.numero_cuota}`
  form.fecha_pago = new Date().toISOString().slice(0, 10)
}

/* Reset del formulario después de registrar pago */
function resetForm() {
  form.monto_total_pago = 0
  form.concepto_pago = ''
  form.numero_comprobante = null
  form.metodo_pago = ''
  form.enCuotas = 'no'
  form.cantidadCuotas = 1
  form.primeraFecha = ''
  form.planCuotas = []
  cuotaSeleccionada.value = null
}

/* Registrar pago */
async function registrarPago() {
  try {
    limpiarMensajes()

    if (!proyecto.value) {
      mensajeError.value = 'No se encontró proyecto.'
      return
    }

    if (!form.metodo_pago) {
      mensajeError.value = 'Debe seleccionar método de pago.'
      return
    }

    if (!form.fecha_pago) {
      mensajeError.value = 'Debe indicar la fecha de pago.'
      return
    }

    if (!form.monto_total_pago || form.monto_total_pago <= 0) {
      mensajeError.value = 'El monto del pago debe ser mayor a 0.'
      return
    }

    const payload = {
      id_proyecto: proyecto.value.id_proyecto,
      tipo_pago: 'cliente',
      metodo_pago: form.metodo_pago,
      numero_comprobante: form.numero_comprobante,
      concepto_pago: form.concepto_pago,
      fecha_pago: form.fecha_pago,
      monto_total_pago: form.monto_total_pago,
      en_cuotas: form.enCuotas === 'si',
      plan: form.enCuotas === 'si' ? form.planCuotas : [],
      id_cuota: cuotaSeleccionada.value ? cuotaSeleccionada.value.id_cuota : null,
    }

    if (
      form.metodo_pago === 'qr' &&
      form.enCuotas === 'si' &&
      form.planCuotas.length
    ) {
      payload.monto_total_pago = form.planCuotas[0].monto
    }

    const res = await axios.post('cliente/pagos', payload)

    if (!res.data.ok) {
      mensajeError.value = res.data.message || 'Ocurrió un error al registrar el pago.'
      return
    }

    if (form.metodo_pago === 'qr') {
      const imgBase64 = res.data.qr_image
      qrImage.value = imgBase64 ? 'data:image/png;base64,' + imgBase64 : null
      qrTransaccion.value = res.data.transaction || null
      mensajeExito.value =
        res.data.message || 'QR generado correctamente. Escanea el código para completar tu pago.'
    } else {
      mensajeExito.value = res.data.message || 'Pago realizado con éxito. ¡Gracias por tu pago!'
      // actualizamos el total pagado en el front
      totalPagadoLocal.value += Number(form.monto_total_pago || 0)
      await cargarCuotasPendientes()
      resetForm()
    }
  } catch (e) {
    console.error(e)
    mensajeError.value = 'Error al registrar el pago.'
  }
}

/* Consultar QR */
async function consultarQR() {
  try {
    limpiarMensajes()

    const res = await axios.post('cliente/pagos/consultar', {
      codigo: qrTransaccion.value,
    })

    if (res.data.isPaid) {
      // Ocultamos el QR al confirmarse el pago
      qrImage.value = null
      qrTransaccion.value = null

      const monto = Number(res.data.pago?.monto_total_pago || 0)
      totalPagadoLocal.value += monto

      await cargarCuotasPendientes()
      resetForm()

      mensajeExito.value = 'Pago realizado con éxito. ¡Gracias por tu pago!'
    } else {
      mensajeError.value = 'Estado del pago: ' + (res.data.estado || 'Pendiente')
    }
  } catch (e) {
    console.error(e)
    mensajeError.value = 'Error al verificar el estado del pago.'
  }
}

/* Cuotas pendientes */
async function cargarCuotasPendientes() {
  const r = await axios.get('cliente/pagos/cuotas-pendientes')
  cuotasPendientes.value = r.data.cuotas || []
  verificarAlertaVencimiento()
}

/* Init */
onMounted(() => {
  form.fecha_pago = new Date().toISOString().slice(0, 10)
  cargarCuotasPendientes()
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
