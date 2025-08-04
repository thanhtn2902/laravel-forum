<template>
    <AppLayout title="Notifications">
        <Container>
            <PageHeading>Notifications</PageHeading>

            <div class="flex justify-between items-center mb-6">
                <p class="text-gray-600">
                    {{ notifications.meta.total }} notification(s)
                </p>
                <button
                    v-if="notificationStore.unreadCount > 0"
                    @click="markAllAsRead"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    :disabled="submitting"
                >
                   Mark all as read
                </button>
            </div>

            <div v-if="notificationStore.notifications.length === 0 && !notificationStore.isLoading" class="text-center py-12">
                <p class="text-gray-500">No notifications yet.</p>
            </div>

            <!-- Loading state -->
            <div v-if="notificationStore.isLoading" class="text-center py-12">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-2 text-gray-500">Loading notifications...</p>
            </div>

            <div v-else class="space-y-4">
                <div
                    v-for="notification in notificationStore.notifications"
                    :key="notification.id"
                    class="bg-white border rounded-lg p-4 hover:bg-gray-50 transition-colors"
                    :class="{ 'border-l-4 border-l-blue-500': !notification.read_at }"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <HandThumbUpIcon class="w-4 h-4 text-blue-600" />
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900">
                                        {{ notification.data.message }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ relativeDate(notification.created_at) }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-2">
                                <span class="text-sm font-medium text-blue-700 leading-relaxed">
                                    {{ notification.data.likeable_title }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <span
                                v-if="!notification.read_at"
                                class="inline-block w-2 h-2 bg-blue-500 rounded-full"
                            ></span>
                            <button
                                v-if="!notification.read_at"
                                @click="markAsRead(notification.id)"
                                class="text-gray-400 hover:text-gray-600 text-xs"
                            >
                                Mark as read
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <Pagination :meta="notifications.meta" class="mt-6" />
        </Container>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Container from '@/Components/Container.vue'
import PageHeading from '@/Components/PageHeading.vue'
import Pagination from '@/Components/Pagination.vue'
import { HandThumbUpIcon } from '@heroicons/vue/20/solid'
import { usePage } from '@inertiajs/vue3'
import { relativeDate } from '@/Utilities/Date.js'
import { ref, onMounted, onUnmounted } from 'vue'
import { useNotifications } from '@/composables/useNotifications'

const props = defineProps(['notifications'])
const user = usePage().props.auth.user

const submitting = ref(false)

// Use the notification store
const notificationStore = useNotifications()

// Initialize with server-side data if available
onMounted(async () => {
    // Initialize the store with server-side data
    if (props.notifications?.data) {
        notificationStore.notifications = props.notifications.data
    }

    // Also fetch fresh data to ensure we have the latest
    await notificationStore.fetchNotifications()

    if (user?.id && window.Echo) {
        const channel = window.Echo.private(`App.Models.User.${user.id}`)

        // Listen for new notifications
        channel.notification((notification) => {
            console.log('New notification received:', notification)
            // Add the new notification to the beginning of the array
            notificationStore.notifications.unshift(notification)
        })

        // Listen for notification marked as read (handles both single and all cases)
        channel.listen('.notification.marked-as-read', (data) => {
            if (data.notification_id === null) {
                // Mark all notifications as read
                notificationStore.markAllNotificationsAsRead()
            } else {
                // Mark specific notification as read
                notificationStore.markNotificationAsRead(data.notification_id)
            }
        })
    } else {
        console.warn('Echo not available or user not authenticated')
    }
})

onUnmounted(() => {
    // Clean up Echo subscription
    if (user?.id && window.Echo) {
        window.Echo.leave(`App.Models.User.${user.id}`)
    }
})

const markAsRead = async (notificationId) => {
    try {
        await notificationStore.markedNotification(notificationId)
    } catch (error) {
        console.error('Failed to mark notification as read:', error)
    }
}

const markAllAsRead = async () => {
    if (submitting.value) return // Prevent multiple calls

    submitting.value = true
    try {
        await notificationStore.markedNotification()
    } catch (error) {
        console.error('Failed to mark all notifications as read:', error)
    } finally {
        submitting.value = false
    }
}
</script>
