<template>
  <div class="min-h-screen bg-gray-900 text-white relative overflow-hidden">
    <!-- Animated Background -->
    <div class="absolute inset-0 pointer-events-none z-0">
      <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-gray-900 via-gray-900 to-gray-950"></div>
      <div class="absolute top-1/4 -left-40 w-96 h-96 rounded-full blur-3xl opacity-10 animate-pulse" style="background-color:#01ff19"></div>
      <div class="absolute bottom-1/4 -right-40 w-96 h-96 rounded-full blur-3xl opacity-10 animate-pulse delay-1000" style="background-color:#35a7ff"></div>
      <div class="absolute inset-0 opacity-[0.02]" style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 50px 50px;"></div>
    </div>

    <div class="max-w-7xl mx-auto p-4 sm:p-6 space-y-6 sm:space-y-8 relative z-10">

      <!-- Header -->
      <div class="text-center mb-8 sm:mb-12 space-y-4">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-500/10 border border-green-500/30 rounded-full mb-4">
          <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
          <span class="text-green-400 text-sm font-medium">Support Center — Online 24/7</span>
        </div>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-4 bg-gradient-to-r from-white via-gray-200 to-gray-300 bg-clip-text text-transparent">
          How Can We Help?
        </h1>
        <p class="text-gray-400 text-base sm:text-lg max-w-xl mx-auto">
          Our team is here to help you trade cryptocurrencies with confidence on BitChest.
        </p>
      </div>

      <!-- Quick Links -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <button
          v-for="link in quickLinks"
          :key="link.label"
          @click="scrollTo(link.target)"
          class="group bg-gray-800/60 border border-gray-700/50 hover:border-green-500/40 rounded-xl p-4 text-center transition-all duration-200 hover:bg-gray-700/40"
        >
          <span class="text-2xl mb-2 block">{{ link.icon }}</span>
          <span class="text-sm text-gray-300 group-hover:text-white transition-colors">{{ link.label }}</span>
        </button>
      </div>

      <!-- FAQ Section -->
      <section id="faq" class="bg-gradient-to-br from-gray-800/60 to-gray-800/40 backdrop-blur-xl rounded-2xl p-6 sm:p-8 border border-gray-700/50 shadow-lg">
        <h2 class="text-2xl sm:text-3xl font-bold mb-6 flex items-center gap-3">
          <div class="p-2 bg-green-500/20 rounded-lg">
            <HelpCircleIcon class="h-6 w-6 text-green-400" />
          </div>
          <span>Frequently Asked Questions</span>
        </h2>

        <!-- Category Filter -->
        <div class="flex flex-wrap gap-2 mb-5">
          <button
            v-for="cat in faqCategories"
            :key="cat"
            @click="activeCategory = cat"
            :class="[
              'px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200',
              activeCategory === cat
                ? 'bg-green-500/20 text-green-400 border border-green-500/40'
                : 'bg-gray-700/50 text-gray-400 border border-gray-700/50 hover:border-gray-500/50 hover:text-gray-200'
            ]"
          >
            {{ cat }}
          </button>
        </div>

        <div class="space-y-3">
          <div
            v-for="(faq, index) in filteredFaqs"
            :key="index"
            class="group bg-gray-700/30 rounded-xl overflow-hidden border border-gray-700/50 hover:border-green-500/30 transition-all duration-300"
          >
            <button
              class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-700/30 transition-all duration-300"
              @click="openFaq = openFaq === index ? null : index"
            >
              <div class="flex items-center gap-3 pr-4">
                <span class="text-xs font-medium px-2 py-0.5 rounded-md" :class="categoryBadge(faq.category)">{{ faq.category }}</span>
                <span class="font-medium text-white">{{ faq.question }}</span>
              </div>
              <ChevronDownIcon
                :class="[
                  'h-5 w-5 flex-shrink-0 text-gray-400 transform transition-all duration-300',
                  openFaq === index ? 'rotate-180 text-green-400' : 'group-hover:text-green-400'
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

      <!-- Ticket System -->
      <section id="tickets" class="bg-gradient-to-br from-gray-800/60 to-gray-800/40 backdrop-blur-xl rounded-2xl p-6 sm:p-8 border border-gray-700/50 shadow-lg">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
          <h2 class="text-2xl sm:text-3xl font-bold flex items-center gap-3">
            <div class="p-2 bg-blue-500/20 rounded-lg">
              <TicketIcon class="h-6 w-6 text-blue-400" />
            </div>
            <span>Support Tickets</span>
          </h2>
          <button
            @click="showNewTicketForm = !showNewTicketForm"
            class="group relative px-5 py-2.5 bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded-xl transition-all duration-200 hover:scale-105 hover:shadow-xl hover:shadow-green-500/30 flex items-center space-x-2 font-medium"
          >
            <PlusCircleIcon class="h-5 w-5 transition-transform group-hover:rotate-90" />
            <span>New Ticket</span>
          </button>
        </div>

        <!-- New Ticket Form -->
        <Transition name="slide-fade">
          <div v-if="showNewTicketForm" class="mb-6 bg-gray-700/30 rounded-xl p-6 border border-gray-700/50">
            <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
              <FileText class="h-4 w-4 text-gray-400" />
              Open a new support request
            </h3>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Category</label>
                <select
                  v-model="newTicket.category"
                  class="w-full bg-gray-800/50 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:border-green-500/50 transition-all"
                >
                  <option value="" disabled>Select a category...</option>
                  <option v-for="cat in ticketCategories" :key="cat" :value="cat">{{ cat }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Subject</label>
                <input
                  v-model="newTicket.subject"
                  type="text"
                  class="w-full bg-gray-800/50 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:border-green-500/50 transition-all"
                  placeholder="e.g. My withdrawal is still pending after 24h"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Description</label>
                <textarea
                  v-model="newTicket.message"
                  class="w-full bg-gray-800/50 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:border-green-500/50 transition-all h-32 resize-none"
                  placeholder="Describe your issue in detail. Include your account email, any error messages, and steps to reproduce..."
                ></textarea>
              </div>
              <div class="p-3 bg-blue-500/10 border border-blue-500/20 rounded-lg text-sm text-blue-300">
                <strong class="text-blue-200">Tip:</strong> Include your account email and the crypto/amount involved for faster resolution.
              </div>
              <div class="flex justify-end gap-3">
                <button
                  @click="showNewTicketForm = false"
                  class="px-5 py-2.5 text-gray-400 hover:text-white bg-gray-700/50 hover:bg-gray-700 rounded-xl transition-all font-medium"
                >
                  Cancel
                </button>
                <button
                  @click="handleNewTicket"
                  :disabled="!newTicket.subject || !newTicket.message || !newTicket.category"
                  class="group px-6 py-2.5 bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 disabled:opacity-40 disabled:cursor-not-allowed text-white rounded-xl transition-all hover:scale-105 hover:shadow-lg hover:shadow-green-500/30 font-medium flex items-center gap-2"
                >
                  <SendIcon class="h-4 w-4 transition-transform group-hover:translate-x-0.5" />
                  Submit Ticket
                </button>
              </div>
            </div>
          </div>
        </Transition>

        <!-- Success Message -->
        <Transition name="slide-fade">
          <div v-if="ticketSuccess" class="mb-4 p-4 bg-green-500/10 border border-green-500/30 rounded-xl flex items-center gap-3 text-green-300">
            <CheckCircleIcon class="h-5 w-5 flex-shrink-0" />
            <span>Your ticket has been submitted! We'll respond within 24 hours.</span>
          </div>
        </Transition>

        <!-- Empty State -->
        <div v-if="tickets.length === 0" class="text-center py-12 text-gray-400">
          <div class="inline-block p-6 bg-gray-700/20 rounded-full mb-4">
            <MessageSquareIcon class="h-12 w-12 opacity-50" />
          </div>
          <p class="text-lg font-medium">No support tickets yet</p>
          <p class="text-sm text-gray-500 mt-2">Create your first ticket to get help from our team</p>
        </div>

        <!-- Ticket List -->
        <div v-else class="space-y-3">
          <div
            v-for="ticket in tickets"
            :key="ticket.id"
            class="group bg-gray-700/30 rounded-xl p-5 sm:p-6 border border-gray-700/50 hover:border-blue-500/40 transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/5"
          >
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1 space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                  <span class="text-xs font-medium px-2 py-0.5 rounded-md" :class="categoryBadge(ticket.category)">{{ ticket.category }}</span>
                  <h3 class="font-semibold text-white">{{ ticket.subject }}</h3>
                </div>
                <p class="text-gray-400 text-sm leading-relaxed">{{ ticket.message }}</p>
                <div class="flex flex-wrap items-center gap-4 text-sm">
                  <span class="text-gray-500 flex items-center gap-1.5">
                    <ClockIcon class="h-3.5 w-3.5" />
                    {{ ticket.date }}
                  </span>
                  <span
                    :class="[
                      'flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg font-medium text-xs',
                      ticket.status === 'open'
                        ? 'bg-green-500/20 text-green-400 border border-green-500/30'
                        : ticket.status === 'in_progress'
                          ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30'
                          : 'bg-gray-600/20 text-gray-400 border border-gray-600/30'
                    ]"
                  >
                    <component
                      :is="ticket.status === 'open' ? CheckCircleIcon : ticket.status === 'in_progress' ? ClockIcon : XCircleIcon"
                      class="h-3 w-3"
                    />
                    {{ ticket.status === 'in_progress' ? 'In Progress' : ticket.status }}
                  </span>
                  <span v-if="ticket.ticketId" class="text-gray-600 text-xs font-mono">#{{ ticket.ticketId }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Contact Options -->
      <section id="contact" class="grid sm:grid-cols-3 gap-4">
        <div
          v-for="option in contactOptions"
          :key="option.title"
          class="bg-gradient-to-br from-gray-800/60 to-gray-800/40 border border-gray-700/50 rounded-2xl p-6 hover:border-green-500/30 transition-all duration-300 hover:shadow-lg group"
        >
          <div class="p-3 rounded-xl w-fit mb-4" :class="option.iconBg">
            <component :is="option.icon" class="h-6 w-6" :class="option.iconColor" />
          </div>
          <h3 class="font-semibold text-white mb-1">{{ option.title }}</h3>
          <p class="text-gray-400 text-sm mb-3">{{ option.description }}</p>
          <span class="text-xs font-medium px-2.5 py-1 rounded-lg bg-gray-700/50 text-gray-300">{{ option.availability }}</span>
        </div>
      </section>

    </div>

    <!-- BitChest Support Bot — ChatWidget -->
    <ChatWidget
      :open="showChatWidget"
      :user-email="currentUserEmail"
      @toggle="showChatWidget = !showChatWidget"
      @close="showChatWidget = false"
    />

    <UserFooter />
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import {
  ChevronDown as ChevronDownIcon,
  Ticket as TicketIcon,
  Send as SendIcon,
  Clock as ClockIcon,
  CheckCircle as CheckCircleIcon,
  XCircle as XCircleIcon,
  PlusCircle as PlusCircleIcon,
  HelpCircle as HelpCircleIcon,
  MessageSquare as MessageSquareIcon,
  FileText,
  X,
  Mail,
  Phone,
  BookOpen,
} from 'lucide-vue-next';

