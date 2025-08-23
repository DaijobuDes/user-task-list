<template>
  <div class="flex h-dvh bg-background text-foreground">


    <!-- Main Content -->
    <main class="flex-1 flex flex-col">


      <!-- Welcome state (no messages yet) -->
      <div v-if="!activeMessages.length" class="flex-1">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 md:px-8 py-10">
          <div class="text-center space-y-3">
            <h1 class="text-3xl sm:text-4xl font-semibold tracking-tight">What can I help you with today?</h1>
            <p class="text-muted-foreground">Start a conversation or try one of these suggestions.</p>
          </div>

          <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <Card v-for="s in suggestions" :key="s.title" class="cursor-pointer hover:shadow" @click="useSuggestion(s)">
              <CardHeader>
                <CardTitle class="text-base flex items-center gap-2">
                  <component :is="s.icon" class="h-4 w-4" />
                  {{ s.title }}
                </CardTitle>
              </CardHeader>
              <CardContent>
                <p class="text-sm text-muted-foreground">{{ s.desc }}</p>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>

    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'

// shadcn-vue components
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
// import { Textarea } from '@/components/ui/textarea'
// import { ScrollArea } from '@/components/ui/scroll-area'
import { Separator } from '@/components/ui/separator'
import { DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu'
import { Tooltip, TooltipTrigger, TooltipContent, TooltipProvider } from '@/components/ui/tooltip'
import { Sheet, SheetTrigger, SheetContent } from '@/components/ui/sheet'
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar'
// import { Badge } from '@/components/ui/badge'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'

// icons (lucide-vue-next)
import { Plus, Send, Mic, Paperclip, Settings, Sidebar as SidebarIcon, MessageSquare, MoreHorizontal, Sparkles, Lightbulb, Code2, BookOpen } from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()

const userAvatar = 'https://i.pravatar.cc/80?img=5'
const botAvatar = 'https://i.pravatar.cc/80?img=15'

const collapse = ref(false) // reserved if you later add resizable sidebar

// Mock data
const chats = ref([
  { id: 'welcome', title: 'Trip plan to Tokyo', updatedAt: 'Aug 1' },
  { id: 'dev-help', title: 'Fixing a Nuxt bug', updatedAt: 'Jul 28' },
])

const messages = ref<Record<string, Array<{ id: string; role: 'user' | 'assistant'; content: string; time: string }>>>({
  // seed with an example chat
  'dev-help': [
    { id: '1', role: 'user', content: 'Nuxt route middleware not firing on client navs — ideas?', time: '10:02' },
    { id: '2', role: 'assistant', content: 'Check that your middleware is defined under `middleware/` and exported as a function. Also verify `defineNuxtRouteMiddleware` usage and that it\'s not server-only.', time: '10:03' },
  ],
})

const activeChatId = ref<string>((route.query.chat as string) || 'new')

const activeMessages = computed(() => messages.value[activeChatId.value] || [])

function openChat(id: string) {
  activeChatId.value = id
  router.replace({ query: { chat: id } })
}

function startNewChat() {
  activeChatId.value = 'new'
  router.replace({ query: { chat: 'new' } })
}

const input = ref('')
const taRef = ref<HTMLTextAreaElement | null>(null)

function autoResize() {
  const ta = taRef.value
  if (!ta) return
  ta.style.height = 'auto'
  ta.style.height = Math.min(200, ta.scrollHeight) + 'px'
}

function send() {
  const text = input.value.trim()
  if (!text) return

  const id = crypto.randomUUID()
  if (!messages.value[activeChatId.value]) messages.value[activeChatId.value] = []

  messages.value[activeChatId.value].push({ id, role: 'user', content: text, time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) })
  input.value = ''
  nextTick(() => autoResize())

  // fake assistant reply
  setTimeout(() => {
    messages.value[activeChatId.value].push({ id: crypto.randomUUID(), role: 'assistant', content: `You said: "${text}"\n\nThis is a placeholder assistant response. Wire this to your API route to stream real answers.`, time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) })
  }, 300)

  // Update or create chat title
  const existing = chats.value.find(c => c.id === activeChatId.value)
  const preview = text.slice(0, 30)
  if (existing) {
    existing.title = preview
    existing.updatedAt = new Date().toLocaleDateString(undefined, { month: 'short', day: 'numeric' })
  } else {
    const newId = activeChatId.value === 'new' ? crypto.randomUUID() : activeChatId.value
    chats.value.unshift({ id: newId, title: preview, updatedAt: new Date().toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) })
    if (activeChatId.value === 'new') openChat(newId)
  }
}

function useSuggestion(s: any) {
  input.value = s.prompt
  nextTick(() => send())
}

function onSettings() {
  // open your settings modal here
  alert('Open settings modal…')
}
function onAttach() {
  alert('Attach file…')
}
function onVoice() {
  alert('Start voice input…')
}
function onImport() {
  alert('Import chats…')
}
function onExport() {
  alert('Export chats…')
}
function onClear() {
  if (confirm('Clear all chats?')) {
    chats.value = []
    for (const k of Object.keys(messages.value)) delete messages.value[k]
    startNewChat()
  }
}

const models = ref([
  { id: 'gpt-4o-mini', label: 'GPT-4o mini', desc: 'Fast & cheap, good quality', badge: 'Default' },
  { id: 'gpt-4.1', label: 'GPT-4.1', desc: 'Reasoning-optimized' },
  { id: 'o3-mini', label: 'o3-mini', desc: 'High reasoning on a budget' },
])
const activeModel = ref(models.value[0])

const suggestions = [
  { title: 'Summarize a PDF', desc: 'Paste a link or text and get a TL;DR', icon: BookOpen, prompt: 'Summarize this paper:\n' },
  { title: 'Write code', desc: 'Generate a Nuxt 3 component', icon: Code2, prompt: 'Create a Nuxt 3 component that renders a responsive card grid.' },
  { title: 'Brainstorm', desc: 'Get ideas to kickstart a project', icon: Lightbulb, prompt: 'Brainstorm 10 startup ideas around developer tooling.' },
]

onMounted(() => {
  // ensure textarea height is nice on load
  nextTick(() => autoResize())
})
</script>

<!-- Tailwind prose tweak for long-form answers -->
<style scoped>
.prose :where(code):not(:where([class~=not-prose] *)) {
  background: color-mix(in oklab, var(--muted) 60%, transparent);
  padding: .1rem .35rem;
  border-radius: .4rem;
}
</style>
