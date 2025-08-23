// stores/useDateStore.ts
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import {
  today,
  getLocalTimeZone,
  parseDate,
  type DateValue
} from '@internationalized/date'

export const useCalendarStore = defineStore('date', () => {
  // keep ISO string as the real state (safe for Pinia)
  const isoDate = ref<string>(today(getLocalTimeZone()).toString())

  // expose DateValue as computed
  const date = computed<DateValue>({
    get: () => parseDate(isoDate.value),
    set: (val) => {
      isoDate.value = val.toString()
    }
  })

  // Pretty format
  const formattedDate = computed(() => {
    const jsDate = new Date(isoDate.value) // convert to JS Date
    return new Intl.DateTimeFormat('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    }).format(jsDate)
  })

  return {
    isoDate,        // "2025-08-22"
    date,           // DateValue (safe getter/setter)
    formattedDate   // "August 22, 2025"
  }
})
