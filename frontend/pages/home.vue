<script lang="ts">
export const description = "A sidebar with a calendar."
export const iframeHeight = "800px"
</script>

<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue'
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbList,
  BreadcrumbPage,
} from '@/components/ui/breadcrumb'
import { Separator } from '@/components/ui/separator'
import {
  SidebarInset,
  SidebarProvider,
  SidebarTrigger,
} from '@/components/ui/sidebar'
import { Checkbox } from "@/components/ui/checkbox"
import { Button } from "@/components/ui/button"
import {
  AlertDialog,
  AlertDialogTrigger,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogFooter,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogCancel,
  AlertDialogAction,
} from "@/components/ui/alert-dialog/"
import { Trash2 } from "lucide-vue-next"

import { useCalendarStore } from '@/stores/useCalendarStore';
import { useAuthStore } from '@/stores/useAuthStore';
import type { Task, TaskResponse } from '@/types/IndexTaskResponse';
import type { StoreTaskResponse } from '@/types/StoreTaskResponse';

const calendar = useCalendarStore()
const auth = useAuthStore()
const config = useRuntimeConfig();
const router = useRouter()

const backendUrl = config.public.apiUrl;

const items = ref<Task[]>([])
const newMessage = ref()
const submitMessage = ref()
const contentBox = ref<string>()



if (!auth.isAuthenticated) {
  router.push("/")
}

watch(calendar, async () => {
  const response = await fetchTasks()
  items.value = response.data
  sortItemsInPlace()
})

watch(items, async () => {
  console.log(items.value)
})

onMounted(async () => {
  const response = await fetchTasks()
  items.value = response.data
  sortItemsInPlace()
})

async function fetchTasks() {
  try {
  const response = await $fetch<TaskResponse>(`${backendUrl}/api/tasks`, {
    method: "GET",
    query: {
      date: calendar.isoDate,
    },
    headers: {
      Authorization: `Bearer ${auth.authToken}`
    }
  })
  console.log(response)
  return response
  } catch (err: any) {
    return {"data": []}
  }
}

const sortItemsInPlace = () => {
  items.value.sort((a, b) => {
    if (a.position !== b.position) return a.position - b.position
    return a.id - b.id
  })
}

const submitTask = async () => {
  if (!newMessage.value.trim()) return
  try {
    const newPos = items.value.length + 1
    const response = await $fetch<StoreTaskResponse>(`${backendUrl}/api/tasks/`, {
      method: "POST",
      body: {
        content: newMessage.value,
        date: calendar.isoDate,
        is_finished: 0,
        position: newPos,
      },
      headers: {
        Authorization: `Bearer ${auth.authToken}`
      }
    })

    console.log(response)

    items.value.push({
      id: response.data.id, // make sure backend returns id
      user_id: response.data.user_id,
      content: newMessage.value,
      is_finished: 0,
      position: newPos,
    })

    sortItemsInPlace()
    newMessage.value = ""
  } catch (err: any) {

  }
}

const dragIndex = ref<number | null>(null)

const onTaskChange = async (id: number, state: boolean|number, content: string) => {
  const payload = {
    content: content,
    is_finished: state
  }

  try {
    await $fetch(`${backendUrl}/api/tasks/${id}`, {
      method: "PUT",
      body: payload,
      headers: {
        Authorization: `Bearer ${auth.authToken}`
      }
    })
  } catch (err: any) {
    console.error("Failed to update positions", err)
    // optionally rollback or show error toast
  }
}

const onDragStart = (index: number) => {
  dragIndex.value = index
}

const onDrop = async (index: number) => {
  if (dragIndex.value === null) return

  // reorder locally
  const dragged = items.value.splice(dragIndex.value, 1)[0]
  items.value.splice(index, 0, dragged)
  dragIndex.value = null

  // reassign positions
  items.value.forEach((item, idx) => {
    item.position = idx + 1
  })
  sortItemsInPlace()

  // build payload: { items: [ {id, pos}, {id, pos} ] }
  const payload = {
    items: items.value.map((item, idx) => ({
      id: item.id,
      position: idx + 1, // 1-based index
    })),
  }

  try {
    await $fetch(`${backendUrl}/api/tasks/update-positions`, {
      method: "PUT",
      body: payload,
      headers: {
        Authorization: `Bearer ${auth.authToken}`
      }
    })
  } catch (err: any) {
    console.error("Failed to update positions", err)
    // optionally rollback or show error toast
  }
}

