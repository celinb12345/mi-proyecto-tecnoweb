<template>
  <div
    :class="[
      'p-8 rounded-2xl shadow-md transition-colors duration-300',
      props.darkMode ? 'bg-gray-800 text-gray-100' : 'bg-white text-gray-900',
      props.fontClass
    ]"
  >
    <h2 class="text-2xl font-bold text-blue-500 mb-6">Gestión de Compras</h2>

    <!-- FILTRO POR FECHAS -->
    <div class="grid grid-cols-3 gap-4 mb-6">
      <div>
        <label class="block text-sm font-semibold mb-1">Desde</label>
        <input
          type="date"
          v-model="fechaDesde"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-900'
          ]"
        />
      </div>
      <div>
        <label class="block text-sm font-semibold mb-1">Hasta</label>
        <input
          type="date"
          v-model="fechaHasta"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-900'
          ]"
        />
      </div>
      <div class="flex items-end gap-2">
        <button @click="buscarCompras" class="btn-blue w-full">Buscar</button>
        <button @click="toggleLista" class="btn-gray w-full">
          {{ mostrarLista ? 'Ocultar Lista' : 'Listar' }}
        </button>
      </div>
    </div>

    <!-- FORMULARIO DE COMPRA -->
    <form @submit.prevent="guardar" class="grid grid-cols-2 gap-4 mb-6">
      <!-- FECHA -->
      <div>
        <label class="block text-sm font-semibold mb-1">Fecha</label>
        <input
          type="date"
          v-model="form.fecha_compra"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-900'
          ]"
        />
      </div>

      <!-- PROVEEDOR -->
      <div>
        <label class="block text-sm font-semibold mb-1">Proveedor</label>
        <select
          v-model="form.id_proveedor"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-900'
          ]"
        >
          <option disabled value="">Seleccione un proveedor</option>
          <option
            v-for="p in proveedores"
            :key="p.id_proveedor"
            :value="p.id_proveedor"
          >
            {{ p.nombre_empres_prov }}
          </option>
        </select>
      </div>

      <!-- PROYECTO -->
      <div>
        <label class="block text-sm font-semibold mb-1">Proyecto</label>
        <select
          v-model="form.id_proyecto"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-900'
          ]"
        >
          <option value="">Sin proyecto</option>
          <option
            v-for="pr in proyectos"
            :key="pr.id_proyecto"
            :value="pr.id_proyecto"
          >
            {{ pr.nombre_pro }}
          </option>
        </select>
      </div>

      <!-- MÉTODO DE PAGO -->
      <div>
        <label class="block text-sm font-semibold mb-1">Método de Pago</label>
        <select
          v-model="form.metodo_pago_comp"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-900'
          ]"
        >
          <option disabled value="">Seleccione</option>
          <option value="Efectivo">Efectivo</option>
          <option value="QR">QR</option>
        </select>
      </div>

      <!-- ESTADO -->
      <div>
        <label class="block text-sm font-semibold mb-1">Estado</label>
        <select
          v-model="form.estado_compra"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-900'
          ]"
        >
          <option disabled value="">Seleccione</option>
          <option value="Pendiente">Pendiente</option>
          <option value="Completada">Completada</option>
          <option value="Anulada">Anulada</option>
        </select>
      </div>

      <!-- OBSERVACIÓN -->
      <div class="col-span-2">
        <label class="block text-sm font-semibold mb-1">Observación</label>
        <textarea
          v-model="form.observacion_comp"
          rows="2"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100 placeholder-gray-400'
              : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400'
          ]"
        ></textarea>
      </div>

      <!-- TOTAL -->
      <div>
        <label class="block text-sm font-semibold mb-1">Total compra</label>
        <input
          type="text"
          :class="[
            inputBaseClass,
            props.darkMode
              ? 'bg-gray-900 border-gray-600 text-gray-100'
              : 'bg-gray-100 border-gray-300 text-gray-900'
          ]"
          :value="Number(form.total_compra || 0).toFixed(2)"
          readonly
        />
      </div>

      <div class="col-span-2 flex justify-end gap-2">
        <button type="submit" class="btn-green">
          {{ editando ? 'Actualizar' : 'Registrar' }}
        </button>

        <button
          v-if="editando"
          type="button"
          @click="cancelarEdicion"
          class="btn-gray"
        >
          Cancelar
        </button>
      </div>
    </form>

    <!-- TABLA DE COMPRAS -->
    <div v-if="mostrarLista" class="overflow-x-auto mb-8">
      <table class="w-full border border-gray-200 rounded-lg text-sm text-center">
        <thead class="bg-blue-600 text-white">
          <tr>
            <th class="px-2 py-2 border border-blue-500/60">Fecha</th>
            <th class="px-2 py-2 border border-blue-500/60">Proveedor</th>
            <th class="px-2 py-2 border border-blue-500/60">Proyecto</th>
            <th class="px-2 py-2 border border-blue-500/60">Total</th>
            <th class="px-2 py-2 border border-blue-500/60">Método</th>
            <th class="px-2 py-2 border border-blue-500/60">Estado</th>
            <th class="px-2 py-2 border border-blue-500/60">Acciones</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="c in paginatedCompras"
            :key="c.id_compra"
            :class="props.darkMode
              ? 'border-b border-gray-700 hover:bg-gray-700'
              : 'border-b hover:bg-gray-50'"
          >
            <td class="px-2 py-2">{{ formatFecha(c.fecha_compra) }}</td>
            <td class="px-2 py-2">{{ c.proveedor?.nombre_empres_prov }}</td>
            <td class="px-2 py-2">{{ c.proyecto?.nombre_pro || '—' }}</td>
            <td class="px-2 py-2">
              S/ {{ Number(c.total_compra || 0).toFixed(2) }}
            </td>
            <td class="px-2 py-2">{{ c.metodo_pago_comp }}</td>
            <td class="px-2 py-2">{{ c.estado_compra }}</td>

            <td class="px-2 py-2 flex gap-2 justify-center">
              <!-- EDITAR -->
              <button
                @click="editar(c)"
                class="px-3 py-1 rounded-full text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700"
              >
                ✏️ Editar
              </button>

              <!-- ELIMINAR -->
              <button
                v-if="c.estado_compra !== 'Completada'"
                @click="eliminar(c.id_compra)"
                class="px-3 py-1 rounded-full text-xs font-semibold text-white bg-red-600 hover:bg-red-700"
              >
                🗑 Eliminar
              </button>

              <!-- DETALLE COMPRA -->
              <button
                v-if="c.estado_compra === 'Completada'"
                @click="toggleDetalleCompra(c)"
                class="px-3 py-1 rounded-full text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700"
              >
                {{ compraSeleccionada && compraSeleccionada.id_compra === c.id_compra && mostrarDetalle
                  ? 'Ocultar detalle'
                  : 'Detalle compra' }}
              </button>
            </td>
          </tr>

          <tr v-if="!compras.length">
            <td
              colspan="7"
              class="text-center py-3"
              :class="props.darkMode ? 'text-gray-300' : 'text-gray-500'"
            >
              No hay compras en el rango seleccionado
            </td>
          </tr>
        </tbody>
      </table>

      <!-- PAGINACIÓN -->
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
          <b>{{ compras.length }}</b>
          compras
        </div>

        <div class="flex flex-wrap items-center gap-1">
          <button
            class="px-2 py-1 rounded border"
            :class="props.darkMode
              ? 'bg-gray-800 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-700'"
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
            :class="props.darkMode
              ? 'bg-gray-800 border-gray-600 text-gray-100'
              : 'bg-white border-gray-300 text-gray-700'"
            @click="nextPage"
            :disabled="currentPage === totalPages"
          >
            ➡
          </button>
        </div>
      </div>
    </div>

    <!-- PANEL DETALLE COMPRA -->
    <div
      v-if="mostrarDetalle && compraSeleccionada"
      class="mt-6 border-t pt-4 space-y-4"
    >
      <div class="flex items-center justify-between mb-2">
        <h3 class="text-lg font-semibold text-blue-500">
          Detalle de compra #{{ compraSeleccionada.id_compra }}
        </h3>
        <button class="btn-gray" @click="cerrarDetalle">
          Cerrar detalle
        </button>
      </div>

      <!-- INFO RESUMEN -->
      <p class="text-sm">
        <b>Proveedor:</b>
        {{ compraSeleccionada.proveedor?.nombre_empres_prov || '—' }} ·
        <b>Fecha:</b> {{ formatFecha(compraSeleccionada.fecha_compra) }} ·
        <b>Total actual:</b> S/
        {{ Number(totalDetalle || 0).toFixed(2) }}
      </p>

      <!-- FORM NUEVO DETALLE -->
      <form
        class="grid grid-cols-4 gap-4 items-end"
        @submit.prevent="agregarDetalle"
      >
        <!-- PRODUCTO -->
        <div class="col-span-2">
          <label class="block text-xs font-semibold mb-1">Producto</label>
          <select
            v-model="detalleForm.id_producto"
            @change="onProductoChange"
            :class="[
              inputBaseClass,
              props.darkMode
                ? 'bg-gray-900 border-gray-600 text-gray-100'
                : 'bg-white border-gray-300 text-gray-900'
            ]"
            required
          >
            <option value="">Seleccione producto</option>
            <option
              v-for="prod in productos"
              :key="prod.id_producto"
              :value="prod.id_producto"
            >
              {{ prod.nombre_prod }}
            </option>
          </select>
        </div>

        <!-- CANTIDAD -->
        <div>
          <label class="block text-xs font-semibold mb-1">Cantidad</label>
          <input
            type="number"
            min="1"
            step="1"
            v-model.number="detalleForm.cantidad"
            :class="[
              inputBaseClass,
              props.darkMode
                ? 'bg-gray-900 border-gray-600 text-gray-100'
                : 'bg-white border-gray-300 text-gray-900'
            ]"
            required
          />
        </div>

        <!-- PRECIO UNITARIO -->
        <div>
          <label class="block text-xs font-semibold mb-1">
            Precio unitario
          </label>
          <input
            type="number"
            min="0"
            step="0.01"
            v-model.number="detalleForm.precio_unitario"
            :class="[
              inputBaseClass,
              props.darkMode
                ? 'bg-gray-900 border-gray-600 text-gray-100'
                : 'bg-white border-gray-300 text-gray-900'
            ]"
            required
          />
        </div>

        <!-- SUBTOTAL -->
        <div>
          <label class="block text-xs font-semibold mb-1">Subtotal</label>
          <input
            type="text"
            :value="Number(detalleForm.subtotal || 0).toFixed(2)"
            :class="[
              inputBaseClass,
              props.darkMode
                ? 'bg-gray-900 border-gray-600 text-gray-100'
                : 'bg-gray-100 border-gray-300 text-gray-900'
            ]"
            readonly
          />
        </div>

        <!-- BOTÓN AGREGAR -->
        <div class="col-span-4 flex justify-end">
          <button type="submit" class="btn-green">
            ➕ Agregar detalle
          </button>
        </div>
      </form>

      <!-- TABLA DETALLES -->
      <div class="overflow-x-auto">
        <table class="w-full text-xs md:text-sm border border-gray-300">
          <thead class="bg-gray-100">
            <tr>
              <th class="border px-2 py-1 text-left">Producto</th>
              <th class="border px-2 py-1 text-center">Cantidad</th>
              <th class="border px-2 py-1 text-right">Precio unitario</th>
              <th class="border px-2 py-1 text-right">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="d in detalles" :key="d.id_detalle_compra || d._tmpId">
              <td class="border px-2 py-1">
                {{ d.producto?.nombre_prod || d.nombre_prod || '—' }}
              </td>
              <td class="border px-2 py-1 text-center">
                {{ d.cantidad }}
              </td>
              <td class="border px-2 py-1 text-right">
                {{ Number(d.precio_unitario || 0).toFixed(2) }}
              </td>
              <td class="border px-2 py-1 text-right">
                {{ Number(d.subtotal || 0).toFixed(2) }}
              </td>
            </tr>
            <tr v-if="!detalles.length">
              <td colspan="4" class="text-center py-2 text-gray-500">
                Sin detalles registrados aún.
              </td>
            </tr>
          </tbody>
          <tfoot v-if="detalles.length">
            <tr class="bg-gray-100 font-semibold">
              <td class="border px-2 py-1 text-right" colspan="3">
                Total detalle:
              </td>
              <td class="border px-2 py-1 text-right">
                {{ Number(totalDetalle || 0).toFixed(2) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  darkMode: { type: Boolean, default: false },
  fontClass: { type: String, default: 'font-sans' }
})

