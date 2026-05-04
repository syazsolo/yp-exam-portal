<script setup>
import DataTable from "@/Components/DataTable.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";

defineProps({ exams: Array });

const columns = [
    {
        key: "title",
        label: "Title",
        type: "link",
        href: (exam) => route("lecturer.exams.show", exam.id),
    },
    { key: "subject", label: "Subject" },
    { key: "status", label: "Status", type: "status" },
    {
        key: "time_limit",
        label: "Duration",
        format: (value) => `${value}min`,
    },
    { key: "sessions_count", label: "Sessions" },
];

const actions = [
    {
        type: "edit",
        href: (exam) => route("lecturer.exams.edit", exam.id),
        show: (exam) => exam.status !== "active",
    },
    {
        type: "delete",
        show: (exam) => exam.status !== "active",
    },
];

function destroy(exam) {
    if (confirm("Delete exam?"))
        router.delete(route("lecturer.exams.destroy", exam.id));
}
</script>

<template>
    <Head title="Exams" />
    <AuthenticatedLayout title="Exams">
        <div class="mx-auto max-w-5xl space-y-4 px-4 py-8">
            <div class="flex justify-end">
                <Link
                    :href="route('lecturer.exams.create')"
                    class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700"
                    >+ New Exam</Link
                >
            </div>
            <DataTable
                :columns="columns"
                :rows="exams"
                :actions="actions"
                empty-message="No exams yet."
                @delete="destroy"
            />
        </div>
    </AuthenticatedLayout>
</template>
