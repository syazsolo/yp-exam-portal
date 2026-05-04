<script setup>
import DataTable from "@/Components/DataTable.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";

defineProps({ schoolClass: Object });

const studentColumns = [
    { key: "name", label: "Name", type: "primary" },
    { key: "email", label: "Email" },
];
</script>

<template>
    <Head :title="schoolClass.name" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('lecturer.classes.index')"
                    class="text-sm text-gray-400 hover:text-gray-600"
                    >&lt; Classes</Link
                >
                <h2 class="text-xl font-semibold text-gray-800">
                    {{ schoolClass.name }}
                </h2>
            </div>
        </template>
        <div class="mx-auto max-w-4xl space-y-6 px-4 py-8">
            <!-- subjects -->
            <div class="rounded-lg border bg-white p-5">
                <h3 class="mb-3 font-semibold text-gray-700">Subjects</h3>
                <div class="flex flex-wrap gap-2">
                    <span
                        v-for="s in schoolClass.subjects"
                        :key="s.id"
                        class="rounded bg-gray-100 px-3 py-1 text-sm text-gray-700"
                        >{{ s.name }}</span
                    >
                    <span
                        v-if="schoolClass.subjects.length === 0"
                        class="text-sm text-gray-400"
                        >None assigned.</span
                    >
                </div>
            </div>

            <!-- students list -->
            <DataTable
                :columns="studentColumns"
                :rows="schoolClass.students"
                empty-message="No students yet."
            >
                <template #caption>
                    <h3 class="font-semibold text-gray-700">
                        Students ({{ schoolClass.students.length }})
                    </h3>
                </template>
            </DataTable>
        </div>
    </AuthenticatedLayout>
</template>
