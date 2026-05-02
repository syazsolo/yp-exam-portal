<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm, Link } from '@inertiajs/vue3'

const props  = defineProps({ exam: Object, subjects: Array })
const form   = useForm({
    title:           props.exam.title,
    subject_id:      props.exam.subject_id,
    time_limit_minutes: props.exam.time_limit_minutes,
    starts_at:       props.exam.starts_at ? props.exam.starts_at.slice(0,16) : '',
    ends_at:         props.exam.ends_at   ? props.exam.ends_at.slice(0,16)   : '',
})
const submit = () => form.patch(route('lecturer.exams.update', props.exam.id))
</script>

<template>
    <Head title="Edit Exam" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-semibold text-gray-800">Edit Exam</h2></template>
        <div class="py-8 max-w-lg mx-auto px-4">
            <div class="bg-white border rounded-lg p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input v-model="form.title" type="text" class="w-full border rounded px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                    <select v-model="form.subject_id" class="w-full border rounded px-3 py-2 text-sm">
                        <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Time Limit (minutes)</label>
                    <input v-model="form.time_limit_minutes" type="number" min="1" class="w-full border rounded px-3 py-2 text-sm" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Starts At</label>
                        <input v-model="form.starts_at" type="datetime-local" class="w-full border rounded px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ends At</label>
                        <input v-model="form.ends_at" type="datetime-local" class="w-full border rounded px-3 py-2 text-sm" />
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button @click="submit" :disabled="form.processing" class="bg-gray-900 text-white text-sm px-4 py-2 rounded hover:bg-gray-700 disabled:opacity-50">Update</button>
                    <Link :href="route('lecturer.exams.show', exam.id)" class="text-sm text-gray-500 hover:underline self-center">Cancel</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
