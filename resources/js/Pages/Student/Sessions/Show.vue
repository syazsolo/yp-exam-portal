<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted, computed } from 'vue'

const props = defineProps({ session: Object, answeredMap: Object })

// countdown
const remaining = ref(0)
let timer = null

onMounted(() => {
    const deadline = new Date(props.session.deadline).getTime()
    function tick() {
        remaining.value = Math.max(0, Math.floor((deadline - Date.now()) / 1000))
        if (remaining.value === 0) {
            clearInterval(timer)
            router.post(route('student.sessions.submit', props.session.id))
        }
    }
    tick()
    timer = setInterval(tick, 1000)
})
onUnmounted(() => clearInterval(timer))

const timeLabel = computed(() => {
    const m = Math.floor(remaining.value / 60).toString().padStart(2, '0')
    const s = (remaining.value % 60).toString().padStart(2, '0')
    return `${m}:${s}`
})
const timerCls = computed(() => remaining.value < 60 ? 'text-red-600' : 'text-gray-700')

// answers — one form per question
const answers = ref({ ...props.answeredMap })

function saveAnswer(question) {
    const payload = { question_id: question.id }
    if (question.type === 'mcq') {
        payload.selected_option_id = answers.value[question.id]?.selected_option_id ?? null
    } else {
        payload.text_answer = answers.value[question.id]?.text_answer ?? ''
    }
    router.post(route('student.sessions.answers.save', props.session.id), payload, { preserveScroll: true })
}

function submit() {
    if (confirm('Submit exam? You cannot change answers after this.')) {
        router.post(route('student.sessions.submit', props.session.id))
    }
}

function selectOption(questionId, optionId) {
    if (!answers.value[questionId]) answers.value[questionId] = {}
    answers.value[questionId].selected_option_id = optionId
}
</script>

<template>
    <Head :title="session.exam.title" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h2 class="text-xl font-semibold text-gray-800">{{ session.exam.title }}</h2>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500">Time remaining:</span>
                    <span class="text-2xl font-mono font-bold" :class="timerCls">{{ timeLabel }}</span>
                </div>
            </div>
        </template>

        <div class="py-8 max-w-3xl mx-auto px-4 space-y-6">
            <div v-for="(q, i) in session.exam.questions" :key="q.id" class="bg-white border rounded-lg p-5 space-y-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-gray-400">Q{{ i + 1 }}</span>
                    <span class="text-xs bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">{{ q.points }} pt{{ q.points !== 1 ? 's' : '' }}</span>
                    <span v-if="answers[q.id]" class="text-xs text-green-600">✓ answered</span>
                </div>
                <p class="text-sm font-medium text-gray-800">{{ q.body }}</p>

                <!-- MCQ -->
                <div v-if="q.type === 'mcq'" class="space-y-2">
                    <label v-for="opt in q.options" :key="opt.id"
                        class="flex items-center gap-3 p-2 rounded border cursor-pointer text-sm"
                        :class="answers[q.id]?.selected_option_id === opt.id ? 'border-gray-800 bg-gray-50' : 'border-gray-200 hover:bg-gray-50'">
                        <input type="radio" :name="`q_${q.id}`" :value="opt.id"
                            :checked="answers[q.id]?.selected_option_id === opt.id"
                            @change="selectOption(q.id, opt.id)" class="shrink-0" />
                        {{ opt.body }}
                    </label>
                </div>

                <!-- Open text -->
                <div v-else>
                    <textarea :value="answers[q.id]?.text_answer ?? ''"
                        @input="e => { if (!answers[q.id]) answers[q.id] = {}; answers[q.id].text_answer = e.target.value }"
                        rows="4" placeholder="Type your answer here..."
                        class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400" />
                </div>

                <button @click="saveAnswer(q)" class="text-xs bg-gray-100 text-gray-700 px-3 py-1 rounded hover:bg-gray-200">Save Answer</button>
            </div>

            <!-- submit -->
            <div class="flex justify-end">
                <button @click="submit" class="bg-green-700 text-white text-sm px-6 py-2 rounded hover:bg-green-800">Submit Exam</button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
