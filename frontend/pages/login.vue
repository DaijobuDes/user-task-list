<script setup lang="ts">
import { ref } from "vue"
import { Input } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import { Label } from "@/components/ui/label"
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from "@/components/ui/card"
import LoginForm from "@/components/LoginForm.vue"


const config = useRuntimeConfig();
const backendUrl = config.public.apiUrl;

const email = ref("")
const password = ref("")
const loading = ref(false)
const errorMessage = ref("")

const router = useRouter()

// Store token in cookie (Nuxt composable)
const authToken = useCookie<string | null>("auth_token", {
  maxAge: 60 * 60 * 24, // 1 day
})

const handleLogin = async () => {
  errorMessage.value = ""
  loading.value = true

  try {
    const response = await $fetch<{ token: string }>(`${backendUrl}/api/auth/login`, {
      method: "POST",
      body: {
        email: email.value,
        password: password.value,
      },
    })

    // Save token into cookie
    authToken.value = response.token

    // Redirect to home/dashboard
    router.push("/home")
  } catch (err: any) {
    errorMessage.value = err?.data?.message || "Invalid credentials"
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex h-screen w-full items-center justify-center px-4">
    <LoginForm />
  </div>
</template>

<!-- <template>
  <div class="flex items-center justify-center min-h-screen bg-gray-100">
    <Card class="w-full max-w-md shadow-lg rounded-2xl">
      <CardHeader>
        <CardTitle class="text-center text-xl">Login</CardTitle>
      </CardHeader>
      <CardContent class="space-y-4">
        <div v-if="errorMessage" class="text-red-600 text-sm">
          {{ errorMessage }}
        </div>
        <div class="space-y-2">
          <Label for="email">Email</Label>
          <Input id="email" type="email" v-model="email" placeholder="Enter your email" />
        </div>
        <div class="space-y-2">
          <Label for="password">Password</Label>
          <Input id="password" type="password" v-model="password" placeholder="Enter your password" />
        </div>
      </CardContent>
      <CardFooter>
        <Button class="w-full" :disabled="loading" @click="handleLogin">
          <span v-if="loading">Logging in...</span>
          <span v-else>Login</span>
        </Button>
      </CardFooter>
    </Card>
  </div>
</template> -->
