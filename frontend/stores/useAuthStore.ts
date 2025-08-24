import { defineStore } from "pinia"
import { ref } from "vue"

export const useAuthStore = defineStore("auth", () => {
  const email = ref("")
  const name = ref("")
  const password = ref("")
  const loading = ref(false)
  const errorMessage = ref<string | null>(null)
  const config = useRuntimeConfig();
  const backendUrl = config.public.apiUrl;
  const router = useRouter()

  const authToken = useCookie<string | null>("auth_token", {
    maxAge: 60 * 60 * 24, // 1 day
  })


  const login = async () => {
    loading.value = true
    errorMessage.value = null

    try {
      const response = await $fetch<{ name: string, token: string }>(`${backendUrl}/api/auth/login`, {
        method: "POST",
        body: {
          email: email.value,
          password: password.value,
        },
      })

      name.value = response.name
      authToken.value = response.token

      router.push("/home")
    } catch (err: any) {
      errorMessage.value = err?.data?.message || "Invalid credentials"
    } finally {
      loading.value = false
    }
  }

  const logout = () => {
    authToken.value = null
    email.value = ""
    password.value = ""
    router.push("/")
  }

  const isAuthenticated = computed(() => !!authToken.value)

  return {
    email,
    name,
    password,
    loading,
    errorMessage,
    authToken,
    isAuthenticated,
    login,
    logout,
  }
})