const inputBaseClass =
  'rounded-lg p-2 w-full focus:ring-2 focus:ring-blue-400 focus:outline-none transition-colors duration-200'

const compras = ref([])
const proveedores = ref([])
const proyectos = ref([])

const mostrarLista = ref(false)
const editando = ref(false)
const idEditar = ref(null)

const fechaDesde = ref('')
const fechaHasta = ref('')

const form = ref({
  id_compra: '',
  fecha_compra: '',
  total_compra: '',
  metodo_pago_comp: '',
  estado_compra: '',
  observacion_comp: '',
  id_proveedor: '',
  id_proyecto: ''
})

/* DETALLE COMPRA */
const mostrarDetalle = ref(false)
const compraSeleccionada = ref(null)
const detalles = ref([])
const productos = ref([])

const detalleForm = ref({
  id_compra: '',
  id_producto: '',
  cantidad: 1,
  precio_unitario: 0,
  subtotal: 0
})

const totalDetalle = computed(() =>
  detalles.value.reduce((s, d) => s + Number(d.subtotal || 0), 0)
)

watch(
  () => [detalleForm.value.cantidad, detalleForm.value.precio_unitario],
  () => {
    const c = Number(detalleForm.value.cantidad || 0)
    const p = Number(detalleForm.value.precio_unitario || 0)
    detalleForm.value.subtotal = c * p
  }
)

