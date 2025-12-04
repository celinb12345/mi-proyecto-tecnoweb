<!-- resources/js/Pages/cliente/Dashboard.vue -->
<template>
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
        <!-- Título + botón colapsar -->
        <div class="flex items-center justify-between mb-4">
          <h2
            v-if="!sidebarCollapsed"
            class="text-xl font-bold text-blue-500 whitespace-nowrap"
          >
            Panel del Cliente
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
      <header class="mb-6">
        <h1 class="text-3xl font-bold mb-2">
          Bienvenido, {{ nombreCliente }}
        </h1>
        <p
          class="text-sm md:text-base"
          :class="darkMode ? 'text-gray-300' : 'text-gray-600'"
        >
          Aquí puedes revisar el avance de tu obra, pagos, ubicación del proyecto y datos de contacto.
        </p>
      </header>

      <!-- AVANCE DE LA OBRA -->
      <section
        v-if="current === 'avance'"
        :class="[cardClass, 'space-y-4']"
      >
        <h2 class="text-2xl font-semibold text-blue-500">
          Avance de la obra
        </h2>

        <div v-if="!proyecto">
          <p :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
            Aún no tienes un proyecto asignado. Cuando el propietario registre tu
            obra, podrás ver aquí el avance.
          </p>
        </div>

        <div v-else class="space-y-4">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
              <h3 class="text-xl font-bold">
                {{ proyecto.nombre_pro }}
              </h3>
              <p :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
                {{ proyecto.descripcion_pro || 'Sin descripción registrada.' }}
              </p>
            </div>

            <div class="text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
              <p><span class="font-semibold">Fecha inicio:</span> {{ proyecto.fecha_inicio_pro }}</p>
              <p><span class="font-semibold">Fecha fin:</span> {{ proyecto.fecha_fin_pro }}</p>
              <p>
                <span class="font-semibold">Estado:</span>
                {{ metrics?.estado || proyecto.estado_proyecto }}
              </p>
            </div>
          </div>

          <!-- Barra de progreso -->
          <div v-if="metrics" class="space-y-2">
            <div class="flex justify-between text-sm" :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
              <span>Progreso estimado</span>
              <span>{{ metrics.porcentaje }} %</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
              <div
                class="h-3 rounded-full transition-all"
                :class="metrics.porcentaje >= 100 ? 'bg-green-500' : 'bg-blue-500'"
                :style="{ width: metrics.porcentaje + '%' }"
              ></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4">
              <div
                class="p-3 rounded-xl text-center"
                :class="darkMode ? 'bg-blue-900/40' : 'bg-blue-50'"
              >
                <p class="text-xs uppercase" :class="darkMode ? 'text-gray-300' : 'text-gray-500'">
                  Días totales
                </p>
                <p class="text-xl font-bold text-blue-500">
                  {{ metrics.total_dias }}
                </p>
              </div>

              <div
                class="p-3 rounded-xl text-center"
                :class="darkMode ? 'bg-green-900/40' : 'bg-green-50'"
              >
                <p class="text-xs uppercase" :class="darkMode ? 'text-gray-300' : 'text-gray-500'">
                  Días transcurridos
                </p>
                <p class="text-xl font-bold text-green-500">
                  {{ metrics.dias_transcurridos }}
                </p>
              </div>

              <div
                class="p-3 rounded-xl text-center"
                :class="metrics.dias_restantes > 0
                  ? (darkMode ? 'bg-red-900/40' : 'bg-red-50')
                  : (darkMode ? 'bg-gray-700' : 'bg-gray-100')"
              >
                <p class="text-xs uppercase" :class="darkMode ? 'text-gray-300' : 'text-gray-500'">
                  Días restantes
                </p>
                <p
                  class="text-xl font-bold"
                  :class="metrics.dias_restantes > 0 ? 'text-red-500' : (darkMode ? 'text-gray-200' : 'text-gray-600')"
                >
                  {{ metrics.dias_restantes }}
                </p>
              </div>
            </div>

            <p class="text-xs" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">
              Cálculo realizado al día: {{ metrics.hoy }}
            </p>
          </div>
        </div>
      </section>

      <!-- PAGOS: aquí solo montamos el componente PagosCliente -->
      <PagosCliente
        v-if="current === 'pagos'"
        :proyecto="proyecto"
        :propietario="propietario"
        :dark-mode="darkMode"
      />

      <!-- UBICACIÓN DEL PROYECTO -->
      <section
        v-if="current === 'ubicacion'"
        :class="[cardClass, 'space-y-4']"
      >
        <h2 class="text-2xl font-semibold text-blue-500">Ubicación del proyecto</h2>

        <div v-if="!proyecto">
          <p :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
            Aún no tienes un proyecto registrado, por lo tanto no hay ubicación para mostrar.
          </p>
        </div>

        <div v-else class="space-y-4">
          <p :class="darkMode ? 'text-gray-300' : 'text-gray-700'">
            Dirección registrada:
            <span class="font-semibold">
              {{ proyecto.direccion_pro || 'Sin dirección registrada.' }}
            </span>
          </p>

          <div
            v-if="proyecto.direccion_pro"
            ref="mapRef"
            class="w-full h-72 rounded-lg border overflow-hidden"
          ></div>

          <p v-else class="text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-500'">
            No hay una dirección válida para mostrar en el mapa.
          </p>
        </div>
      </section>

      <!-- CONTACTO -->
      <section
        v-if="current === 'contacto'"
        :class="[cardClass, 'space-y-3']"
      >
        <h2 class="text-2xl font-semibold text-blue-500 mb-2">Contacto del propietario</h2>

        <div v-if="!propietario">
          <p :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
            No se encontró información del propietario de tu proyecto.
          </p>
        </div>

        <div v-else class="space-y-2">
          <p>👤 Nombre:
            <span class="font-semibold">
              {{ propietario.nombre_propietario }} {{ propietario.apellido_propietario }}
            </span>
          </p>

          <p>📧 Correo:
            <span class="font-semibold">{{ propietario.correo_propietario }}</span>
          </p>

          <p>📞 Teléfono:
            <span class="font-semibold">{{ propietario.telefono_propietario }}</span>
          </p>

          <p>📍 Dirección:
            <span class="font-semibold">{{ propietario.direccion_propietario }}</span>
          </p>
        </div>
      </section>
    </main>
  </div>
