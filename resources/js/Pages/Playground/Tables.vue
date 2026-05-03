<script setup>
import { ref } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ClassTableExample from "@/Pages/Playground/Partials/ClassTableExample.vue";
import EmptyTableExample from "@/Pages/Playground/Partials/EmptyTableExample.vue";
import ExamTableExample from "@/Pages/Playground/Partials/ExamTableExample.vue";
import { Head } from "@inertiajs/vue3";

const lastAction = ref("No action yet");
</script>

<template>
    <Head title="Table Playground" />
    <AuthenticatedLayout title="Table Playground">
        <div class="mx-auto max-w-6xl space-y-8 px-4 py-8">
            <div
                class="rounded-lg border bg-white p-5 text-sm text-ink-soft shadow-[var(--shadow-paper)]"
            >
                <p class="font-semibold text-ink">DataTable API</p>
                <p class="mt-1">
                    Pass <code>columns</code>, <code>rows</code>, and optional
                    <code>actions</code>. The component renders the table shell,
                    empty state, status pills, tag groups, links, and standard
                    action buttons.
                </p>
                <p class="mt-3 text-xs uppercase tracking-label text-ink-mute">
                    {{ lastAction }}
                </p>
            </div>

            <ExamTableExample @action="lastAction = $event" />
            <ClassTableExample @action="lastAction = $event" />
            <EmptyTableExample />
        </div>
    </AuthenticatedLayout>
</template>
