<template>
    <div class="relative">
        <!-- Notification Bell Icon -->
        <button
            @click="toggleDropdown"
            class="relative p-2 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            <BellIcon class="h-6 w-6" />
            <!-- Unread indicator -->
            <span
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown -->
        <div
            v-show="showDropdown"
            class="absolute right-0 z-50 mt-2 w-80 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5"
            @click.stop
        >
            <div class="p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-medium text-gray-900">Notifications</h3>
                    <Link
                        :href="route('notifications.index')"
                        class="text-sm text-blue-600 hover:text-blue-800"
                    >
                        View all
                    </Link>
                </div>

                <div v-if="recentNotifications.length === 0" class="text-center py-6">
                    <BellIcon class="mx-auto h-12 w-12 text-gray-400" />
                    <p class="mt-2 text-sm text-gray-500">No notifications yet</p>
                </div>

                <div v-else class="space-y-3 max-h-96 overflow-y-auto">
                    <div
                        v-for="notification in recentNotifications"
                        :key="notification.id"
                        class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 cursor-pointer"
                        :class="{ 'bg-blue-50': !notification.read_at }"
                        @click="handleNotificationClick(notification)"
                    >
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
                        <div v-if="!notification.read_at" class="flex-shrink-0">
                            <span class="inline-block w-2 h-2 bg-blue-500 rounded-full"></span>
                        </div>
                    </div>
                </div>

                <div v-if="unreadCount > 0" class="mt-3 pt-3 border-t border-gray-200">
                    <button
                        @click="markAllAsRead"
                        class="w-full text-center text-sm text-blue-600 hover:text-blue-800 font-medium"
                    >
                        Mark all as read
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Overlay to close dropdown -->
    <div
        v-if="showDropdown"
        class="fixed inset-0 z-40"
        @click="showDropdown = false"
    ></div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { BellIcon, HandThumbUpIcon } from '@heroicons/vue/24/outline'
import { Link, router, usePage } from '@inertiajs/vue3'
import { relativeDate } from '@/Utilities/Date.js'
import axios from 'axios'

const showDropdown = ref(false)
const unreadCount = ref(0)
const recentNotifications = ref([])

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value
    if (showDropdown.value) {
        loadNotifications()
    }
}

const loadNotifications = async () => {
    try {
        const response = await axios.get(route('notifications.unread-count'))
        unreadCount.value = response.data.count

        // You might want to load recent notifications here
        // For now, we'll just update the count
    } catch (error) {
        console.error('Failed to load notifications:', error)
    }
}

const handleNotificationClick = (notification) => {
    if (!notification.read_at) {
        markAsRead(notification.id)
    }
    router.visit(notification.data.likeable_url)
    showDropdown.value = false
}

const markAsRead = async (notificationId) => {
    try {
        await axios.patch(route('notifications.read', notificationId))
        unreadCount.value = Math.max(0, unreadCount.value - 1)
    } catch (error) {
        console.error('Failed to mark notification as read:', error)
    }
}

const markAllAsRead = async () => {
    try {
        await axios.patch(route('notifications.mark-all-read'))
        unreadCount.value = 0
        showDropdown.value = false
    } catch (error) {
        console.error('Failed to mark all notifications as read:', error)
    }
}

// Set up real-time notifications
onMounted(() => {
    loadNotifications()

    // Set up Echo for real-time notifications if available
    if (window.Echo) {
        const user = usePage().props.auth.user
        window.Echo.private(`App.Models.User.${user.id}`)
            .notification((notification) => {
                unreadCount.value++
                recentNotifications.value.unshift(notification)

                // Optional: Show a toast notification
                console.log('New notification:', notification)
            })
    }
})

onUnmounted(() => {
    if (window.Echo) {
        const user = usePage().props.auth.user
        window.Echo.leave(`App.Models.User.${user.id}`)
    }
})
</script>
