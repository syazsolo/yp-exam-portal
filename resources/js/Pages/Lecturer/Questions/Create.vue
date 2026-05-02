<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";

const props = defineProps({ exam: Object });
const form = useForm({
    type: "mcq",
    text: "",
    weight: 1,
    options: [
        { text: "", is_correct: true },
        { text: "", is_correct: false },
        { text: "", is_correct: false },
        { text: "", is_correct: false },
    ],
    correct_option: 0,
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
    form.post(route("lecturer.exams.questions.store", props.exam.id));
}
</script>

<template>
    <Head title="Add Question" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('lecturer.exams.show', exam.id)"
                    class="text-sm text-gray-400 hover:text-gray-600"
                    >← {{ exam.title }}</Link
                >
                <h2 class="text-xl font-semibold text-gray-800">
                    Add Question
                </h2>
            </div>
        </template>
        <div class="mx-auto max-w-2xl px-4 py-8">
            <div class="space-y-5 rounded-lg border bg-white p-6">
                <!-- type -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700"
                        >Question Type</label
                    >
                    <div class="flex gap-4">
                        <label
                            class="flex cursor-pointer items-center gap-2 text-sm"
                        >
                            <input
                                type="radio"
                                v-model="form.type"
                                value="mcq"
                            />
                            Multiple Choice
                        </label>
                        <label
                            class="flex cursor-pointer items-center gap-2 text-sm"
                        >
                            <input
                                type="radio"
                                v-model="form.type"
                                value="open_text"
                            />
                            Open Text
                        </label>
                    </div>
                </div>

                <!-- body -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700"
                        >Question</label
                    >
                    <textarea
                        v-model="form.text"
                        rows="3"
                        class="w-full rounded border px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                    />
                    <p
                        v-if="form.errors.text"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ form.errors.text }}
                    </p>
                </div>

                <!-- points -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700"
                        >Points</label
                    >
                    <input
                        v-model="form.weight"
                        type="number"
                        min="1"
                        class="w-32 rounded border px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                    />
                </div>

                <!-- mcq options -->
                <div v-if="form.type === 'mcq'">
                    <label class="mb-2 block text-sm font-medium text-gray-700"
                        >Options
                        <span class="text-xs font-normal text-gray-400"
                            >(select the correct one)</span
                        ></label
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
                                class="text-xs text-red-400 hover:text-red-600"
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
                    <p
                        v-if="form.errors.options"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ form.errors.options }}
                    </p>
                </div>

                <div class="flex gap-3 pt-2">
                    <button
                        @click="submit"
                        :disabled="form.processing"
                        class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700 disabled:opacity-50"
                    >
                        Save Question
                    </button>
                    <Link
                        :href="route('lecturer.exams.show', exam.id)"
                        class="self-center text-sm text-gray-500 hover:underline"
                        >Cancel</Link
                    >
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