import UserFooter from '@/components/UserFooter.vue';
// AJOUTE ces deux imports — ne touche à rien d'autre
import ChatWidget from '@/components/support/ChatWidget.vue';
import { useAuthStore } from '@/stores/auth';

// ─── Types ───────────────────────────────────────────────────────────────────

interface SupportTicket {
  id: string;
  ticketId: string;
  subject: string;
  message: string;
  category: string;
  status: 'open' | 'in_progress' | 'closed';
  date: string;
}

interface FAQ {
  question: string;
  answer: string;
  category: string;
}

// ─── State ───────────────────────────────────────────────────────────────────

const authStore = useAuthStore();
authStore.hydrate();

const openFaq = ref<number | null>(null);
const showNewTicketForm = ref(false);
const showChatWidget = ref(false);
const currentUserEmail = computed(() => authStore.user?.email ?? '');
const ticketSuccess = ref(false);
const activeCategory = ref('All');

const newTicket = ref({ subject: '', message: '', category: '' });

// ─── Data ─────────────────────────────────────────────────────────────────────

const quickLinks = [
  { label: 'FAQ', icon: '❓', target: 'faq' },
  { label: 'Open Ticket', icon: '🎫', target: 'tickets' },
  { label: 'Contact', icon: '📧', target: 'contact' },
  { label: 'Live Chat', icon: '💬', target: 'chat' },
];

