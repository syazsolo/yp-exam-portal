<script setup>
import DataTable from "@/Components/DataTable.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";

defineProps({ availableExams: Array, mySessions: Array });

const sessionColumns = [
    { key: "exam_title", label: "Exam", type: "primary" },
    { key: "subject", label: "Subject" },
    { key: "state", label: "Status", type: "status" },
    { key: "score_label", label: "Score" },
];

const sessionActions = [
    {
        name: "open",
        icon: "eye",
        label: (session) => (session.state === "pending" ? "Continue" : "View"),
        variant: (session) =>
            session.state === "pending" ? "primary" : "default",
        href: (session) => route("student.exam-sessions.show", session.id),
    },
];

function start(examId) {
    if (confirm("Start this exam? The timer will begin immediately.")) {
        router.post(route("student.exams.sessions.start", examId));
    }
}
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout title="Dashboard">
        <div class="mx-auto max-w-4xl space-y-8 px-4 py-8">
            <div
                v-if="$page.props.flash?.success"
                class="rounded border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800"
            >
                {{ $page.props.flash.success }}
            </div>

            <!-- available exams -->
            <div>
                <h3 class="mb-3 font-semibold text-gray-700">
                    Available Exams
                </h3>
                <div
                    v-if="availableExams.length === 0"
                    class="text-sm text-gray-400"
                >
                    No active exams assigned to your class right now.
                </div>
                <div class="space-y-3">
                    <div
                        v-for="e in availableExams"
                        :key="e.id"
                        class="flex items-center justify-between rounded-lg border bg-white p-4"
                    >
                        <div>
                            <p class="font-medium text-gray-800">
                                {{ e.title }}
                            </p>
                            <p class="mt-0.5 text-xs text-gray-400">
                                {{ e.subject }} - {{ e.time_limit }} min
                            </p>
                            <p
                                v-if="e.ends_at"
                                class="mt-0.5 text-xs text-red-500"
                            >
                                Closes
                                {{ new Date(e.ends_at).toLocaleString() }}
                            </p>
                        </div>
                        <button
                            @click="start(e.id)"
                            class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700"
                        >
                            Start Exam
                        </button>
                    </div>
                </div>
            </div>

            <!-- my sessions -->
            <div>
                <h3 class="mb-3 font-semibold text-gray-700">My Attempts</h3>
                <DataTable
                    :columns="sessionColumns"
                    :rows="mySessions"
                    :actions="sessionActions"
                    empty-message="No attempts yet."
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
