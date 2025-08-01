import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useNotificationStore = defineStore('notifications', () => {
    // State
    const notifications = ref([])
    const isLoading = ref(false)
    const isInitialized = ref(false)

    // Getters
    const unreadCount = computed(() => {
        return notifications.value.filter(n => !n.read_at).length
    })

    const recentNotifications = computed(() => {
        return notifications.value.slice(0, 10)
    })

    // Actions
    const initialize = async () => {
        if (isInitialized.value) return

        await fetchNotifications()
        isInitialized.value = true
    }

    const fetchNotifications = async () => {
        if (isLoading.value) return

        isLoading.value = true
        try {
            const response = await axios.get(route('api.notifications.list'))
            notifications.value = response.data.notifications || []
        } catch (error) {
            console.error('Failed to fetch notifications:', error)
        } finally {
            isLoading.value = false
        }
    }

    const markedNotification = async (notificationId = null) => {
        try {
            const payload = notificationId ? { mark_all: false, notification_id: notificationId} : { mark_all: true }
            await axios.patch(route('api.notifications.read'), payload)
            // Real-time update will be handled by Echo event listener in components
            console.log('marking done...')
        } catch (error) {
            console.error('Failed to mark notification(s) as read:', error)
            throw error
        }
    }

    // State mutation methods (to be called by components when Echo events are received)
    const markNotificationAsRead = (notificationId) => {
        const notification = notifications.value.find(n => n.id === notificationId)
        if (notification && !notification.read_at) {
            notification.read_at = new Date().toISOString()
        }
    }

    const markAllNotificationsAsRead = () => {
        notifications.value.forEach(notification => {
            if (!notification.read_at) {
                notification.read_at = new Date().toISOString()
            }
        })
    }

    return {
        // State
        notifications,
        isLoading,
        isInitialized,

        // Getters
        unreadCount,
        recentNotifications,

        // Actions
        initialize,
        fetchNotifications,
        markedNotification,

        // State mutations (for Echo events)
        markNotificationAsRead,
        markAllNotificationsAsRead
    }
})