const faqCategories = ['All', 'Account', 'Wallet', 'Trading', 'Security', 'Withdrawals'];

const ticketCategories = [
  'Account & KYC',
  'Deposits & Withdrawals',
  'Trading Issue',
  'Portfolio / Wallet',
  'Security',
  'Other',
];

const contactOptions = [
  {
    title: 'Email Support',
    description: 'Send us a detailed email and receive a reply within 24 hours.',
    availability: 'Response in < 24h',
    icon: Mail,
    iconBg: 'bg-blue-500/20',
    iconColor: 'text-blue-400',
  },
  {
    title: 'Documentation',
    description: 'Browse our knowledge base and platform guides.',
    availability: 'Always available',
    icon: BookOpen,
    iconBg: 'bg-green-500/20',
    iconColor: 'text-green-400',
  },
  {
    title: 'Priority Support',
    description: 'For urgent security or account access issues.',
    availability: '24/7 for critical issues',
    icon: Phone,
    iconBg: 'bg-orange-500/20',
    iconColor: 'text-orange-400',
  },
];

const faqs: FAQ[] = [
  // Account
  {
    category: 'Account',
    question: 'How do I create a BitChest account?',
    answer: 'Your account is created by a BitChest administrator. You will receive a temporary password by email. Log in at the login page with your email and that temporary password, then change it immediately from your profile settings.',
  },
  {
    category: 'Account',
    question: 'How do I update my personal information?',
    answer: 'Once logged in, go to your profile settings from the sidebar. You can update your name, email address and password there. Note: passwords are never visible to administrators.',
  },
  {
    category: 'Account',
    question: 'I forgot my password. What should I do?',
    answer: 'Use the "Forgot password" link on the login page, or open a support ticket. A new temporary password will be generated by an administrator and sent to your registered email.',
  },
  // Wallet
  {
    category: 'Wallet',
    question: 'What is my starting balance?',
    answer: 'During the prototyping phase, every new account is credited with €500 to allow you to explore the platform. This balance is used to buy crypto-currencies at their current market rate.',
  },
  {
    category: 'Wallet',
    question: 'How is my portfolio value calculated?',
    answer: 'For each crypto you own, BitChest calculates a weighted average purchase price. Your current profit or loss (plus-value) is the difference between the total value at the current market rate and your total purchase cost.',
  },
  {
    category: 'Wallet',
    question: 'Where can I see all my past purchases?',
    answer: 'In the "My Wallet" section, select any cryptocurrency to see the full history of purchases: date, quantity and price per unit at the time of purchase.',
  },
  // Trading
  {
    category: 'Trading',
    question: 'Which cryptocurrencies are available on BitChest?',
    answer: 'BitChest currently supports 10 cryptocurrencies: Bitcoin (BTC), Ethereum (ETH), Ripple (XRP), Bitcoin Cash (BCH), Cardano (ADA), Litecoin (LTC), NEM (XEM), Stellar (XLM), IOTA (MIOTA) and Dash (DASH). More assets may be added in future releases.',
  },
  {
    category: 'Trading',
    question: 'How do I buy a cryptocurrency?',
    answer: 'Navigate to the "Market" or "Cryptos" section, select the asset you want, choose the quantity and confirm your purchase at the current market price. Your euro balance will be debited instantly.',
  },
  {
    category: 'Trading',
    question: 'How do I sell a cryptocurrency?',
    answer: 'In your wallet, select the cryptocurrency you want to sell. Click "Sell" and confirm. You will receive the current market value in euros, credited to your balance immediately.',
  },
  // Security
  {
    category: 'Security',
    question: 'Is my account secure?',
    answer: 'BitChest uses bank-level encryption to protect your data and funds. Passwords are hashed and never stored in plain text. Administrators cannot view or modify your password.',
  },
  {
    category: 'Security',
    question: 'I think my account has been compromised. What should I do?',
    answer: 'Change your password immediately from your profile settings. If you cannot log in, open a priority support ticket or contact us directly. We will secure your account and investigate.',
  },
  // Withdrawals
  {
    category: 'Withdrawals',
    question: 'How long do withdrawals take?',
    answer: 'During the prototyping phase, withdrawals are simulated and processed instantly. On the production platform, processing times will depend on the network and banking partners.',
  },
  {
    category: 'Withdrawals',
    question: 'Why is my withdrawal pending?',
    answer: 'A pending withdrawal may be waiting for a security review. If your withdrawal has been pending for more than 2 hours, please open a support ticket with your account email and the amount/crypto involved.',
  },
];

