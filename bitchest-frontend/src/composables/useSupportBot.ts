import { ref, unref, type MaybeRef } from 'vue'
import { useAuthStore } from '@/stores/auth'

export interface BotMessage {
  role: 'user' | 'assistant'
  content: string
  timestamp: Date
}

const apiBase = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000'
/** Base URL sans /chat : soit le bot direct (8001), soit le proxy Laravel …/api/support */
const BOT_API_URL =
  import.meta.env.VITE_BOT_API_URL ?? `${apiBase}/api/support`

export function useSupportBot(userEmail?: MaybeRef<string | undefined>) {
  const auth = useAuthStore()
  auth.hydrate()

  const messages = ref<BotMessage[]>([
    {
      role: 'assistant',
      content: "Welcome to BitChest Support! I'm your support assistant. I can help you with your account, wallet, trading questions, or guide you to the right resource. How can I help you today?",
      timestamp: new Date(),
    },
  ])

  const isLoading = ref(false)
  const error = ref<string | null>(null)

  async function sendMessage(userInput: string): Promise<void> {
    const trimmed = userInput.trim()
    if (!trimmed || isLoading.value) return

    error.value = null

    messages.value.push({
      role: 'user',
      content: trimmed,
      timestamp: new Date(),
    })

    isLoading.value = true

    try {
      const history = messages.value
        .slice(1)
        .map(({ role, content }) => ({ role, content }))

      const headers: Record<string, string> = {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      }
      if (auth.token) {
        headers.Authorization = `Bearer ${auth.token}`
      }

      const response = await fetch(`${BOT_API_URL}/chat`, {
        method: 'POST',
        headers,
        body: JSON.stringify({
          messages: history,
          user_email: unref(userEmail) || undefined,
        }),
      })

      if (!response.ok) {
        const data = await response.json().catch(() => ({})) as Record<string, unknown>
        const detail = typeof data?.detail === 'string' ? data.detail : undefined
        const message =
          typeof data?.message === 'string' ? data.message : undefined
        const firstValidation =
          data?.errors && typeof data.errors === 'object'
            ? String(Object.values(data.errors as Record<string, unknown[]>).flat()[0] ?? '')
            : undefined
        throw new Error(
          detail || message || firstValidation || `Server error ${response.status}`
        )
      }

      const data = await response.json()

      messages.value.push({
        role: 'assistant',
        content: data.reply,
        timestamp: new Date(),
      })
    } catch (err: any) {
      error.value = err.message ?? 'Connection error'
      messages.value.push({
        role: 'assistant',
        content: "I'm having trouble connecting right now. Please try again in a moment, or open a support ticket above.",
        timestamp: new Date(),
      })
    } finally {
      isLoading.value = false
    }
  }

  function resetConversation(): void {
    messages.value = [
      {
        role: 'assistant',
        content: "Welcome to BitChest Support! How can I help you today?",
        timestamp: new Date(),
      },
    ]
    error.value = null
  }

  return { messages, isLoading, error, sendMessage, resetConversation }
}

