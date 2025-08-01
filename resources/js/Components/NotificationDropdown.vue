<template>
    <div class="relative">
        <!-- Notification Bell Button -->
        <button
            @click="toggleDropdown"
            class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-full"
        >
            <BellIcon class="h-6 w-6" />
            <span
                v-if="notificationStore.unreadCount > 0"
                class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full min-w-[1.25rem] h-5"
            >
                {{ notificationStore.unreadCount >= 10 ? '10+' : notificationStore.unreadCount }}
            </span>
        </button>

        <!-- Dropdown Menu -->
        <div
            v-if="isOpen"
            class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
            @click.stop
        >
            <!-- Header -->
            <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Notifications</h3>
                <Link
                    :href="route('notifications.index')"
                    class="text-sm text-blue-600 hover:text-blue-800"
                    @click="closeDropdown"
                >
                    View all
                </Link>
            </div>

            <!-- Notifications List -->
            <div class="max-h-96 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 hover:scrollbar-thumb-gray-400">
                <div v-if="notificationStore.recentNotifications.length === 0 && !notificationStore.isLoading" class="px-4 py-8 text-center">
                    <BellIcon class="mx-auto h-8 w-8 text-gray-400" />
                    <p class="mt-2 text-sm text-gray-500">No new notifications</p>
                </div>

                <!-- Loading state -->
                <div v-if="notificationStore.isLoading" class="px-4 py-8 text-center">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="mt-2 text-sm text-gray-500">Loading notifications...</p>
                </div>

                <div v-else>
                    <div
                        v-for="notification in notificationStore.recentNotifications"
                        :key="notification.id"
                        class="px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-b-0 cursor-pointer"
                        :class="{ 'bg-blue-50': !notification.read_at }"
                        @click="handleNotificationClick(notification)"
                    >
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <HandThumbUpIcon class="w-4 h-4 text-blue-600" />
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900">
                                    {{ notification.data?.message }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1 truncate">
                                    {{ notification.data?.likeable_title }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ relativeDate(notification.created_at) }}
                                </p>
                            </div>
                            <div v-if="!notification.read_at" class="flex-shrink-0">
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div v-if="notificationStore.unreadCount > 0" class="px-4 py-3 border-t border-gray-200">
                <button
                    @click="markAllAsRead"
                    class="w-full text-center text-sm text-blue-600 hover:text-blue-800 font-medium"
                    :disabled="submitting"
                >
                    Mark all as read
                </button>
            </div>
        </div>

        <!-- Backdrop -->
        <div
            v-if="isOpen"
            @click="closeDropdown"
            class="fixed inset-0 z-40"
        ></div>
    </div>
</template>

<script setup>
import { BellIcon, HandThumbUpIcon } from '@heroicons/vue/24/outline'
import { Link, usePage } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted } from 'vue'
import { relativeDate } from '@/Utilities/Date.js'
import { useNotifications } from '@/composables/useNotifications'

const isOpen = ref(false)
const submitting = ref(false)
const user = usePage().props.auth.user

// Use the notification store
const notificationStore = useNotifications()

// Methods
const toggleDropdown = () => {
    isOpen.value = !isOpen.value
}

const closeDropdown = () => {
    isOpen.value = false
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
        closeDropdown()
    }
}

const handleNotificationClick = async (notification) => {
    try {
        await notificationStore.markedNotification(notification.id)
        closeDropdown()
    } catch (error) {
        console.error('Failed to mark notification as read:', error)
    }
}

// Handle click outside
const handleClickOutside = (event) => {
    if (!event.target.closest('.relative')) {
        closeDropdown()
    }
}

// Lifecycle
onMounted(() => {
    document.addEventListener('click', handleClickOutside)

    if (user?.id && window.Echo) {
        const channel = window.Echo.private(`App.Models.User.${user.id}`)

        // Listen for new notifications
        channel.notification((notification) => {
            console.log('heyy')
            // Refetch notifications to ensure consistency
            notificationStore.fetchNotifications()
        })

        // Listen for notification marked as read (handles both single and all cases)
        channel.listen('.notification.marked-as-read', (data) => {
            if (data.notification_id === null) {
                // Mark all notifications as read (use state mutation, not API call)
                notificationStore.markAllNotificationsAsRead()
            } else {
                // Mark specific notification as read (use state mutation, not API call)
                notificationStore.markNotificationAsRead(data.notification_id)
            }
        })

    } else {
        console.warn('Echo not available or user not authenticated')
    }
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
    // Clean up Echo subscription
    if (user?.id && window.Echo) {
        window.Echo.leave(`App.Models.User.${user.id}`)
    }
})
</script>
