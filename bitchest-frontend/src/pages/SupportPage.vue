<template>
  <div class="min-h-screen bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto p-6 space-y-8">
      <!-- Header -->
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold mb-4">How Can We Help?</h1>
        <p class="text-gray-400 text-lg">Get the support you need, when you need it</p>
      </div>

      <!-- FAQ Section -->
      <section class="bg-gray-800 rounded-2xl p-6 border border-gray-700">
        <h2 class="text-2xl font-bold mb-6 flex items-center">
          <HelpCircleIcon class="h-6 w-6 mr-2 text-blue-400" />
          Frequently Asked Questions
        </h2>

        <div class="space-y-4">
          <div v-for="(faq, index) in faqs" :key="index" class="bg-gray-700/50 rounded-xl overflow-hidden transition-all duration-300">
            <button
              class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-700/70 transition-colors"
              @click="openFaq = openFaq === index ? null : index"
            >
              <span class="font-medium">{{ faq.question }}</span>
              <ChevronDownIcon :class="['h-5 w-5 transform transition-transform duration-300', openFaq === index ? 'rotate-180' : '']" />
            </button>

            <div v-if="openFaq === index" class="px-6 py-4 text-gray-300 bg-gray-700/30">
              {{ faq.answer }}
            </div>
          </div>
        </div>
      </section>

      <!-- Ticket System -->
      <section class="bg-gray-800 rounded-2xl p-6 border border-gray-700">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold flex items-center">
            <TicketIcon class="h-6 w-6 mr-2 text-blue-400" />
            Support Tickets
          </h2>
          <button @click="showNewTicketForm = true" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center space-x-2">
            <PlusCircleIcon class="h-5 w-5" />
            <span>New Ticket</span>
          </button>
        </div>

        <div v-if="showNewTicketForm" class="mb-6 bg-gray-700/30 rounded-xl p-6">
          <form @submit.prevent="handleNewTicket" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">Subject</label>
              <input v-model="newTicket.subject" type="text" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">Message</label>
              <textarea v-model="newTicket.message" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 h-32"></textarea>
            </div>
            <div class="flex justify-end space-x-4">
              <button type="button" @click="showNewTicketForm = false" class="px-4 py-2 text-gray-400 hover:text-white transition-colors">Cancel</button>
              <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Submit Ticket</button>
            </div>
          </form>
        </div>

        <div v-if="tickets.length === 0" class="text-center py-12 text-gray-400">
          <MessageSquareIcon class="h-12 w-12 mx-auto mb-4 opacity-50" />
          <p>No support tickets yet</p>
        </div>

        <div v-else class="space-y-4">
          <div v-for="ticket in tickets" :key="ticket.id" class="bg-gray-700/30 rounded-xl p-6 hover:bg-gray-700/50 transition-all duration-300">
            <div class="flex items-start justify-between">
              <div class="space-y-1">
                <h3 class="font-semibold">{{ ticket.subject }}</h3>
                <p class="text-gray-400 text-sm">{{ ticket.message }}</p>
                <div class="flex items-center space-x-4 text-sm">
                  <span class="text-gray-500 flex items-center">
                    <ClockIcon class="h-4 w-4 mr-1" /> {{ ticket.date }}
                  </span>
                  <span :class="['flex items-center', ticket.status === 'open' ? 'text-green-400' : 'text-gray-400']">
                    <component :is="ticket.status === 'open' ? CheckCircleIcon : XCircleIcon" class="h-4 w-4 mr-1" />
                    {{ ticket.status }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Floating Chat Widget -->
      <div class="fixed bottom-6 right-6 z-50">
        <div v-if="showChatWidget" class="mb-4 w-80 bg-gray-800 rounded-2xl border border-gray-700 shadow-2xl">
          <div class="p-4 border-b border-gray-700 flex items-center space-x-2">
            <BotIcon class="h-6 w-6 text-blue-400" />
            <div>
              <h3 class="font-semibold">Support Assistant</h3>
              <p class="text-xs text-gray-400">Typically replies instantly</p>
            </div>
          </div>

          <div class="p-4 h-80 overflow-y-auto">
            <div class="space-y-4">
              <div class="flex items-start">
                <div class="bg-gray-700 rounded-lg p-3 ml-auto">
                  <p class="text-sm">How can I help you today?</p>
                </div>
              </div>
            </div>
          </div>

          <form @submit.prevent="handleChatSubmit" class="p-4 border-t border-gray-700">
            <div class="flex items-center space-x-2">
              <input v-model="message" type="text" placeholder="Type your message..." class="flex-1 bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
              <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-lg">
                <SendIcon class="h-5 w-5" />
              </button>
            </div>
          </form>
        </div>

        <button @click="showChatWidget = !showChatWidget" class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-full shadow-lg transition-all duration-300 transform hover:scale-110">
          <MessageCircleIcon class="h-6 w-6" />
        </button>
      </div>
    </div>

    <!-- Footer - Full Width -->
    <FooterSection />
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
  MessageSquare as MessageSquareIcon
} from 'lucide-vue-next';

import FooterSection from '../components/sectionsLanding/FooterSection.vue';

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
/* animations and styles rely on Tailwind utilities; add any custom CSS here if needed */
</style>