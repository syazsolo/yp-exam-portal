<script setup>
import DataTable from "@/Components/DataTable.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";

defineProps({ classes: Array });

const columns = [
    { key: "name", label: "Class", type: "primary" },
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

const actions = [
    {
        type: "view",
        label: "View",
        icon: "settings",
        href: (schoolClass) => route("lecturer.classes.show", schoolClass.id),
        variant: "primary",
    },
];
</script>

<template>
    <Head title="Classes" />
    <AuthenticatedLayout title="Classes">
        <div class="mx-auto max-w-5xl space-y-4 px-4 py-8">
            <div
                v-if="$page.props.flash?.success"
                class="rounded border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800"
            >
                {{ $page.props.flash.success }}
            </div>
            <DataTable
                :columns="columns"
                :rows="classes"
                :actions="actions"
                empty-message="No assigned classes yet."
            />
        </div>
    </AuthenticatedLayout>
</template>
