<template>
    <Link
        :href="href"
        class="whitespace-nowrap rounded-full py-0.5 px-3 border border-pink-500 text-pink-500 hover:bg-indigo-400 hover:text-indigo-100 hover:border-indigo-100 trigger-hover-transition"
        :class="{'bg-indigo-400 text-indigo-100 border-indigo-100': filled}"
    >
        <slot></slot>
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        required: true,
    },
    filled: {
        default: false
    }
})

</script>
