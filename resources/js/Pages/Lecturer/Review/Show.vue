<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm, router, Link } from '@inertiajs/vue3'

const props = defineProps({ session: Object })

function gradeForm(answer) {
    return useForm({
        points_awarded:   answer.points_awarded ?? 0,
        reviewer_comment: answer.reviewer_comment ?? '',
    })
}

const forms = props.session.answers
    .filter(a => a.type === 'open_text')
    .reduce((acc, a) => { acc[a.id] = gradeForm(a); return acc }, {})

function submitGrade(answerId) {
    forms[answerId].patch(route('lecturer.sessions.answers.grade', [props.session.id, answerId]))
}

function finalize() {
    if (confirm('Finalize grading for this session?')) {
        router.post(route('lecturer.sessions.finalize', props.session.id))
    }
}
</script>

<template>
    <Head title="Review Session" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('lecturer.exams.show', session.exam.id)" class="text-gray-400 hover:text-gray-600 text-sm">← {{ session.exam.title }}</Link>
                <h2 class="text-xl font-semibold text-gray-800">Review – {{ session.student }}</h2>
            </div>
        </template>
        <div class="py-8 max-w-3xl mx-auto px-4 space-y-4">
            <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-800 rounded px-4 py-2 text-sm">{{ $page.props.flash.success }}</div>

            <div v-for="(answer, i) in session.answers" :key="answer.id" class="bg-white border rounded-lg p-5 space-y-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-gray-400">Q{{ i + 1 }}</span>
                    <span class="text-xs bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded uppercase">{{ answer.type === 'mcq' ? 'MCQ' : 'Open Text' }}</span>
                    <span class="text-xs text-gray-400">max {{ answer.question.points }} pts</span>
                </div>
                <p class="text-sm font-medium text-gray-800">{{ answer.question.body }}</p>

                <!-- MCQ -->
                <div v-if="answer.type === 'mcq'">
                    <p class="text-sm text-gray-600">
                        Answer: <span :class="answer.selected_option?.is_correct ? 'text-green-700 font-medium' : 'text-red-600 font-medium'">
                            {{ answer.selected_option?.body ?? '(no answer)' }}
                        </span>
                        <span class="ml-2 text-xs text-gray-400">{{ answer.points_awarded }} / {{ answer.question.points }} pts</span>
                    </p>
                </div>

                <!-- Open text -->
                <div v-else class="space-y-3">
                    <div class="bg-gray-50 border rounded p-3 text-sm text-gray-700 whitespace-pre-wrap">{{ answer.text_answer || '(no answer provided)' }}</div>

                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                            <label class="text-xs font-medium text-gray-600 w-24">Points Awarded</label>
                            <input v-model="forms[answer.id].points_awarded" type="number"
                                :min="0" :max="answer.question.points"
                                class="w-20 border rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400" />
                            <span class="text-xs text-gray-400">/ {{ answer.question.points }}</span>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-600">Comment <span class="font-normal text-gray-400">(optional)</span></label>
                            <textarea v-model="forms[answer.id].reviewer_comment" rows="2"
                                class="mt-1 w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400" />
                        </div>
                        <button @click="submitGrade(answer.id)" :disabled="forms[answer.id].processing"
                            class="bg-gray-800 text-white text-xs px-3 py-1.5 rounded hover:bg-gray-700 disabled:opacity-50">
                            {{ answer.points_awarded !== null ? 'Update Grade' : 'Save Grade' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- finalize -->
            <div class="bg-white border rounded-lg p-5 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    <span v-if="session.all_reviewed" class="text-green-700 font-medium">✓ All answers reviewed.</span>
                    <span v-else class="text-yellow-700">Some open-text answers not yet graded.</span>
                </div>
                <button @click="finalize" :disabled="!session.all_reviewed"
                    class="bg-green-700 text-white text-sm px-4 py-2 rounded hover:bg-green-800 disabled:opacity-40 disabled:cursor-not-allowed">
                    Finalize & Grade Session
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
