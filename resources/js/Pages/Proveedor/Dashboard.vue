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
            Panel del Proveedor
          </h2>

          <button
            @click="toggleSidebar"
            class="p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700"
            :title="sidebarCollapsed ? 'Expandir menú' : 'Colapsar menú'"
          >
            <img
              :src="blueprintIcon2"
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
              alt="Modo oscuro"
              class="w-4 h-4"
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
            <img :src="btn.icon" class="w-5 h-5 object-contain" :alt="btn.label" />
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

    <!-- 📋 CONTENIDO PRINCIPAL (DERECHA) -->
    <main class="flex-1 p-6 md:p-8 transition-colors duration-300">
      <header class="mb-6">
        <h1 class="text-3xl font-bold mb-2">
          Bienvenido, {{ nombreProveedor }}
        </h1>
        <p
          class="text-sm md:text-base"
          :class="darkMode ? 'text-gray-300' : 'text-gray-600'"
        >
          Este es tu panel personal. Aquí puedes revisar tus pagos, solicitudes de
          compra, ubicación del proyecto, datos de contacto del propietario y tu perfil.
        </p>
      </header>

      <!-- 💰 PAGOS -->
      <Pagos
        v-if="current === 'pagos'"
        :dark-mode="darkMode"
        :pagos="pagos"
        :qr-proveedor="qrProveedor"
        :proveedor="proveedor"
      />

      <!-- 📦 SOLICITUDES DE COMPRA -->
      <section v-if="current === 'compras'">
        <Compras
          :dark-mode="darkMode"
          @ver-proyecto="verProyectoDesdeCompra"
        />
      </section>

      <!-- 📍 UBICACIÓN DEL PROYECTO -->
      <Ubicacion
        v-if="current === 'ubicacion'"
        :dark-mode="darkMode"
        :proyecto="proyectoSeleccionado"
      />

      <!-- ☎️ CONTACTO -->
      <Contacto
        v-if="current === 'contacto'"
        :dark-mode="darkMode"
        :propietario="propietario"
        :proveedor="proveedor"
        :user="user"
      />

      <!-- 👤 PERFIL -->
      <Perfil
        v-if="current === 'perfil'"
        :dark-mode="darkMode"
        :proveedor="proveedor"
        :user="user"
      />
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js' // ✅


// componentes hijos
import Compras from './Compras.vue'
import Pagos from './Pagos.vue'
import Ubicacion from './Ubicacion.vue'
import Contacto from './Contacto.vue'
import Perfil from './Perfil.vue'

// Iconos
import blueprintIcon2 from '@/Assets/icons/arrows.png'
import sunIcon from '../../Assets/icons/sol.png'
import moonIcon from '../../Assets/icons/moon.png'
import moneyIcon from '../../Assets/icons/credit-card.png'
import planningIcon from '../../Assets/icons/email.png'
import blueprintIcon from '../../Assets/icons/map.png'
import clockIcon from '../../Assets/icons/contact-information.png'
import manIcon from '../../Assets/icons/man.png'

const { props } = usePage()
const user        = props.user || {}
const proveedor   = props.proveedor || (user.proveedor || {})
const proyecto    = props.proyectoAsignado || null
const pagos       = props.pagos || []
const propietario = props.propietarioContacto || null
const qrProveedor = props.qrProveedor || null

// proyecto que se mostrará en la pestaña "ubicacion"
const proyectoSeleccionado = ref(proyecto)

// Estado UI
const current = ref('pagos')
const darkMode = ref(false)
const sidebarCollapsed = ref(false)

function toggleDarkMode () {
  darkMode.value = !darkMode.value
}

function toggleSidebar () {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

// Nombre que se muestra arriba
const nombreProveedor = computed(() => {
  return proveedor?.nombre_empres_prov || user.name || 'Proveedor'
})

// Botones menú
const botones = [
  { view: 'pagos',     label: 'Pagos',                  icon: moneyIcon },
  { view: 'compras',   label: 'Solicitud de compra',    icon: planningIcon },
  { view: 'ubicacion', label: 'Ubicación del proyecto', icon: blueprintIcon },
  { view: 'contacto',  label: 'Contacto',               icon: clockIcon },
  { view: 'perfil',    label: 'Mi perfil',              icon: manIcon }
]

function goTo (view) {
  current.value = view
}

// llamado desde Compras.vue cuando se pulsa el botón "Ver"
function verProyectoDesdeCompra (proyecto) {
  proyectoSeleccionado.value = proyecto
  current.value = 'ubicacion'
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
