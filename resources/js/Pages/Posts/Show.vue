<template>
    <AppLayout>
        <Container>
            <h1 class="text-2xl font-bold">{{ post.title }}</h1>

            <span class="block mt-1 text-sm text-gray-600">{{ postedDate }} ago by {{ post.user.name }}</span>

            <p class="mt-5">
                <pre class="-indent-20 text-wrap font-sans">
                    {{ post.body }}
                </pre>
            </p>
        </Container>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';
import { formatDistance, parseISO } from 'date-fns'
import Container from '@/Components/Container.vue';

const props = defineProps(['post'])

const postedDate = computed(() => formatDistance(parseISO(props.post.created_at), new Date()))
</script>