</template>
<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import L from 'leaflet'
import { route } from 'ziggy-js' // ✅


// 👇 NUEVO: importamos el componente de pagos del cliente
import PagosCliente from './PagosCliente.vue'

// iconos
import blueprintIcon from '@/Assets/icons/arrows.png'
import construction from '@/Assets/icons/construction.png'
import moneyIcon from '@/Assets/icons/credit-card.png'
import inventoryIcon from '@/Assets/icons/map.png'
import manIcon from '@/Assets/icons/man.png'
import sunIcon from '@/Assets/icons/sol.png'
import moonIcon from '@/Assets/icons/moon.png'

const { props } = usePage()
const user          = props.user || {}
const proyecto      = props.proyecto || null
const metrics       = props.metrics || null
const propietario   = props.propietario || null
const nombreCliente = props.nombreCliente || (user.name || '')

// estado UI
const current = ref('avance')
const darkMode = ref(false)
const sidebarCollapsed = ref(false)

function toggleDarkMode () {
  darkMode.value = !darkMode.value
}
function toggleSidebar () {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

// botones con iconos
const botones = [
  { view: 'avance',    label: 'Avance de la obra',      icon: construction },
  { view: 'pagos',     label: 'Pagos',                  icon: moneyIcon },
  { view: 'ubicacion', label: 'Ubicación del proyecto', icon: inventoryIcon },
  { view: 'contacto',  label: 'Contacto',               icon: manIcon },
]

function goTo (view) {
  current.value = view
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
    darkMode.value ? 'bg-gray-800 text-gray-100' : 'bg-white text-gray-800'
  ].join(' ')
)

// =======================
// MAPA (Leaflet + OpenStreetMap)
// =======================
const mapRef = ref(null)
let mapInstance = null
let markerInstance = null

const defaultCenter = { lat: -17.8, lng: -63.2 }

function initMapIfNeeded () {
  if (!mapRef.value || mapInstance) return

  mapInstance = L.map(mapRef.value).setView(
    [defaultCenter.lat, defaultCenter.lng],
    13
  )

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(mapInstance)

  markerInstance = L.marker([defaultCenter.lat, defaultCenter.lng]).addTo(mapInstance)
}

async function mostrarProyectoEnMapa () {
  if (!proyecto || !proyecto.direccion_pro) return

  initMapIfNeeded()
  if (!mapInstance || !markerInstance) return

  try {
    const url = `https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(
      proyecto.direccion_pro
    )}`

    const res = await fetch(url, {
      headers: {
        'Accept-Language': 'es',
        'User-Agent': 'melamina-cliente/1.0'
      }
    })

    const data = await res.json()
    if (!data.length) {
      console.warn('No se encontró la dirección en el mapa')
      return
    }

    const { lat, lon } = data[0]
    const latNum = parseFloat(lat)
    const lonNum = parseFloat(lon)

    mapInstance.setView([latNum, lonNum], 17)
    markerInstance.setLatLng([latNum, lonNum])
  } catch (err) {
    console.error('Error al geocodificar dirección del proyecto:', err)
  }
}

onMounted(() => {
  if (current.value === 'ubicacion') {
    nextTick(() => {
      mostrarProyectoEnMapa()
    })
  }
})

watch(current, (val) => {
  if (val === 'ubicacion') {
    nextTick(() => {
      mostrarProyectoEnMapa()
    })
  }
})
</script>

<style scoped>
button {
  transition: all 0.2s ease;
}
</style>
