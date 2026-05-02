<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

defineProps({ session: Object })

const statusCls = (s) => ({
    pending_review: 'bg-orange-100 text-orange-800',
    scored:       'bg-green-100 text-green-800',
    submitted:    'bg-blue-100 text-blue-800',
}[s] ?? 'bg-gray-100 text-gray-600')
</script>

<template>
    <Head title="Result" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-semibold text-gray-800">{{ session.exam.title }} — Result</h2></template>
        <div class="py-8 max-w-3xl mx-auto px-4 space-y-6">

            <!-- summary card -->
            <div class="bg-white border rounded-lg p-6 flex items-center gap-8">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Status</p>
                    <span class="mt-1 inline-block px-2 py-0.5 rounded text-sm font-semibold uppercase" :class="statusCls(session.status)">{{ session.status.replace('_', ' ') }}</span>
                </div>
                <div v-if="session.status === 'scored'">
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Score</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ session.score_label }}</p>
                    <p class="text-sm text-gray-500">{{ session.score_pct }}%</p>
                </div>
                <div v-else class="text-sm text-gray-500">
                    <span v-if="session.status === 'pending_review'">Your open-text answers are being reviewed by the lecturer.</span>
                    <span v-else>Score will be available after grading.</span>
                </div>
            </div>

            <!-- per-answer breakdown -->
            <div class="space-y-4">
                <div v-for="(a, i) in session.answers" :key="i" class="bg-white border rounded-lg p-4 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400">Q{{ i + 1 }}</span>
                        <span class="text-xs bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded uppercase">{{ a.type === 'mcq' ? 'MCQ' : 'Open Text' }}</span>
                        <span class="text-xs text-gray-400">{{ a.question.points }} pts</span>
                    </div>
                    <p class="text-sm font-medium text-gray-800">{{ a.question.body }}</p>

                    <!-- MCQ result -->
                    <div v-if="a.type === 'mcq'" class="text-sm">
                        <span class="text-gray-600">Your answer: </span>
                        <span :class="a.selected_option?.is_correct ? 'text-green-700 font-medium' : 'text-red-600 font-medium'">
                            {{ a.selected_option?.body ?? '(not answered)' }}
                        </span>
                        <span class="ml-2 text-xs text-gray-400">{{ a.points_awarded ?? 0 }} / {{ a.question.points }} pts</span>
                    </div>

                    <!-- Open text result -->
                    <div v-else class="space-y-2">
                        <div class="bg-gray-50 border rounded p-3 text-sm text-gray-700 whitespace-pre-wrap">{{ a.text_answer || '(no answer)' }}</div>
                        <div v-if="a.points_awarded !== null" class="text-sm">
                            <span class="text-gray-500">Score: </span>
                            <span class="font-medium text-gray-800">{{ a.points_awarded }} / {{ a.question.points }} pts</span>
                        </div>
                        <div v-if="a.reviewer_comment" class="text-sm">
                            <span class="text-gray-500 text-xs">Lecturer comment: </span>
                            <span class="text-gray-700 italic">{{ a.reviewer_comment }}</span>
                        </div>
                        <div v-if="a.points_awarded === null" class="text-xs text-orange-600">Pending review</div>
                    </div>
                </div>
            </div>

            <div class="flex justify-start">
                <Link :href="route('student.dashboard')" class="text-sm text-gray-500 hover:underline">← Back to Dashboard</Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
