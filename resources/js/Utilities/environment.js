import { computed } from "vue"

export const isProduction = computed(() => import.meta.env.PROD)
