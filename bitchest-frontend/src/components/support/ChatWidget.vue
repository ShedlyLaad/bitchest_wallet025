<template>
  <div class="fixed bottom-6 right-6 z-50">
    <Transition name="slide-up">
      <div
        v-if="open"
        class="mb-4 w-80 sm:w-96 bg-gray-900/95 backdrop-blur-xl rounded-2xl border border-gray-700/50 shadow-2xl overflow-hidden flex flex-col"
        style="max-height: 520px;"
      >
        <!-- Header -->
        <div class="p-4 border-b border-gray-700/50 bg-gray-800/80 flex items-center gap-3 flex-shrink-0">
          <div class="p-2 bg-green-500/20 rounded-lg">
            <BotIcon class="h-5 w-5 text-green-400" />
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="font-semibold text-white text-sm">BitChest Support Bot</h3>
            <div class="flex items-center gap-2 mt-0.5">
              <div :class="['w-1.5 h-1.5 rounded-full', isLoading ? 'bg-yellow-400 animate-pulse' : 'bg-green-400 animate-pulse']"></div>
              <p class="text-xs text-gray-400 truncate">{{ isLoading ? 'Typing...' : 'Powered by Google Gemini' }}</p>
            </div>
          </div>
          <button @click="$emit('close')" class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-700/50 rounded-lg transition-colors flex-shrink-0">
            <X class="h-4 w-4" />
          </button>
        </div>

        <!-- Messages -->
        <div ref="chatBox" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-900/30" style="min-height: 260px;">
          <div v-for="(msg, i) in messages" :key="i" :class="['flex', msg.role === 'user' ? 'justify-end' : 'justify-start']">
            <div v-if="msg.role === 'assistant'" class="flex-shrink-0 mr-2 mt-1">
              <div class="w-6 h-6 bg-green-500/20 rounded-full flex items-center justify-center">
                <BotIcon class="h-3.5 w-3.5 text-green-400" />
              </div>
            </div>
            <div
              :class="[
                'rounded-xl px-3.5 py-2.5 text-sm max-w-[82%] leading-relaxed',
                msg.role === 'user'
                  ? 'bg-green-600 text-white rounded-tr-sm'
                  : 'bg-gray-800/90 text-gray-200 border border-gray-700/50 rounded-tl-sm'
              ]"
            >
              <p class="whitespace-pre-wrap break-words">{{ msg.content }}</p>
              <p class="text-[10px] mt-1 opacity-50 text-right">{{ formatTime(msg.timestamp) }}</p>
            </div>
          </div>

          <!-- Typing indicator -->
          <div v-if="isLoading" class="flex justify-start">
            <div class="flex-shrink-0 mr-2 mt-1">
              <div class="w-6 h-6 bg-green-500/20 rounded-full flex items-center justify-center">
                <BotIcon class="h-3.5 w-3.5 text-green-400" />
              </div>
            </div>
            <div class="bg-gray-800/90 border border-gray-700/50 rounded-xl rounded-tl-sm px-4 py-3 flex items-center gap-1.5">
              <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
              <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
              <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
            </div>
          </div>
        </div>

        <!-- Quick Replies -->
        <div v-if="messages.length <= 1" class="px-3 pt-2 pb-1 flex flex-wrap gap-1.5 border-t border-gray-700/30 flex-shrink-0">
          <button
            v-for="r in quickReplies" :key="r"
            @click="handleQuickReply(r)"
            :disabled="isLoading"
            class="text-xs px-2.5 py-1 bg-gray-800 hover:bg-gray-700 disabled:opacity-40 border border-gray-700/50 text-gray-300 rounded-lg transition-colors"
          >{{ r }}</button>
        </div>

        <!-- Error -->
        <div v-if="error" class="mx-3 mb-2 px-3 py-2 bg-red-500/10 border border-red-500/30 rounded-lg text-xs text-red-300 flex-shrink-0">
          Connection issue — please try again.
        </div>

        <!-- Input -->
        <div class="p-3 border-t border-gray-700/50 bg-gray-800/30 flex items-center gap-2 flex-shrink-0">
          <input
            ref="inputRef"
            v-model="inputText"
            type="text"
            placeholder="Ask anything about BitChest..."
            maxlength="500"
            class="flex-1 bg-gray-700/50 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:border-green-500/50 transition-all"
            @keyup.enter="submit"
          />
          <button
            @click="submit"
            :disabled="!inputText.trim() || isLoading"
            class="p-2 bg-green-600 hover:bg-green-700 disabled:opacity-40 disabled:cursor-not-allowed text-white rounded-lg transition-all hover:scale-105 flex-shrink-0"
          >
            <SendIcon class="h-4 w-4" />
          </button>
        </div>
      </div>
    </Transition>

    <!-- Toggle button -->
    <button
      @click="$emit('toggle')"
      class="group relative bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white p-4 rounded-full shadow-lg transition-all duration-300 hover:scale-110 hover:shadow-xl hover:shadow-green-500/40"
    >
      <MessageCircleIcon class="h-6 w-6 relative z-10" />
      <div v-if="!open" class="absolute -top-1 -right-1 w-3 h-3 bg-green-400 rounded-full border-2 border-gray-900 animate-pulse"></div>
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, nextTick, toRef } from 'vue'
import { Bot as BotIcon, MessageCircle as MessageCircleIcon, Send as SendIcon, X } from 'lucide-vue-next'
import { useSupportBot } from '@/composables/useSupportBot'

const props = defineProps<{ open: boolean; userEmail?: string }>()
defineEmits<{ (e: 'toggle'): void; (e: 'close'): void }>()

const { messages, isLoading, error, sendMessage } = useSupportBot(toRef(props, 'userEmail'))

const inputText = ref('')
const chatBox = ref<HTMLElement | null>(null)
const inputRef = ref<HTMLInputElement | null>(null)

const quickReplies = [
  'How do I buy crypto?',
  'Check my balance',
  'Withdrawal help',
  'Reset my password',
  'Open a ticket',
]

async function submit() {
  const text = inputText.value.trim()
  if (!text || isLoading.value) return
  inputText.value = ''
  await sendMessage(text)
}

async function handleQuickReply(text: string) {
  await sendMessage(text)
}

function formatTime(date: Date): string {
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

watch(() => messages.value.length, async () => {
  await nextTick()
  if (chatBox.value) chatBox.value.scrollTop = chatBox.value.scrollHeight
})

watch(() => props.open, async (val) => {
  if (val) { await nextTick(); inputRef.value?.focus() }
})
</script>

<style scoped>
.slide-up-enter-active { transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.slide-up-leave-active { transition: all 0.2s ease-in; }
.slide-up-enter-from, .slide-up-leave-to { transform: translateY(20px) scale(0.95); opacity: 0; }
</style>

