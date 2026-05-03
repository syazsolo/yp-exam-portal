<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";

defineProps({ exams: Array });

function start(examId) {
    if (confirm("Start this exam? The timer will begin immediately.")) {
        router.post(route("student.exams.sessions.start", examId));
    }
}
</script>

<template>
    <Head title="Available Exams" />
    <AuthenticatedLayout title="Available Exams">
        <div class="mx-auto max-w-3xl space-y-4 px-4 py-8">
            <div v-if="exams.length === 0" class="text-sm text-gray-400">
                No active exams right now.
            </div>
            <div
                v-for="e in exams"
                :key="e.id"
                class="flex items-center justify-between rounded-lg border bg-white p-5"
            >
                <div>
                    <p class="font-medium text-gray-800">{{ e.title }}</p>
                    <p class="mt-0.5 text-xs text-gray-400">
                        {{ e.subject }} · {{ e.time_limit }} min
                    </p>
                    <p v-if="e.ends_at" class="mt-0.5 text-xs text-red-500">
                        Closes {{ new Date(e.ends_at).toLocaleString() }}
                    </p>
                </div>
                <button
                    @click="start(e.id)"
                    class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700"
                >
                    Start
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