watch(totalDetalle, val => {
  if (
    compraSeleccionada.value &&
    form.value.id_compra === compraSeleccionada.value.id_compra
  ) {
    form.value.total_compra = val
  }
})

/* PAGINACIÓN */
const currentPage = ref(1)
const pageSize = ref(5)

const totalPages = computed(() =>
  compras.value.length
    ? Math.ceil(compras.value.length / pageSize.value)
    : 1
)

const paginatedCompras = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return compras.value.slice(start, start + pageSize.value)
})

const pageInfo = computed(() => {
  if (!compras.value.length) return { from: 0, to: 0 }
  const from = (currentPage.value - 1) * pageSize.value + 1
  const to = Math.min(currentPage.value * pageSize.value, compras.value.length)
  return { from, to }
})

watch(compras, () => {
  if (currentPage.value > totalPages.value) {
    currentPage.value = totalPages.value
  }
})

function goToPage (page) {
  if (page >= 1 && page <= totalPages.value) currentPage.value = page
}
function prevPage () {
  if (currentPage.value > 1) currentPage.value--
}
function nextPage () {
  if (currentPage.value < totalPages.value) currentPage.value++
}

/* FECHA */
function formatFecha (fecha) {
  if (!fecha) return ''
  return new Date(fecha).toLocaleDateString('es-BO', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  })
}

