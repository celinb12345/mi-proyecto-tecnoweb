<template>
  <!-- CONTENEDOR GENERAL -->
  <div
    :class="[
      'flex min-h-screen transition-colors duration-300',
      darkMode ? 'bg-gray-900 text-gray-100' : 'bg-gray-100 text-gray-900'
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
        <!-- Título + botón para colapsar -->
        <div class="flex items-center justify-between mb-4">
          <h2
            v-if="!sidebarCollapsed"
            class="text-xl font-bold text-blue-500 whitespace-nowrap"
          >
            Panel del Trabajador
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

        <!-- Menú de navegación -->
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
      <header class="mb-6">
        <h1 class="text-3xl font-bold mb-2">
          Bienvenido, {{ nombreCompleto }}
        </h1>
        <p
          class="text-sm md:text-base"
          :class="darkMode ? 'text-gray-300' : 'text-gray-600'"
        >
          Este es tu panel personal. Usa el menú de la izquierda para ver tu sueldo,
          proyecto asignado, horas trabajadas, pagos y perfil.
        </p>
      </header>

      <!-- AQUÍ SE CARGA LA VISTA HIJA -->
      <component
        :is="currentComponent"
        :dark-mode="darkMode"
        :user="user"
        :trabajador="trabajador"
        :proyecto="proyecto"
        :asistencias="asistencias"
        :mes-actual="mesActual"
        :pagos-recibidos="pagosRecibidos"
        :qr-trabajador="qrTrabajador"
      />
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js' // ✅


// iconos
import moneyIcon from '@/Assets/icons/money.png'
import planningIcon from '@/Assets/icons/planning.png'
import clockIcon from '@/Assets/icons/clock.png'
import manIcon from '@/Assets/icons/man.png'
import sunIcon from '@/Assets/icons/sol.png'
import moonIcon from '@/Assets/icons/moon.png'
import blueprintIcon from '@/Assets/icons/arrows.png'

// 👇 Importa las vistas hijas (créales sus archivos dentro de Pages/Trabajador)
import VistaSueldo from './Sueldo.vue'
import VistaProyecto from './Proyecto.vue'
import VistaHoras from './Horas.vue'
import VistaPagos from './Pagos.vue'
import VistaPerfil from './Perfil.vue'

const { props } = usePage()
const user = props.user || {}
const trabajador = user.trabajador || {}
const proyecto = props.proyectoAsignado || null
const asistencias = props.asistencias || []
const mesActual = props.mesActual || null
const pagosRecibidos = props.pagosRecibidos || []
const qrTrabajador = props.qrTrabajador || null

// estado layout
const current = ref('proyecto') // vista inicial
const darkMode = ref(false)
const sidebarCollapsed = ref(false)

function toggleDarkMode () {
  darkMode.value = !darkMode.value
}
function toggleSidebar () {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

const nombreCompleto = computed(() => {
  const nom = trabajador?.nombre_trabajador ?? user?.name ?? ''
  const ape = trabajador?.apellido_trabajador ?? ''
  return `${nom} ${ape}`.trim()
})

// botones del menú
const botones = [
  { view: 'sueldo',   label: 'Sueldo',             icon: moneyIcon },
  { view: 'proyecto', label: 'Proyecto asignado', icon: planningIcon },
  { view: 'horas',    label: 'Horas trabajadas',  icon: clockIcon },
  { view: 'pagos',    label: 'Pagos / QR',        icon: moneyIcon },
  { view: 'perfil',   label: 'Mi perfil',         icon: manIcon }
]

// Mapa de vistas -> componente hijo
const componentMap = {
  sueldo: VistaSueldo,
  proyecto: VistaProyecto,
  horas: VistaHoras,
  pagos: VistaPagos,
  perfil: VistaPerfil
}

const currentComponent = computed(() => {
  return componentMap[current.value] || VistaProyecto
})

function goTo (view) {
  current.value = view
}


function logout () {
  router.post(route('logout'), {}, {
    replace: true,
  })
}

</script>

<style scoped>
button {
  transition: all 0.2s ease;
}
</style>