const deleteItem = async (id: number) => {
  items.value = items.value.filter(item => item.id !== id)
  sortItemsInPlace()

  try {
    await $fetch(`${backendUrl}/api/tasks/${id}`, {
      method: "DELETE",
      headers: {
        Authorization: `Bearer ${auth.authToken}`
      }
    })
  } catch (err: any) {
    console.error(err);
  }

}

</script>

<template>
  <SidebarProvider>
    <AppSidebar />
    <SidebarInset>
      <header class="bg-background sticky top-0 flex h-16 shrink-0 items-center gap-2 border-b px-4">
        <SidebarTrigger class="-ml-1" />
        <Separator
          orientation="vertical"
          class="mr-2 data-[orientation=vertical]:h-4"
        />
        <Breadcrumb>
          <BreadcrumbList>
            <BreadcrumbItem>
              <BreadcrumbPage>{{ calendar.formattedDate }}</BreadcrumbPage>
            </BreadcrumbItem>
          </BreadcrumbList>
        </Breadcrumb>
      </header>
      <!-- <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="grid auto-rows-min gap-4 md:grid-cols-5">
          <div v-for="i in 20" :key="i" class="aspect-square rounded-xl bg-muted/50" />
        </div>
      </div> -->

      <!-- Content area -->
      <div class="flex flex-1 flex-col p-4">
        <!-- Case 1: No tasks -->
        <div
          v-if="!items || items.length === 0"
          class="flex flex-col items-center justify-center flex-1"
        >
          <div
            class="w-full max-w-2xl rounded-2xl border bg-background p-4 shadow-md"
          >
            <textarea
              v-model="newMessage"
              rows="4"
              placeholder="What do you want to do on this day???"
              class="w-full resize-none rounded-lg border p-3 focus:outline-none focus:ring focus:ring-primary"
            />
            <div class="flex justify-end mt-2">
              <button
                @click="submitTask"
                class="rounded-lg bg-primary px-4 py-2 text-white hover:bg-primary/90"
              >
                Submit
              </button>
            </div>
          </div>
        </div>

        <!-- Case 2: Tasks exist -->
        <div v-else class="flex flex-1 flex-col">
          <!-- Task list (scrollable if long) -->
          <div class="flex-1 space-y-2 overflow-y-auto">
            <div
              v-for="(item, index) in items"
              :key="item.id"
              class="flex items-center gap-3 rounded-xl bg-muted/50 p-3 cursor-grab"
              draggable="true"
              @dragstart="onDragStart(index)"
              @dragover.prevent
              @drop="onDrop(index)"
            >
              <!-- Checkbox -->
              <Checkbox
                v-model="item.is_finished"
                @click="onTaskChange(item.id, !item.is_finished, item.content)"
              />

              <!-- Editable task content -->
              <textarea
                v-model="item.content"
                class="flex-1 resize-none rounded-md bg-transparent p-1 focus:outline-none"
                :class="{ 'line-through text-muted-foreground': item.is_finished }"
                rows="1"
                @blur="onTaskChange(item.id, item.is_finished, item.content)"
              />

              <!-- Delete button -->
              <AlertDialog>
                <AlertDialogTrigger as-child>
                  <Button variant="ghost" size="icon">
                    <Trash2 class="h-4 w-4 text-red-500" />
                  </Button>
                </AlertDialogTrigger>
                <AlertDialogContent>
                  <AlertDialogHeader>
                    <AlertDialogTitle>Delete Task</AlertDialogTitle>
                    <AlertDialogDescription>
                      Are you sure you want to delete this task? This action cannot be undone.
                    </AlertDialogDescription>
                  </AlertDialogHeader>
                  <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction @click="deleteItem(item.id)">
                      Delete
                    </AlertDialogAction>
                  </AlertDialogFooter>
                </AlertDialogContent>
              </AlertDialog>
            </div>
          </div>

          <!-- Input bar pinned at bottom -->
          <div class="flex items-center gap-2 border-t pt-2 mt-2">
            <textarea
              v-model="newMessage"
              placeholder="Add a new task..."
              rows="1"
              class="flex-1 resize-none rounded-lg border p-2 focus:outline-none focus:ring focus:ring-primary"
            />
            <button
              @click="submitTask"
              class="rounded-lg bg-primary px-4 py-2 text-white hover:bg-primary/90"
            >
              Submit
            </button>
          </div>
        </div>
      </div>



    </SidebarInset>
  </SidebarProvider>
</template>
