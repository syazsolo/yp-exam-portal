<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'

defineProps({ subjects: Array })

function destroy(id) {
    if (confirm('Delete this subject?')) router.delete(route('lecturer.subjects.destroy', id))
}
</script>

<template>
    <Head title="Subjects" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-semibold text-gray-800">Subjects</h2></template>
        <div class="py-8 max-w-4xl mx-auto px-4 space-y-4">
            <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-800 rounded px-4 py-2 text-sm">{{ $page.props.flash.success }}</div>
            <div class="flex justify-end">
                <Link :href="route('lecturer.subjects.create')" class="bg-gray-900 text-white text-sm px-4 py-2 rounded hover:bg-gray-700">+ New Subject</Link>
            </div>
            <div class="bg-white border rounded-lg overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-400 uppercase tracking-wide">
                        <tr>
                            <th class="px-4 py-3 text-left">Name</th>
                            <th class="px-4 py-3 text-left">Description</th>
                            <th class="px-4 py-3 text-left">Classes</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="subjects.length === 0"><td colspan="4" class="px-4 py-6 text-center text-gray-400">No subjects yet.</td></tr>
                        <tr v-for="s in subjects" :key="s.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">{{ s.name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ s.description || '—' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ s.class_count }}</td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <Link :href="route('lecturer.subjects.edit', s.id)" class="text-sm text-gray-600 hover:underline">Edit</Link>
                                <button @click="destroy(s.id)" class="text-sm text-red-600 hover:underline">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
