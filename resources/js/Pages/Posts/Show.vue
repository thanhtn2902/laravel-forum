<template>
    <AppLayout>
        <Container>
            <PageHeading class="mb-2">{{ post.title }}</PageHeading>
            <Pill>
                {{ post.topic.name }}
            </Pill>

            <span class="block mt-2 text-sm text-gray-600">{{ postedDate }} ago by {{ post.user.name }}</span>
            <article class="mt-5 prose prose-sm max-w-none" v-html="post.html"></article>

            <div class="mt-12">
                <h2 class="text-2xl font-semibold">Comments</h2>

                <form v-if="$page.props.auth.user" @submit.prevent="submit">
                    <div>
                        <InputLabel for="body" class="sr-only">Comment</InputLabel>
                        <MarkdownEditor id="body" v-model="commentForm.body" placeholder="Your comment here!" ref="commentTextAreaRef" editor-class="min-h-[160px]" />
                        <InputError :message="commentForm.errors.body" class="mt-2"/>
                    </div>

                    <PrimaryButton
                        type="submit"
                        class="mt-3 disabled:opacity-40 disabled:cursor-not-allowed"
                        :disabled="commentForm.processing"
                    >
                       {{ buttonText }}
                    </PrimaryButton>

                    <SecondaryButton
                        v-if="commentIdBeingEdited"
                        type="button"
                        class="ml-2"
                        @click="cancelEditComment"
                    >
                        Cancel
                    </SecondaryButton>
                </form>

                <ul class="divide-y mt-4">
                    <li v-for="comment in comments.data" :key="comment.id" class="px-2 py-4">
                        <Comment @delete="deleteComment" @edit="editComment" :comment="comment"/>
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
import InputError from '@/Components/InputError.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { computed, ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { relativeDate } from '@/Utilities/Date.js'
import { useConfirm } from '@/Utilities/Composables/useConfirm.js';
import MarkdownEditor from '@/Components/MarkdownEditor.vue';
import PageHeading from '@/Components/PageHeading.vue';
import Pill from '@/Components/Pill.vue';

const props = defineProps(['post', 'comments'])

const postedDate = computed(() => relativeDate(props.post.created_at))

const commentForm = useForm({
    body: '',
})

const { confirmation } = useConfirm()

const commentIdBeingEdited = ref(null)
const commentTextAreaRef = ref(null)
const commentBeingEdited = computed(() => props.comments.data.find(comment => comment.id === commentIdBeingEdited.value))
const buttonText = computed(() => commentIdBeingEdited.value ? 'Update Comment' : 'Add Comment')

const editComment = (commentId) => {
    commentIdBeingEdited.value = commentId
    commentForm.body = commentBeingEdited.value?.body
    commentTextAreaRef.value?.focus()
}

const cancelEditComment = () => {
    commentIdBeingEdited.value = null
    commentForm.reset()
}

const addComment = () => commentForm.post(route('posts.comments.store', props.post.id), {
    preserveScroll: true,
    onSuccess: () => commentForm.reset()
})

const updateComment = async () => {
    if(!await confirmation('Are you sure you want to update this comment?')) {
        commentTextAreaRef.value?.focus()
        return
    }

    commentForm.put(route('comments.update', {
        'comment': commentIdBeingEdited.value,
        'page': props.comments.meta.current_page
    }), {
        preserveScroll: true,
        onSuccess: () => cancelEditComment()
    })
}

const deleteComment = async (commentId) => {
    if(! await confirmation('Are you sure you want to delete this comment?')) {
        return
    }

    router.delete(route('comments.destroy', { comment: commentId, page: props.comments.meta.current_page }), {
        preserveScroll: true
    })
}

const submit = () => commentIdBeingEdited.value ? updateComment() : addComment()

</script>
