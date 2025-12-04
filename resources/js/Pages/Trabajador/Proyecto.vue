<template>
  <section
    :class="[
      'rounded-2xl shadow p-6',
      darkMode ? 'bg-gray-800 text-gray-100' : 'bg-white text-gray-800'
    ]"
  >
    <h3 class="text-xl font-bold text-blue-500 mb-4">Proyecto asignado</h3>

    <div v-if="proyecto">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div class="space-y-1">
          <p><b>Nombre del proyecto:</b> {{ proyecto.nombre_pro }}</p>
          <p><b>Estado:</b> {{ proyecto.estado_proyecto }}</p>
          <p><b>Dirección:</b> {{ proyecto.direccion_pro }}</p>
        </div>
        <div class="space-y-1">
          <p><b>Fecha inicio:</b> {{ proyecto.fecha_inicio_pro }}</p>
          <p><b>Fecha fin:</b> {{ proyecto.fecha_fin_pro }}</p>
          <p><b>ID proyecto:</b> {{ proyecto.id_proyecto }}</p>
        </div>
      </div>

      <div
        class="p-3 rounded text-sm mb-4"
        :class="
          darkMode
            ? 'bg-blue-900/40 border border-blue-700'
            : 'bg-blue-50 border-l-4 border-blue-400'
        "
      >
        <b>Descripción:</b><br />
        {{ proyecto.descripcion_pro || 'Sin descripción registrada.' }}
      </div>

      <!-- MAPA -->
      <div v-if="proyecto.direccion_pro" class="mt-4">
        <h4 class="font-semibold mb-2">Ubicación en mapa</h4>
        <div
          ref="mapRef"
          class="w-full h-64 rounded-xl border border-gray-300 overflow-hidden"
        ></div>
        <p class="mt-1 text-xs text-gray-500">
          Mapa de referencia (OpenStreetMap). El marcador indica la dirección registrada.
        </p>
      </div>
    </div>

    <div
      v-else
      class="p-4 rounded text-sm"
      :class="
        darkMode
          ? 'bg-yellow-900/40 border border-yellow-700'
          : 'bg-yellow-50 border-l-4 border-yellow-400'
      "
    >
      Todavía no tienes un proyecto asignado o no se encontró la asignación reciente.
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'
import L from 'leaflet'

const props = defineProps({
  darkMode: Boolean,
  proyecto: { type: Object, default: null }
})

const mapRef = ref(null)
let mapInstance = null
let markerInstance = null

const defaultCenter = { lat: -17.8, lng: -63.2 }

async function mostrarProyectoEnMapa () {
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
    console.error('Error geocodificando dirección del proyecto:', e)
  }
}

onMounted(() => {
  if (props.proyecto && props.proyecto.direccion_pro) {
    mostrarProyectoEnMapa()
  }
})
</script>
