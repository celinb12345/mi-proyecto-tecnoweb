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
      Gestión de Productos
    </h2>

    <!-- FILTRO POR CATEGORÍA -->
    <div class="flex items-end gap-4 mb-6">
      <div class="flex-1">
        <label class="block text-sm font-semibold mb-1">Filtrar por categoría</label>
        <select
          v-model="filtroCategoria"
          class="input"
          :class="inputClass"
          @change="listarProductos"
        >
          <option value="todos">Todas</option>
          <option
            v-for="c in categorias"
            :key="c.id_categoria"
            :value="c.id_categoria"
          >
            {{ c.cat_nombre }}
          </option>
        </select>
      </div>

      <!-- Botón listar / ocultar -->
      <button @click="toggleLista" class="btn-blue">
        {{ mostrarLista ? 'Ocultar lista' : 'Listar productos' }}
      </button>

      <button @click="toggleCategoriasPanel" class="btn-gray">
        {{ mostrarCategorias ? 'Ocultar categorías' : 'Categorías' }}
      </button>
    </div>

    <!-- FORMULARIO PRODUCTO -->
    <form @submit.prevent="guardarProducto" class="grid grid-cols-2 gap-4 mb-8">
      <div>
        <label class="block text-sm font-semibold mb-1">Nombre</label>
        <input
          v-model="formProd.nombre_prod"
          type="text"
          class="input"
          :class="inputClass"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Tipo de producto</label>
        <input
          v-model="formProd.tipo_producto"
          type="text"
          class="input"
          :class="inputClass"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Categoría</label>
        <select
          v-model="formProd.id_categoria"
          class="input"
          :class="inputClass"
        >
          <option disabled value="">Seleccione categoría</option>
          <option
            v-for="c in categorias"
            :key="c.id_categoria"
            :value="c.id_categoria"
          >
            {{ c.cat_nombre }}
          </option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Precio unitario</label>
        <input
          v-model="formProd.precio_unitario_prod"
          type="number"
          step="0.01"
          class="input"
          :class="inputClass"
        />
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Stock</label>
        <input
          v-model="formProd.stock_prod"
          type="number"
          step="0.01"
          class="input"
          :class="inputClass"
        />
      </div>

      <div class="flex items-center gap-2 col-span-2">
        <input
          id="disp"
          type="checkbox"
          v-model="formProd.prod_disponible"
          class="h-4 w-4"
        />
        <label for="disp" class="text-sm font-semibold">Producto disponible</label>
      </div>

      <div class="col-span-2">
        <label class="block text-sm font-semibold mb-1">Descripción</label>
        <textarea
          v-model="formProd.descripcion_prod"
          rows="2"
          class="input"
          :class="inputClass"
        ></textarea>
      </div>

      <!-- FOTO DEL PRODUCTO -->
      <div>
        <label class="block text-sm font-semibold mb-1">Foto</label>
        <input
          type="file"
          ref="fotoInput"
          @change="onFileChange($event, 'foto')"
          class="input"
          :class="[
            darkMode
              ? 'bg-gray-900 border-gray-700 text-gray-100 file:bg-blue-600 file:text-white'
              : 'bg-white border-gray-300 text-gray-900 file:bg-blue-600 file:text-white'
          ]"
        />
        <div v-if="previewFoto" class="mt-2 flex items-center gap-2">
          <img
            :src="previewFoto"
            class="w-24 h-24 object-cover rounded-lg border"
            :class="darkMode ? 'border-gray-600' : 'border-gray-300'"
          />
          <button type="button" @click="quitarFoto" class="text-red-500 text-sm">
            ✖ Quitar
          </button>
        </div>
      </div>

      <div class="col-span-2 flex justify-end gap-2">
        <button type="submit" class="btn-green">
          {{ editandoProd ? 'Actualizar producto' : 'Registrar producto' }}
        </button>
        <button
          v-if="editandoProd"
          type="button"
          @click="cancelarEdicionProd"
          class="btn-gray"
        >
          Cancelar
        </button>
      </div>
    </form>

    <!-- PANEL DE CATEGORÍAS -->
    <div
      v-if="mostrarCategorias"
      :class="[
        'mb-8 border rounded-xl p-4',
        darkMode ? 'bg-gray-900 border-gray-700' : 'bg-gray-50 border-gray-200'
      ]"
    >
      <h3
        class="font-bold text-lg mb-4"
        :class="darkMode ? 'text-blue-400' : 'text-blue-700'"
      >
        Categorías
      </h3>

      <form @submit.prevent="guardarCategoria" class="grid grid-cols-2 gap-4 mb-4">
        <div>
          <label class="block text-sm font-semibold mb-1">Nombre categoría</label>
          <input
            v-model="formCat.cat_nombre"
            type="text"
            class="input"
            :class="inputClass"
          />
        </div>
        <div>
          <label class="block text-sm font-semibold mb-1">Descripción</label>
          <input
            v-model="formCat.cat_descripcion"
            type="text"
            class="input"
            :class="inputClass"
          />
        </div>
        <div class="col-span-2 flex justify-end gap-2">
          <button type="submit" class="btn-blue">
            {{ editandoCat ? 'Actualizar categoría' : 'Registrar categoría' }}
          </button>
          <button
            v-if="editandoCat"
            type="button"
            @click="cancelarEdicionCat"
            class="btn-gray"
          >
            Cancelar
          </button>
        </div>
      </form>

      <div class="overflow-x-auto">
        <table
          :class="[
            'w-full rounded-lg',
            darkMode ? 'border border-gray-700 bg-gray-900' : 'border border-gray-200 bg-white'
          ]"
        >
          <thead class="bg-blue-600 text-white">
            <tr>
              <th class="px-2 py-1">ID</th>
              <th>Nombre</th>
              <th>Descripción</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="c in categorias"
              :key="c.id_categoria"
              :class="[
                'border-b',
                darkMode
                  ? 'border-gray-700 hover:bg-gray-800'
                  : 'border-gray-200 hover:bg-gray-50'
              ]"
            >
              <td class="px-2 py-1 text-center">{{ c.id_categoria }}</td>
              <td class="px-2 py-1">{{ c.cat_nombre }}</td>
              <td class="px-2 py-1">{{ c.cat_descripcion }}</td>
              <td class="px-2 py-1 text-center space-x-3">
                <button
                  @click="editarCategoria(c)"
                  class="text-blue-500 text-sm font-semibold"
                >
                  ✏️ Editar
                </button>
                <button
                  @click="eliminarCategoria(c.id_categoria)"
                  class="text-red-500 text-sm font-semibold"
                >
                  🗑 Eliminar
                </button>
              </td>
            </tr>
            <tr v-if="!categorias.length">
              <td
                colspan="4"
                class="text-center py-3"
                :class="darkMode ? 'text-gray-300' : 'text-gray-500'"
              >
                No hay categorías registradas
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- LISTA DE PRODUCTOS -->
    <div v-if="mostrarLista" class="overflow-x-auto">
      <table
        :class="[
          'w-full rounded-lg',
          darkMode ? 'border border-gray-700' : 'border border-gray-200'
        ]"
      >
        <thead class="bg-blue-600 text-white">
          <tr>
            <!-- ID oculto en tabla -->
            <th class="px-2 py-2 text-left">Nombre</th>
            <th class="px-2 py-2 text-left">Tipo</th>
            <th class="px-2 py-2 text-left">Categoría</th>
            <th class="px-2 py-2 text-right">Precio</th>
            <th class="px-2 py-2 text-right">Stock</th>
            <th class="px-2 py-2 text-center">Disp.</th>
            <th class="px-2 py-2 text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="p in paginatedProductos"
            :key="p.id_producto"
            :class="[
              'border-b',
              darkMode
                ? 'border-gray-700 hover:bg-gray-800'
                : 'border-gray-200 hover:bg-gray-50'
            ]"
          >
            <!-- sin columna de ID -->
            <td class="px-2 py-2">{{ p.nombre_prod }}</td>
            <td class="px-2 py-2">{{ p.tipo_producto }}</td>
            <td class="px-2 py-2">{{ p.categoria ? p.categoria.cat_nombre : '' }}</td>
            <td class="px-2 py-2 text-right">
              S/ {{ Number(p.precio_unitario_prod || 0).toFixed(2) }}
            </td>
            <td class="px-2 py-2 text-right">{{ p.stock_prod }}</td>
            <td class="px-2 py-2 text-center">
              <span
                class="px-2 py-1 rounded-full text-xs font-semibold"
                :class="
                  p.prod_disponible
                    ? 'bg-green-100 text-green-700'
                    : 'bg-red-100 text-red-700'
                "
              >
                {{ p.prod_disponible ? 'Sí' : 'No' }}
              </span>
            </td>
            <td class="px-2 py-2 text-center space-x-3">
              <button
                @click="editarProducto(p)"
                class="text-blue-500 text-sm font-semibold"
              >
                ✏️ Editar
              </button>
              <button
                @click="eliminarProducto(p.id_producto)"
                class="text-red-500 text-sm font-semibold"
              >
                🗑 Eliminar
              </button>
            </td>
          </tr>
          <tr v-if="!productos.length">
            <td
              colspan="7"
              class="text-center py-3"
              :class="darkMode ? 'text-gray-300' : 'text-gray-500'"
            >
              No hay productos para la categoría seleccionada
            </td>
          </tr>
        </tbody>
      </table>

      <!-- PAGINACIÓN -->
      <div
        v-if="productos.length"
        class="flex flex-col sm:flex-row justify-between items-center gap-3 mt-4 text-sm"
      >
        <span :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
          Mostrando
          <b>{{ startItem }}</b>
          -
          <b>{{ endItem }}</b>
          de
          <b>{{ productos.length }}</b>
          productos
        </span>

        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="prevPage"
            :disabled="currentPage === 1"
            class="px-3 py-1 rounded-lg border text-xs font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
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
            class="px-3 py-1 rounded-lg border text-xs font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
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
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'
const baseUrl = import.meta.env.VITE_APP_URL.replace(/\/$/, '')

