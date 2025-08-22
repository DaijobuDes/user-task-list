<script setup lang="ts">
import { useAuthStore } from "@/stores/useAuthStore"

const auth = useAuthStore()
const router = useRouter()

if (!auth.isAuthenticated) {
  router.push('/')
}

</script>

<template>
  <div>
    <NuxtRouteAnnouncer />
    <NuxtWelcome />
  </div>
</template>