/* CARGAR LISTADO */
onMounted(() => {
  buscarCompras()
})

function cargarDatos (params = {}) {
  return axios
    .get('propietario/Construccion/compras', { params })
    .then(res => {
      compras.value = res.data.compras || []
      proveedores.value = res.data.proveedores || []
      proyectos.value = res.data.proyectos || []
      currentPage.value = 1
    })
    .catch(err => {
      console.error('Error al obtener compras', err.response?.data || err)
      alert('Error al obtener compras')
    })
}

function buscarCompras () {
  const params = {}
  if (fechaDesde.value) params.desde = fechaDesde.value
  if (fechaHasta.value) params.hasta = fechaHasta.value

  return cargarDatos(params).then(() => {
    mostrarLista.value = true
  })
}

function toggleLista () {
  mostrarLista.value = !mostrarLista.value
  if (mostrarLista.value) buscarCompras()
}

/* GUARDAR */
function guardar () {
  const payload = { ...form.value }

  // Normalizar proyecto vacío
  if (!payload.id_proyecto) {
    payload.id_proyecto = null
  }

  const url = editando.value
    ? `propietario/Construccion/compras/${idEditar.value}`
    : 'propietario/Construccion/compras'

  axios
    .post(url, payload)
    .then(res => {
      if (!res.data || !res.data.success) {
        console.error('Respuesta inesperada al guardar compra', res.data)
        alert('Error al guardar compra')
        return
      }

      alert(editando.value ? 'Compra actualizada' : 'Compra registrada')

      // Importante: cualquier error aquí NO debe disparar el catch de axios
      try {
        if (
          res.data.compra &&
          res.data.compra.estado_compra === 'Completada'
        ) {
          buscarCompras()
            .then(() => {
              const comp = compras.value.find(
                c => c.id_compra === res.data.compra.id_compra
              )
              if (comp) toggleDetalleCompra(comp)
            })
            .catch(err => {
              console.error(
                'Error al recargar compras / abrir detalle',
                err.response?.data || err
              )
            })
        } else {
          limpiar()
          buscarCompras()
        }
      } catch (e) {
        console.error('Error posterior al guardado (JS)', e)
        // No relanzamos para que no caiga en el catch de axios
      }
    })
    .catch(err => {
      console.error('Error HTTP al guardar compra', err.response?.data || err)
      alert('Error al guardar compra')
    })
}

