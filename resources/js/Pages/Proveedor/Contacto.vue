<template>
  <section
    :class="[
      'rounded-2xl shadow p-6 max-w-xl space-y-4 transition-colors duration-300',
      darkMode ? 'bg-gray-800 text-gray-100' : 'bg-white text-gray-800'
    ]"
  >
    <h3 class="text-xl font-bold text-blue-500 mb-2">☎️ Contacto</h3>

    <!-- Propietario -->
    <div>
      <h4 class="font-semibold mb-1">Propietario de la empresa</h4>
      <p>
        <b>Nombre:</b>
        {{
          propietario
            ? (propietario.nombre_propietario || '') +
              ' ' +
              (propietario.apellido_propietario || '')
            : 'No registrado'
        }}
      </p>
      <p>
        <b>Teléfono:</b>
        {{ propietario?.telefono_propietario || 'No registrado' }}
      </p>
      <p>
        <b>Correo:</b>
        {{ propietario?.correo_propietario || 'No registrado' }}
      </p>
      <p>
        <b>Dirección:</b>
        {{ propietario?.direccion_propietario || 'No registrada' }}
      </p>
    </div>
  </section>
</template>

<script setup>
const props = defineProps({
  darkMode: { type: Boolean, default: false },
  propietario: { type: Object, default: null },
  proveedor: { type: Object, default: () => ({}) },
  user: { type: Object, default: () => ({}) }
})
</script>
