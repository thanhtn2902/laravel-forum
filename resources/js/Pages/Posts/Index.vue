<template>
    <AppLayout>
        <Container>
            <ul class="divide-y space-y-2">
                <li v-for="post in posts.data" :key="post.id">
                    <Link :href="post.routes.show" class="block group px-2 py-4">
                        <span class="font-bold text-lg group-hover:text-indigo-500"> {{ post.title }} </span>
                        <span class="block mt-1 text-sm text-gray-600"> {{ formattedDate(post) }} ago by {{ post.user.name }}</span>
                    </Link>
                    <Link href="/" class="rounded-full py-0.5 px-3 border border-pink-500 text-pink-500 hover:bg-indigo-400 hover:text-indigo-100 hover:border-indigo-100 transition hover:duration-200 duration-300">
                        {{ post.topic.name }}
                    </Link>
                </li>
            </ul>
            <Pagination :meta="posts.meta"/>
        </Container>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import Pagination from '@/Components/Pagination.vue';
import { Link } from '@inertiajs/vue3';
import { relativeDate } from '@/Utilities/Date.js';

defineProps(['posts']);

const formattedDate = (post) => relativeDate(post.created_at)

</script>
