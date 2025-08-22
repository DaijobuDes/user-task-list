<script setup lang="ts">
import { Calendar } from '@/components/ui/calendar'
import {
  SidebarGroup,
  SidebarGroupContent,
} from '@/components/ui/sidebar'

import { useCalendarStore } from '@/stores/useCalendarStore';
const calendar = useCalendarStore()
// import { getLocalTimeZone, parseDate, today, type DateValue } from '@internationalized/date'

// // const date = ref<DateValue>(today(getLocalTimeZone()))
// const date = ref<DateValue>(parseDate("2025-08-02"))

// watch(date, (newDate) => {
//     console.log(newDate.toString())
// })

</script>

<template>
  <SidebarGroup class="px-0">
    <SidebarGroupContent>
      <Calendar v-model="calendar.date" class="[&_[role=gridcell].bg-accent]:bg-sidebar-primary [&_[role=gridcell].bg-accent]:text-sidebar-primary-foreground [&_[role=gridcell]]:w-[33px]" />
    </SidebarGroupContent>
  </SidebarGroup>
</template>
