<template>
    <AppLayout>
        <Container>
            <h1 class="text-3xl font-bold">{{ post.title }}</h1>

            <span class="block mt-1 text-sm text-gray-600">{{ postedDate }} ago by {{ post.user.name }}</span>

            <article class="mt-5">
                <pre class="-indent-20 text-wrap font-sans">
                    {{ post.body }}
                </pre>
            </article>

            <div class="mt-12">
                <h2 class="text-2xl font-semibold">Comments</h2>

                <form v-if="$page.props.auth.user" @submit.prevent="addComment">
                    <div>
                        <InputLabel for="body" class="sr-only">Comment</InputLabel>
                        <TextArea id="body" v-model="commentForm.body" placeholder="Your comment here!" rows="4" />
                        <InputError :message="commentForm.errors.body" class="mt-2"/>
                    </div>

                    <PrimaryButton
                        type="submit"
                        class="mt-3 disabled:opacity-40 disabled:cursor-not-allowed"
                        :disabled="commentForm.processing"
                    >
                        Add Comment
                    </PrimaryButton>
                </form>

                <ul class="divide-y mt-4">
                    <li v-for="comment in comments.data" :key="comment.id" class="px-2 py-4">
                        <Comment :comment="comment"/>
                    </li>
                </ul>

                <Pagination :meta="comments.meta" :only="['comments']"/>
            </div>
        </Container>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import Pagination from '@/Components/Pagination.vue';
import Comment from '@/Components/Comment.vue'
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextArea from '@/Components/TextArea.vue';
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { relativeDate } from '@/Utilities/Date.js'
import InputError from '@/Components/InputError.vue';

const props = defineProps(['post', 'comments'])

const postedDate = computed(() => relativeDate(props.post.created_at))

const commentForm = useForm({
    body: '',
})

const addComment = () => commentForm.post(route('posts.comments.store', props.post.id), {
    preserveScroll: true,
    onSuccess: () => commentForm.reset()
})

</script>
