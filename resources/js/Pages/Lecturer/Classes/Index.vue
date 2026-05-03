<script setup>
import DataTable from "@/Components/DataTable.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";

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
        type: "manage",
        label: "Manage",
        icon: "settings",
        href: (schoolClass) => route("lecturer.classes.show", schoolClass.id),
        variant: "primary",
    },
    {
        type: "edit",
        href: (schoolClass) => route("lecturer.classes.edit", schoolClass.id),
    },
    { type: "delete" },
];

function destroy(schoolClass) {
    if (confirm("Delete this class?"))
        router.delete(route("lecturer.classes.destroy", schoolClass.id));
}
</script>

<template>
    <Head title="Classes" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">Classes</h2>
        </template>
        <div class="mx-auto max-w-5xl space-y-4 px-4 py-8">
            <div
                v-if="$page.props.flash?.success"
                class="rounded border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800"
            >
                {{ $page.props.flash.success }}
            </div>
            <div class="flex justify-end">
                <Link
                    :href="route('lecturer.classes.create')"
                    class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700"
                    >+ New Class</Link
                >
            </div>
            <DataTable
                :columns="columns"
                :rows="classes"
                :actions="actions"
                empty-message="No classes yet."
                @delete="destroy"
            />
        </div>
    </AuthenticatedLayout>
</template>
