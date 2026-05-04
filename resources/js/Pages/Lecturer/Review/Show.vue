<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, Link } from "@inertiajs/vue3";
import axios from "axios";
import { computed, onUnmounted, ref } from "vue";
import {
    cloneGradeMap,
    isGradeComplete,
    isGradeDirty,
    isGradeInvalid,
    normalizedGrade,
} from "./grading-state.js";

const props = defineProps({ session: Object });

const grades = ref(cloneGradeMap(initialGradeMap()));
const savedGrades = ref(cloneGradeMap(initialGradeMap()));
const savingGrades = ref({});
const saveErrors = ref({});
const saveTimers = {};
const retryTimers = {};

const openTextAnswers = computed(() =>
    props.session.answers.filter((answer) => answer.type === "open_text"),
);
const allReviewed = computed(
    () =>
        openTextAnswers.value.length > 0 &&
        openTextAnswers.value.every(
            (answer) =>
                isGradeComplete(answer, savedGrades.value) &&
                !isGradeDirty(answer, grades.value, savedGrades.value) &&
                !isGradeInvalid(answer, grades.value) &&
                !savingGrades.value[answer.id],
        ),
);

onUnmounted(() => {
    Object.values(saveTimers).forEach(clearTimeout);
    Object.values(retryTimers).forEach(clearTimeout);
});

function initialGradeMap() {
    return props.session.answers
        .filter((answer) => answer.type === "open_text")
        .reduce((acc, answer) => {
            acc[answer.id] = {
                score: answer.score ?? "",
                reviewer_comment: answer.reviewer_comment ?? "",
            };

            return acc;
        }, {});
}

function setDraftGrade(answerId, grade) {
    grades.value = {
        ...grades.value,
        [answerId]: {
            ...(grades.value[answerId] ?? {}),
            ...grade,
        },
    };
}

function updateScore(answer, value) {
    setDraftGrade(answer.id, { score: value });
    scheduleGradeSave(answer, 700);
}

function updateComment(answer, value) {
    setDraftGrade(answer.id, { reviewer_comment: value });
    scheduleGradeSave(answer, 1000);
}

function shouldSaveGrade(answer) {
    return (
        !savingGrades.value[answer.id] &&
        !isGradeInvalid(answer, grades.value) &&
        isGradeDirty(answer, grades.value, savedGrades.value)
    );
}

function scheduleGradeSave(answer, delay) {
    clearTimeout(saveTimers[answer.id]);
    clearTimeout(retryTimers[answer.id]);

    if (!shouldSaveGrade(answer)) return;

    saveTimers[answer.id] = setTimeout(() => saveGrade(answer), delay);
}

async function saveGrade(answer) {
    if (!shouldSaveGrade(answer)) return;

    const snapshot = normalizedGrade(answer, grades.value);

    savingGrades.value = {
        ...savingGrades.value,
        [answer.id]: true,
    };
    saveErrors.value = {
        ...saveErrors.value,
        [answer.id]: null,
    };

    try {
        await axios.patch(
            route("lecturer.answers.score", answer.id),
            snapshot,
            {
                headers: { Accept: "application/json" },
            },
        );
        savedGrades.value = {
            ...savedGrades.value,
            [answer.id]: { ...snapshot },
        };
    } catch {
        saveErrors.value = {
            ...saveErrors.value,
            [answer.id]: "save interrupted",
        };
    } finally {
        savingGrades.value = {
            ...savingGrades.value,
            [answer.id]: false,
        };

        if (
            isGradeDirty(answer, grades.value, savedGrades.value) &&
            !isGradeInvalid(answer, grades.value)
        ) {
            retryTimers[answer.id] = setTimeout(() => saveGrade(answer), 2500);
        }
    }
}

function gradeStatusLabel(answer) {
    if (shouldShowInvalidGrade(answer)) return "invalid score";
    if (savingGrades.value[answer.id]) return "saving...";
    if (isGradeDirty(answer, grades.value, savedGrades.value)) {
        return "unsaved changes";
    }
    if (saveErrors.value[answer.id]) return saveErrors.value[answer.id];
    if (isGradeComplete(answer, savedGrades.value)) return "saved";

    return "not graded";
}

function gradeStatusClass(answer) {
    if (shouldShowInvalidGrade(answer)) return "text-red-600";
    if (savingGrades.value[answer.id]) return "text-gray-500";
    if (isGradeDirty(answer, grades.value, savedGrades.value)) {
        return "text-amber-700";
    }
    if (saveErrors.value[answer.id]) return "text-red-600";
    if (isGradeComplete(answer, savedGrades.value)) return "text-green-600";

    return "text-gray-400";
}

function shouldShowInvalidGrade(answer) {
    return (
        isGradeInvalid(answer, grades.value) &&
        isGradeDirty(answer, grades.value, savedGrades.value)
    );
}

function finalize() {
    if (allReviewed.value && confirm("Finalize grading for this session?")) {
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
                    >&larr; {{ session.exam.title }}</Link
                >
                <h2 class="text-xl font-semibold text-gray-800">
                    Review &ndash; {{ session.student }}
                </h2>
            </div>
        </template>

        <div class="mx-auto max-w-3xl space-y-4 px-4 py-8">
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
                    <span
                        v-if="answer.type === 'open_text'"
                        class="text-xs"
                        :class="gradeStatusClass(answer)"
                    >
                        {{ gradeStatusLabel(answer) }}
                    </span>
                </div>

                <p class="text-sm font-medium text-gray-800">
                    {{ answer.question.text }}
                </p>

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
                                :value="grades[answer.id].score"
                                type="number"
                                :min="0"
                                :max="answer.question.weight"
                                class="w-20 rounded border px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                                :class="
                                    shouldShowInvalidGrade(answer)
                                        ? 'border-red-400'
                                        : ''
                                "
                                @blur="scheduleGradeSave(answer, 0)"
                                @input="
                                    updateScore(answer, $event.target.value)
                                "
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
                                :value="grades[answer.id].reviewer_comment"
                                rows="2"
                                class="mt-1 w-full rounded border px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                                @blur="scheduleGradeSave(answer, 0)"
                                @input="
                                    updateComment(answer, $event.target.value)
                                "
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="flex items-center justify-between rounded-lg border bg-white p-5"
            >
                <div class="text-sm text-gray-600">
                    <span v-if="allReviewed" class="font-medium text-green-700"
                        >All answers reviewed.</span
                    >
                    <span v-else class="text-yellow-700"
                        >Some open-text answers not yet graded.</span
                    >
                </div>
                <button
                    @click="finalize"
                    :disabled="!allReviewed"
                    class="rounded bg-green-700 px-4 py-2 text-sm text-white hover:bg-green-800 disabled:cursor-not-allowed disabled:opacity-40"
                >
                    Finalize & Grade Session
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
