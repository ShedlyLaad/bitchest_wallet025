<template>
  <div class="min-h-screen bg-gray-900 text-white relative overflow-hidden">
    <!-- Enhanced Animated Background -->
    <div class="absolute inset-0 pointer-events-none z-0">
      <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-gray-900 via-gray-900 to-gray-950"></div>
      <div 
        class="absolute top-1/4 -left-40 w-96 h-96 rounded-full blur-3xl opacity-10 animate-pulse"
        :style="{ backgroundColor: 'var(--blue-dark)' }"
      ></div>
      <div 
        class="absolute bottom-1/4 -right-40 w-96 h-96 rounded-full blur-3xl opacity-10 animate-pulse delay-1000"
        :style="{ backgroundColor: 'var(--blue)' }"
      ></div>
      <div class="absolute inset-0 opacity-[0.02]" style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 50px 50px;"></div>
    </div>

    <div class="max-w-7xl mx-auto p-4 sm:p-6 space-y-6 sm:space-y-8 relative z-10">
      <!-- Enhanced Header -->
      <div class="text-center mb-8 sm:mb-12 space-y-4">
        <div class="inline-block p-4 bg-gradient-to-br from-blue-600/20 to-blue-800/10 rounded-2xl border border-blue-500/30 backdrop-blur-sm mb-4">
          <HelpCircleIcon class="h-12 w-12 text-blue-400 mx-auto" />
        </div>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-4 bg-gradient-to-r from-white via-gray-200 to-gray-300 bg-clip-text text-transparent">
          How Can We Help?
        </h1>
        <p class="text-gray-400 text-base sm:text-lg">Get the support you need, when you need it</p>
      </div>

      <!-- Enhanced FAQ Section -->
      <section class="bg-gradient-to-br from-gray-800/60 to-gray-800/40 backdrop-blur-xl rounded-2xl p-6 sm:p-8 border border-gray-700/50 shadow-lg hover:shadow-xl transition-all duration-300">
        <h2 class="text-2xl sm:text-3xl font-bold mb-6 flex items-center gap-3">
          <div class="p-2 bg-blue-500/20 rounded-lg">
            <HelpCircleIcon class="h-6 w-6 text-blue-400" />
          </div>
          <span>Frequently Asked Questions</span>
        </h2>

        <div class="space-y-3">
          <div 
            v-for="(faq, index) in faqs" 
            :key="index" 
            class="group bg-gradient-to-br from-gray-700/40 to-gray-800/40 backdrop-blur-sm rounded-xl overflow-hidden border border-gray-700/50 hover:border-blue-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/10"
          >
            <button
              class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-700/50 transition-all duration-300"
              @click="openFaq = openFaq === index ? null : index"
            >
              <span class="font-medium text-white pr-4">{{ faq.question }}</span>
              <ChevronDownIcon 
                :class="[
                  'h-5 w-5 flex-shrink-0 text-gray-400 transform transition-all duration-300',
                  openFaq === index ? 'rotate-180 text-blue-400' : 'group-hover:text-blue-400'
                ]" 
              />
            </button>

            <Transition name="slide-fade">
              <div v-if="openFaq === index" class="px-6 py-4 text-gray-300 bg-gray-800/30 border-t border-gray-700/50">
                <p class="leading-relaxed">{{ faq.answer }}</p>
              </div>
            </Transition>
          </div>
        </div>
      </section>

      <!-- Enhanced Ticket System -->
      <section class="bg-gradient-to-br from-gray-800/60 to-gray-800/40 backdrop-blur-xl rounded-2xl p-6 sm:p-8 border border-gray-700/50 shadow-lg hover:shadow-xl transition-all duration-300">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
          <h2 class="text-2xl sm:text-3xl font-bold flex items-center gap-3">
            <div class="p-2 bg-blue-500/20 rounded-lg">
              <TicketIcon class="h-6 w-6 text-blue-400" />
            </div>
            <span>Support Tickets</span>
          </h2>
          <button 
            @click="showNewTicketForm = true" 
            class="group relative px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded-xl transition-all duration-200 transform hover:scale-105 hover:shadow-xl hover:shadow-blue-500/30 flex items-center space-x-2 font-medium"
          >
            <PlusCircleIcon class="h-5 w-5 transition-transform group-hover:rotate-90" />
            <span>New Ticket</span>
          </button>
        </div>

        <Transition name="slide-fade">
          <div v-if="showNewTicketForm" class="mb-6 bg-gradient-to-br from-gray-700/40 to-gray-800/40 backdrop-blur-sm rounded-xl p-6 border border-gray-700/50 shadow-lg">
            <form @submit.prevent="handleNewTicket" class="space-y-5">
              <div class="space-y-2">
                <label class="flex items-center gap-2 text-sm font-semibold text-gray-300">
                  <FileText class="h-4 w-4 text-gray-400" />
                  Subject
                </label>
                <div class="relative group">
                  <div class="absolute inset-0 bg-gradient-to-r from-blue-500/0 to-purple-500/0 group-hover:from-blue-500/5 group-hover:to-purple-500/5 rounded-xl transition-all duration-300"></div>
                  <input 
                    v-model="newTicket.subject" 
                    type="text" 
                    required 
                    class="relative w-full bg-gray-800/50 backdrop-blur-sm border-2 border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200"
                    placeholder="Enter ticket subject"
                  />
                </div>
              </div>
              <div class="space-y-2">
                <label class="flex items-center gap-2 text-sm font-semibold text-gray-300">
                  <MessageSquareIcon class="h-4 w-4 text-gray-400" />
                  Message
                </label>
                <div class="relative group">
                  <div class="absolute inset-0 bg-gradient-to-r from-blue-500/0 to-purple-500/0 group-hover:from-blue-500/5 group-hover:to-purple-500/5 rounded-xl transition-all duration-300"></div>
                  <textarea 
                    v-model="newTicket.message" 
                    required 
                    class="relative w-full bg-gray-800/50 backdrop-blur-sm border-2 border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200 h-32 resize-none"
                    placeholder="Describe your issue..."
                  ></textarea>
                </div>
              </div>
              <div class="flex justify-end gap-3 pt-2">
                <button 
                  type="button" 
                  @click="showNewTicketForm = false" 
                  class="px-5 py-2.5 text-gray-400 hover:text-white bg-gray-700/50 hover:bg-gray-700 rounded-xl transition-all duration-200 font-medium"
                >
                  Cancel
                </button>
                <button 
                  type="submit" 
                  class="group relative px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded-xl transition-all duration-200 transform hover:scale-105 hover:shadow-xl hover:shadow-blue-500/30 font-medium overflow-hidden"
                >
                  <span class="relative z-10 flex items-center gap-2">
                    <SendIcon class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                    Submit Ticket
                  </span>
                  <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/0 to-transparent group-hover:via-white/10 transition-all duration-700 transform -translate-x-full group-hover:translate-x-full"></div>
                </button>
              </div>
            </form>
          </div>
        </Transition>

        <div v-if="tickets.length === 0" class="text-center py-12 text-gray-400">
          <div class="inline-block p-6 bg-gray-700/20 rounded-full mb-4">
            <MessageSquareIcon class="h-12 w-12 opacity-50" />
          </div>
          <p class="text-lg font-medium">No support tickets yet</p>
          <p class="text-sm text-gray-500 mt-2">Create your first ticket to get started</p>
        </div>

        <div v-else class="space-y-3">
          <div 
            v-for="ticket in tickets" 
            :key="ticket.id" 
            class="group relative bg-gradient-to-br from-gray-700/40 to-gray-800/40 backdrop-blur-sm rounded-xl p-5 sm:p-6 border border-gray-700/50 hover:border-blue-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/10 hover:scale-[1.01] overflow-hidden"
          >
            <div class="absolute inset-0 bg-gradient-to-r from-blue-500/0 to-purple-500/0 group-hover:from-blue-500/5 group-hover:to-purple-500/5 transition-all duration-300"></div>
            <div class="relative flex items-start justify-between gap-4">
              <div class="flex-1 space-y-3">
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-blue-500/20 rounded-lg group-hover:bg-blue-500/30 transition-colors">
                    <TicketIcon class="h-4 w-4 text-blue-400" />
                  </div>
                  <h3 class="font-semibold text-white text-lg">{{ ticket.subject }}</h3>
                </div>
                <p class="text-gray-400 text-sm leading-relaxed pl-11">{{ ticket.message }}</p>
                <div class="flex flex-wrap items-center gap-4 text-sm pl-11">
                  <span class="text-gray-500 flex items-center gap-1.5">
                    <ClockIcon class="h-4 w-4" />
                    {{ ticket.date }}
                  </span>
                  <span 
                    :class="[
                      'flex items-center gap-1.5 px-3 py-1 rounded-lg font-medium text-xs',
                      ticket.status === 'open' 
                        ? 'bg-green-500/20 text-green-400 border border-green-500/30' 
                        : 'bg-gray-600/20 text-gray-400 border border-gray-600/30'
                    ]"
                  >
                    <component :is="ticket.status === 'open' ? CheckCircleIcon : XCircleIcon" class="h-3.5 w-3.5" />
                    {{ ticket.status }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Enhanced Floating Chat Widget -->
      <div class="fixed bottom-6 right-6 z-50">
        <Transition name="slide-up">
          <div v-if="showChatWidget" class="mb-4 w-80 sm:w-96 bg-gradient-to-br from-gray-800/95 to-gray-900/95 backdrop-blur-xl rounded-2xl border border-gray-700/50 shadow-2xl overflow-hidden">
            <div class="p-4 border-b border-gray-700/50 bg-gradient-to-r from-gray-800/80 to-gray-800/60 flex items-center space-x-3">
              <div class="p-2 bg-blue-500/20 rounded-lg">
                <BotIcon class="h-5 w-5 text-blue-400" />
              </div>
              <div class="flex-1">
                <h3 class="font-semibold text-white">Support Assistant</h3>
                <div class="flex items-center gap-2 mt-0.5">
                  <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                  <p class="text-xs text-gray-400">Typically replies instantly</p>
                </div>
              </div>
              <button 
                @click="showChatWidget = false" 
                class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-700/50 rounded-lg transition-colors"
              >
                <X class="h-4 w-4" />
              </button>
            </div>

            <div class="p-4 h-80 overflow-y-auto bg-gray-900/30">
              <div class="space-y-4">
                <div class="flex items-start">
                  <div class="bg-gradient-to-br from-blue-600/20 to-blue-800/10 rounded-xl p-3 ml-auto border border-blue-500/30 max-w-[80%]">
                    <p class="text-sm text-white">How can I help you today?</p>
                  </div>
                </div>
              </div>
            </div>

            <form @submit.prevent="handleChatSubmit" class="p-4 border-t border-gray-700/50 bg-gray-800/30">
              <div class="flex items-center space-x-2">
                <div class="relative flex-1 group">
                  <div class="absolute inset-0 bg-gradient-to-r from-blue-500/0 to-purple-500/0 group-hover:from-blue-500/5 group-hover:to-purple-500/5 rounded-lg transition-all duration-300"></div>
                  <input 
                    v-model="message" 
                    type="text" 
                    placeholder="Type your message..." 
                    class="relative flex-1 bg-gray-700/50 border-2 border-gray-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200" 
                  />
                </div>
                <button 
                  type="submit" 
                  class="group p-2.5 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded-lg transition-all duration-200 transform hover:scale-110 hover:shadow-lg hover:shadow-blue-500/30"
                >
                  <SendIcon class="h-5 w-5 transition-transform group-hover:translate-x-0.5" />
                </button>
              </div>
            </form>
          </div>
        </Transition>

        <button 
          @click="showChatWidget = !showChatWidget" 
          class="group relative bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white p-4 rounded-full shadow-lg transition-all duration-300 transform hover:scale-110 hover:shadow-xl hover:shadow-blue-500/40 overflow-hidden"
          :style="{ boxShadow: showChatWidget ? '0 0 30px rgba(59, 130, 246, 0.5)' : '0 4px 14px rgba(59, 130, 246, 0.3)' }"
        >
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/0 to-transparent group-hover:via-white/10 transition-all duration-700 transform -translate-x-full group-hover:translate-x-full"></div>
          <MessageCircleIcon class="h-6 w-6 relative z-10 transition-transform group-hover:scale-110" />
          <div v-if="!showChatWidget" class="absolute -top-1 -right-1 w-3 h-3 bg-green-400 rounded-full border-2 border-gray-900 animate-pulse"></div>
        </button>
      </div>
    </div>

    <!-- Footer - Enhanced -->
    <UserFooter />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { RouterLink } from 'vue-router';
import {
  ChevronDown as ChevronDownIcon,
  MessageCircle as MessageCircleIcon,
  Ticket as TicketIcon,
  Send as SendIcon,
  Clock as ClockIcon,
  CheckCircle as CheckCircleIcon,
  XCircle as XCircleIcon,
  PlusCircle as PlusCircleIcon,
  HelpCircle as HelpCircleIcon,
  Bot as BotIcon,
  MessageSquare as MessageSquareIcon,
  FileText,
  X
} from 'lucide-vue-next';

import UserFooter from '@/components/UserFooter.vue';

interface SupportTicket {
  id: string;
  subject: string;
  message: string;
  status: 'open' | 'closed';
  date: string;
}

interface FAQ {
  question: string;
  answer: string;
}

const openFaq = ref<number | null>(null);
const showNewTicketForm = ref(false);
const showChatWidget = ref(false);
const message = ref('');
const newTicket = ref({ subject: '', message: '' });

const faqs: FAQ[] = [
  { question: "How do I open a support ticket?", answer: "Click on the 'New Ticket' button below the ticket list. Fill in the subject and description of your issue, then submit the form. We'll respond within 24 hours." },
  { question: "What is the typical response time?", answer: "We aim to respond to all tickets within 24 hours. Priority issues are handled even faster. You'll receive email notifications for all updates." },
  { question: "How can I track my ticket status?", answer: "All your tickets are listed in the 'My Tickets' section. You can view their current status, updates, and full conversation history." },
  { question: "What information should I include in my ticket?", answer: "Please include: your account email, detailed description of the issue, any error messages, and steps to reproduce the problem if applicable." }
];

const tickets = ref<SupportTicket[]>([
  { id: '1', subject: 'Account Verification Issue', message: 'Having trouble with KYC verification...', status: 'open', date: '2024-02-15' },
  { id: '2', subject: 'Withdrawal Pending', message: 'My withdrawal has been pending for 2 hours...', status: 'closed', date: '2024-02-14' }
]);

function handleNewTicket() {
  // placeholder logic - push to tickets list locally
  tickets.value.unshift({
    id: String(Date.now()),
    subject: newTicket.value.subject,
    message: newTicket.value.message,
    status: 'open',
    date: new Date().toLocaleDateString()
  });
  newTicket.value = { subject: '', message: '' };
  showNewTicketForm.value = false;
}

function handleChatSubmit() {
  // placeholder chat handling
  console.log('Chat message:', message.value);
  message.value = '';
}
</script>

<style scoped>
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.2s ease-in;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}

.slide-up-enter-active {
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.slide-up-leave-active {
  transition: all 0.2s ease-in;
}

.slide-up-enter-from {
  transform: translateY(20px) scale(0.95);
  opacity: 0;
}

.slide-up-leave-to {
  transform: translateY(20px) scale(0.95);
  opacity: 0;
}
</style>