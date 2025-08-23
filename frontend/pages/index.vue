<script setup lang="ts">
import LoginForm from '@/components/LoginForm.vue'
import { useAuthStore } from "@/stores/useAuthStore"

const auth = useAuthStore()
const router = useRouter()

if (auth.isAuthenticated) {
  router.push('/home')
}
</script>

<template>
  <div class="flex min-h-svh w-full items-center justify-center p-6 md:p-10">
    <div class="w-full max-w-sm">
      <LoginForm @submit.prevent="auth.login" />
    </div>
  </div>
</template>
