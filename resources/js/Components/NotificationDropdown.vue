<template>
    <div class="relative">
        <!-- Notification Bell Button -->
        <button
            @click="toggleDropdown"
            class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-full"
        >
            <BellIcon class="h-6 w-6" />
            <span
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full min-w-[1.25rem] h-5"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
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
            <div class="max-h-96 overflow-y-auto">
                <div v-if="notifications.length === 0" class="px-4 py-8 text-center">
                    <BellIcon class="mx-auto h-8 w-8 text-gray-400" />
                    <p class="mt-2 text-sm text-gray-500">No new notifications</p>
                </div>

                <div v-else>
                    <div
                        v-for="notification in notifications.slice(0, 5)"
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
            <div v-if="unreadCount > 0" class="px-4 py-3 border-t border-gray-200">
                <button
                    @click="markAllAsRead"
                    class="w-full text-center text-sm text-blue-600 hover:text-blue-800 font-medium"
                    :disabled="markingAllAsRead"
                >
                    {{ markingAllAsRead ? 'Marking...' : 'Mark all as read' }}
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
import { Link, router } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { relativeDate } from '@/Utilities/Date.js'
import axios from 'axios'

const isOpen = ref(false)
const notifications = ref([])
const unreadCount = ref(0)
const markingAllAsRead = ref(false)

// Computed
const hasUnreadNotifications = computed(() => unreadCount.value > 0)

// Methods
const toggleDropdown = () => {
    isOpen.value = !isOpen.value
    if (isOpen.value) {
        fetchNotifications()
    }
}

const closeDropdown = () => {
    isOpen.value = false
}

const fetchNotifications = async () => {
    try {``
        const response = await axios.get(route('api.notifications.list'))
        console.log(response)
        notifications.value = response.data.notifications || []
    } catch (error) {
        console.error('Failed to fetch notifications:', error)
    }
}

const fetchUnreadCount = async () => {
    try {
        const response = await axios.get(route('api.notifications.unread-count'))
        unreadCount.value = response.data.count
    } catch (error) {
        console.error('Failed to fetch unread count:', error)
    }
}

const markAsRead = async (notificationId) => {
    try {
        await axios.patch(route('api.notifications.read', notificationId))
        // Real-time update will be handled by Echo event listener
    } catch (error) {
        console.error('Failed to mark notification as read:', error)
    }
}

const markAllAsRead = async () => {
    markingAllAsRead.value = true
    try {
        await axios.patch(route('api.notifications.mark-all-read'))
        // Real-time update will be handled by Echo event listener
    } catch (error) {
        console.error('Failed to mark all notifications as read:', error)
    } finally {
        markingAllAsRead.value = false
    }
}

const handleNotificationClick = async (notification) => {
    await markAsRead(notification.id)
    closeDropdown()
    // router.visit(notification.data.likeable_url)
}

// Handle click outside
const handleClickOutside = (event) => {
    if (!event.target.closest('.relative')) {
        closeDropdown()
    }
}

// Lifecycle
onMounted(() => {
    // Fetch initial unread count when component mounts
    fetchUnreadCount()

    document.addEventListener('click', handleClickOutside)

    // Set up real-time notifications with Laravel Echo (Reverb)
    const userId = document.head.querySelector('meta[name="user-id"]')?.getAttribute('content')

    if (userId && window.Echo) {
        const channel = window.Echo.private(`App.Models.User.${userId}`)

        // Listen for new notifications
        channel.notification((notification) => {
            console.log('New notification received via Reverb:', notification)

            // Add new notification to the list (at the beginning)
            notifications.value.unshift(notification)

            // Update unread count
            unreadCount.value++

            // Optional: You can add a toast notification here
            // showToast(`New notification: ${notification.message}`)
        })

        // Listen for notification marked as read
        channel.listen('.notification.marked-as-read', (data) => {
            console.log('Notification marked as read:', data)

            // Update the notification in the list
            const notificationIndex = notifications.value.findIndex(n => n.id === data.notification_id)
            if (notificationIndex !== -1) {
                notifications.value[notificationIndex].read_at = new Date().toISOString()
            }

            // Update unread count
            unreadCount.value = data.new_unread_count
        })

        // Listen for all notifications marked as read
        channel.listen('.notifications.all-marked-as-read', (data) => {
            console.log('All notifications marked as read:', data)

            // Mark all notifications as read in the local state
            notifications.value.forEach(notification => {
                if (!notification.read_at) {
                    notification.read_at = new Date().toISOString()
                }
            })

            // Update unread count
            unreadCount.value = data.new_unread_count
        })

        channel.error((error) => {
            console.error('Echo connection error:', error)
        })
    } else {
        console.warn('Echo not available or user not authenticated')
    }
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)

    // Clean up Echo subscription
    const userId = document.head.querySelector('meta[name="user-id"]')?.getAttribute('content')
    if (userId && window.Echo) {
        window.Echo.leave(`App.Models.User.${userId}`)
    }
})
</script>
