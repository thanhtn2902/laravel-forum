<template>
    <AppLayout title="Notifications">
        <Container>
            <PageHeading>Notifications</PageHeading>

            <div class="flex justify-between items-center mb-6">
                <p class="text-gray-600">
                    {{ notifications.total }} notification(s)
                </p>
                <button
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                >
                    Mark all as read
                </button>
            </div>

            <div v-if="notificationsData.length === 0" class="text-center py-12">
                <p class="text-gray-500">No notifications yet.</p>
            </div>

            <div v-else class="space-y-4">
                <div
                    v-for="notification in notificationsData"
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
                                <Link
                                    :href="notification.data.likeable_url"
                                    class="text-sm text-blue-600 hover:text-blue-800"
                                    @click="markAsRead(notification.id)"
                                >
                                    {{ notification.data.likeable_title }}
                                </Link>
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
import { Link, router } from '@inertiajs/vue3'
import { relativeDate } from '@/Utilities/Date.js'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import axios from 'axios'

const props = defineProps(['notifications'])

// Make notifications reactive for real-time updates
const notificationsData = ref([...props.notifications.data])

const unreadCount = computed(() => {
    return notificationsData.value.filter(n => !n.read_at).length
})

const markAsRead = async (notificationId) => {
    try {
        await axios.patch(route('api.notifications.read', notificationId))
        // Real-time update will be handled by Echo event listener
    } catch (error) {
        console.error('Failed to mark notification as read:', error)
    }
}

const markAllAsRead = async () => {
    try {
        await axios.patch(route('api.notifications.mark-all-read'))
        // Real-time update will be handled by Echo event listener
    } catch (error) {
        console.error('Failed to mark all notifications as read:', error)
    }
}

// Set up real-time notifications
onMounted(() => {
    const userId = document.head.querySelector('meta[name="user-id"]')?.getAttribute('content')

    if (userId && window.Echo) {
        const channel = window.Echo.private(`App.Models.User.${userId}`)

        // Listen for new notifications
        channel.notification((notification) => {
            console.log('New notification received via Reverb:', notification)
            // Add new notification to the list (at the beginning)
            notificationsData.value.unshift(notification)
        })

        // Listen for notification marked as read
        channel.listen('.notification.marked-as-read', (data) => {
            console.log('Notification marked as read:', data)

            // Update the notification in the list
            const notification = notificationsData.value.find(n => n.id === data.notification_id)
            if (notification && !notification.read_at) {
                notification.read_at = new Date().toISOString()
            }
        })

        // Listen for all notifications marked as read
        channel.listen('.notifications.all-marked-as-read', (data) => {
            console.log('All notifications marked as read:', data)

            // Mark all notifications as read in the local state
            notificationsData.value.forEach(notification => {
                if (!notification.read_at) {
                    notification.read_at = new Date().toISOString()
                }
            })
        })

        channel.error((error) => {
            console.error('Echo connection error:', error)
        })
    } else {
        console.warn('Echo not available or user not authenticated')
    }
})

onUnmounted(() => {
    // Clean up Echo subscription
    const userId = document.head.querySelector('meta[name="user-id"]')?.getAttribute('content')
    if (userId && window.Echo) {
        window.Echo.leave(`App.Models.User.${userId}`)
    }
})
</script>