const storageUrl = (path) => {
  if (!path) return null
  if (String(path).startsWith('http')) return path   

  const appUrl = baseUrl
  const projectRoot = appUrl.replace('/public', '')

  // aquí realmente están los archivos en el server
  return `${projectRoot}/storage/app/public/${path}`
}

/* Props para modo oscuro y fuente desde el dashboard */
const props = defineProps({
  darkMode: { type: Boolean, default: false },
  fontClass: { type: String, default: 'font-sans' }
})

const darkMode = computed(() => props.darkMode)
const fontClass = computed(() => props.fontClass)

/* ====== ESTADO ====== */
const productos = ref([])
const categorias = ref([])

const filtroCategoria = ref('todos')
const mostrarLista = ref(false)

const formProd = ref({
  id_producto: '',
  nombre_prod: '',
  tipo_producto: '',
  descripcion_prod: '',
  precio_unitario_prod: '',
  stock_prod: '',
  prod_disponible: true,
  foto_prod: '',
  id_categoria: '',
  foto: null
})

const editandoProd = ref(false)
const idProdEditar = ref(null)

const previewFoto = ref(null)
const fotoInput = ref(null)

/* ====== CATEGORÍAS ====== */
const mostrarCategorias = ref(false)
const formCat = ref({
  id_categoria: '',
  cat_nombre: '',
  cat_descripcion: ''
})
const editandoCat = ref(false)
const idCatEditar = ref(null)

