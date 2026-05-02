<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, Link } from "@inertiajs/vue3";

defineProps({ availableExams: Array, mySessions: Array });

const statusCls = (s) =>
    ({
        pending: "bg-yellow-100 text-yellow-800",
        submitted: "bg-blue-100 text-blue-800",
        pending_review: "bg-orange-100 text-orange-800",
        scored: "bg-green-100 text-green-800",
        invalid: "bg-red-100 text-red-800",
    })[s] ?? "bg-gray-100 text-gray-600";

function start(examId) {
    if (confirm("Start this exam? The timer will begin immediately.")) {
        router.post(route("student.exams.sessions.start", examId));
    }
}
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <template #header
            ><h2 class="text-xl font-semibold text-gray-800">
                Dashboard
            </h2></template
        >
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
                                {{ e.subject }} · {{ e.time_limit }} min
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
                <div class="overflow-hidden rounded-lg border bg-white">
                    <table class="w-full text-sm">
                        <thead
                            class="bg-gray-50 text-xs uppercase tracking-wide text-gray-400"
                        >
                            <tr>
                                <th class="px-4 py-3 text-left">Exam</th>
                                <th class="px-4 py-3 text-left">Subject</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Score</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="mySessions.length === 0">
                                <td
                                    colspan="5"
                                    class="px-4 py-6 text-center text-gray-400"
                                >
                                    No attempts yet.
                                </td>
                            </tr>
                            <tr
                                v-for="s in mySessions"
                                :key="s.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-4 py-3 font-medium">
                                    {{ s.exam_title }}
                                </td>
                                <td class="px-4 py-3 text-gray-500">
                                    {{ s.subject }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="rounded px-2 py-0.5 text-xs font-semibold uppercase"
                                        :class="statusCls(s.state)"
                                        >{{ s.state.replace("_", " ") }}</span
                                    >
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ s.score_label }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link
                                        v-if="s.state === 'pending'"
                                        :href="
                                            route(
                                                'student.exam-sessions.show',
                                                s.id,
                                            )
                                        "
                                        class="text-xs text-blue-600 hover:underline"
                                        >Continue</Link
                                    >
                                    <Link
                                        v-else
                                        :href="
                                            route(
                                                'student.exam-sessions.show',
                                                s.id,
                                            )
                                        "
                                        class="text-xs text-gray-500 hover:underline"
                                        >View</Link
                                    >
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
