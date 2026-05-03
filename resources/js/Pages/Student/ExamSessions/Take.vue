<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { computed, onMounted, onUnmounted, ref } from "vue";

const props = defineProps({ session: Object, answeredMap: Object });

const answers = ref({ ...props.answeredMap });
const savedAnswers = ref({ ...props.answeredMap });
const remaining = ref(0);
const submitting = ref(false);
let timer = null;

const isExpired = computed(() => remaining.value <= 0);
const unansweredMcqCount = computed(
    () =>
        props.session.exam.questions.filter(
            (q) =>
                q.type === "mcq" &&
                !savedAnswers.value[q.id]?.selected_option_id,
        ).length,
);
const allRequiredMcqsAnswered = computed(() => unansweredMcqCount.value === 0);
const manualSubmitDisabled = computed(
    () => isExpired.value || submitting.value || !allRequiredMcqsAnswered.value,
);
const timerCls = computed(() =>
    remaining.value < 60 ? "text-red-600" : "text-gray-700",
);
const timeLabel = computed(() => {
    const m = Math.floor(remaining.value / 60)
        .toString()
        .padStart(2, "0");
    const s = (remaining.value % 60).toString().padStart(2, "0");

    return `${m}:${s}`;
});

onMounted(() => {
    const deadline = new Date(props.session.deadline).getTime();

    function tick() {
        remaining.value = Math.max(
            0,
            Math.floor((deadline - Date.now()) / 1000),
        );

        if (remaining.value === 0) {
            clearInterval(timer);
            submit(false);
        }
    }

    tick();
    timer = setInterval(tick, 1000);
});

onUnmounted(() => clearInterval(timer));

function selectOption(questionId, optionId) {
    if (isExpired.value) return;
    if (!answers.value[questionId]) answers.value[questionId] = {};
    answers.value[questionId].selected_option_id = optionId;
}

function updateTextAnswer(questionId, value) {
    if (isExpired.value) return;
    if (!answers.value[questionId]) answers.value[questionId] = {};
    answers.value[questionId].text_answer = value;
}

function saveAnswer(question) {
    if (isExpired.value || submitting.value) return;

    const payload = { question_id: question.id };
    if (question.type === "mcq") {
        payload.selected_option_id =
            answers.value[question.id]?.selected_option_id ?? null;
    } else {
        payload.text_answer = answers.value[question.id]?.text_answer ?? "";
    }

    router.post(
        route("student.exam-sessions.answers.save", props.session.id),
        payload,
        {
            preserveScroll: true,
            onSuccess: () => {
                savedAnswers.value = {
                    ...savedAnswers.value,
                    [question.id]: { ...answers.value[question.id] },
                };
            },
        },
    );
}

function submit(confirmFirst = true) {
    if (submitting.value) return;
    const autoSubmitted = !confirmFirst;

    if (!autoSubmitted && !allRequiredMcqsAnswered.value) return;

    if (
        confirmFirst &&
        !confirm("Submit exam? You cannot change answers after this.")
    ) {
        return;
    }

    submitting.value = true;
    router.post(
        route("student.exam-sessions.submit", props.session.id),
        { auto_submitted: autoSubmitted },
        {
            onFinish: () => {
                submitting.value = false;
            },
        },
    );
}
</script>

<template>
    <Head :title="session.exam.title" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex w-full items-center justify-between gap-4">
                <h2 class="text-xl font-semibold text-gray-800">
                    {{ session.exam.title }}
                </h2>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-500">Time remaining</span>
                    <span
                        class="font-mono text-2xl font-bold"
                        :class="timerCls"
                    >
                        {{ timeLabel }}
                    </span>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-3xl space-y-6 px-4 py-8">
            <div
                v-if="isExpired || submitting"
                class="rounded border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-800"
            >
                Time is up. Your exam is being submitted.
            </div>
            <div
                v-else-if="!allRequiredMcqsAnswered"
                class="rounded border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800"
            >
                Answer and save all multiple-choice questions before submitting.
            </div>
            <div
                v-if="$page.props.errors?.answers"
                class="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
            >
                {{ $page.props.errors.answers }}
            </div>

            <div
                v-for="(q, i) in session.exam.questions"
                :key="q.id"
                class="space-y-3 rounded-lg border bg-white p-5"
            >
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-gray-400">
                        Q{{ i + 1 }}
                    </span>
                    <span
                        class="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600"
                    >
                        {{ q.points }} pt{{ q.points !== 1 ? "s" : "" }}
                    </span>
                    <span
                        v-if="savedAnswers[q.id]"
                        class="text-xs text-green-600"
                    >
                        answered
                    </span>
                </div>

                <p class="text-sm font-medium text-gray-800">{{ q.body }}</p>

                <div v-if="q.type === 'mcq'" class="space-y-2">
                    <label
                        v-for="opt in q.options"
                        :key="opt.id"
                        class="flex cursor-pointer items-center gap-3 rounded border p-2 text-sm"
                        :class="
                            answers[q.id]?.selected_option_id === opt.id
                                ? 'border-gray-800 bg-gray-50'
                                : 'border-gray-200 hover:bg-gray-50'
                        "
                    >
                        <input
                            type="radio"
                            :name="`q_${q.id}`"
                            :value="opt.id"
                            :checked="
                                answers[q.id]?.selected_option_id === opt.id
                            "
                            :disabled="isExpired || submitting"
                            class="shrink-0"
                            @change="selectOption(q.id, opt.id)"
                        />
                        {{ opt.body }}
                    </label>
                </div>

                <textarea
                    v-else
                    :value="answers[q.id]?.text_answer ?? ''"
                    :disabled="isExpired || submitting"
                    rows="4"
                    placeholder="Type your answer here..."
                    class="w-full rounded border px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 disabled:bg-gray-50"
                    @input="updateTextAnswer(q.id, $event.target.value)"
                />

                <button
                    :disabled="isExpired || submitting"
                    class="rounded bg-gray-100 px-3 py-1 text-xs text-gray-700 hover:bg-gray-200 disabled:cursor-not-allowed disabled:opacity-50"
                    @click="saveAnswer(q)"
                >
                    Save Answer
                </button>
            </div>

            <div class="flex justify-end">
                <button
                    :disabled="manualSubmitDisabled"
                    data-testid="submit-exam-button"
                    class="rounded bg-green-700 px-6 py-2 text-sm text-white hover:bg-green-800 disabled:cursor-not-allowed disabled:opacity-50"
                    @click="submit(true)"
                >
                    Submit Exam
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
