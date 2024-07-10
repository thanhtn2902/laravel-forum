<template>
    <AppLayout title="Create a Post">
        <Container>
            <h1 class="text-3xl font-bold">Create Post</h1>

            <form @submit.prevent="createPost" class="space-y-4">
                <div class="mt-6">
                    <InputLabel for="title" class="sr-only">Title</InputLabel>
                    <TextInput id="title" class="w-full" v-model="form.title" placeholder="Your title.." />
                    <InputError :message="form.errors.title" class="mt-1"/>
                </div>

                <div>
                    <InputLabel for="body" class="sr-only">Body</InputLabel>
                    <MarkdownEditor v-model="form.body" placeholder="What's on your mind?...">
                        <template #toolbar="{ editor }">
                            <button
                                v-if="!isProduction"
                                @click="autofill"
                                type="button"
                                class="px-3 py-2 transition duration-300 hover:duration-150"
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

const form = useForm({
    'body': '',
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
