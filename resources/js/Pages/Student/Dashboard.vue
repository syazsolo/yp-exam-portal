<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router, Link } from '@inertiajs/vue3'

defineProps({ availableExams: Array, mySessions: Array })

const statusCls = (s) => ({
    in_progress:  'bg-yellow-100 text-yellow-800',
    submitted:    'bg-blue-100 text-blue-800',
    under_review: 'bg-orange-100 text-orange-800',
    graded:       'bg-green-100 text-green-800',
}[s] ?? 'bg-gray-100 text-gray-600')

function start(examId) {
    if (confirm('Start this exam? The timer will begin immediately.')) {
        router.post(route('student.exams.start', examId))
    }
}
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-semibold text-gray-800">Dashboard</h2></template>
        <div class="py-8 max-w-4xl mx-auto px-4 space-y-8">
            <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-800 rounded px-4 py-2 text-sm">{{ $page.props.flash.success }}</div>

            <!-- available exams -->
            <div>
                <h3 class="font-semibold text-gray-700 mb-3">Available Exams</h3>
                <div v-if="availableExams.length === 0" class="text-sm text-gray-400">No active exams assigned to your class right now.</div>
                <div class="space-y-3">
                    <div v-for="e in availableExams" :key="e.id" class="bg-white border rounded-lg p-4 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-800">{{ e.title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ e.subject }} · {{ e.time_limit }} min</p>
                            <p v-if="e.ends_at" class="text-xs text-red-500 mt-0.5">Closes {{ new Date(e.ends_at).toLocaleString() }}</p>
                        </div>
                        <button @click="start(e.id)" class="bg-gray-900 text-white text-sm px-4 py-2 rounded hover:bg-gray-700">Start Exam</button>
                    </div>
                </div>
            </div>

            <!-- my sessions -->
            <div>
                <h3 class="font-semibold text-gray-700 mb-3">My Attempts</h3>
                <div class="bg-white border rounded-lg overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-400 uppercase tracking-wide">
                            <tr>
                                <th class="px-4 py-3 text-left">Exam</th>
                                <th class="px-4 py-3 text-left">Subject</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Score</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="mySessions.length === 0"><td colspan="5" class="px-4 py-6 text-center text-gray-400">No attempts yet.</td></tr>
                            <tr v-for="s in mySessions" :key="s.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">{{ s.exam_title }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ s.subject }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold uppercase" :class="statusCls(s.status)">{{ s.status.replace('_', ' ') }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ s.score_label }}</td>
                                <td class="px-4 py-3 text-right">
                                    <Link v-if="s.status === 'in_progress'" :href="route('student.sessions.show', s.id)" class="text-xs text-blue-600 hover:underline">Continue</Link>
                                    <Link v-else :href="route('student.sessions.result', s.id)" class="text-xs text-gray-500 hover:underline">View</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
