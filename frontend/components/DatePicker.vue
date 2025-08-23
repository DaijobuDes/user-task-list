<script setup lang="ts">
import { Calendar } from '@/components/ui/calendar'
import {
  SidebarGroup,
  SidebarGroupContent,
} from '@/components/ui/sidebar'

import { useCalendarStore } from '@/stores/useCalendarStore';
const calendar = useCalendarStore()

</script>

<template>
  <SidebarGroup class="px-0">
    <SidebarGroupContent>
      <Calendar v-model="calendar.date" class="[&_[role=gridcell].bg-accent]:bg-sidebar-primary [&_[role=gridcell].bg-accent]:text-sidebar-primary-foreground [&_[role=gridcell]]:w-[33px]" />
    </SidebarGroupContent>
  </SidebarGroup>
</template>
