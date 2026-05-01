<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'

defineProps({ classes: Array })
function destroy(id) {
    if (confirm('Delete this class?')) router.delete(route('lecturer.classes.destroy', id))
}
</script>

<template>
    <Head title="Classes" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-semibold text-gray-800">Classes</h2></template>
        <div class="py-8 max-w-5xl mx-auto px-4 space-y-4">
            <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-800 rounded px-4 py-2 text-sm">{{ $page.props.flash.success }}</div>
            <div class="flex justify-end">
                <Link :href="route('lecturer.classes.create')" class="bg-gray-900 text-white text-sm px-4 py-2 rounded hover:bg-gray-700">+ New Class</Link>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div v-if="classes.length === 0" class="col-span-2 text-sm text-gray-400">No classes yet.</div>
                <div v-for="c in classes" :key="c.id" class="bg-white border rounded-lg p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-semibold text-gray-800">{{ c.name }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ c.students }} students</p>
                        </div>
                        <div class="flex gap-2 text-sm">
                            <Link :href="route('lecturer.classes.show', c.id)" class="text-gray-500 hover:underline">Manage</Link>
                            <Link :href="route('lecturer.classes.edit', c.id)" class="text-gray-500 hover:underline">Edit</Link>
                            <button @click="destroy(c.id)" class="text-red-500 hover:underline">Delete</button>
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-1">
                        <span v-for="s in c.subjects" :key="s.id" class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ s.name }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
