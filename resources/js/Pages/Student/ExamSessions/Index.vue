<script setup>
import DataTable from "@/Components/DataTable.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";

defineProps({ sessions: Array });

const columns = [
    {
        key: "exam_title",
        label: "Exam",
        type: "link",
        href: (session) => route("student.exam-sessions.show", session.id),
    },
    { key: "subject", label: "Subject" },
    { key: "state", label: "Status", type: "status" },
    { key: "score_label", label: "Score" },
];
</script>

<template>
    <Head title="Exam History" />
    <AuthenticatedLayout title="My Exam Sessions">
        <div class="mx-auto max-w-4xl px-4 py-8">
            <DataTable
                :columns="columns"
                :rows="sessions"
                :actions="[]"
                empty-message="No past sessions."
            />
        </div>
    </AuthenticatedLayout>
</template>
