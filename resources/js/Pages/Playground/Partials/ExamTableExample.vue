<script setup>
import DataTable from "@/Components/DataTable.vue";

const emit = defineEmits(["action"]);

const rows = [
    {
        id: "exam-1",
        title: "Midterm Assessment",
        subject: "Mathematics",
        status: "draft",
        duration: 45,
        sessions: 0,
    },
    {
        id: "exam-2",
        title: "Final Lab Check",
        subject: "Computer Science",
        status: "active",
        duration: 60,
        sessions: 18,
    },
    {
        id: "exam-3",
        title: "Essay Review",
        subject: "English",
        status: "closed",
        duration: 30,
        sessions: 24,
    },
];

const columns = [
    {
        key: "title",
        label: "Exam",
        type: "link",
        href: () => route("playground.tables"),
    },
    { key: "subject", label: "Subject" },
    { key: "status", label: "Status", type: "status" },
    {
        key: "duration",
        label: "Duration",
        format: (value) => `${value} min`,
    },
    {
        key: "sessions",
        label: "Sessions",
        align: "right",
        cellClass: "tabular-nums",
    },
];

const actions = [
    {
        type: "edit",
        show: (exam) => exam.status !== "active",
    },
    {
        type: "delete",
        show: (exam) => exam.status !== "active",
    },
];

function report({ action, row }) {
    emit("action", `${action} on ${row.title}`);
}

function reportDelete(row) {
    emit("action", `delete on ${row.title}`);
}
</script>

<template>
    <section class="space-y-3">
        <h3 class="font-semibold text-gray-700">Exam Table</h3>
        <DataTable
            :columns="columns"
            :rows="rows"
            :actions="actions"
            empty-message="No exams in this playground."
            @action="report"
            @delete="reportDelete"
        />
    </section>
</template>
