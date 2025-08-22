import { defineStore } from "pinia"
import { ref } from "vue"

export const useAuthStore = defineStore("auth", () => {
  const email = ref("")
  const password = ref("")
  const loading = ref(false)
  const errorMessage = ref<string | null>(null)
  const config = useRuntimeConfig();
  const backendUrl = config.public.apiUrl;
  const router = useRouter()

  const authToken = useCookie<string | null>("auth_token", {
    maxAge: 60 * 60 * 24, // 1 day
  })


  async function login() {
    loading.value = true
    errorMessage.value = null

    try {
      const response = await $fetch<{ token: string }>(`${backendUrl}/api/auth/login`, {
        method: "POST",
        body: {
          email: email.value,
          password: password.value,
        },
      })

      authToken.value = response.token

      router.push("/home")
    } catch (err: any) {
      errorMessage.value = err?.data?.message || "Invalid credentials"
    } finally {
      loading.value = false
    }
  }

  function logout() {
    authToken.value = null
    email.value = ""
    password.value = ""
    router.push("/login")
  }

  const isAuthenticated = computed(() => !!authToken.value)

  return {
    email,
    password,
    loading,
    errorMessage,
    authToken,
    isAuthenticated,
    login,
    logout,
  }
})
