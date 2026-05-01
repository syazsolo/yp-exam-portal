<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm, Link } from '@inertiajs/vue3'

const props = defineProps({ subject: Object })
const form  = useForm({ name: props.subject.name, description: props.subject.description ?? '' })
const submit = () => form.patch(route('lecturer.subjects.update', props.subject.id))
</script>

<template>
    <Head title="Edit Subject" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-semibold text-gray-800">Edit Subject</h2></template>
        <div class="py-8 max-w-lg mx-auto px-4">
            <div class="bg-white border rounded-lg p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input v-model="form.name" type="text" class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400" />
                    <p v-if="form.errors.name" class="text-red-600 text-xs mt-1">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea v-model="form.description" rows="3" class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400" />
                </div>
                <div class="flex gap-3 pt-2">
                    <button @click="submit" :disabled="form.processing" class="bg-gray-900 text-white text-sm px-4 py-2 rounded hover:bg-gray-700 disabled:opacity-50">Update</button>
                    <Link :href="route('lecturer.subjects.index')" class="text-sm text-gray-500 hover:underline self-center">Cancel</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
