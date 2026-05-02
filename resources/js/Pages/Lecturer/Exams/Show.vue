<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";

const props = defineProps({ exam: Object, sessions: Array });

const statusCls = (s) =>
    ({
        active: "bg-green-100 text-green-800",
        closed: "bg-red-100 text-red-800",
        draft: "bg-gray-100 text-gray-600",
        pending: "bg-yellow-100 text-yellow-800",
        submitted: "bg-blue-100 text-blue-800",
        pending_review: "bg-orange-100 text-orange-800",
        scored: "bg-green-100 text-green-800",
        invalid: "bg-red-100 text-red-800",
    })[s] ?? "bg-gray-100 text-gray-600";

function destroyQuestion(qid) {
    if (confirm("Delete question?"))
        router.delete(route("lecturer.questions.destroy", qid));
}
</script>

<template>
    <Head :title="exam.title" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('lecturer.exams.index')"
                    class="text-sm text-gray-400 hover:text-gray-600"
                    >← Exams</Link
                >
                <h2 class="text-xl font-semibold text-gray-800">
                    {{ exam.title }}
                </h2>
                <span
                    class="rounded px-2 py-0.5 text-xs font-semibold uppercase"
                    :class="statusCls(exam.status)"
                    >{{ exam.status }}</span
                >
            </div>
        </template>

        <div class="mx-auto max-w-5xl space-y-6 px-4 py-8">
            <div
                v-if="$page.props.flash?.success"
                class="rounded border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800"
            >
                {{ $page.props.flash.success }}
            </div>

            <!-- meta -->
            <div
                class="flex flex-wrap gap-6 rounded-lg border bg-white p-5 text-sm text-gray-600"
            >
                <div>
                    <span class="text-xs uppercase text-gray-400">Subject</span>
                    <p class="mt-0.5 font-medium">{{ exam.subject }}</p>
                </div>
                <div>
                    <span class="text-xs uppercase text-gray-400"
                        >Duration</span
                    >
                    <p class="mt-0.5 font-medium">{{ exam.time_limit }} min</p>
                </div>
                <div>
                    <span class="text-xs uppercase text-gray-400">Starts</span>
                    <p class="mt-0.5 font-medium">
                        {{
                            exam.starts_at
                                ? new Date(exam.starts_at).toLocaleString()
                                : "—"
                        }}
                    </p>
                </div>
                <div>
                    <span class="text-xs uppercase text-gray-400">Ends</span>
                    <p class="mt-0.5 font-medium">
                        {{
                            exam.ends_at
                                ? new Date(exam.ends_at).toLocaleString()
                                : "—"
                        }}
                    </p>
                </div>
                <div class="ml-auto flex items-center gap-2">
                    <Link
                        v-if="exam.status !== 'active'"
                        :href="route('lecturer.exams.edit', exam.id)"
                        class="rounded bg-gray-100 px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-200"
                        >Edit Exam</Link
                    >
                </div>
            </div>

            <!-- questions -->
            <div>
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-700">
                        Questions ({{ exam.questions.length }})
                    </h3>
                    <Link
                        :href="
                            route('lecturer.exams.questions.create', exam.id)
                        "
                        class="rounded bg-gray-900 px-3 py-1.5 text-xs text-white hover:bg-gray-700"
                        >+ Add Question</Link
                    >
                </div>
                <div class="space-y-3">
                    <div
                        v-if="exam.questions.length === 0"
                        class="text-sm text-gray-400"
                    >
                        No questions yet.
                    </div>
                    <div
                        v-for="(q, i) in exam.questions"
                        :key="q.id"
                        class="rounded-lg border bg-white p-4"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="mb-1 flex items-center gap-2">
                                    <span
                                        class="text-xs font-semibold text-gray-400"
                                        >Q{{ i + 1 }}</span
                                    >
                                    <span
                                        class="rounded bg-gray-100 px-1.5 py-0.5 text-xs uppercase text-gray-600"
                                        >{{
                                            q.type === "mcq"
                                                ? "MCQ"
                                                : "Open Text"
                                        }}</span
                                    >
                                    <span class="text-xs text-gray-400"
                                        >{{ q.weight }} pt{{
                                            q.weight !== 1 ? "s" : ""
                                        }}</span
                                    >
                                </div>
                                <p class="text-sm text-gray-800">
                                    {{ q.text }}
                                </p>
                                <ul
                                    v-if="q.type === 'mcq'"
                                    class="mt-2 space-y-1"
                                >
                                    <li
                                        v-for="o in q.options"
                                        :key="o.id"
                                        class="flex items-center gap-1.5 text-xs"
                                        :class="
                                            o.is_correct
                                                ? 'font-medium text-green-700'
                                                : 'text-gray-500'
                                        "
                                    >
                                        <span>{{
                                            o.is_correct ? "✓" : "○"
                                        }}</span>
                                        {{ o.body }}
                                    </li>
                                </ul>
                            </div>
                            <div class="flex shrink-0 gap-2">
                                <Link
                                    :href="
                                        route('lecturer.questions.edit', q.id)
                                    "
                                    class="text-xs text-gray-500 hover:underline"
                                    >Edit</Link
                                >
                                <button
                                    @click="destroyQuestion(q.id)"
                                    class="text-xs text-red-500 hover:underline"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- sessions -->
            <div>
                <h3 class="mb-3 font-semibold text-gray-700">
                    Sessions ({{ sessions.length }})
                </h3>
                <div class="overflow-hidden rounded-lg border bg-white">
                    <table class="w-full text-sm">
                        <thead
                            class="bg-gray-50 text-xs uppercase tracking-wide text-gray-400"
                        >
                            <tr>
                                <th class="px-4 py-3 text-left">Student</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Score</th>
                                <th class="px-4 py-3 text-left">Submitted</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="sessions.length === 0">
                                <td
                                    colspan="5"
                                    class="px-4 py-6 text-center text-gray-400"
                                >
                                    No sessions yet.
                                </td>
                            </tr>
                            <tr
                                v-for="s in sessions"
                                :key="s.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-4 py-3 font-medium">
                                    {{ s.student }}
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
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    {{
                                        s.submitted_at
                                            ? new Date(
                                                  s.submitted_at,
                                              ).toLocaleString()
                                            : "—"
                                    }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link
                                        v-if="s.state === 'pending_review'"
                                        :href="
                                            route(
                                                'lecturer.sessions.review',
                                                s.id,
                                            )
                                        "
                                        class="text-xs text-blue-600 hover:underline"
                                        >Review</Link
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
