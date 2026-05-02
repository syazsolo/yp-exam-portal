<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";

defineProps({ exams: Array });

const statusCls = (s) =>
    ({
        active: "bg-green-100 text-green-800",
        closed: "bg-red-100 text-red-800",
        draft: "bg-gray-100 text-gray-600",
    })[s] ?? "bg-gray-100 text-gray-600";

function destroy(id) {
    if (confirm("Delete exam?"))
        router.delete(route("lecturer.exams.destroy", id));
}
</script>

<template>
    <Head title="Exams" />
    <AuthenticatedLayout>
        <template #header
            ><h2 class="text-xl font-semibold text-gray-800">
                Exams
            </h2></template
        >
        <div class="mx-auto max-w-5xl space-y-4 px-4 py-8">
            <div
                v-if="$page.props.flash?.success"
                class="rounded border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800"
            >
                {{ $page.props.flash.success }}
            </div>
            <div class="flex justify-end">
                <Link
                    :href="route('lecturer.exams.create')"
                    class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700"
                    >+ New Exam</Link
                >
            </div>
            <div class="overflow-hidden rounded-lg border bg-white">
                <table class="w-full text-sm">
                    <thead
                        class="bg-gray-50 text-xs uppercase tracking-wide text-gray-400"
                    >
                        <tr>
                            <th class="px-4 py-3 text-left">Title</th>
                            <th class="px-4 py-3 text-left">Subject</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Duration</th>
                            <th class="px-4 py-3 text-left">Sessions</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="exams.length === 0">
                            <td
                                colspan="6"
                                class="px-4 py-6 text-center text-gray-400"
                            >
                                No exams yet.
                            </td>
                        </tr>
                        <tr
                            v-for="e in exams"
                            :key="e.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-4 py-3">
                                <Link
                                    :href="route('lecturer.exams.show', e.id)"
                                    class="font-medium hover:underline"
                                    >{{ e.title }}</Link
                                >
                            </td>
                            <td class="px-4 py-3 text-gray-500">
                                {{ e.subject }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded px-2 py-0.5 text-xs font-semibold uppercase"
                                    :class="statusCls(e.status)"
                                    >{{ e.status }}</span
                                >
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ e.time_limit }}min
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ e.sessions_count }}
                            </td>
                            <td class="space-x-2 px-4 py-3 text-right">
                                <Link
                                    v-if="e.status !== 'active'"
                                    :href="route('lecturer.exams.edit', e.id)"
                                    class="text-xs text-gray-500 hover:underline"
                                    >Edit</Link
                                >
                                <button
                                    v-if="e.status !== 'active'"
                                    @click="destroy(e.id)"
                                    class="text-xs text-red-500 hover:underline"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
