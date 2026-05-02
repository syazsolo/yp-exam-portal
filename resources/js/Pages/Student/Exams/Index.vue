<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router } from '@inertiajs/vue3'

defineProps({ exams: Array })

function start(examId) {
    if (confirm('Start this exam? The timer will begin immediately.')) {
        router.post(route('student.exams.sessions.start', examId))
    }
}
</script>

<template>
    <Head title="Available Exams" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-semibold text-gray-800">Available Exams</h2></template>
        <div class="py-8 max-w-3xl mx-auto px-4 space-y-4">
            <div v-if="exams.length === 0" class="text-sm text-gray-400">No active exams right now.</div>
            <div v-for="e in exams" :key="e.id" class="bg-white border rounded-lg p-5 flex items-center justify-between">
                <div>
                    <p class="font-medium text-gray-800">{{ e.title }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ e.subject }} · {{ e.time_limit }} min</p>
                    <p v-if="e.ends_at" class="text-xs text-red-500 mt-0.5">Closes {{ new Date(e.ends_at).toLocaleString() }}</p>
                </div>
                <button @click="start(e.id)" class="bg-gray-900 text-white text-sm px-4 py-2 rounded hover:bg-gray-700">Start</button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
