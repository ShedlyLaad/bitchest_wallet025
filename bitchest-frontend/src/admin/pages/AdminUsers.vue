<template>
  <div class="space-y-4 sm:space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent">Users Management</h1>
        <p class="text-gray-400 mt-1 text-sm sm:text-base">Manage platform users and their activities</p>
      </div>

      <button
        @click="openCreateModal"
        class="flex items-center space-x-2 px-4 py-2 rounded-lg text-white transition-all hover:scale-105 shadow-lg shadow-blue-500/20 font-medium"
        :style="{ backgroundColor: 'var(--blue)' }"
      >
        <Plus class="h-4 w-4" />
        <span>Create User</span>
      </button>
    </div>

    <!-- Success Message -->
    <div v-if="createdTempPassword" class="bg-gray-800 rounded-xl p-6 border border-gray-700">
      <div class="flex items-start space-x-3">
        <Check class="h-5 w-5 mt-0.5" :style="{ color: 'var(--accent-green)' }" />
        <div class="flex-1">
          <h3 class="font-semibold text-white mb-2">User Created Successfully</h3>
          <p class="text-gray-300 text-sm mb-4">
            A temporary password has been generated. Share it with the user. They can change it in their private area.
          </p>
          <p class="text-gray-300 text-sm mb-4">
            For the prototype phase, the new account is credited with €500.
          </p>

          <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
            <div class="flex items-center justify-between">
              <code class="text-gray-300 font-mono text-sm">{{ maskedPassword }}</code>
              <button @click="handleCopyPassword" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-white text-sm" :style="{ backgroundColor: 'var(--blue-dark)' }">
                <template v-if="copiedPassword">
                  <Check class="h-4 w-4" />
                  <span>Copied!</span>
                </template>
                <template v-else>
                  <Copy class="h-4 w-4" />
                  <span>Copy</span>
                </template>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-else-if="successMessage" class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-sm text-green-400">
      {{ successMessage }}
    </div>

    <!-- Users Table -->
    <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl border border-gray-700 shadow-lg overflow-hidden">
      <div class="p-4 border-b border-gray-700 bg-gray-800/50">
        <h3 class="text-lg font-semibold text-white">Users List</h3>
        <p class="text-xs text-gray-400 mt-1">{{ users.length }} total users</p>
      </div>
      <div v-if="errorMessage" class="px-4 py-3 text-sm text-red-400 border-b border-gray-700 bg-red-900/10">{{ errorMessage }}</div>
      <div v-else-if="loading" class="px-4 py-3 text-sm text-gray-400 border-b border-gray-700">Loading users...</div>
      <div v-else-if="successMessage" class="px-4 py-3 text-sm text-green-400 border-b border-gray-700 bg-green-900/10">Nouvelle création : email envoyé.</div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-800/70 sticky top-0">
            <tr class="border-b border-gray-700">
              <th class="text-left p-4 text-xs text-gray-400 font-medium uppercase tracking-wider">Email</th>
              <th class="text-left p-4 text-xs text-gray-400 font-medium uppercase tracking-wider">Role</th>
              <th class="text-left p-4 text-xs text-gray-400 font-medium uppercase tracking-wider">Status</th>
              <th class="text-left p-4 text-xs text-gray-400 font-medium uppercase tracking-wider">Created At</th>
              <th class="text-right p-4 text-xs text-gray-400 font-medium uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="user in users" 
              :key="user.id" 
              @click="openUserDetails(user.id)"
              class="border-b border-gray-700/50 hover:bg-gray-700/30 transition-all duration-200 cursor-pointer group"
            >
              <td class="p-4">
                <div class="flex items-center gap-3">
                  <div class="relative w-10 h-10 flex-shrink-0">
                    <div class="w-10 h-10 rounded-lg overflow-hidden border-2 border-blue-500/30 bg-gradient-to-br from-blue-500/20 to-blue-600/20 flex items-center justify-center group-hover:border-blue-400/50 transition-colors">
                      <template v-if="getProfilePictureUrl(user.profile_picture)">
                        <img
                          :src="getProfilePictureUrl(user.profile_picture)!"
                          :alt="user.name || user.email"
                          class="w-full h-full object-cover"
                          @error="(e: any) => { e.target.style.display = 'none'; }"
                        />
                      </template>
                      <span v-else class="text-blue-400 font-bold text-sm">
                        {{ user.email.charAt(0).toUpperCase() }}
                      </span>
                    </div>
                    <!-- Status indicator -->
                    <div 
                      v-if="user.status === 'active'" 
                      class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-500 rounded-full border-2 border-gray-900 animate-pulse"
                      title="Active user"
                    ></div>
                  </div>
                  <div>
                    <div class="text-white font-medium group-hover:text-blue-300 transition-colors">{{ user.email }}</div>
                    <div v-if="user.name" class="text-gray-400 text-xs">{{ user.name }}</div>
                  </div>
                </div>
              </td>
              <td class="p-4">
                <span class="px-3 py-1.5 rounded-lg text-xs font-medium inline-flex items-center gap-1.5" :style="roleStyle(user.role)">
                  <span v-if="user.role === 'admin'" class="w-1.5 h-1.5 rounded-full bg-current"></span>
                  {{ user.role }}
                </span>
              </td>
              <td class="p-4">
                <span class="px-3 py-1.5 rounded-lg text-xs font-medium inline-flex items-center gap-1.5" :style="statusStyle(user.status)">
                  <span class="w-1.5 h-1.5 rounded-full bg-current animate-pulse"></span>
                  {{ user.status }}
                </span>
              </td>
              <td class="p-4 text-gray-400 text-sm">{{ formatDate(user.created_at) }}</td>
            <td class="p-4">
              <div class="flex items-center justify-end space-x-2" @click.stop>
                <button
                  v-if="user.status === 'pending_validation'"
                  class="px-3 py-2 rounded-lg text-sm transition-all hover:scale-105"
                  :style="{ backgroundColor: 'var(--accent-green)', color: 'var(--bg)' }"
                  @click="handleApproveUser(user.id)"
                  title="Validate account"
                >
                  Approve
                </button>
                <button
                  v-if="user.status !== 'blocked'"
                  class="px-3 py-2 rounded-lg text-sm transition-all hover:scale-105"
                  :style="{ backgroundColor: 'var(--accent-red)', color: 'var(--bg)' }"
                  @click="handleBlockUser(user.id)"
                  title="Block account"
                >
                  Block
                </button>
                <button 
                  @click.stop="handleDeleteUser(user.id)" 
                  class="p-2 rounded-lg transition-all hover:scale-105" 
                  :style="{ backgroundColor: 'var(--accent-red)', color: 'var(--bg)' }" 
                  title="Delete"
                >
                  <Trash2 class="h-4 w-4" />
                </button>
              </div>
            </td>
          </tr>
            </tbody>
          </table>
        </div>
      </div>

    <!-- User Details Modal - Inspiré de Transaction Details -->
    <Transition name="modal">
      <div
        v-if="isUserDetailsModalOpen && selectedUserDetails"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="closeUserDetailsModal"
      >
        <!-- Backdrop with blur -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-md"></div>
        
        <!-- Modal Content -->
        <div class="relative z-10 bg-white/5 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
          <!-- Header -->
          <div class="px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="relative w-12 h-12 flex-shrink-0">
                <div class="w-12 h-12 rounded-xl overflow-hidden border-2 border-blue-500/30 bg-gradient-to-br from-blue-500/20 to-blue-600/20 flex items-center justify-center">
                  <img
                    v-if="getProfilePictureUrl(selectedUserDetails.user.profile_picture)"
                    :src="getProfilePictureUrl(selectedUserDetails.user.profile_picture)!"
                    :alt="selectedUserDetails.user.name || selectedUserDetails.user.email"
                    class="w-full h-full object-cover"
                    @error="(e: any) => { e.target.style.display = 'none'; }"
                  />
                  <span v-else class="text-blue-400 font-bold text-xl">
                    {{ selectedUserDetails.user.email?.charAt(0).toUpperCase() || 'U' }}
                  </span>
                </div>
                <div 
                  v-if="selectedUserDetails.user.status === 'active'" 
                  class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-gray-900"
                  title="Active user"
                ></div>
              </div>
              <div>
                <h2 class="text-xl font-semibold text-white">User Details</h2>
                <p class="text-xs text-gray-400 mt-0.5">{{ selectedUserDetails.user.email }}</p>
              </div>
            </div>
            <button
              @click="closeUserDetailsModal"
              class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-white hover:bg-white/10 rounded-lg transition-colors"
            >
              <X class="h-4 w-4" />
            </button>
          </div>
          
          <!-- Content -->
          <div class="overflow-y-auto flex-1">
            <div class="p-6 space-y-5">
              <!-- User Info Section - Editable -->
              <div class="space-y-3">
                <div class="text-xs font-medium text-gray-400 uppercase tracking-wider">User Information</div>
                <div class="grid grid-cols-2 gap-3">
                  <div class="p-4 bg-white/5 rounded-xl border border-white/5">
                    <div class="text-xs text-gray-400 mb-1">First Name</div>
                    <div v-if="!isEditingUser" class="text-sm font-medium text-white">
                      {{ selectedUserDetails.user.first_name || 'N/A' }}
                    </div>
                    <input
                      v-else
                      v-model="editUserForm.first_name"
                      type="text"
                      class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  <div class="p-4 bg-white/5 rounded-xl border border-white/5">
                    <div class="text-xs text-gray-400 mb-1">Last Name</div>
                    <div v-if="!isEditingUser" class="text-sm font-medium text-white">
                      {{ selectedUserDetails.user.last_name || 'N/A' }}
                    </div>
                    <input
                      v-else
                      v-model="editUserForm.last_name"
                      type="text"
                      class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  <div class="p-4 bg-white/5 rounded-xl border border-white/5 col-span-2">
                    <div class="text-xs text-gray-400 mb-1">Email</div>
                    <div class="text-sm font-medium text-white break-all">
                      {{ selectedUserDetails.user.email }}
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Email cannot be modified</p>
                  </div>
                </div>
                <div class="flex justify-end gap-2">
                  <button
                    v-if="!isEditingUser"
                    @click="startEditingUser"
                    class="px-4 py-2 bg-blue-500/20 hover:bg-blue-500/30 text-blue-300 rounded-lg text-sm font-medium transition-colors border border-blue-500/30"
                  >
                    <Edit class="h-4 w-4 inline mr-2" />
                    Edit
                  </button>
                  <template v-else>
                    <button
                      @click="cancelEditingUser"
                      class="px-4 py-2 bg-gray-700/50 hover:bg-gray-700 text-white rounded-lg text-sm font-medium transition-colors"
                    >
                      Cancel
                    </button>
                    <button
                      @click="saveUserChanges"
                      :disabled="savingUser"
                      class="px-4 py-2 bg-green-500/20 hover:bg-green-500/30 text-green-300 rounded-lg text-sm font-medium transition-colors border border-green-500/30 disabled:opacity-50"
                    >
                      {{ savingUser ? 'Saving...' : 'Save' }}
                    </button>
                  </template>
                </div>
              </div>

              <!-- Stats Cards -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-blue-500/10 rounded-xl border border-blue-500/20">
                  <div class="flex items-center justify-between mb-2">
                    <Euro class="h-5 w-5 text-blue-400" />
                    <span class="text-xs text-gray-400">Balance</span>
                  </div>
                  <div class="text-2xl font-bold text-white">{{ formatEUR(selectedUserDetails.balance) }}</div>
                </div>
                <div class="p-4 bg-green-500/10 rounded-xl border border-green-500/20">
                  <div class="flex items-center justify-between mb-2">
                    <Wallet class="h-5 w-5 text-green-400" />
                    <span class="text-xs text-gray-400">Portfolio</span>
                  </div>
                  <div class="text-2xl font-bold text-white">{{ formatEUR(selectedUserDetails.statistics.total_portfolio_value) }}</div>
                </div>
                <div :class="[
                  'p-4 rounded-xl border',
                  selectedUserDetails.statistics.total_gain_loss >= 0
                    ? 'bg-green-500/10 border-green-500/20'
                    : 'bg-red-500/10 border-red-500/20'
                ]">
                  <div class="flex items-center justify-between mb-2">
                    <component :is="selectedUserDetails.statistics.total_gain_loss >= 0 ? TrendingUp : TrendingDown" 
                      :class="['h-5 w-5', selectedUserDetails.statistics.total_gain_loss >= 0 ? 'text-green-400' : 'text-red-400']" />
                    <span class="text-xs text-gray-400">Gain/Loss</span>
                  </div>
                  <div :class="['text-2xl font-bold', selectedUserDetails.statistics.total_gain_loss >= 0 ? 'text-green-400' : 'text-red-400']">
                    {{ selectedUserDetails.statistics.total_gain_loss >= 0 ? '+' : '' }}{{ formatEUR(selectedUserDetails.statistics.total_gain_loss) }}
                  </div>
                </div>
              </div>

              <!-- Statistics Grid -->
              <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="p-3 bg-white/5 rounded-lg border border-white/5">
                  <div class="text-xs text-gray-400 mb-1">Transactions</div>
                  <div class="text-lg font-bold text-white">{{ selectedUserDetails.statistics.total_transactions }}</div>
                </div>
                <div class="p-3 bg-white/5 rounded-lg border border-white/5">
                  <div class="text-xs text-gray-400 mb-1">Buy Orders</div>
                  <div class="text-lg font-bold text-green-400">{{ selectedUserDetails.statistics.buy_transactions }}</div>
                </div>
                <div class="p-3 bg-white/5 rounded-lg border border-white/5">
                  <div class="text-xs text-gray-400 mb-1">Sell Orders</div>
                  <div class="text-lg font-bold text-red-400">{{ selectedUserDetails.statistics.sell_transactions }}</div>
                </div>
                <div class="p-3 bg-white/5 rounded-lg border border-white/5">
                  <div class="text-xs text-gray-400 mb-1">Volume</div>
                  <div class="text-lg font-bold text-white">{{ formatEUR(selectedUserDetails.statistics.total_volume) }}</div>
                </div>
              </div>

              <!-- Portfolio -->
              <div v-if="selectedUserDetails.portfolio && selectedUserDetails.portfolio.length > 0">
                <div class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-3">Portfolio Holdings</div>
                <div class="bg-white/5 rounded-xl border border-white/5 overflow-hidden">
                  <div class="overflow-x-auto">
                    <table class="w-full">
                      <thead class="bg-white/5 border-b border-white/10">
                        <tr>
                          <th class="text-left p-3 text-xs text-gray-400 font-medium uppercase">Crypto</th>
                          <th class="text-right p-3 text-xs text-gray-400 font-medium uppercase">Quantity</th>
                          <th class="text-right p-3 text-xs text-gray-400 font-medium uppercase">Price</th>
                          <th class="text-right p-3 text-xs text-gray-400 font-medium uppercase">Value</th>
                          <th class="text-right p-3 text-xs text-gray-400 font-medium uppercase">Gain/Loss</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(item, idx) in selectedUserDetails.portfolio" :key="idx" class="border-b border-white/5 hover:bg-white/5">
                          <td class="p-3">
                            <div class="flex items-center gap-2">
                              <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center border border-white/10 overflow-hidden">
                                <img
                                  v-if="getCryptoIcon(item.crypto?.symbol || '')"
                                  :src="getCryptoIcon(item.crypto?.symbol || '')"
                                  :alt="item.crypto?.symbol || 'N/A'"
                                  class="w-full h-full object-contain p-1"
                                  @error="(e: any) => { e.target.style.display = 'none'; }"
                                />
                                <span v-else class="text-white font-bold text-xs">{{ item.crypto?.symbol || 'N/A' }}</span>
                              </div>
                              <div>
                                <div class="text-white font-medium text-sm">{{ item.crypto?.symbol || 'N/A' }}</div>
                                <div class="text-gray-400 text-xs">{{ item.crypto?.name || 'Unknown' }}</div>
                              </div>
                            </div>
                          </td>
                          <td class="p-3 text-right text-white font-medium text-sm">{{ parseFloat(item.quantity || 0).toFixed(8) }}</td>
                          <td class="p-3 text-right text-gray-300 text-sm">{{ formatEUR(item.current_price || 0) }}</td>
                          <td class="p-3 text-right text-white font-medium text-sm">{{ formatEUR(item.current_value || 0) }}</td>
                          <td class="p-3 text-right">
                            <div :class="['font-medium text-sm', (item.gain_loss || 0) >= 0 ? 'text-green-400' : 'text-red-400']">
                              {{ (item.gain_loss || 0) >= 0 ? '+' : '' }}{{ formatEUR(item.gain_loss || 0) }}
                            </div>
                            <div :class="['text-xs', (item.gain_loss_percent || 0) >= 0 ? 'text-green-400/70' : 'text-red-400/70']">
                              {{ (item.gain_loss_percent || 0) >= 0 ? '+' : '' }}{{ (item.gain_loss_percent || 0).toFixed(2) }}%
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Recent Transactions -->
              <div v-if="selectedUserDetails.recent_transactions && selectedUserDetails.recent_transactions.length > 0">
                <div class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-3">Recent Transactions</div>
                <div class="bg-white/5 rounded-xl border border-white/5 overflow-hidden">
                  <div class="overflow-x-auto">
                    <table class="w-full">
                      <thead class="bg-white/5 border-b border-white/10">
                        <tr>
                          <th class="text-left p-3 text-xs text-gray-400 font-medium uppercase">Type</th>
                          <th class="text-left p-3 text-xs text-gray-400 font-medium uppercase">Crypto</th>
                          <th class="text-right p-3 text-xs text-gray-400 font-medium uppercase">Quantity</th>
                          <th class="text-right p-3 text-xs text-gray-400 font-medium uppercase">Price</th>
                          <th class="text-right p-3 text-xs text-gray-400 font-medium uppercase">Total</th>
                          <th class="text-right p-3 text-xs text-gray-400 font-medium uppercase">Date</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(tx, idx) in selectedUserDetails.recent_transactions" :key="idx" class="border-b border-white/5 hover:bg-white/5">
                          <td class="p-3">
                            <span :class="[
                              'px-2 py-1 rounded text-xs font-medium',
                              tx.type === 'buy' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'
                            ]">
                              {{ tx.type === 'buy' ? 'Buy' : 'Sell' }}
                            </span>
                          </td>
                          <td class="p-3">
                            <div class="flex items-center gap-2">
                              <div class="w-6 h-6 rounded bg-white/5 flex items-center justify-center border border-white/10 overflow-hidden">
                                <img
                                  v-if="getCryptoIcon(tx.portfolio?.crypto?.symbol || '')"
                                  :src="getCryptoIcon(tx.portfolio?.crypto?.symbol || '')"
                                  :alt="tx.portfolio?.crypto?.symbol || 'N/A'"
                                  class="w-full h-full object-contain p-0.5"
                                  @error="(e: any) => { e.target.style.display = 'none'; }"
                                />
                                <span v-else class="text-white font-bold text-[10px]">{{ tx.portfolio?.crypto?.symbol?.charAt(0) || '?' }}</span>
                              </div>
                              <span class="text-white font-medium text-sm">{{ tx.portfolio?.crypto?.symbol || 'N/A' }}</span>
                            </div>
                          </td>
                          <td class="p-3 text-right text-gray-300 text-sm">{{ parseFloat(tx.quantity || 0).toFixed(8) }}</td>
                          <td class="p-3 text-right text-gray-300 text-sm">{{ formatEUR(tx.price_at_transaction || 0) }}</td>
                          <td class="p-3 text-right text-white font-medium text-sm">{{ formatEUR(tx.euro_amount || 0) }}</td>
                          <td class="p-3 text-right text-gray-400 text-xs">{{ formatDate(tx.created_at) }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="px-6 py-4 border-t border-white/10 flex justify-end">
            <button
              @click="closeUserDetailsModal"
              class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg text-sm font-medium transition-colors"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Create User Modal -->
    <div v-if="isCreateModalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-gray-800 rounded-xl border border-gray-700 max-w-md w-full p-6">
        <h2 class="text-xl font-bold text-white mb-4">Create New User</h2>

        <form @submit.prevent="handleCreateUser" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">Email <span style="color:var(--accent-red)">*</span></label>
            <input v-model="createFormData.email" type="email" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">First Name <span style="color:var(--accent-red)">*</span></label>
            <input v-model="createFormData.firstName" type="text" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">Last Name <span style="color:var(--accent-red)">*</span></label>
            <input v-model="createFormData.lastName" type="text" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">Role <span style="color:var(--accent-red)">*</span></label>
            <select v-model="createFormData.role" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2">
              <option value="client">Client</option>
              <option value="admin">Admin</option>
            </select>
          </div>

          <div class="flex items-center justify-end space-x-3 pt-4">
            <button type="button" @click="closeCreateModal" class="px-4 py-2 rounded-lg text-gray-300 hover:bg-gray-700">Cancel</button>
            <button type="submit" :disabled="createLoading" class="px-4 py-2 rounded-lg text-white disabled:opacity-60 disabled:cursor-not-allowed" :style="{ backgroundColor: 'var(--accent-green)' }">
              {{ createLoading ? 'Creating...' : 'Create User' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Plus, Trash2, Copy, Check, X, Wallet, TrendingUp, TrendingDown, Euro, Edit } from 'lucide-vue-next';
import { approveUser, blockUser, createAdminUser, deleteUser as deleteUserApi, getAdminUsers, getAdminUserDetails, updateAdminUser } from '@/services/api';
import { useAuthStore } from '@/stores/auth';
import { formatEUR } from '@/utils/format';
import { getCryptoIcon } from '@/utils/cryptoIcons';
import type { AuthUser } from '@/types';

interface CreateUserData {
  email: string;
  firstName: string;
  lastName: string;
  role: 'client' | 'admin';
}

const users = ref<AuthUser[]>([]);
const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const isCreateModalOpen = ref(false);
const createFormData = ref<CreateUserData>({ email: '', firstName: '', lastName: '', role: 'client' });
const createdTempPassword = ref<string | null>(null);
const copiedPassword = ref(false);
const createLoading = ref(false);

// User details modal
const isUserDetailsModalOpen = ref(false);
const selectedUserDetails = ref<any>(null);
const userDetailsLoading = ref(false);
const userDetailsError = ref('');
const isEditingUser = ref(false);
const editUserForm = ref({ first_name: '', last_name: '' });
const savingUser = ref(false);

onMounted(async () => {
  const auth = useAuthStore();
  auth.hydrate?.();
  
  if (!auth.token) {
    errorMessage.value = 'Not authenticated. Please login.';
    return;
  }
  
  // Ensure user is fetched
  if (!auth.user && auth.token) {
    try {
      await auth.fetchCurrentUser();
    } catch (e) {
      console.error('Failed to fetch user:', e);
      errorMessage.value = 'Failed to authenticate. Please login again.';
      return;
    }
  }
  
  await fetchUsers();
});

async function fetchUsers() {
  loading.value = true;
  errorMessage.value = '';
  try {
    users.value = await getAdminUsers();
  } catch (e: any) {
    errorMessage.value = e?.response?.data?.message || 'Impossible de charger les utilisateurs';
  } finally {
    loading.value = false;
  }
}

async function handleCreateUser() {
  if (!createFormData.value.email || !createFormData.value.firstName || !createFormData.value.lastName) return;

  createLoading.value = true;
  errorMessage.value = '';
  try {
    const payload = {
      name: `${createFormData.value.firstName} ${createFormData.value.lastName}`.trim(),
      email: createFormData.value.email
    };
    const { user, temporary_password } = await createAdminUser(payload);
    users.value = [...users.value, user];
    createdTempPassword.value = temporary_password;
    successMessage.value = 'Utilisateur créé. Un email avec mot de passe temporaire a été envoyé.';
    createFormData.value = { email: '', firstName: '', lastName: '', role: 'client' };
    isCreateModalOpen.value = false;
  } catch (e: any) {
    errorMessage.value = e?.response?.data?.message || 'Création impossible';
  } finally {
    createLoading.value = false;
  }
}

function handleCopyPassword() {
  if (createdTempPassword.value) {
    navigator.clipboard.writeText(createdTempPassword.value);
    copiedPassword.value = true;
    setTimeout(() => (copiedPassword.value = false), 2000);
  }
}

async function handleDeleteUser(id: number) {
  if (!confirm('Are you sure you want to delete this user?')) return;
  await deleteUserApi(id);
  users.value = users.value.filter((u) => u.id !== id);
}

async function handleApproveUser(id: number) {
  errorMessage.value = '';
  try {
    const { user, message } = await approveUser(id);
    users.value = users.value.map((u) => (u.id === id ? user : u));
    successMessage.value = message || 'Utilisateur validé';
  } catch (e: any) {
    errorMessage.value = e?.response?.data?.message || 'Validation impossible';
  }
}

async function handleBlockUser(id: number) {
  errorMessage.value = '';
  try {
    const { user, message } = await blockUser(id);
    users.value = users.value.map((u) => (u.id === id ? user : u));
    successMessage.value = message || 'Utilisateur bloqué';
  } catch (e: any) {
    errorMessage.value = e?.response?.data?.message || 'Blocage impossible';
  }
}

function openCreateModal() {
  isCreateModalOpen.value = true;
  createdTempPassword.value = null;
  copiedPassword.value = false;
}

function closeCreateModal() {
  isCreateModalOpen.value = false;
  createdTempPassword.value = null;
}

const maskedPassword = computed(() => {
  if (!createdTempPassword.value) return '';
  const pw = createdTempPassword.value;
  return pw.split('').map((char, i) => (i < 2 || i >= pw.length - 2 ? char : '•')).join('');
});

function roleStyle(role: 'client' | 'admin') {
  if (role === 'admin') {
    return { backgroundColor: 'var(--blue-opacity-20)', color: 'var(--blue)' };
  }
  return { backgroundColor: 'rgb(55,65,81)', color: 'rgb(209,213,219)' };
}

function statusStyle(status: AuthUser['status']) {
  const base = { color: 'rgb(17,24,39)' };
  const map: Record<AuthUser['status'], Record<string, string>> = {
    pending: { backgroundColor: 'rgb(251,191,36)', color: 'rgb(17,24,39)' },
    pending_validation: { backgroundColor: 'rgb(251,146,60)', color: 'rgb(17,24,39)' },
    active: { backgroundColor: 'rgb(52,211,153)', color: 'rgb(17,24,39)' },
    blocked: { backgroundColor: 'rgb(239,68,68)', color: 'rgb(17,24,39)' }
  };
  return map[status] || base;
}

function formatDate(value?: string) {
  if (!value) return '';
  return value.split('T')[0] ?? value;
}

function isValidImagePath(path: string | null | undefined): boolean {
  if (!path) return false;
  const value = String(path);
  const trimmed = value.trim();
  if (trimmed === '' || trimmed === 'null' || trimmed === 'undefined' || trimmed === 'NULL' || trimmed === 'UNDEFINED') return false;
  return true;
}

function getProfilePictureUrl(profilePicture: string | null | undefined): string | null {
  if (!isValidImagePath(profilePicture)) return null;
  const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000';
  const path = (profilePicture as string).replace(/\\/g, '/');
  return `${baseUrl}/storage/${path}`;
}

async function openUserDetails(userId: number) {
  isUserDetailsModalOpen.value = true;
  userDetailsLoading.value = true;
  userDetailsError.value = '';
  isEditingUser.value = false;
  try {
    selectedUserDetails.value = await getAdminUserDetails(userId);
    editUserForm.value = {
      first_name: selectedUserDetails.value.user.first_name || '',
      last_name: selectedUserDetails.value.user.last_name || '',
    };
  } catch (e: any) {
    userDetailsError.value = e?.response?.data?.message || 'Impossible de charger les détails';
  } finally {
    userDetailsLoading.value = false;
  }
}

function closeUserDetailsModal() {
  isUserDetailsModalOpen.value = false;
  selectedUserDetails.value = null;
  userDetailsError.value = '';
  isEditingUser.value = false;
}

function startEditingUser() {
  if (!selectedUserDetails.value) return;
  editUserForm.value = {
    first_name: selectedUserDetails.value.user.first_name || '',
    last_name: selectedUserDetails.value.user.last_name || '',
  };
  isEditingUser.value = true;
}

function cancelEditingUser() {
  isEditingUser.value = false;
  if (selectedUserDetails.value) {
    editUserForm.value = {
      first_name: selectedUserDetails.value.user.first_name || '',
      last_name: selectedUserDetails.value.user.last_name || '',
    };
  }
}

async function saveUserChanges() {
  if (!selectedUserDetails.value) return;
  
  savingUser.value = true;
  try {
    const { user } = await updateAdminUser(selectedUserDetails.value.user.id, {
      first_name: editUserForm.value.first_name,
      last_name: editUserForm.value.last_name,
    });
    
    // Update local state
    selectedUserDetails.value.user = user;
    
    // Update users list
    const userIndex = users.value.findIndex(u => u.id === user.id);
    if (userIndex !== -1) {
      users.value[userIndex] = user;
    }
    
    isEditingUser.value = false;
    successMessage.value = 'User updated successfully';
    setTimeout(() => { successMessage.value = ''; }, 3000);
  } catch (e: any) {
    userDetailsError.value = e?.response?.data?.message || 'Failed to update user';
  } finally {
    savingUser.value = false;
  }
}
</script>

<style scoped>
/* Modal Transitions */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}

.modal-enter-active .relative.z-10,
.modal-leave-active .relative.z-10 {
  transition: transform 0.2s ease, opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .relative.z-10,
.modal-leave-to .relative.z-10 {
  transform: scale(0.95);
  opacity: 0;
}

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: rgba(31, 41, 55, 0.5);
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: rgba(107, 114, 128, 0.5);
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: rgba(107, 114, 128, 0.7);
}
</style>
