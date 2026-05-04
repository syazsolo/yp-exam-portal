<script setup>
import DataTable from "@/Components/DataTable.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";

defineProps({ classes: Array });

const columns = [
    {
        key: "name",
        label: "Class",
        type: "link",
        href: (schoolClass) => route("lecturer.classes.show", schoolClass.id),
    },
    {
        key: "students",
        label: "Students",
        format: (value) => `${value} students`,
    },
    {
        key: "subjects",
        label: "Subjects",
        type: "tags",
        empty: "None",
    },
];
</script>

<template>
    <Head title="Classes" />
    <AuthenticatedLayout title="Classes">
        <div class="mx-auto max-w-5xl space-y-4 px-4 py-8">
            <DataTable
                :columns="columns"
                :rows="classes"
                :actions="[]"
                empty-message="No assigned classes yet."
            />
        </div>
    </AuthenticatedLayout>
</template>
