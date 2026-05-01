<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
    stats: Object,
    recentExams: Array,
    classes: Array,
    pendingReviews: Number,
})

const statusClass = (s) => ({
    active: 'bg-green-100 text-green-800',
    closed: 'bg-red-100 text-red-800',
    draft:  'bg-gray-100 text-gray-600',
}[s] ?? 'bg-gray-100 text-gray-600')
</script>

<template>
    <Head title="Overview" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-semibold text-gray-800">Overview</h2></template>

        <div class="py-8 max-w-6xl mx-auto px-4 space-y-8">

            <!-- flash -->
            <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-800 rounded px-4 py-2 text-sm">
                {{ $page.props.flash.success }}
            </div>

            <!-- pending reviews banner -->
            <div v-if="pendingReviews > 0" class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded px-4 py-2 text-sm">
                {{ pendingReviews }} session(s) awaiting open-text review.
            </div>

            <!-- stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="(item, key) in [
                    { label: 'Active Exams', value: stats.active_exams, sub: 'Currently running' },
                    { label: 'Total Exams',  value: stats.total_exams,  sub: 'All time' },
                    { label: 'Classes',      value: stats.classes,      sub: 'Active groups' },
                    { label: 'Students',     value: stats.students,     sub: 'Across all classes' },
                ]" :key="key" class="bg-white border rounded-lg p-5">
                    <p class="text-xs uppercase tracking-wide text-gray-400">{{ item.label }}</p>
                    <p class="text-4xl font-bold mt-1" :class="key === 0 ? 'text-red-800' : 'text-gray-900'">{{ item.value }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ item.sub }}</p>
                </div>
            </div>

            <!-- recent exams -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-700">Recent Exams</h3>
                    <Link :href="route('lecturer.exams.index')" class="text-sm text-gray-500 hover:text-gray-700">View all →</Link>
                </div>
                <div class="bg-white border rounded-lg overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-400 uppercase tracking-wide">
                            <tr>
                                <th class="px-4 py-3 text-left">Title</th>
                                <th class="px-4 py-3 text-left">Subject</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Duration</th>
                                <th class="px-4 py-3 text-left">Sessions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="recentExams.length === 0">
                                <td colspan="5" class="px-4 py-6 text-center text-gray-400">No exams yet.</td>
                            </tr>
                            <tr v-for="exam in recentExams" :key="exam.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <Link :href="route('lecturer.exams.show', exam.id)" class="font-medium hover:underline">{{ exam.title }}</Link>
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ exam.subject }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold uppercase" :class="statusClass(exam.status)">{{ exam.status }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ exam.time_limit }}min</td>
                                <td class="px-4 py-3 text-gray-600">{{ exam.sessions_count }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- classes -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-700">Classes</h3>
                    <Link :href="route('lecturer.classes.index')" class="text-sm text-gray-500 hover:text-gray-700">Manage →</Link>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div v-if="classes.length === 0" class="col-span-4 text-sm text-gray-400">
                        No classes yet. <Link :href="route('lecturer.classes.create')" class="underline">Create one</Link>.
                    </div>
                    <div v-for="c in classes" :key="c.id" class="bg-white border rounded-lg p-4">
                        <p class="font-semibold text-gray-800">{{ c.name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ c.students }} students</p>
                        <div class="mt-2 flex flex-wrap gap-1">
                            <span v-for="s in c.subjects" :key="s" class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ s }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
