<template>
  <div
    class="min-h-screen flex items-center justify-center relative overflow-hidden
           bg-gradient-to-br from-[#2b0004] via-[#4b0b10] to-[#1a0203]"
  >
    <!-- ✨ Luces decorativas -->
    <div
      class="absolute -top-32 -left-24 w-96 h-96 bg-red-500/25 rounded-full blur-[120px]
             shadow-[0_0_80px_40px_rgba(248,113,113,0.5)]"
    ></div>

    <div
      class="absolute bottom-0 right-0 w-[420px] h-[420px] bg-orange-400/20 rounded-full blur-[130px]
             shadow-[0_0_90px_40px_rgba(251,146,60,0.65)]"
    ></div>

    <!-- TARJETA -->
    <div
      class="w-full max-w-md mx-4 bg-white/10 backdrop-blur-2xl rounded-3xl
             border border-red-200/30 shadow-[0_15px_55px_rgba(249,115,22,0.5)]
             p-8 animate-fade-in"
    >

      <!-- HEADER -->
      <div class="text-center mb-8">
        <div
          class="mx-auto mb-4 w-fit px-4 py-1.5 rounded-full
                 bg-gradient-to-r from-red-600 via-red-500 to-orange-400
                 text-white text-[11px] tracking-[0.4em] uppercase font-semibold
                 shadow-[0_4px_12px_rgba(249,115,22,0.6)]"
        >
          Acceso al sistema
        </div>

        <h2
          class="text-3xl font-black text-white drop-shadow-[0_4px_10px_rgba(0,0,0,0.6)]"
          style="font-family: 'Poppins', sans-serif;"
        >
          Iniciar sesión
        </h2>
        <p class="text-red-200/80 text-xs mt-1">
          Ingresa tus credenciales para continuar.
        </p>
      </div>

      <!-- FORMULARIO -->
      <form @submit.prevent="submit" class="space-y-4">

        <!-- ID Usuario -->
        <div>
          <label class="block text-xs font-semibold text-red-100 mb-1">ID Usuario</label>
          <input
            type="text"
            v-model="form.id_usuario"
            class="w-full px-3 py-2 rounded-lg
                   bg-[#1a0b0b]/60 border border-red-400/40 text-red-50 text-sm
                   focus:outline-none focus:ring-2 focus:ring-orange-400/70
                   focus:border-orange-400 transition"
            placeholder="Ej: propietario1, cliente2..."
            autocomplete="username"
          />
          <p v-if="form.errors.id_usuario" class="text-red-300 text-xs mt-1">
            {{ form.errors.id_usuario }}
          </p>
        </div>

        <!-- CONTRASEÑA -->
        <div>
          <label class="block text-xs font-semibold text-red-100 mb-1">Contraseña</label>
          <input
            type="password"
            v-model="form.password"
            class="w-full px-3 py-2 rounded-lg
                   bg-[#1a0b0b]/60 border border-red-400/40 text-red-50 text-sm
                   focus:outline-none focus:ring-2 focus:ring-orange-400/70
                   focus:border-orange-400 transition"
            placeholder="••••••••"
            autocomplete="current-password"
          />
          <p v-if="form.errors.password" class="text-red-300 text-xs mt-1">
            {{ form.errors.password }}
          </p>
        </div>

        <!-- ERROR GENERAL -->
        <p v-if="formErrors" class="text-red-300 text-xs mt-1">
          {{ formErrors }}
        </p>

        <!-- BOTÓN -->
        <div class="mt-6">
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full inline-flex justify-center items-center gap-2 px-5 py-3 rounded-full
                   bg-gradient-to-r from-red-600 via-red-500 to-orange-400
                   text-white text-sm font-bold tracking-wide
                   shadow-[0_10px_30px_rgba(249,115,22,0.8)]
                   hover:shadow-[0_12px_40px_rgba(255,124,40,1)]
                   transition-all duration-300 transform
                   hover:-translate-y-1 hover:scale-[1.03]
                   active:scale-95 disabled:opacity-60"
          >
            <span v-if="!form.processing">Entrar</span>
            <span v-else>Ingresando...</span>
            <span class="text-lg">➡️</span>
          </button>
        </div>
      </form>

      <!-- VOLVER -->
      <div class="mt-6 text-center">
        <button
          type="button"
          @click="goHome"
          class="text-xs text-red-200 hover:text-white underline underline-offset-2"
        >
          ⬅ Volver al inicio
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const form = useForm({
  id_usuario: '',
  password: '',
})

const formErrors = ref('')

function submit() {
  formErrors.value = ''

  form.post('login', {
    onError: () => {
      if (form.errors.id_usuario && !form.errors.password) {
        formErrors.value = form.errors.id_usuario
      }
    },
    onFinish: () => {
      form.reset('password')
    },
  })
}

function goHome() {
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

/* Fuente corporativa */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap');
</style>
