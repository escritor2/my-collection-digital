import { get, set, del } from 'idb-keyval'

export type OfflineJob = {
  id: string
  createdAt: number
  method: 'POST' | 'PUT' | 'PATCH' | 'DELETE'
  url: string
  body?: any
}

const KEY = 'offline:queue:v1'

function uid() {
  return `${Date.now()}_${Math.random().toString(16).slice(2)}`
}

export async function enqueueJob(job: Omit<OfflineJob, 'id' | 'createdAt'>) {
  const current = ((await get(KEY)) as OfflineJob[] | undefined) ?? []
  const next: OfflineJob[] = [...current, { ...job, id: uid(), createdAt: Date.now() }]
  await set(KEY, next)
  return next
}

export async function readQueue() {
  return (((await get(KEY)) as OfflineJob[] | undefined) ?? [])
}

export async function clearQueue() {
  await del(KEY)
}

export async function removeJob(id: string) {
  const current = await readQueue()
  await set(KEY, current.filter(j => j.id !== id))
}

