<script setup>
import DataTable from "@/Components/DataTable.vue";

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
];

const actions = [{ type: "edit" }, { type: "delete" }];
</script>

<template>
    <section class="space-y-3">
        <h3 class="font-semibold text-gray-700">Empty State</h3>
        <DataTable
            :columns="columns"
            :rows="[]"
            :actions="actions"
            empty-message="The empty state is owned by DataTable."
        />
    </section>
</template>