/* ====== PAGINACIÓN (PRODUCTOS) ====== */
const currentPage = ref(1)
const perPage = ref(10)

const totalPages = computed(() =>
  productos.value.length
    ? Math.ceil(productos.value.length / perPage.value)
    : 0
)

const paginatedProductos = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  return productos.value.slice(start, start + perPage.value)
})

const startItem = computed(() => {
  if (!productos.value.length) return 0
  return (currentPage.value - 1) * perPage.value + 1
})

const endItem = computed(() => {
  if (!productos.value.length) return 0
  return Math.min(currentPage.value * perPage.value, productos.value.length)
})

function nextPage() {
  if (currentPage.value < totalPages.value) currentPage.value++
}

function prevPage() {
  if (currentPage.value > 1) currentPage.value--
}

/* Reset de página cuando cambian productos */
watch(productos, () => {
  currentPage.value = 1
})

/* ====== CLASES INPUT (modo claro/oscuro) ====== */
const inputClass = computed(() =>
  darkMode.value
    ? 'bg-gray-900 border-gray-700 text-gray-100 placeholder-gray-400'
    : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400'
)

/* ====== CARGA INICIAL ====== */
onMounted(() => {
  listarProductos()
})

function toggleLista() {
  mostrarLista.value = !mostrarLista.value
  if (mostrarLista.value && !productos.value.length) {
    listarProductos()
  }
}

function listarProductos() {
  const params = {}
  if (filtroCategoria.value && filtroCategoria.value !== 'todos') {
    params.id_categoria = filtroCategoria.value
  }

  axios
    .get('propietario/melamina/productos', { params })
    .then((res) => {
      productos.value = res.data.productos || []
      categorias.value = res.data.categorias || []
    })
    .catch((err) => {
      console.error('Error al obtener productos:', err)
      alert('Error al obtener productos')
    })
}

/* ====== ARCHIVOS (FOTO) ====== */
function onFileChange(e, tipo) {
  const file = e.target.files[0]
  if (!file) return
  if (tipo === 'foto') {
    formProd.value.foto = file
    previewFoto.value = URL.createObjectURL(file)
  }
}

function quitarFoto() {
  formProd.value.foto = null
  previewFoto.value = null
  if (fotoInput.value) fotoInput.value.value = null
}

