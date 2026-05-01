<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm, Link } from '@inertiajs/vue3'

const props  = defineProps({ subjects: Array })
const form   = useForm({ title: '', subject_id: '', time_limit_mins: 30, starts_at: '', ends_at: '' })
const submit = () => form.post(route('lecturer.exams.store'))
</script>

<template>
    <Head title="New Exam" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-semibold text-gray-800">New Exam</h2></template>
        <div class="py-8 max-w-lg mx-auto px-4">
            <div class="bg-white border rounded-lg p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input v-model="form.title" type="text" class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400" />
                    <p v-if="form.errors.title" class="text-red-600 text-xs mt-1">{{ form.errors.title }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                    <select v-model="form.subject_id" class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
                        <option value="">— Select —</option>
                        <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                    <p v-if="form.errors.subject_id" class="text-red-600 text-xs mt-1">{{ form.errors.subject_id }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Time Limit (minutes)</label>
                    <input v-model="form.time_limit_mins" type="number" min="1" max="300" class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Starts At</label>
                        <input v-model="form.starts_at" type="datetime-local" class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400" />
                        <p v-if="form.errors.starts_at" class="text-red-600 text-xs mt-1">{{ form.errors.starts_at }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ends At</label>
                        <input v-model="form.ends_at" type="datetime-local" class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400" />
                        <p v-if="form.errors.ends_at" class="text-red-600 text-xs mt-1">{{ form.errors.ends_at }}</p>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button @click="submit" :disabled="form.processing" class="bg-gray-900 text-white text-sm px-4 py-2 rounded hover:bg-gray-700 disabled:opacity-50">Create & Add Questions</button>
                    <Link :href="route('lecturer.exams.index')" class="text-sm text-gray-500 hover:underline self-center">Cancel</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
