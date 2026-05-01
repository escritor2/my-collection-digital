import { ref, onMounted } from 'vue'

type Prefs = {
  dyslexiaMode: boolean
}

const KEY = 'accessibility:prefs:v1'

export function useAccessibilityPrefs() {
  const dyslexiaMode = ref(false)

  const apply = () => {
    if (typeof document === 'undefined') return
    document.documentElement.classList.toggle('dyslexia', dyslexiaMode.value)
  }

  const save = () => {
    if (typeof localStorage === 'undefined') return
    const prefs: Prefs = { dyslexiaMode: dyslexiaMode.value }
    localStorage.setItem(KEY, JSON.stringify(prefs))
  }

  const load = () => {
    try {
      const raw = localStorage.getItem(KEY)
      if (!raw) return
      const p = JSON.parse(raw) as Partial<Prefs>
      dyslexiaMode.value = !!p.dyslexiaMode
    } catch {
      // ignore
    }
  }

  const setDyslexiaMode = (v: boolean) => {
    dyslexiaMode.value = v
    apply()
    save()
  }

  onMounted(() => {
    load()
    apply()
  })

  return { dyslexiaMode, setDyslexiaMode }
}

