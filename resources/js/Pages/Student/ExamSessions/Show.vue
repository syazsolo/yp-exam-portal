<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'

defineProps({ session: Object })
</script>

<template>
    <Head title="Session Detail" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-semibold text-gray-800">{{ session.exam.title }}</h2></template>
        <div class="py-8 max-w-4xl mx-auto px-4 space-y-6">
            <div v-if="session.score" class="rounded border border-gray-200 bg-white p-4">
                <div class="text-sm text-gray-500">Score</div>
                <div class="text-2xl font-semibold">{{ session.score.label }} ({{ session.score.percent }}%)</div>
            </div>

            <div v-for="(a, i) in session.answers" :key="i" class="rounded border border-gray-200 bg-white p-4">
                <div class="font-medium">{{ a.question.text }}</div>
                <div class="text-sm text-gray-500 mb-2">Worth {{ a.question.weight }}</div>
                <div v-if="a.selected_option" class="text-sm">
                    Selected: <span :class="a.selected_option.is_correct ? 'text-green-600' : 'text-red-600'">{{ a.selected_option.body }}</span>
                </div>
                <div v-else-if="a.text_answer" class="text-sm">
                    <div class="whitespace-pre-wrap">{{ a.text_answer }}</div>
                    <div v-if="a.reviewer_comment" class="mt-2 text-xs text-gray-600">Reviewer: {{ a.reviewer_comment }}</div>
                </div>
                <div class="text-sm text-gray-700 mt-1">Score: {{ a.score ?? '—' }}</div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
