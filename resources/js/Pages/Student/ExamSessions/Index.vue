<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

defineProps({ sessions: Array })
</script>

<template>
    <Head title="Exam History" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-semibold text-gray-800">My Exam Sessions</h2></template>
        <div class="py-8 max-w-4xl mx-auto px-4">
            <ul class="divide-y rounded border border-gray-200 bg-white">
                <li v-for="s in sessions" :key="s.id" class="p-4">
                    <Link :href="route('student.exam-sessions.show', s.id)" class="block">
                        <div class="font-medium">{{ s.exam_title }}</div>
                        <div class="text-sm text-gray-500">{{ s.subject }} · {{ s.state }} · {{ s.score_label }}</div>
                    </Link>
                </li>
                <li v-if="!sessions.length" class="p-4 text-gray-500 text-sm">No past sessions.</li>
            </ul>
        </div>
    </AuthenticatedLayout>
</template>
