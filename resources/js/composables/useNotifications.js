import { onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notificationStore'

export function useNotifications() {
    const notificationStore = useNotificationStore()

    onMounted(async () => {
        await notificationStore.initialize()
    })

    return notificationStore
}
