<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm, router, Link } from '@inertiajs/vue3'

const props      = defineProps({ schoolClass: Object, subjects: Array })
const addForm    = useForm({ email: '' })
const addStudent = () => addForm.post(route('lecturer.classes.students.add', props.schoolClass.id), { onSuccess: () => addForm.reset() })

function removeStudent(userId) {
    if (confirm('Remove student?')) router.delete(route('lecturer.classes.students.remove', [props.schoolClass.id, userId]))
}
</script>

<template>
    <Head :title="schoolClass.name" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('lecturer.classes.index')" class="text-gray-400 hover:text-gray-600 text-sm">← Classes</Link>
                <h2 class="text-xl font-semibold text-gray-800">{{ schoolClass.name }}</h2>
            </div>
        </template>
        <div class="py-8 max-w-4xl mx-auto px-4 space-y-6">
            <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-800 rounded px-4 py-2 text-sm">{{ $page.props.flash.success }}</div>

            <!-- subjects -->
            <div class="bg-white border rounded-lg p-5">
                <h3 class="font-semibold text-gray-700 mb-3">Subjects</h3>
                <div class="flex flex-wrap gap-2">
                    <span v-for="s in schoolClass.subjects" :key="s.id" class="bg-gray-100 text-gray-700 text-sm px-3 py-1 rounded">{{ s.name }}</span>
                    <span v-if="schoolClass.subjects.length === 0" class="text-sm text-gray-400">None assigned.</span>
                </div>
            </div>

            <!-- add student -->
            <div class="bg-white border rounded-lg p-5">
                <h3 class="font-semibold text-gray-700 mb-3">Add Student by Email</h3>
                <div class="flex gap-2">
                    <input v-model="addForm.email" type="email" placeholder="student@example.com"
                        class="flex-1 border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400" />
                    <button @click="addStudent" :disabled="addForm.processing"
                        class="bg-gray-900 text-white text-sm px-4 py-2 rounded hover:bg-gray-700 disabled:opacity-50">Add</button>
                </div>
                <p v-if="addForm.errors.email" class="text-red-600 text-xs mt-1">{{ addForm.errors.email }}</p>
            </div>

            <!-- students list -->
            <div class="bg-white border rounded-lg overflow-hidden">
                <div class="px-5 py-3 border-b bg-gray-50">
                    <h3 class="font-semibold text-gray-700">Students ({{ schoolClass.students.length }})</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="text-xs text-gray-400 uppercase tracking-wide bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left">Name</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="schoolClass.students.length === 0"><td colspan="3" class="px-4 py-6 text-center text-gray-400">No students yet.</td></tr>
                        <tr v-for="s in schoolClass.students" :key="s.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">{{ s.name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ s.email }}</td>
                            <td class="px-4 py-3 text-right">
                                <button @click="removeStudent(s.id)" class="text-red-500 text-xs hover:underline">Remove</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
