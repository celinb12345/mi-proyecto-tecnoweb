<template>
  <div
    :class="[
      'flex min-h-screen transition-colors duration-300',
      darkMode ? 'bg-gray-900 text-gray-100' : 'bg-gray-100 text-gray-900',
      fontClass
    ]"
  >
    <!-- 🧭 BARRA LATERAL -->
    <aside
      :class="[
        'shadow-md p-4 flex flex-col justify-between transition-all duration-300',
        sidebarCollapsed ? 'w-20' : 'w-64',
        darkMode ? 'bg-gray-800 text-gray-100' : 'bg-white text-gray-800'
      ]"
    >
      <div>
        <!-- Título + botón colapsar -->
        <div class="flex items-center justify-between mb-4">
          <h2
            v-if="!sidebarCollapsed"
            class="text-xl font-bold text-blue-500 whitespace-nowrap"
          >
            Panel Melamina
          </h2>

          <button
            @click="toggleSidebar"
            class="p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700"
            :title="sidebarCollapsed ? 'Expandir menú' : 'Colapsar menú'"
          >
            <img
              :src="blueprintIcon"
              class="w-5 h-5 transition-transform"
              :class="sidebarCollapsed ? 'rotate-180' : ''"
              alt="Colapsar menú"
            />
          </button>
        </div>

        <!-- Toggle modo oscuro -->
        <div
          class="flex items-center justify-between mb-4 px-2 py-2 rounded-lg"
          :class="darkMode ? 'bg-gray-700' : 'bg-gray-100'"
        >
          <span
            v-if="!sidebarCollapsed"
            class="text-xs font-semibold uppercase tracking-wide"
          >
            Modo oscuro
          </span>

          <button
            @click="toggleDarkMode"
            class="flex items-center gap-2 text-sm font-medium"
          >
            <img
              v-if="!sidebarCollapsed"
              :src="darkMode ? moonIcon : sunIcon"
              class="w-5 h-5"
              alt="Modo oscuro"
            />
            <span
              :class="[
                'w-10 h-5 flex items-center rounded-full p-1 transition-all duration-200',
                darkMode ? 'bg-blue-500' : 'bg-gray-300'
              ]"
            >
              <span
                :class="[
                  'w-4 h-4 bg-white rounded-full shadow transform transition-transform duration-200',
                  darkMode ? 'translate-x-5' : 'translate-x-0'
                ]"
              ></span>
            </span>
          </button>
        </div>

        <!-- Menú -->
        <nav class="space-y-2 mt-4">
          <button
            v-for="btn in botones"
            :key="btn.view"
            @click="goTo(btn.view)"
            :class="[
              'w-full flex items-center gap-2 px-3 py-2 rounded-lg font-medium text-sm transition-colors',
              current === btn.view
                ? 'bg-blue-600 text-white'
                : darkMode
                  ? 'text-gray-200 hover:bg-gray-700'
                  : 'text-gray-700 hover:bg-blue-100'
            ]"
          >
            <img :src="btn.icon" class="w-5 h-5" :alt="btn.label" />
            <span v-if="!sidebarCollapsed" class="truncate">{{ btn.label }}</span>
          </button>
        </nav>
      </div>

      <!-- 🚪 BOTÓN SALIR -->
      <div class="mt-6">
        <button
          @click="logout"
          class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold transition"
        >
          🔒 Salir
        </button>
      </div>
    </aside>

    <!-- 📋 CONTENIDO PRINCIPAL -->
    <main class="flex-1 p-6 md:p-8 transition-colors duration-300">
      <!-- CABECERA: nombre + fuente + buscador -->
      <header class="mb-6 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-3xl font-bold mb-1">
              Bienvenido, {{ user.name }}
            </h1>
            <p
              class="text-sm md:text-base"
              :class="darkMode ? 'text-gray-300' : 'text-gray-600'"
            >
              Este es el panel principal del propietario de Melamina. Desde aquí puedes
              gestionar tus proyectos, servicios, proveedores, clientes, compras,
              productos y más.
            </p>
          </div>

          <!-- Selector de tipografía -->
          <div class="flex flex-col items-start md:items-end gap-2 text-xs">
            <span class="uppercase tracking-wide mb-1">
              Tipo de letra
            </span>
            <div class="flex gap-1">
              <button
                v-for="opt in fontOptions"
                :key="opt.key"
                type="button"
                @click="setFont(opt.key)"
                :class="[
                  'px-3 py-1 rounded-full border text-xs font-semibold transition-all',
                  fontKey === opt.key
                    ? 'bg-blue-600 text-white border-blue-500 shadow'
                    : darkMode
                      ? 'bg-gray-700 text-gray-200 border-gray-600 hover:bg-gray-600'
                      : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100'
                ]"
              >
                {{ opt.label }}
              </button>
            </div>
          </div>
        </div>

        <!-- Buscador de secciones -->
        <div class="mt-2 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
          <div class="text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
            Escribe el nombre de una sección (por ejemplo: "Proyectos", "Pagos", "Inventario")
            y te llevaré directamente.
          </div>

          <form
            class="flex gap-2 w-full md:w-auto"
            @submit.prevent="buscarSeccion"
          >
            <input
              v-model="searchView"
              type="text"
              placeholder="Buscar sección..."
              class="flex-1 md:w-64 px-3 py-2 rounded-lg border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="darkMode
                ? 'bg-gray-800 border-gray-700 text-gray-100 placeholder-gray-400'
                : 'bg-white border-gray-300 text-gray-800 placeholder-gray-400'"
            />
            <button
              type="submit"
              class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow"
            >
              Ir
            </button>
          </form>
        </div>
      </header>
                <section v-if="current === 'dashboard'">
  <DashboardEstadisticas :dark-mode="darkMode" />
