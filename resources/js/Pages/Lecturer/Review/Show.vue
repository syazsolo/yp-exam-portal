<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, router, Link } from "@inertiajs/vue3";

const props = defineProps({ session: Object });

function gradeForm(answer) {
    return useForm({
        score: answer.score ?? 0,
        reviewer_comment: answer.reviewer_comment ?? "",
    });
}

const forms = props.session.answers
    .filter((a) => a.type === "open_text")
    .reduce((acc, a) => {
        acc[a.id] = gradeForm(a);
        return acc;
    }, {});

function submitGrade(answerId) {
    forms[answerId].patch(route("lecturer.answers.score", answerId));
}

function finalize() {
    if (confirm("Finalize grading for this session?")) {
        router.post(route("lecturer.sessions.finalize", props.session.id));
    }
}
</script>

<template>
    <Head title="Review Session" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('lecturer.exams.show', session.exam.id)"
                    class="text-sm text-gray-400 hover:text-gray-600"
                    >← {{ session.exam.title }}</Link
                >
                <h2 class="text-xl font-semibold text-gray-800">
                    Review – {{ session.student }}
                </h2>
            </div>
        </template>
        <div class="mx-auto max-w-3xl space-y-4 px-4 py-8">
            <div
                v-if="$page.props.flash?.success"
                class="rounded border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800"
            >
                {{ $page.props.flash.success }}
            </div>

            <div
                v-for="(answer, i) in session.answers"
                :key="answer.id"
                class="space-y-3 rounded-lg border bg-white p-5"
            >
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-gray-400"
                        >Q{{ i + 1 }}</span
                    >
                    <span
                        class="rounded bg-gray-100 px-1.5 py-0.5 text-xs uppercase text-gray-600"
                        >{{ answer.type === "mcq" ? "MCQ" : "Open Text" }}</span
                    >
                    <span class="text-xs text-gray-400"
                        >max {{ answer.question.weight }} pts</span
                    >
                </div>
                <p class="text-sm font-medium text-gray-800">
                    {{ answer.question.text }}
                </p>

                <!-- MCQ -->
                <div v-if="answer.type === 'mcq'">
                    <p class="text-sm text-gray-600">
                        Answer:
                        <span
                            :class="
                                answer.selected_option?.is_correct
                                    ? 'font-medium text-green-700'
                                    : 'font-medium text-red-600'
                            "
                        >
                            {{ answer.selected_option?.body ?? "(no answer)" }}
                        </span>
                        <span class="ml-2 text-xs text-gray-400"
                            >{{ answer.score }} /
                            {{ answer.question.weight }} pts</span
                        >
                    </p>
                </div>

                <!-- Open text -->
                <div v-else class="space-y-3">
                    <div
                        class="whitespace-pre-wrap rounded border bg-gray-50 p-3 text-sm text-gray-700"
                    >
                        {{ answer.text_answer || "(no answer provided)" }}
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                            <label
                                class="w-24 text-xs font-medium text-gray-600"
                                >Points Awarded</label
                            >
                            <input
                                v-model="forms[answer.id].score"
                                type="number"
                                :min="0"
                                :max="answer.question.weight"
                                class="w-20 rounded border px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                            />
                            <span class="text-xs text-gray-400"
                                >/ {{ answer.question.weight }}</span
                            >
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-600"
                                >Comment
                                <span class="font-normal text-gray-400"
                                    >(optional)</span
                                ></label
                            >
                            <textarea
                                v-model="forms[answer.id].reviewer_comment"
                                rows="2"
                                class="mt-1 w-full rounded border px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                            />
                        </div>
                        <button
                            @click="submitGrade(answer.id)"
                            :disabled="forms[answer.id].processing"
                            class="rounded bg-gray-800 px-3 py-1.5 text-xs text-white hover:bg-gray-700 disabled:opacity-50"
                        >
                            {{
                                answer.score !== null
                                    ? "Update Grade"
                                    : "Save Grade"
                            }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- finalize -->
            <div
                class="flex items-center justify-between rounded-lg border bg-white p-5"
            >
                <div class="text-sm text-gray-600">
                    <span
                        v-if="session.all_reviewed"
                        class="font-medium text-green-700"
                        >✓ All answers reviewed.</span
                    >
                    <span v-else class="text-yellow-700"
                        >Some open-text answers not yet graded.</span
                    >
                </div>
                <button
                    @click="finalize"
                    :disabled="!session.all_reviewed"
                    class="rounded bg-green-700 px-4 py-2 text-sm text-white hover:bg-green-800 disabled:cursor-not-allowed disabled:opacity-40"
                >
                    Finalize & Grade Session
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
