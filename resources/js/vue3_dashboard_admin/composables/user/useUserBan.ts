import { ref, computed } from 'vue'
import { useApi } from '@/composables/useApi'
import { showToast } from '@/composables/useSweetAlert'
import { apiRoutes } from '@/config/apiRoutes'

export interface BanHistoryItem {
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

export interface BanForm {
  reason: string
  is_forever: boolean
  banned_until: string | null
}

export interface UnbanForm {
  reason: string
}

export const useUserBan = () => {
  const { get, post } = useApi()

  // Loading states
  const banLoading = ref(false)
  const unbanLoading = ref(false)
  const historyLoading = ref(false)

  // Ban history state
  const banHistory = ref<BanHistoryItem[]>([])

  // Submit ban form
  const submitBan = async (userId: string | number, banForm: BanForm) => {
    if (!banForm.reason.trim()) {
      showToast({ icon: 'error', title: 'Error', text: 'Please provide a ban reason' })
      return false
    }

    if (!banForm.is_forever && !banForm.banned_until) {
      showToast({ icon: 'error', title: 'Error', text: 'Please select a ban duration' })
      return false
    }

    banLoading.value = true
    try {
      const response = await post(apiRoutes.users.ban(userId), {
        reason: banForm.reason,
        is_forever: banForm.is_forever,
        banned_until: banForm.is_forever ? null : banForm.banned_until
      })

      if (!response.ok) {
        throw new Error('Failed to ban user')
      }

      showToast({ icon: 'success', title: 'Success', text: 'User banned successfully' })
      return true
    } catch (error) {
      console.error('Error banning user:', error)
      showToast({ icon: 'error', title: 'Error', text: 'Failed to ban user' })
      return false
    } finally {
      banLoading.value = false
    }
  }

  // Submit unban form
  const submitUnban = async (userId: string | number, unbanForm: UnbanForm) => {
    if (!unbanForm.reason.trim()) {
      showToast({ icon: 'error', title: 'Error', text: 'Please provide an unban reason' })
      return false
    }

    unbanLoading.value = true
    try {
      const response = await post(apiRoutes.users.unban(userId), {
        reason: unbanForm.reason
      })

      if (!response.ok) {
        throw new Error('Failed to unban user')
      }

      showToast({ icon: 'success', title: 'Success', text: 'User unbanned successfully' })
      return true
    } catch (error) {
      console.error('Error unbanning user:', error)
      showToast({ icon: 'error', title: 'Error', text: 'Failed to unban user' })
      return false
    } finally {
      unbanLoading.value = false
    }
  }

  // Reset ban form
  const resetBanForm = () => {
    // This function is now just a placeholder since forms are managed locally
    // The actual reset logic should be in the component
  }

  // Reset unban form
  const resetUnbanForm = () => {
    // This function is now just a placeholder since forms are managed locally
    // The actual reset logic should be in the component
  }

  // Fetch ban history
  const fetchBanHistory = async (userId: string | number) => {
    historyLoading.value = true
    try {
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

  // Computed property to get current ban reason
  const currentBanReason = computed(() => {
    if (banHistory.value.length === 0) {
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

  // Format date utility
  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString()
  }

  return {
    banLoading,
    unbanLoading,
    historyLoading,
    banHistory,
    currentBanReason,
    submitBan,
    submitUnban,
    resetBanForm,
    resetUnbanForm,
    fetchBanHistory,
    formatDate
  }
}