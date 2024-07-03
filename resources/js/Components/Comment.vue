<template>
    <div class="sm:flex">
        <div class="mb-4 flex-shrink-0 sm:mb-0 sm:mr-4">
            <img :src="comment.user.profile_photo_url" alt="User Profile" class="h-10 w-10 rounded-full" />
        </div>
        <div>
            <p class="mt-1 break-all">{{ comment.body }}</p>
            <span class="first-letter:uppercase block pt-1 text-xs text-gray-600">By {{ comment.user.name }} {{ relativeDate(comment.created_at) }}</span>
            <div class="mt-1">
                <form @submit.prevent="deleteComment" v-if="comment.can?.delete">
                    <button type="submit">Delete</button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { relativeDate } from "@/Utilities/Date.js";
import { router } from "@inertiajs/vue3";

const props = defineProps(['comment'])

const deleteComment = () => router.delete(route('comments.destroy', props.comment.id), {
    preserveScroll: true
})

</script>