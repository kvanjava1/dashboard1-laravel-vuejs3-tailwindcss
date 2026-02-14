<template>
    <AdminLayout>
        <!-- Header Section -->
        <PageHeader>
            <template #title>
                <PageHeaderTitle title="Ban User" />
            </template>
            <template #actions>
                <PageHeaderActions>
                    <ActionButton icon="arrow_back" @click="goBack">
                        Back to Users
                    </ActionButton>
                </PageHeaderActions>
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 gap-6">
            <!-- Box 1: User Information -->
            <div>
                <ContentBox>
                    <ContentBoxHeader>
                        <template #title>
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-xl">person</span>
                                <ContentBoxTitle title="User Information" />
                            </div>
                        </template>
                    </ContentBoxHeader>
                    <ContentBoxBody>
                        <div class="space-y-6">
                            <!-- User Avatar and Name -->
                            <div class="flex items-center gap-4 p-4 bg-gradient-to-r from-slate-50 to-slate-100 rounded-lg border border-slate-200">
                                <img
                                    :src="user.profile_image || '/images/default-avatar.png'"
                                    :alt="user.name"
                                    class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-md"
                                />
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-slate-800">{{ user.name }}</h3>
                                    <p class="text-slate-600">
                                        {{ user.email }}
                                    </p>
                                    <div class="flex items-center gap-4 mt-2">
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ user.role_display_name || user.role }}
                                        </span>
                                        <StatusBadge :status="user.status" />
                                    </div>
                                </div>
                            </div>

                            <!-- User Details Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="material-symbols-outlined text-slate-500 text-sm">calendar_today</span>
                                        <span class="text-sm font-medium text-slate-700">Joined Date</span>
                                    </div>
                                    <p class="text-slate-800 font-semibold">{{ user.joined_date }}</p>
                                </div>

                                <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="material-symbols-outlined text-slate-500 text-sm">block</span>
                                        <span class="text-sm font-medium text-slate-700">Ban Status</span>
                                    </div>
                                    <p
                                        :class="[
                                            'font-semibold mb-2',
                                            user.is_banned ? 'text-red-600' : 'text-green-600'
                                        ]"
                                    >
                                        {{ user.is_banned ? 'Currently Banned' : 'Not Banned' }}
                                    </p>
                                    <div v-if="currentBanReason" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-md">
                                        <div class="flex items-start gap-2">
                                            <span class="material-symbols-outlined text-red-500 text-sm mt-0.5">info</span>
                                            <div>
                                                <p class="text-sm font-medium text-red-800 mb-1">Ban Reason:</p>
                                                <p class="text-sm text-red-700">{{ currentBanReason.reason }}</p>
                                                <p v-if="currentBanReason.is_forever" class="text-xs text-red-600 mt-1">
                                                    Permanent ban
                                                </p>
                                                <p v-else-if="currentBanReason.banned_until" class="text-xs text-red-600 mt-1">
                                                    Until: {{ formatDate(currentBanReason.banned_until) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </ContentBoxBody>
                </ContentBox>
            </div>

            <!-- Box 2: Ban History -->
            <div>
                <ContentBox>
                    <ContentBoxHeader>
                        <template #title>
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-xl">history</span>
                                <ContentBoxTitle title="Ban History" />
                            </div>
                        </template>
                    </ContentBoxHeader>
                    <ContentBoxBody>
                        <div v-if="banHistory.length === 0" class="text-center py-12">
                            <div class="bg-slate-50 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                                <span class="material-symbols-outlined text-slate-400 text-3xl">history</span>
                            </div>
                            <h3 class="text-lg font-medium text-slate-600 mb-2">No Ban History</h3>
                            <p class="text-sm text-slate-500">This user has never been banned or unbanned.</p>
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-200">
                                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Action</th>
                                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Reason</th>
                                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Duration</th>
                                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Performed By</th>
                                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="history in banHistory"
                                        :key="history.id"
                                        :class="[
                                            'border-b border-slate-100 hover:bg-slate-50 transition-colors',
                                            history.action === 'ban' ? 'bg-red-50/30' : 'bg-green-50/30'
                                        ]"
                                    >
                                        <td class="py-4 px-4">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    :class="[
                                                        'p-2 rounded-full',
                                                        history.action === 'ban'
                                                            ? 'bg-red-100 text-red-600'
                                                            : 'bg-green-100 text-green-600'
                                                    ]"
                                                >
                                                    <span class="material-symbols-outlined text-sm">
                                                        {{ history.action === 'ban' ? 'block' : 'check_circle' }}
                                                    </span>
                                                </div>
                                                <span
                                                    :class="[
                                                        'font-medium capitalize',
                                                        history.action === 'ban' ? 'text-red-700' : 'text-green-700'
                                                    ]"
                                                >
                                                    {{ history.action }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <p class="text-slate-700 text-sm">{{ history.reason }}</p>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div v-if="history.action === 'ban'" class="text-sm">
                                                <span v-if="history.is_forever" class="text-red-600 font-medium">Permanent</span>
                                                <span v-else-if="history.banned_until" class="text-slate-600">
                                                    Until {{ formatDate(history.banned_until) }}
                                                </span>
                                                <span v-else class="text-slate-500">-</span>
                                            </div>
                                            <span v-else class="text-slate-500 text-sm">-</span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="text-slate-700 text-sm">{{ history.performed_by?.name || 'System' }}</span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="text-slate-700 text-sm">{{ formatDate(history.created_at) }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </ContentBoxBody>
                </ContentBox>
            </div>

            <!-- Box 3: Ban Form -->
            <div>
                <ContentBox>
                    <ContentBoxHeader>
                        <template #title>
                            <div class="flex items-center gap-2">
                                <span
                                    class="material-symbols-outlined text-xl"
                                    :class="user.is_banned ? 'text-green-600' : 'text-primary'"
                                >
                                    {{ user.is_banned ? 'check_circle' : 'block' }}
                                </span>
                                <ContentBoxTitle :title="user.is_banned ? 'Unban User' : 'Ban User'" />
                            </div>
                        </template>
                    </ContentBoxHeader>
                    <ContentBoxBody>
                        <!-- Show Unban Form if User is Banned -->
                        <div v-if="user.is_banned" class="space-y-6">
                            <!-- Current Ban Status Alert -->
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <span class="material-symbols-outlined text-red-500 text-xl">warning</span>
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-red-800 mb-2">User is Currently Banned</h3>
                                        <div v-if="currentBanReason" class="space-y-2">
                                            <p class="text-sm text-red-700">
                                                <span class="font-medium">Reason:</span> {{ currentBanReason.reason }}
                                            </p>
                                            <p class="text-sm text-red-700">
                                                <span class="font-medium">Type:</span>
                                                <span v-if="currentBanReason.is_forever" class="text-red-600 font-medium">Permanent Ban</span>
                                                <span v-else-if="currentBanReason.banned_until" class="text-red-600 font-medium">
                                                    Temporary (until {{ formatDate(currentBanReason.banned_until) }})
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Unban Form -->
                            <form @submit.prevent="submitUnban" class="space-y-6">
                                <FormField
                                    v-model="unbanForm.reason"
                                    label="Unban Reason"
                                    type="textarea"
                                    placeholder="Enter the reason for unbanning this user..."
                                    :required="true"
                                    rows="4"
                                />

                                <!-- Action Buttons -->
                                <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                                    <Button
                                        variant="outline"
                                        class="w-full sm:w-auto"
                                        left-icon="close"
                                        @click="resetUnbanForm"
                                        :disabled="unbanLoading"
                                    >
                                        Cancel
                                    </Button>
                                    <Button
                                        variant="success"
                                        class="w-full sm:w-auto"
                                        left-icon="check_circle"
                                        :loading="unbanLoading"
                                        type="submit"
                                    >
                                        Unban User
                                    </Button>
                                </div>
                            </form>
                        </div>

                        <!-- Show Ban Form if User is Not Banned -->
                        <div v-else class="space-y-6">
                            <form @submit.prevent="submitBan" class="space-y-6">
                                <!-- Ban Reason -->
                                <FormField
                                    v-model="banForm.reason"
                                    label="Ban Reason"
                                    type="textarea"
                                    placeholder="Enter the reason for banning this user..."
                                    :required="true"
                                    rows="4"
                                />

                                <!-- Ban Duration -->
                                <div class="space-y-4">
                                    <label class="block text-sm font-semibold text-slate-700 mb-3">Ban Duration</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <label
                                            :class="[
                                                'relative flex items-center p-4 rounded-lg border-2 cursor-pointer transition-all',
                                                banForm.is_forever
                                                    ? 'border-red-300 bg-red-50 shadow-sm'
                                                    : 'border-slate-200 bg-white hover:border-slate-300'
                                            ]"
                                        >
                                            <input
                                                v-model="banForm.is_forever"
                                                type="radio"
                                                name="duration"
                                                :value="true"
                                                class="sr-only"
                                            />
                                            <div class="flex items-center gap-3">
                                                <div
                                                    :class="[
                                                        'w-4 h-4 rounded-full border-2 flex items-center justify-center',
                                                        banForm.is_forever
                                                            ? 'border-red-500 bg-red-500'
                                                            : 'border-slate-300'
                                                    ]"
                                                >
                                                    <div
                                                        v-if="banForm.is_forever"
                                                        class="w-2 h-2 rounded-full bg-white"
                                                    ></div>
                                                </div>
                                                <div>
                                                    <div class="font-medium text-slate-800">Permanent Ban</div>
                                                    <div class="text-sm text-slate-500">User will be banned forever</div>
                                                </div>
                                            </div>
                                            <div v-if="banForm.is_forever" class="absolute top-2 right-2">
                                                <span class="material-symbols-outlined text-red-500 text-sm">check_circle</span>
                                            </div>
                                        </label>

                                        <label
                                            :class="[
                                                'relative flex items-center p-4 rounded-lg border-2 cursor-pointer transition-all',
                                                !banForm.is_forever
                                                    ? 'border-blue-300 bg-blue-50 shadow-sm'
                                                    : 'border-slate-200 bg-white hover:border-slate-300'
                                            ]"
                                        >
                                            <input
                                                v-model="banForm.is_forever"
                                                type="radio"
                                                name="duration"
                                                :value="false"
                                                class="sr-only"
                                            />
                                            <div class="flex items-center gap-3">
                                                <div
                                                    :class="[
                                                        'w-4 h-4 rounded-full border-2 flex items-center justify-center',
                                                        !banForm.is_forever
                                                            ? 'border-blue-500 bg-blue-500'
                                                            : 'border-slate-300'
                                                    ]"
                                                >
                                                    <div
                                                        v-if="!banForm.is_forever"
                                                        class="w-2 h-2 rounded-full bg-white"
                                                    ></div>
                                                </div>
                                                <div>
                                                    <div class="font-medium text-slate-800">Temporary Ban</div>
                                                    <div class="text-sm text-slate-500">Set specific end date</div>
                                                </div>
                                            </div>
                                            <div v-if="!banForm.is_forever" class="absolute top-2 right-2">
                                                <span class="material-symbols-outlined text-blue-500 text-sm">check_circle</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Banned Until DateTime -->
                                <div v-if="!banForm.is_forever" class="space-y-3">
                                    <label class="block text-sm font-semibold text-slate-700">
                                        Ban End Date <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="material-symbols-outlined text-slate-400 text-sm">calendar_today</span>
                                        </div>
                                        <input
                                            v-model="banForm.banned_until"
                                            type="datetime-local"
                                            required
                                            class="w-full rounded-lg border border-slate-300 bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all pl-10 pr-4 py-3"
                                            placeholder="Select end date and time"
                                        />
                                    </div>
                                    <p class="text-xs text-slate-500">The user will be automatically unbanned at this date and time.</p>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                                    <Button
                                        variant="outline"
                                        class="w-full sm:w-auto"
                                        left-icon="close"
                                        @click="resetForm"
                                        :disabled="loading"
                                    >
                                        Reset
                                    </Button>
                                    <Button
                                        variant="danger"
                                        class="w-full sm:w-auto"
                                        left-icon="block"
                                        :loading="loading"
                                        type="submit"
                                    >
                                        Ban User
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </ContentBoxBody>
                </ContentBox>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue'