</section>



      <!-- Secciones dinámicas (pasamos darkMode + fontClass a las hijas) -->
      <div v-if="current === 'proyectos'">
        <Proyectos :dark-mode="darkMode" :font-class="fontClass" />
      </div>
      <div v-if="current === 'servicios'">
        <Servicios :dark-mode="darkMode" :font-class="fontClass" />
      </div>
      <div v-if="current === 'proveedores'">
        <Proveedores :dark-mode="darkMode" :font-class="fontClass" />
      </div>
      <div v-if="current === 'clientes'">
        <Clientes :dark-mode="darkMode" :font-class="fontClass" />
      </div>
      <div v-if="current === 'pagos'">
        <Pagos :dark-mode="darkMode" :font-class="fontClass" />
      </div>
      <div v-if="current === 'trabajadores'">
        <Trabajadores :dark-mode="darkMode" :font-class="fontClass" />
      </div>
      <div v-if="current === 'inventario'">
        <Inventario :dark-mode="darkMode" :font-class="fontClass" />
      </div>
      <div v-if="current === 'compra'">
        <Compra :dark-mode="darkMode" :font-class="fontClass" />
      </div>
      <div v-if="current === 'producto'">
        <Producto :dark-mode="darkMode" :font-class="fontClass" />
      </div>
      <div v-if="current === 'reportes'">
        <Reportes :dark-mode="darkMode" :font-class="fontClass" />
      </div>
      <div v-if="current === 'usuarios'">
        <Usuarios :dark-mode="darkMode" :font-class="fontClass" />
      </div>
      <div v-if="current === 'perfil'">
        <Perfil :dark-mode="darkMode" :font-class="fontClass" />
      </div>
    </main>
  </div>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js' // ✅


// Vistas
import Proyectos from './Proyectos.vue'
import Servicios from './Servicios.vue'
import Proveedores from './Proveedores.vue'
import Clientes from './Clientes.vue'
import Pagos from './Pagos.vue'
import Trabajadores from './Trabajadores.vue'
import Inventario from './Inventario.vue'
import Compra from './Compra.vue'
import Reportes from './Reportes.vue'
import Perfil from './Perfil.vue'
import Usuarios from './Usuarios.vue'
import Producto from './Producto.vue'
import DashboardEstadisticas from './DashboardEstadisticas.vue'

