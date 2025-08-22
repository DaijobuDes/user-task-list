<script setup lang="ts">
import type { SidebarProps } from '@/components/ui/sidebar'

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

const props = defineProps<SidebarProps>()
// This is sample data.
const data = {
  user: {
    name: "demo user",
    email: "m@example.com",
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
      <!-- <Calendars :calendars="data.calendars" /> -->
    </SidebarContent>
    <SidebarFooter>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton>
            <Search />
            <span>Search for tasks</span>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarFooter>
    <SidebarRail />
  </Sidebar>
</template>
