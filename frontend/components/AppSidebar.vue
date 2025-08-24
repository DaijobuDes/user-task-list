<script setup lang="ts">
import type { SidebarProps } from '@/components/ui/sidebar'
import { useDebounceFn } from "@vueuse/core"

import { Plus, Search } from "lucide-vue-next"
import Calendars from '@/components/Calendars.vue'
import DatePicker from '@/components/DatePicker.vue'
import NavUser from '@/components/NavUser.vue'
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarRail,
  SidebarSeparator,
} from '@/components/ui/sidebar'
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList
} from '@/components/ui/command'

import { useAuthStore } from "@/stores/useAuthStore"
import { useCalendarStore } from '@/stores/useCalendarStore'
import type { Task, TaskResponse } from '@/types/IndexTaskResponse'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import { parseDate } from '@internationalized/date'

const auth = useAuthStore()
const config = useRuntimeConfig()
const calendar = useCalendarStore()
const backendUrl = config.public.apiUrl

const open = ref(false)
const searchQuery = ref("")
const results = ref<any[]>([])

const truncate = (str: string, n: number) => {
  return str.length > n ? str.slice(0, n) + "…" : str
}

const fetchTasks = useDebounceFn(async (query: string) => {
  if (!query.trim()) {
    results.value = []
    return
  }

  try {
    const response = await $fetch<TaskResponse>(`${backendUrl}/api/tasks/search`, {
      method: "GET",
      query: { term: query },
      headers: {
        Authorization: `Bearer ${auth.authToken}`,
      },
    })

    results.value = response.data || []
  } catch (err) {
    console.error("Search failed:", err)
    results.value = []
  }
}, 100) // debounce delay in ms

// watch searchQuery and debounce API calls
watch(searchQuery, (val) => {
  fetchTasks(val)
})

const goToDate = (task: Task) => {
  calendar.date = parseDate(task.date)
  open.value = false;
}

const props = defineProps<SidebarProps>()
// This is sample data.
const data = {
  user: {
    name: auth.name,
    email: auth.email,
    // avatar: "/avatars/shadcn.jpg",
    avatar: "https://cdn.discordapp.com/avatars/451974524053749780/30d1ebd03955b6bf6df3491217b062c0.png?size=256"
  },
  // calendars: [
  //   {
  //     name: "My Calendars",
  //     items: ["Personal", "Work", "Family"],
  //   },
  //   {
  //     name: "Favorites",
  //     items: ["Holidays", "Birthdays"],
  //   },
  //   {
  //     name: "Other",
  //     items: ["Travel", "Reminders", "Deadlines"],
  //   },
  // ],
}
</script>

<template>
  <Sidebar v-bind="props">
    <SidebarHeader class="h-16 border-b border-sidebar-border">
      <NavUser :user="data.user" />
    </SidebarHeader>

    <SidebarContent>
      <DatePicker />
      <SidebarSeparator class="mx-0" />
    </SidebarContent>

    <SidebarFooter>
      <!-- Trigger button -->
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton @click="open = true">
            <Search />
            <span>Search for tasks</span>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>

      <!-- Command Palette in a Dialog -->
      <Dialog v-model:open="open">
        <DialogContent class="p-0 max-w-lg">
          <Command :should-filter="false" class="rounded-lg border shadow-lg">
            <CommandInput
              v-model="searchQuery"
              placeholder="Search for tasks..."
              class="h-12 px-4"
            />
            <CommandList>
              <CommandGroup>
                <CommandEmpty v-if="!results.length">
                  No tasks found.
                </CommandEmpty>

                <CommandItem
                  v-for="task in results"
                  :key="task.id"
                  :value="task.content"
                  @select="goToDate(task)"
                  class="cursor-pointer truncate"
                >
                  {{ `${task.date} - ${truncate(task.content, 50)}` }}
                </CommandItem>
              </CommandGroup>
            </CommandList>
          </Command>
        </DialogContent>
      </Dialog>
    </SidebarFooter>

    <SidebarRail />
  </Sidebar>
</template>