// Iconos
import blueprintIcon from '@/Assets/icons/arrows.png'
import incioIcon from '@/Assets/icons/house.png'
import projectIcon from '@/Assets/icons/construction.png'
import servicesIcon from '@/Assets/icons/maintenance.png'
import proveIcon from '@/Assets/icons/supplier.png'
import clienteIcon from '@/Assets/icons/client.png'
import pagoIcon from '@/Assets/icons/credit-card.png'
import trabajaIcon from '@/Assets/icons/workers.png'
import inventoryIcon from '@/Assets/icons/inventory.png'
import compraIcon from '@/Assets/icons/order.png'
import productIcon from '@/Assets/icons/product-selling.png'
import reporIcon from '@/Assets/icons/report.png'
import userIcon from '@/Assets/icons/working.png'
import perfilIcon from '@/Assets/icons/user.png'
import sunIcon from '@/Assets/icons/sol.png'
import moonIcon from '@/Assets/icons/moon.png'

const { props } = usePage()
const user = props.user || null

onMounted(() => {
  if (!user || !user.id_usuario) {
    router.visit(route('login'))   // 
  }
})

// estado UI
const current = ref('dashboard')
const darkMode = ref(false)
const sidebarCollapsed = ref(false)

// Tipografías
const fontKey = ref('sans')
const fontOptions = [
  { key: 'sans', label: 'Sans', class: 'font-sans' },
  { key: 'serif', label: 'Serif', class: 'font-serif' },
  { key: 'mono', label: 'Mono', class: 'font-mono' }
]

const fontClass = computed(() => {
  const found = fontOptions.find(f => f.key === fontKey.value)
  return found ? found.class : 'font-sans'
})

function setFont (key) {
  fontKey.value = key
}

function toggleDarkMode () {
  darkMode.value = !darkMode.value
}

function toggleSidebar () {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

// botones con iconos
const botones = [
  { view: 'dashboard',   label: 'Inicio',       icon: incioIcon },
  { view: 'proyectos',   label: 'Proyectos',    icon: projectIcon },
  { view: 'servicios',   label: 'Servicios',    icon: servicesIcon },
  { view: 'proveedores', label: 'Proveedores',  icon: proveIcon },
  { view: 'clientes',    label: 'Clientes',     icon: clienteIcon },
  { view: 'pagos',       label: 'Pagos',        icon: pagoIcon },
  { view: 'trabajadores',label: 'Trabajadores', icon: trabajaIcon },
  { view: 'inventario',  label: 'Inventario',   icon: inventoryIcon },
  { view: 'compra',      label: 'Compra',       icon: compraIcon },
  { view: 'producto',    label: 'Producto',     icon: productIcon },
  { view: 'reportes',    label: 'Reportes',     icon: reporIcon },
  { view: 'usuarios',    label: 'Usuarios',     icon: userIcon },
  { view: 'perfil',      label: 'Mi Perfil',    icon: perfilIcon }
]

function goTo (view) {
  current.value = view
}

// Buscador de secciones
const searchView = ref('')

function buscarSeccion () {
  const texto = searchView.value.trim().toLowerCase()
  if (!texto) return

  const match = botones.find(btn =>
    btn.label.toLowerCase().includes(texto)
  )

  if (match) {
    current.value = match.view
  } else {
    alert('No se encontró ninguna sección con ese nombre.')
  }
}



function logout () {
  router.post(route('logout'), {}, {
    replace: true,
  })
}

// clase común de tarjetas
const cardClass = computed(() =>
  [
    'rounded-2xl shadow p-6 transition-colors duration-300',
    darkMode.value ? 'bg-gray-800 text-gray-100' : 'bg-white text-gray-800',
    fontClass.value
  ].join(' ')
)
</script>


<style scoped>
button {
  transition: all 0.2s ease;
}
</style>