const tickets = ref<SupportTicket[]>([
  {
    id: '1',
    ticketId: 'BCH-00423',
    subject: 'KYC Verification Pending',
    message: 'My account verification has been pending for 3 days. I have uploaded all required documents.',
    category: 'Account & KYC',
    status: 'in_progress',
    date: '2024-03-12',
  },
  {
    id: '2',
    ticketId: 'BCH-00401',
    subject: 'BTC withdrawal not received',
    message: 'I sold 0.5 BTC 48h ago and my euro balance has not been updated.',
    category: 'Deposits & Withdrawals',
    status: 'closed',
    date: '2024-03-08',
  },
]);

// ─── Computed ─────────────────────────────────────────────────────────────────

const filteredFaqs = computed(() => {
  if (activeCategory.value === 'All') return faqs;
  return faqs.filter((f) => f.category === activeCategory.value);
});

// ─── Methods ──────────────────────────────────────────────────────────────────

function categoryBadge(cat: string) {
  const map: Record<string, string> = {
    'Account': 'bg-purple-500/20 text-purple-300',
    'Account & KYC': 'bg-purple-500/20 text-purple-300',
    'Wallet': 'bg-blue-500/20 text-blue-300',
    'Portfolio / Wallet': 'bg-blue-500/20 text-blue-300',
    'Trading': 'bg-green-500/20 text-green-300',
    'Trading Issue': 'bg-green-500/20 text-green-300',
    'Security': 'bg-red-500/20 text-red-300',
    'Withdrawals': 'bg-orange-500/20 text-orange-300',
    'Deposits & Withdrawals': 'bg-orange-500/20 text-orange-300',
    'Other': 'bg-gray-500/20 text-gray-300',
  };
  return map[cat] ?? 'bg-gray-500/20 text-gray-300';
}

function scrollTo(target: string) {
  if (target === 'chat') {
    showChatWidget.value = true;
    return;
  }
  const el = document.getElementById(target);
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function handleNewTicket() {
  if (!newTicket.value.subject || !newTicket.value.message || !newTicket.value.category) return;

  const id = String(Date.now());
  tickets.value.unshift({
    id,
    ticketId: `BCH-${String(Math.floor(10000 + Math.random() * 90000))}`,
    subject: newTicket.value.subject,
    message: newTicket.value.message,
    category: newTicket.value.category,
    status: 'open',
    date: new Date().toLocaleDateString('en-GB'),
  });

  newTicket.value = { subject: '', message: '', category: '' };
  showNewTicketForm.value = false;
  ticketSuccess.value = true;
  setTimeout(() => (ticketSuccess.value = false), 4000);
}

</script>

<style scoped>
.slide-fade-enter-active { transition: all 0.3s ease-out; }
.slide-fade-leave-active { transition: all 0.2s ease-in; }
.slide-fade-enter-from,
.slide-fade-leave-to { transform: translateY(-8px); opacity: 0; }
</style>