/* ====== CRUD PRODUCTO ====== */
function guardarProducto() {
  const fd = new FormData()

  fd.append('nombre_prod', formProd.value.nombre_prod)
  fd.append('tipo_producto', formProd.value.tipo_producto)
  fd.append('descripcion_prod', formProd.value.descripcion_prod || '')
  fd.append('precio_unitario_prod', formProd.value.precio_unitario_prod || 0)
  fd.append('stock_prod', formProd.value.stock_prod || 0)
  fd.append('prod_disponible', formProd.value.prod_disponible ? 1 : 0)
  fd.append('id_categoria', formProd.value.id_categoria || '')

  if (formProd.value.foto) fd.append('foto', formProd.value.foto)

  const url = editandoProd.value
    ? `propietario/melamina/productos/${idProdEditar.value}`
    : 'propietario/melamina/productos'

  axios
    .post(url, fd, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    .then((res) => {
      if (res.data.success) {
        alert(editandoProd.value ? 'Producto actualizado' : 'Producto registrado')
        limpiarProd()
        listarProductos()
      }
    })
    .catch((err) => {
      console.error('Error al guardar producto:', err)
      alert('Error al guardar producto')
    })
}

function editarProducto(p) {
  editandoProd.value = true
  idProdEditar.value = p.id_producto

  formProd.value.id_producto = p.id_producto
  formProd.value.nombre_prod = p.nombre_prod
  formProd.value.tipo_producto = p.tipo_producto
  formProd.value.descripcion_prod = p.descripcion_prod
  formProd.value.precio_unitario_prod = p.precio_unitario_prod
  formProd.value.stock_prod = p.stock_prod
  formProd.value.prod_disponible = !!p.prod_disponible
  formProd.value.id_categoria = p.id_categoria

  formProd.value.foto = null

previewFoto.value = storageUrl(p.foto_prod)

  if (fotoInput.value) fotoInput.value.value = null
}


function cancelarEdicionProd() {
  editandoProd.value = false
  limpiarProd()
}

function eliminarProducto(id) {
  if (!confirm('¿Eliminar este producto?')) return

  axios
    .delete(`propietario/melamina/productos/${id}`)
    .then(() => {
      alert('Producto eliminado')
      listarProductos()
    })
    .catch((err) => {
      console.error('Error al eliminar producto:', err)
      alert('Error al eliminar producto')
    })
}

function limpiarProd() {
  editandoProd.value = false
  idProdEditar.value = null
  previewFoto.value = null
  if (fotoInput.value) fotoInput.value.value = null

  formProd.value = {
    id_producto: '',
    nombre_prod: '',
    tipo_producto: '',
    descripcion_prod: '',
    precio_unitario_prod: '',
    stock_prod: '',
    prod_disponible: true,
    foto_prod: '',
    id_categoria: '',
    foto: null
  }
}

/* ====== CRUD CATEGORÍAS ====== */
function toggleCategoriasPanel() {
  mostrarCategorias.value = !mostrarCategorias.value
}

function guardarCategoria() {
  const payload = {
    cat_nombre: formCat.value.cat_nombre,
    cat_descripcion: formCat.value.cat_descripcion || ''
  }

  const url = editandoCat.value
    ? `propietario/melamina/categorias/${idCatEditar.value}`
    : 'propietario/melamina/categorias'

  axios
    .post(url, payload)
    .then((res) => {
      if (res.data.success) {
        alert(editandoCat.value ? 'Categoría actualizada' : 'Categoría registrada')
        limpiarCat()
        listarProductos()
      }
    })
    .catch((err) => {
      console.error('Error al guardar categoría:', err)
      alert('Error al guardar categoría')
    })
}

function editarCategoria(c) {
  editandoCat.value = true
  idCatEditar.value = c.id_categoria
  formCat.value.cat_nombre = c.cat_nombre
  formCat.value.cat_descripcion = c.cat_descripcion
}

function eliminarCategoria(id) {
  if (
    !confirm('¿Eliminar esta categoría? (Los productos quedarán sin categoría)')
  )
    return

  axios
    .delete(`propietario/melamina/categorias/${id}`)
    .then(() => {
      alert('Categoría eliminada')
      limpiarCat()
      listarProductos()
    })
    .catch((err) => {
      console.error('Error al eliminar categoría:', err)
      alert('Error al eliminar categoría')
    })
}

function cancelarEdicionCat() {
  editandoCat.value = false
  limpiarCat()
}

function limpiarCat() {
  editandoCat.value = false
  idCatEditar.value = null
  formCat.value = {
    id_categoria: '',
    cat_nombre: '',
    cat_descripcion: ''
  }
}
</script>

<style scoped>
.input {
  @apply rounded-lg p-2 w-full focus:ring-2 focus:ring-blue-400 focus:outline-none transition-colors duration-200;
}
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
