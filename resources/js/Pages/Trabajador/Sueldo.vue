<template>
  <section
    :class="[
      'rounded-2xl shadow p-6',
      darkMode ? 'bg-gray-800 text-gray-100' : 'bg-white text-gray-800'
    ]"
  >
    <h3 class="text-xl font-bold text-blue-500 mb-4">Sueldo</h3>
    <ul class="space-y-2">
      <li><b>Cargo:</b> {{ trabajador?.cargo_trabajador }}</li>
      <li><b>Sueldo por día:</b> {{ currency(trabajador?.sueldo_trabajador) }}</li>
      <li><b>Tipo de contrato:</b> {{ trabajador?.tipo_contrato_trab }}</li>
    </ul>
  </section>
</template>

<script setup>
const props = defineProps({
  darkMode: Boolean,
  trabajador: { type: Object, default: () => ({}) }
})

const currency = n =>
  Number(n || 0).toLocaleString('es-BO', { style: 'currency', currency: 'BOB' })
</script>
