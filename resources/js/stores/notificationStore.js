import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export const useNotificationStore = defineStore('notifications', () => {
    // State
    const notifications = ref([])
    const isLoading = ref(false)
    const isInitialized = ref(false)
    const unreadCount = ref(0)

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
            const { data } = await axios.get(route('api.notifications.list'))
            notifications.value = data.notifications
            unreadCount.value = data.unreadCount
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

        // Actions
        initialize,
        fetchNotifications,
        markedNotification,

        // State mutations (for Echo events)
        markNotificationAsRead,
        markAllNotificationsAsRead
    }
})
