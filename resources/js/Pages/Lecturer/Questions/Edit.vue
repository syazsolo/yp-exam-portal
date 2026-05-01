<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm, Link } from '@inertiajs/vue3'

const props = defineProps({ question: Object })
const form  = useForm({
    body:           props.question.body,
    points:         props.question.points,
    options:        props.question.options.map(o => ({ body: o.body })),
    correct_option: props.question.options.findIndex(o => o.is_correct),
})

function addOption()     { form.options.push({ body: '' }) }
function removeOption(i) { if (form.options.length > 2) form.options.splice(i, 1) }
function submit()        { form.patch(route('lecturer.questions.update', props.question.id)) }
</script>

<template>
    <Head title="Edit Question" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('lecturer.exams.show', question.exam_id)" class="text-gray-400 hover:text-gray-600 text-sm">← Exam</Link>
                <h2 class="text-xl font-semibold text-gray-800">Edit Question</h2>
            </div>
        </template>
        <div class="py-8 max-w-2xl mx-auto px-4">
            <div class="bg-white border rounded-lg p-6 space-y-5">
                <div class="text-xs text-gray-400 uppercase tracking-wide">Type: {{ question.type === 'mcq' ? 'Multiple Choice' : 'Open Text' }}</div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Question</label>
                    <textarea v-model="form.body" rows="3" class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Points</label>
                    <input v-model="form.points" type="number" min="1" class="w-32 border rounded px-3 py-2 text-sm" />
                </div>

                <div v-if="question.type === 'mcq'">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Options</label>
                    <div class="space-y-2">
                        <div v-for="(opt, i) in form.options" :key="i" class="flex items-center gap-2">
                            <input type="radio" :value="i" v-model="form.correct_option" />
                            <input v-model="opt.body" type="text" :placeholder="`Option ${i+1}`"
                                class="flex-1 border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400" />
                            <button v-if="form.options.length > 2" @click="removeOption(i)" class="text-red-400 text-xs">✕</button>
                        </div>
                    </div>
                    <button @click="addOption" class="mt-2 text-xs text-gray-500 hover:underline">+ Add option</button>
                </div>

                <div class="flex gap-3 pt-2">
                    <button @click="submit" :disabled="form.processing" class="bg-gray-900 text-white text-sm px-4 py-2 rounded hover:bg-gray-700 disabled:opacity-50">Update</button>
                    <Link :href="route('lecturer.exams.show', question.exam_id)" class="text-sm text-gray-500 hover:underline self-center">Cancel</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
