export type Task = {
  id: number,
  user_id: number,
  date: string,
  content: string,
  is_finished: boolean,
  position: number,
}
export type Links = {}
export type Meta = {}

export type TaskResponse = {
  data: Task[],
  links: Links,
  meta: Meta,
}
