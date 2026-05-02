<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";

const props = defineProps({ question: Object });
const form = useForm({
    text: props.question.text,
    weight: props.question.weight,
    options: props.question.options.map((o) => ({
        text: o.body,
        is_correct: o.is_correct,
    })),
    correct_option: props.question.options.findIndex((o) => o.is_correct),
});

function addOption() {
    form.options.push({ text: "", is_correct: false });
}
function removeOption(i) {
    if (form.options.length > 2) form.options.splice(i, 1);
}
function submit() {
    form.options.forEach((option, index) => {
        option.is_correct = index === form.correct_option;
    });
    form.patch(route("lecturer.questions.update", props.question.id));
}
</script>

<template>
    <Head title="Edit Question" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('lecturer.exams.show', question.exam_id)"
                    class="text-sm text-gray-400 hover:text-gray-600"
                    >← Exam</Link
                >
                <h2 class="text-xl font-semibold text-gray-800">
                    Edit Question
                </h2>
            </div>
        </template>
        <div class="mx-auto max-w-2xl px-4 py-8">
            <div class="space-y-5 rounded-lg border bg-white p-6">
                <div class="text-xs uppercase tracking-wide text-gray-400">
                    Type:
                    {{
                        question.type === "mcq"
                            ? "Multiple Choice"
                            : "Open Text"
                    }}
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700"
                        >Question</label
                    >
                    <textarea
                        v-model="form.text"
                        rows="3"
                        class="w-full rounded border px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700"
                        >Points</label
                    >
                    <input
                        v-model="form.weight"
                        type="number"
                        min="1"
                        class="w-32 rounded border px-3 py-2 text-sm"
                    />
                </div>

                <div v-if="question.type === 'mcq'">
                    <label class="mb-2 block text-sm font-medium text-gray-700"
                        >Options</label
                    >
                    <div class="space-y-2">
                        <div
                            v-for="(opt, i) in form.options"
                            :key="i"
                            class="flex items-center gap-2"
                        >
                            <input
                                type="radio"
                                :value="i"
                                v-model="form.correct_option"
                            />
                            <input
                                v-model="opt.text"
                                type="text"
                                :placeholder="`Option ${i + 1}`"
                                class="flex-1 rounded border px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                            />
                            <button
                                v-if="form.options.length > 2"
                                @click="removeOption(i)"
                                class="text-xs text-red-400"
                            >
                                ✕
                            </button>
                        </div>
                    </div>
                    <button
                        @click="addOption"
                        class="mt-2 text-xs text-gray-500 hover:underline"
                    >
                        + Add option
                    </button>
                </div>

                <div class="flex gap-3 pt-2">
                    <button
                        @click="submit"
                        :disabled="form.processing"
                        class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700 disabled:opacity-50"
                    >
                        Update
                    </button>
                    <Link
                        :href="route('lecturer.exams.show', question.exam_id)"
                        class="self-center text-sm text-gray-500 hover:underline"
                        >Cancel</Link
                    >
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
