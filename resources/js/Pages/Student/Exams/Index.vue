<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";

defineProps({ exams: Array });

function start(examId) {
    if (
        confirm(
            "Starting this exam begins the timer immediately. It cannot be paused, even if you leave or refresh.",
        )
    ) {
        router.post(route("student.exams.start", examId));
    }
}

function actionLabel(exam) {
    if (exam.attempt_state === "pending") return "Return to Exam";
    if (exam.attempt_state) return "View Result";

    return "Start Exam";
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
                <Link
                    v-if="e.session_id && e.attempt_state !== 'pending'"
                    :href="route('student.exam-sessions.show', e.session_id)"
                    class="rounded bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200"
                >
                    {{ actionLabel(e) }}
                </Link>
                <Link
                    v-else-if="e.session_id"
                    :href="route('student.exam-sessions.show', e.session_id)"
                    class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700"
                >
                    {{ actionLabel(e) }}
                </Link>
                <button
                    v-else
                    @click="start(e.id)"
                    class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700"
                >
                    {{ actionLabel(e) }}
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
