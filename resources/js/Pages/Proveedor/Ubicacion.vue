<template>
  <section
    :class="[
      'rounded-2xl shadow p-6 max-w-3xl transition-colors duration-300',
      darkMode ? 'bg-gray-800 text-gray-100' : 'bg-white text-gray-800'
    ]"
  >
    <h3 class="text-xl font-bold text-blue-500 mb-4">📍 Ubicación del proyecto</h3>

    <div v-if="proyecto">
      <p class="mb-1">
        <b>Proyecto:</b> {{ proyecto.nombre_pro }}
      </p>
      <p class="mb-2">
        <b>Dirección registrada:</b>
        {{ proyecto.direccion_pro || 'Sin dirección registrada.' }}
      </p>

      <div v-if="proyecto.direccion_pro" class="mt-4">
        <div
          ref="mapRef"
          class="w-full h-64 rounded-xl border border-gray-300 overflow-hidden"
        ></div>
        <p class="mt-1 text-xs text-gray-500">
          Mapa de referencia (OpenStreetMap). El pin azul indica la dirección del
          proyecto.
        </p>
      </div>

      <p
        v-else
        class="text-sm mt-2"
        :class="darkMode ? 'text-gray-300' : 'text-gray-600'"
      >
        El proyecto no tiene una dirección registrada, por lo que no se puede mostrar en
        el mapa.
      </p>
    </div>

    <div v-else>
      <p :class="darkMode ? 'text-gray-300' : 'text-gray-600'">
        Aún no se encontró un proyecto relacionado con el propietario de este proveedor.
      </p>
    </div>
  </section>
</template>
<script setup>
import { onMounted, ref, watch } from 'vue'
import axios from 'axios'
import L from 'leaflet'

const props = defineProps({
  darkMode: { type: Boolean, default: false },
  proyecto: { type: Object, default: null }
})

const mapRef = ref(null)
let mapInstance = null
let markerInstance = null

const defaultCenter = { lat: -17.8, lng: -63.2 }

async function mostrarMapa () {
  if (!mapRef.value || !props.proyecto || !props.proyecto.direccion_pro) return

  if (!mapInstance) {
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

  try {
    const url = `https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(
      props.proyecto.direccion_pro
    )}`

    const { data } = await axios.get(url)

    if (data && data.length) {
      const { lat, lon } = data[0]
      mapInstance.setView([lat, lon], 17)
      markerInstance.setLatLng([lat, lon])
    }
  } catch (e) {
    console.error('Error geocodificando dirección del proyecto (proveedor):', e)
  }
}

onMounted(() => {
  mostrarMapa()
})

// 🔁 cuando cambie el proyecto (cuando haces click en "Ver")
watch(
  () => props.proyecto,
  () => {
    mostrarMapa()
  }
)
</script>