/* EDITAR */
function editar (c) {
  editando.value = true
  idEditar.value = c.id_compra
  form.value = { ...c }
}

/* CANCELAR */
function cancelarEdicion () {
  editando.value = false
  limpiar()
}

/* ELIMINAR */
function eliminar (id) {
  if (!confirm('¿Eliminar esta compra?')) return

  axios
    .delete(`propietario/Construccion/compras/${id}`)
    .then(() => {
      alert('Compra eliminada')
      buscarCompras()
    })
    .catch(err => {
      console.error('Error al eliminar compra', err.response?.data || err)
      alert('Error al eliminar compra')
    })
}

function limpiar () {
  form.value = {
    id_compra: '',
    fecha_compra: '',
    total_compra: '',
    metodo_pago_comp: '',
    estado_compra: '',
    observacion_comp: '',
    id_proveedor: '',
    id_proyecto: ''
  }
  editando.value = false
  cerrarDetalle()
}

/* DETALLE COMPRA */
function resetDetalleForm () {
  detalleForm.value = {
    id_compra: compraSeleccionada.value
      ? compraSeleccionada.value.id_compra
      : '',
    id_producto: '',
    cantidad: 1,
    precio_unitario: 0,
    subtotal: 0
  }
}

function cargarProductos () {
  return axios
    .get('propietario/Construccion/productos')
    .then(res => {
      productos.value = res.data.productos || []
    })
    .catch(err => {
      console.error('Error al cargar productos', err.response?.data || err)
      productos.value = []
    })
}

function cargarDetalles (idCompra) {
  return axios
    .get(`propietario/Construccion/compras/${idCompra}/detalles`)
    .then(res => {
      detalles.value = res.data.detalles || []
    })
    .catch(err => {
      console.error('Error al cargar detalles', err.response?.data || err)
      detalles.value = []
    })
}

function toggleDetalleCompra (c) {
  if (
    compraSeleccionada.value &&
    compraSeleccionada.value.id_compra === c.id_compra &&
    mostrarDetalle.value
  ) {
    cerrarDetalle()
    return
  }

  compraSeleccionada.value = c
  mostrarDetalle.value = true
  form.value.id_compra = c.id_compra
  form.value.total_compra = c.total_compra

  resetDetalleForm()
  cargarProductos()
  cargarDetalles(c.id_compra)
}

function cerrarDetalle () {
  mostrarDetalle.value = false
  compraSeleccionada.value = null
  detalles.value = []
  resetDetalleForm()
}

function onProductoChange () {
  if (!detalleForm.value.id_producto) return
  const prod = productos.value.find(
    p => p.id_producto === detalleForm.value.id_producto
  )
  if (prod) {
    detalleForm.value.precio_unitario = Number(
      prod.precio_unitario_prod || 0
    )
  }
}

async function agregarDetalle () {
  if (!compraSeleccionada.value) return

  const payload = {
    id_compra: compraSeleccionada.value.id_compra,
    id_producto: detalleForm.value.id_producto,
    cantidad: detalleForm.value.cantidad,
    precio_unitario: detalleForm.value.precio_unitario,
    subtotal: detalleForm.value.subtotal
  }

  try {
    const { data } = await axios.post(
      'propietario/Construccion/compras/detalles',
      payload
    )

    const nuevo = data.detalle || payload
    nuevo._tmpId = Date.now()

    const prod = productos.value.find(
      p => p.id_producto === payload.id_producto
    )
    if (prod) {
      nuevo.producto = prod
      nuevo.nombre_prod = prod.nombre_prod
    }

    detalles.value.push(nuevo)

    const nuevoTotal = totalDetalle.value
    compraSeleccionada.value.total_compra = nuevoTotal
    if (form.value.id_compra === compraSeleccionada.value.id_compra) {
      form.value.total_compra = nuevoTotal
    }

    resetDetalleForm()
  } catch (e) {
    console.error('Error al agregar detalle de compra', e.response?.data || e)
    alert('Error al agregar detalle de compra')
  }
}
</script>

<style scoped>
.btn-blue {
  @apply bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700;
}
.btn-gray {
  @apply bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600;
}
.btn-green {
  @apply bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700;
}
</style>
