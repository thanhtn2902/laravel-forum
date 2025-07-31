<template>
    <AppLayout title="Create a Post">
        <Container>
            <PageHeading>Create a Post</PageHeading>

            <form @submit.prevent="createPost" class="space-y-4">
                <div class="mt-6">
                    <InputLabel for="title" class="sr-only">Title</InputLabel>
                    <TextInput id="title" class="w-full" v-model="form.title" placeholder="Your title.." />
                    <InputError :message="form.errors.title" class="mt-1"/>
                </div>

                <div>
                    <InputLabel for="topic_id">Select a Topic</InputLabel>
                    <select v-model="form.topic_id" id="topic_id" class="mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option v-for="topic in topics" :key="topic.id" :value="topic.id">
                            {{ topic.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.topic_id" class="mt-1"/>
                </div>

                <div>
                    <InputLabel for="body" class="sr-only">Body</InputLabel>
                    <MarkdownEditor v-model="form.body" placeholder="What's on your mind?...">
                        <template #toolbar="{ editor }">
                            <button
                                v-if="!isProduction"
                                @click="autofill"
                                type="button"
                                class="px-3 py-2 trigger-hover-transition"
                                :class=" [ editor.isActive('heading', { level: 4 }) ? 'bg-indigo-500 text-white': 'hover:bg-gray-200']"
                                title="Heading 3"
                            >
                                <i class="ri-article-line"></i>
                            </button>
                        </template>
                    </MarkdownEditor>
                    <InputError :message="form.errors.body" class="mt-1"/>
                </div>

                <div>
                    <PrimaryButton type="submit">
                        Create
                    </PrimaryButton>
                </div>
            </form>
        </Container>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import MarkdownEditor from '@/Components/MarkdownEditor.vue';
import { isProduction } from '@/Utilities/environment';
import PageHeading from '@/Components/PageHeading.vue'

const props = defineProps(['topics'])

const form = useForm({
    'body': '',
    'topic_id': props.topics[0].id,
    'title': ''
})

const createPost = () => form.post(route('posts.store'))

const autofill = async () => {
    if(isProduction.value) {
        return
    }
    const response = await axios.get('/local/post-content');

    form.title = response.data.title
    form.body = response.data.body
}
</script>
