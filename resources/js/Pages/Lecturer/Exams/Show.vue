<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'

const props = defineProps({ exam: Object, sessions: Array })

const statusCls = (s) => ({
    active:       'bg-green-100 text-green-800',
    closed:       'bg-red-100 text-red-800',
    draft:        'bg-gray-100 text-gray-600',
    pending:      'bg-yellow-100 text-yellow-800',
    submitted:    'bg-blue-100 text-blue-800',
    pending_review: 'bg-orange-100 text-orange-800',
    scored:       'bg-green-100 text-green-800',
    invalid:      'bg-red-100 text-red-800',
}[s] ?? 'bg-gray-100 text-gray-600')

function destroyQuestion(qid) {
    if (confirm('Delete question?')) router.delete(route('lecturer.questions.destroy', qid))
}
</script>

<template>
    <Head :title="exam.title" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('lecturer.exams.index')" class="text-gray-400 hover:text-gray-600 text-sm">← Exams</Link>
                <h2 class="text-xl font-semibold text-gray-800">{{ exam.title }}</h2>
                <span class="px-2 py-0.5 rounded text-xs font-semibold uppercase" :class="statusCls(exam.status)">{{ exam.status }}</span>
            </div>
        </template>

        <div class="py-8 max-w-5xl mx-auto px-4 space-y-6">
            <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-800 rounded px-4 py-2 text-sm">{{ $page.props.flash.success }}</div>

            <!-- meta -->
            <div class="bg-white border rounded-lg p-5 flex flex-wrap gap-6 text-sm text-gray-600">
                <div><span class="text-gray-400 text-xs uppercase">Subject</span><p class="font-medium mt-0.5">{{ exam.subject }}</p></div>
                <div><span class="text-gray-400 text-xs uppercase">Duration</span><p class="font-medium mt-0.5">{{ exam.time_limit }} min</p></div>
                <div><span class="text-gray-400 text-xs uppercase">Starts</span><p class="font-medium mt-0.5">{{ exam.starts_at ? new Date(exam.starts_at).toLocaleString() : '—' }}</p></div>
                <div><span class="text-gray-400 text-xs uppercase">Ends</span><p class="font-medium mt-0.5">{{ exam.ends_at ? new Date(exam.ends_at).toLocaleString() : '—' }}</p></div>
                <div class="ml-auto flex gap-2 items-center">
                    <Link v-if="exam.status !== 'active'" :href="route('lecturer.exams.edit', exam.id)" class="bg-gray-100 text-gray-700 text-xs px-3 py-1.5 rounded hover:bg-gray-200">Edit Exam</Link>
                </div>
            </div>

            <!-- questions -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-700">Questions ({{ exam.questions.length }})</h3>
                    <Link :href="route('lecturer.exams.questions.create', exam.id)" class="bg-gray-900 text-white text-xs px-3 py-1.5 rounded hover:bg-gray-700">+ Add Question</Link>
                </div>
                <div class="space-y-3">
                    <div v-if="exam.questions.length === 0" class="text-sm text-gray-400">No questions yet.</div>
                    <div v-for="(q, i) in exam.questions" :key="q.id" class="bg-white border rounded-lg p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-semibold text-gray-400">Q{{ i + 1 }}</span>
                                    <span class="text-xs bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded uppercase">{{ q.type === 'mcq' ? 'MCQ' : 'Open Text' }}</span>
                                    <span class="text-xs text-gray-400">{{ q.weight }} pt{{ q.weight !== 1 ? 's' : '' }}</span>
                                </div>
                                <p class="text-sm text-gray-800">{{ q.text }}</p>
                                <ul v-if="q.type === 'mcq'" class="mt-2 space-y-1">
                                    <li v-for="o in q.options" :key="o.id" class="text-xs flex items-center gap-1.5"
                                        :class="o.is_correct ? 'text-green-700 font-medium' : 'text-gray-500'">
                                        <span>{{ o.is_correct ? '✓' : '○' }}</span> {{ o.body }}
                                    </li>
                                </ul>
                            </div>
                            <div class="flex gap-2 shrink-0">
                                <Link :href="route('lecturer.questions.edit', q.id)" class="text-xs text-gray-500 hover:underline">Edit</Link>
                                <button @click="destroyQuestion(q.id)" class="text-xs text-red-500 hover:underline">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- sessions -->
            <div>
                <h3 class="font-semibold text-gray-700 mb-3">Sessions ({{ sessions.length }})</h3>
                <div class="bg-white border rounded-lg overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-400 uppercase tracking-wide">
                            <tr>
                                <th class="px-4 py-3 text-left">Student</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Score</th>
                                <th class="px-4 py-3 text-left">Submitted</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="sessions.length === 0"><td colspan="5" class="px-4 py-6 text-center text-gray-400">No sessions yet.</td></tr>
                            <tr v-for="s in sessions" :key="s.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium">{{ s.student }}</td>
                                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs font-semibold uppercase" :class="statusCls(s.state)">{{ s.state.replace('_', ' ') }}</span></td>
                                <td class="px-4 py-3 text-gray-600">{{ s.score_label }}</td>
                                <td class="px-4 py-3 text-gray-500 text-xs">{{ s.submitted_at ? new Date(s.submitted_at).toLocaleString() : '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <Link v-if="s.state === 'pending_review'" :href="route('lecturer.sessions.review', s.id)" class="text-xs text-blue-600 hover:underline">Review</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