import type { Ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

// Composables and stores
import { useApi } from '@/composables/useApi'
import { showToast } from '@/composables/useSweetAlert'
import { apiRoutes } from '@/config/apiRoutes'

const { get, post } = useApi()

// Component imports
import AdminLayout from '../../../layouts/AdminLayout.vue'
import PageHeader from '../../../components/ui/PageHeader.vue'
import PageHeaderTitle from '../../../components/ui/PageHeaderTitle.vue'
import PageHeaderActions from '../../../components/ui/PageHeaderActions.vue'
import ActionButton from '../../../components/ui/ActionButton.vue'
import ContentBox from '../../../components/ui/ContentBox.vue'
import ContentBoxBody from '../../../components/ui/ContentBoxBody.vue'
import Button from '../../../components/ui/Button.vue'
import FormField from '../../../components/ui/FormField.vue'
import StatusBadge from '../../../components/ui/StatusBadge.vue'

const route = useRoute()
const router = useRouter()

// User data
interface User {
    id: number
    name: string
    email: string
    profile_image: string
    role: string
    role_display_name: string
    status: string
    joined_date: string
    is_banned: boolean
}

interface BanHistoryItem {
    id: number
    action: 'ban' | 'unban'
    reason: string
    is_forever: boolean
    banned_until: string | null
    created_at: string
    performed_by: {
        id: number
        name: string
        email: string
    } | null
}

const user = ref<User>({
    id: 0,
    name: '',
    email: '',
    profile_image: '',
    role: '',
    role_display_name: '',
    status: '',
    joined_date: '',
    is_banned: false
})

// Ban history
const banHistory: Ref<BanHistoryItem[]> = ref([])

// Computed property to get current ban reason
const currentBanReason = computed(() => {
    if (!user.value.is_banned || banHistory.value.length === 0) {
        return null
    }

    // Find the most recent ban that hasn't been unbanned
    const sortedHistory = [...banHistory.value].sort((a, b) =>
        new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
    )

    for (const entry of sortedHistory) {
        if (entry.action === 'ban') {
            // Check if this ban has been unbanned by looking for a later unban
            const hasUnbanAfter = sortedHistory.some(laterEntry =>
                laterEntry.action === 'unban' &&
                new Date(laterEntry.created_at) > new Date(entry.created_at)
            )
            if (!hasUnbanAfter) {
                return entry
            }
        }
    }

    return null
})

// Loading states
const loading = ref(false)
const historyLoading = ref(false)
const unbanLoading = ref(false)

// Ban form
const banForm = reactive({
    reason: '',
    is_forever: true,
    banned_until: ''
})

// Unban form
const unbanForm = reactive({
    reason: ''
})

// Fetch user data
const fetchUser = async () => {
    try {
        const userId = route.params.id as string
        const response = await get(apiRoutes.users.show(userId))

        if (!response.ok) {
            throw new Error('Failed to fetch user data')
        }

        const data = await response.json()
        user.value = data.user

        // Check if user can be banned
        if (!user.value.protection?.can_ban) {
            showToast({ icon: 'error', title: 'Access Denied', text: 'This user cannot be banned.' })
            router.push({ name: 'user_management.index' })
            return
        }
    } catch (error) {
        console.error('Error fetching user:', error)
        showToast({ icon: 'error', title: 'Error', text: 'Failed to load user data' })
    }
}

// Fetch ban history
const fetchBanHistory = async () => {
    historyLoading.value = true
    try {
        const userId = route.params.id as string
        const response = await get(apiRoutes.users.banHistory(userId))

        if (!response.ok) {
            throw new Error('Failed to fetch ban history')
        }

        const data = await response.json()
        banHistory.value = data.data
    } catch (error) {
        console.error('Error fetching ban history:', error)
        showToast({ icon: 'error', title: 'Error', text: 'Failed to load ban history' })
    } finally {
        historyLoading.value = false
    }
}

// Submit ban form
const submitBan = async () => {
    if (!banForm.reason.trim()) {
        showToast({ icon: 'error', title: 'Error', text: 'Please provide a ban reason' })
        return
    }

    if (!banForm.is_forever && !banForm.banned_until) {
        showToast({ icon: 'error', title: 'Error', text: 'Please select a ban duration' })
        return
    }

    loading.value = true
    try {
        const userId = route.params.id as string
        const response = await post(apiRoutes.users.ban(userId), {
            reason: banForm.reason,
            is_forever: banForm.is_forever,
            banned_until: banForm.is_forever ? null : banForm.banned_until
        })

        if (!response.ok) {
            throw new Error('Failed to ban user')
        }

        showToast({ icon: 'success', title: 'Success', text: 'User banned successfully' })

        // Refresh data
        await fetchUser()
        await fetchBanHistory()
        resetForm()
    } catch (error) {
        console.error('Error banning user:', error)
        showToast({ icon: 'error', title: 'Error', text: 'Failed to ban user' })
    } finally {
        loading.value = false
    }
}

// Reset form
const resetForm = () => {
    banForm.reason = ''
    banForm.is_forever = true
    banForm.banned_until = ''
}

// Submit unban form
const submitUnban = async () => {
    if (!unbanForm.reason.trim()) {
        showToast({ icon: 'error', title: 'Error', text: 'Please provide an unban reason' })
        return
    }

    unbanLoading.value = true
    try {
        const userId = route.params.id as string
        const response = await post(apiRoutes.users.unban(userId), {
            reason: unbanForm.reason
        })

        if (!response.ok) {
            throw new Error('Failed to unban user')
        }

        showToast({ icon: 'success', title: 'Success', text: 'User unbanned successfully' })

        // Refresh data
        await fetchUser()
        await fetchBanHistory()
        resetUnbanForm()
    } catch (error) {
        console.error('Error unbanning user:', error)
        showToast({ icon: 'error', title: 'Error', text: 'Failed to unban user' })
    } finally {
        unbanLoading.value = false
    }
}

// Reset unban form
const resetUnbanForm = () => {
    unbanForm.reason = ''
}

// Format date
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString()
}

// Go back
const goBack = () => {
    router.push({ name: 'user_management.index' })
}

// Initialize
onMounted(() => {
    fetchUser()
    fetchBanHistory()
})
</script>
