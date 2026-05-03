<script setup>
import DataTable from "@/Components/DataTable.vue";

const emit = defineEmits(["action"]);

const rows = [
    {
        id: "class-1",
        name: "Alpha",
        students: 28,
        subjects: [
            { id: "math", name: "Mathematics" },
            { id: "science", name: "Science" },
        ],
    },
    {
        id: "class-2",
        name: "Beta",
        students: 16,
        subjects: [],
    },
];

const columns = [
    { key: "name", label: "Class", type: "primary" },
    {
        key: "students",
        label: "Students",
        format: (value) => `${value} students`,
    },
    { key: "subjects", label: "Subjects", type: "tags" },
];

const actions = [
    { name: "manage", label: "Manage", icon: "settings", variant: "primary" },
    { type: "edit" },
    { type: "delete" },
];

function report({ action, row }) {
    emit("action", `${action} on ${row.name}`);
}

function reportDelete(row) {
    emit("action", `delete on ${row.name}`);
}
</script>

<template>
    <section class="space-y-3">
        <h3 class="font-semibold text-gray-700">Class Table</h3>
        <DataTable
            :columns="columns"
            :rows="rows"
            :actions="actions"
            empty-message="No classes in this playground."
            @action="report"
            @delete="reportDelete"
        />
    </section>
</template>
