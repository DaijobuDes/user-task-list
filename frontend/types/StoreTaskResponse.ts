export type StoredTask = {
  id: number,
  user_id: number,
  content: string,
  is_finished: number,
  position: number,
  date: string
}

export type StoreTaskResponse = {
  data: StoredTask,
}
