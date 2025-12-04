<template>
  <div
    class="min-h-screen flex items-center justify-center relative overflow-hidden
           bg-gradient-to-br from-[#2b0004] via-[#4b0b10] to-[#1a0203]"
  >
    <!-- ✨ Luces decorativas rojo/naranja -->
    <div
      class="absolute -top-32 -left-24 w-96 h-96
             bg-red-500/30 rounded-full blur-[120px]
             shadow-[0_0_60px_30px_rgba(248,113,113,0.5)]"
    ></div>

    <div
      class="absolute bottom-0 right-0 w-[420px] h-[420px]
             bg-orange-400/25 rounded-full blur-[130px]
             shadow-[0_0_80px_40px_rgba(251,146,60,0.7)]"
    ></div>

    <!-- 🌟 Tarjeta principal rojo + blanco -->
    <div
      class="max-w-3xl w-full mx-6
             bg-white/95
             rounded-3xl
             border border-red-100
             shadow-[0_18px_55px_rgba(249,115,22,0.65)]
             p-10 text-center
             animate-fade-in"
    >
      <!-- Banda superior en degradé rojo -->
      <div
        class="mx-auto mb-6 w-full max-w-xs
               px-4 py-1.5 rounded-full
               bg-gradient-to-r from-red-600 via-red-500 to-orange-400
               shadow-[0_8px_25px_rgba(248,113,113,0.7)]
               text-xs tracking-[0.35em] uppercase text-white font-semibold"
      >
        SISTEMA WEB DE GESTIÓN
      </div>

      <!-- Nombre de la empresa -->
      <h1
        class="text-4xl sm:text-5xl md:text-6xl font-black
               text-[#2b0004] mb-4 leading-tight
               drop-shadow-[0_5px_12px_rgba(220,38,38,0.5)]"
        style="font-family: 'Poppins', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;"
      >
        METALUVCAMIRI
        <span class="text-red-600">S.R.L.</span>
      </h1>

      <!-- Subtítulo en rojo suave -->
      <p
        class="text-sm font-semibold tracking-wide uppercase mb-4
               text-red-500/80"
      >
        Gestión integral de proyectos y operaciones
      </p>

      <!-- Descripción -->
      <p
        class="text-gray-700 mb-10 text-sm sm:text-base mx-auto max-w-xl leading-relaxed"
      >
        Plataforma web para administrar proyectos, clientes, proveedores,
        inventario, trabajadores y pagos en un solo lugar, con enfoque profesional
        y pensado para el crecimiento de tu empresa.
      </p>

      <!-- 🔘 Botón Iniciar sesión -->
      <div class="flex justify-center">
        <button
          @click="goToLogin"
          class="inline-flex items-center gap-3 px-9 py-3.5 rounded-full
                 bg-gradient-to-r from-red-600 via-red-500 to-orange-400
                 text-white font-semibold text-base tracking-wide
                 shadow-[0_12px_35px_rgba(248,113,113,0.9)]
                 hover:shadow-[0_16px_45px_rgba(249,115,22,1)]
                 transition-all duration-300 transform
                 hover:-translate-y-1 hover:scale-[1.03]
                 active:scale-95"
        >
          <span>Iniciar sesión</span>
          <span class="text-xl">🔐</span>
        </button>
      </div>

      <!-- Línea decorativa abajo -->
      <div
        class="mt-8 h-[2px] w-24 mx-auto rounded-full
               bg-gradient-to-r from-red-500 via-orange-400 to-red-500"
      ></div>

      <!-- Footer -->
      <div class="mt-6 text-xs text-gray-500 tracking-wide">
        © {{ new Date().getFullYear() }} METALUVCAMIRI S.R.L. — Todos los derechos reservados.
      </div>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'

function goToLogin() {
  router.visit('login')
}
</script>

<style scoped>
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(18px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.9s ease-out;
}

/* Tipografía corporativa (si quieres usar Poppins) */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap');
</style>
