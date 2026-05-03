<script setup>
import DataTable from "@/Components/DataTable.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";

defineProps({ subjects: Array });

const columns = [
    { key: "name", label: "Name", type: "primary" },
    { key: "description", label: "Description", fallback: "None" },
    { key: "class_count", label: "Classes" },
];

const actions = [
    {
        type: "edit",
        href: (subject) => route("lecturer.subjects.edit", subject.id),
    },
    { type: "delete" },
];

function destroy(subject) {
    if (confirm("Delete this subject?"))
        router.delete(route("lecturer.subjects.destroy", subject.id));
}
</script>

<template>
    <Head title="Subjects" />
    <AuthenticatedLayout title="Subjects">
        <div class="mx-auto max-w-4xl space-y-4 px-4 py-8">
            <div
                v-if="$page.props.flash?.success"
                class="rounded border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800"
            >
                {{ $page.props.flash.success }}
            </div>
            <div class="flex justify-end">
                <Link
                    :href="route('lecturer.subjects.create')"
                    class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700"
                    >+ New Subject</Link
                >
            </div>
            <DataTable
                :columns="columns"
                :rows="subjects"
                :actions="actions"
                empty-message="No subjects yet."
                @delete="destroy"
            />
        </div>
    </AuthenticatedLayout>
</template>
