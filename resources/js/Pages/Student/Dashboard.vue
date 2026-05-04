<script setup>
import DataTable from "@/Components/DataTable.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";

defineProps({ availableExams: Array, mySessions: Array });

const sessionColumns = [
    {
        key: "exam_title",
        label: "Exam",
        type: "link",
        href: (session) => route("student.exam-sessions.show", session.id),
    },
    { key: "subject", label: "Subject" },
    { key: "state", label: "Status", type: "status" },
    { key: "score_label", label: "Score" },
];

function start(examId) {
    if (
        confirm(
            "Starting this exam begins the timer immediately. It cannot be paused, even if you leave or refresh.",
        )
    ) {
        router.post(route("student.exams.start", examId));
    }
}

function examActionLabel(exam) {
    if (exam.attempt_state === "pending") return "Return to Exam";
    if (exam.attempt_state) return "View Result";

    return "Start Exam";
}
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout title="Dashboard">
        <div class="w-full space-y-8 px-5 py-6 sm:px-9 sm:py-8">
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
                        class="flex flex-col gap-4 rounded-lg border bg-white p-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="min-w-0">
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
                        <Link
                            v-if="e.session_id && e.attempt_state !== 'pending'"
                            :href="
                                route(
                                    'student.exam-sessions.show',
                                    e.session_id,
                                )
                            "
                            class="inline-flex w-full items-center justify-center rounded bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 sm:w-auto"
                        >
                            {{ examActionLabel(e) }}
                        </Link>
                        <Link
                            v-else-if="e.session_id"
                            :href="
                                route(
                                    'student.exam-sessions.show',
                                    e.session_id,
                                )
                            "
                            class="inline-flex w-full items-center justify-center rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700 sm:w-auto"
                        >
                            {{ examActionLabel(e) }}
                        </Link>
                        <button
                            v-else
                            @click="start(e.id)"
                            class="inline-flex w-full items-center justify-center rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700 sm:w-auto"
                        >
                            {{ examActionLabel(e) }}
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
                    :actions="[]"
                    empty-message="No attempts yet."
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
