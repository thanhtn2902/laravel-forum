<template>
    <AppLayout>
        <Container>
            <div>
                <PageHeading v-text="selectedTopic ? selectedTopic.name : 'All Posts'"/>
                <p v-if="selectedTopic" class="mt-1 text-gray-600 text-sm">{{ selectedTopic.description }}</p>
                <menu class="flex space-x-2 mt-4 pb-3 pt-1 overflow-x-auto">
                    <li>
                        <Pill :href="route('posts.index')" :filled="!selectedTopic">
                            All post
                        </Pill>
                    </li>
                    <li v-for="topic in topics" :key="topic.id" >
                        <Pill :href="route('posts.index', {'topic': topic.slug})" :filled="selectedTopic?.id === topic.id">
                            {{ topic.name }}
                        </Pill>
                    </li>
                </menu>
            </div>
            <ul class="divide-y space-y-2 mt-4">
                <li v-for="post in posts.data" :key="post.id">
                    <Link :href="post.routes.show" class="block group px-2 py-4 ">
                        <span class="font-bold text-lg group-hover:text-indigo-500 trigger-hover-transition"> {{ post.title }} </span>
                        <span class="block mt-1 text-sm text-gray-600"> {{ formattedDate(post) }} by {{ post.user.name }}</span>
                    </Link>
                    <Pill :href="route('posts.index', {'topic': post.topic.slug})">
                        {{ post.topic.name }}
                    </Pill>
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
import Pill from '@/Components/Pill.vue';

defineProps(['posts', 'selectedTopic', 'topics']);

const formattedDate = (post) => relativeDate(post.created_at)

</script>
