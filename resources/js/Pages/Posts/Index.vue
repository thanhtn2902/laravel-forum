<template>
    <AppLayout>
        <Container>
            <div>
                <Link :href="route('posts.index')" v-if="selectedTopic" class="text-indigo-400 hover:text-indigo-700 block mb-2 trigger-hover-transition">
                    Back to All Posts
                </Link>
                <PageHeading v-text="selectedTopic ? selectedTopic.name : 'All Posts'"/>
                <p v-if="selectedTopic" class="mt-1 text-gray-600 text-sm">{{ selectedTopic.description }}</p>
            </div>
            <ul class="divide-y space-y-2 mt-4">
                <li v-for="post in posts.data" :key="post.id">
                    <Link :href="post.routes.show" class="block group px-2 py-4 ">
                        <span class="font-bold text-lg group-hover:text-indigo-500 trigger-hover-transition"> {{ post.title }} </span>
                        <span class="block mt-1 text-sm text-gray-600"> {{ formattedDate(post) }} ago by {{ post.user.name }}</span>
                    </Link>
                    <Link :href="route('posts.index', {'topic': post.topic.slug})" class="rounded-full py-0.5 px-3 border border-pink-500 text-pink-500 hover:bg-indigo-400 hover:text-indigo-100 hover:border-indigo-100 trigger-hover-transition">
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
import PageHeading from '@/Components/PageHeading.vue';

defineProps(['posts', 'selectedTopic']);

const formattedDate = (post) => relativeDate(post.created_at)

</script>
