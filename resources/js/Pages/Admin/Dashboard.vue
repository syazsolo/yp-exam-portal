<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import { computed, reactive, ref } from "vue";

const props = defineProps({
    classes: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
    students: { type: Array, default: () => [] },
});

const editingClassId = ref(null);
const classForm = useForm({
    id: "",
    name: "",
    subject_ids: [],
});

const enrollmentForms = reactive({});
props.students.forEach((student) => {
    enrollmentForms[student.id] = useForm({
        class_id: student.active_class?.id ?? "",
    });
});

const isEditingClass = computed(() => editingClassId.value !== null);
const classFormTitle = computed(() =>
    isEditingClass.value ? "Edit class" : "New class",
);
const classSubmitLabel = computed(() =>
    isEditingClass.value ? "Save changes" : "Create class",
);

function resetClassForm() {
    editingClassId.value = null;
    classForm.reset();
    classForm.subject_ids = [];
    classForm.clearErrors();
}

function editClass(schoolClass) {
    editingClassId.value = schoolClass.id;
    classForm.id = schoolClass.id;
    classForm.name = schoolClass.name;
    classForm.subject_ids = [...schoolClass.subject_ids];
    classForm.clearErrors();
}

function saveClass() {
    if (isEditingClass.value) {
        classForm
            .transform((data) => ({
                name: data.name,
                subject_ids: data.subject_ids,
            }))
            .patch(route("admin.classes.update", editingClassId.value), {
                preserveScroll: true,
                onSuccess: resetClassForm,
            });

        return;
    }

    classForm
        .transform((data) => ({
            id: data.id || null,
            name: data.name,
            subject_ids: data.subject_ids,
        }))
        .post(route("admin.classes.store"), {
            preserveScroll: true,
            onSuccess: resetClassForm,
        });
}

function deleteClass(schoolClass) {
    if (!confirm(`Delete ${schoolClass.name}?`)) return;

    router.delete(route("admin.classes.destroy", schoolClass.id), {
        preserveScroll: true,
    });
}

function enrollmentFormFor(student) {
    if (!enrollmentForms[student.id]) {
        enrollmentForms[student.id] = useForm({
            class_id: student.active_class?.id ?? "",
        });
    }

    return enrollmentForms[student.id];
}

function enrollStudent(student) {
    enrollmentFormFor(student).post(
        route("admin.students.enroll", student.id),
        {
            preserveScroll: true,
        },
    );
}
</script>

<template>
    <Head title="Admin" />
    <AuthenticatedLayout>
        <div
            class="flex flex-col gap-1 border-b border-rule px-5 py-5 sm:px-9 sm:py-6"
        >
            <h1 class="font-display text-2xl font-medium text-ink">Admin</h1>
            <p class="text-xs text-ink-mute">
                Student enrollment and class assignment workspace.
            </p>
        </div>

        <div class="space-y-6 px-5 py-6 sm:px-9 sm:py-8">
            <section
                class="grid gap-5 border border-rule bg-white px-5 py-5 lg:grid-cols-[minmax(280px,380px)_1fr]"
            >
                <form class="space-y-5" @submit.prevent="saveClass">
                    <div>
                        <p
                            class="text-[11px] font-semibold uppercase tracking-label text-ink-mute"
                        >
                            {{ classFormTitle }}
                        </p>
                        <h2 class="mt-2 text-lg font-semibold text-ink">
                            Class management
                        </h2>
                    </div>

                    <div class="space-y-1.5">
                        <InputLabel for="class-id" value="Class ID" />
                        <TextInput
                            id="class-id"
                            v-model="classForm.id"
                            type="text"
                            class="w-full"
                            :disabled="isEditingClass"
                            placeholder="CLS-2026-A"
                        />
                        <InputError :message="classForm.errors.id" />
                    </div>

                    <div class="space-y-1.5">
                        <InputLabel for="class-name" value="Class name" />
                        <TextInput
                            id="class-name"
                            v-model="classForm.name"
                            type="text"
                            class="w-full"
                            placeholder="Form 5 Science"
                        />
                        <InputError :message="classForm.errors.name" />
                    </div>

                    <fieldset class="space-y-2">
                        <legend class="text-sm font-medium text-gray-700">
                            Subjects
                        </legend>
                        <div
                            class="max-h-56 space-y-2 overflow-y-auto border border-rule bg-ivory-50 p-3"
                        >
                            <label
                                v-for="subject in subjects"
                                :key="subject.id"
                                class="flex items-start gap-3 text-sm text-ink-soft"
                            >
                                <input
                                    v-model="classForm.subject_ids"
                                    type="checkbox"
                                    :value="subject.id"
                                    class="mt-1 rounded border-gray-300 text-oxblood focus:ring-oxblood"
                                />
                                <span class="min-w-0">
                                    <span class="block font-medium text-ink">
                                        {{ subject.name }}
                                    </span>
                                    <span
                                        class="block truncate text-xs text-ink-mute"
                                    >
                                        {{
                                            subject.creator?.name ??
                                            "Unassigned"
                                        }}
                                    </span>
                                </span>
                            </label>
                            <p
                                v-if="subjects.length === 0"
                                class="text-sm text-ink-mute"
                            >
                                No subjects yet.
                            </p>
                        </div>
                        <InputError :message="classForm.errors.subject_ids" />
                    </fieldset>

                    <div class="flex flex-wrap gap-2">
                        <PrimaryButton
                            type="submit"
                            :disabled="classForm.processing"
                        >
                            {{ classSubmitLabel }}
                        </PrimaryButton>
                        <SecondaryButton
                            v-if="isEditingClass"
                            type="button"
                            @click="resetClassForm"
                        >
                            Cancel
                        </SecondaryButton>
                    </div>
                </form>

                <div class="overflow-hidden border border-rule bg-ivory-50">
                    <div
                        class="flex items-center justify-between gap-3 border-b border-rule bg-white px-4 py-3"
                    >
                        <p
                            class="text-[11px] font-semibold uppercase tracking-label text-ink-mute"
                        >
                            Classes
                        </p>
                        <span class="text-xs text-ink-mute">
                            {{ classes.length }} total
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table
                            class="min-w-full border-separate border-spacing-0 text-sm"
                        >
                            <thead class="bg-white/70">
                                <tr>
                                    <th
                                        class="border-b border-rule px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-label text-ink-mute"
                                    >
                                        Class
                                    </th>
                                    <th
                                        class="border-b border-rule px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-label text-ink-mute"
                                    >
                                        Subjects
                                    </th>
                                    <th
                                        class="border-b border-rule px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-label text-ink-mute"
                                    >
                                        Students
                                    </th>
                                    <th
                                        class="border-b border-rule px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-label text-ink-mute"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="classes.length === 0">
                                    <td
                                        colspan="4"
                                        class="px-4 py-10 text-center text-sm text-ink-mute"
                                    >
                                        No classes yet.
                                    </td>
                                </tr>
                                <tr
                                    v-for="schoolClass in classes"
                                    :key="schoolClass.id"
                                    class="bg-white/80"
                                >
                                    <td
                                        class="border-b border-rule px-4 py-4 align-top"
                                    >
                                        <p class="font-semibold text-ink">
                                            {{ schoolClass.name }}
                                        </p>
                                        <p class="text-xs text-ink-mute">
                                            {{ schoolClass.id }}
                                        </p>
                                    </td>
                                    <td
                                        class="border-b border-rule px-4 py-4 align-top"
                                    >
                                        <div class="flex flex-wrap gap-1.5">
                                            <span
                                                v-if="
                                                    schoolClass.subjects
                                                        .length === 0
                                                "
                                                class="text-ink-mute"
                                            >
                                                None
                                            </span>
                                            <span
                                                v-for="subject in schoolClass.subjects"
                                                :key="subject.id"
                                                class="rounded-full border border-ink/10 bg-ink/5 px-2.5 py-1 text-xs font-medium text-ink-mute"
                                            >
                                                {{ subject.name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td
                                        class="border-b border-rule px-4 py-4 text-right align-top text-ink-mute"
                                    >
                                        {{ schoolClass.active_students_count }}
                                    </td>
                                    <td
                                        class="border-b border-rule px-4 py-4 align-top"
                                    >
                                        <div
                                            class="flex justify-end gap-1.5 whitespace-nowrap"
                                        >
                                            <button
                                                type="button"
                                                class="rounded-md px-3 py-1.5 text-xs font-semibold text-oxblood transition hover:bg-oxblood/10"
                                                @click="editClass(schoolClass)"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                type="button"
                                                class="rounded-md px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50"
                                                @click="
                                                    deleteClass(schoolClass)
                                                "
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="border border-rule bg-white px-5 py-5">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p
                            class="text-[11px] font-semibold uppercase tracking-label text-ink-mute"
                        >
                            Students
                        </p>
                        <h2 class="mt-2 text-lg font-semibold text-ink">
                            Enrollment
                        </h2>
                    </div>
                    <span class="text-xs text-ink-mute">
                        {{ students.length }} total
                    </span>
                </div>

                <div class="overflow-x-auto border border-rule bg-ivory-50">
                    <table class="min-w-full border-separate border-spacing-0">
                        <thead class="bg-white/70">
                            <tr>
                                <th
                                    class="border-b border-rule px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-label text-ink-mute"
                                >
                                    Student
                                </th>
                                <th
                                    class="border-b border-rule px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-label text-ink-mute"
                                >
                                    Current class
                                </th>
                                <th
                                    class="border-b border-rule px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-label text-ink-mute"
                                >
                                    Assign
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="students.length === 0">
                                <td
                                    colspan="3"
                                    class="px-4 py-10 text-center text-sm text-ink-mute"
                                >
                                    No students yet.
                                </td>
                            </tr>
                            <tr
                                v-for="student in students"
                                :key="student.id"
                                class="bg-white/80"
                            >
                                <td
                                    class="border-b border-rule px-4 py-4 align-top"
                                >
                                    <p class="font-semibold text-ink">
                                        {{ student.name }}
                                    </p>
                                    <p class="text-xs text-ink-mute">
                                        {{ student.email }}
                                    </p>
                                </td>
                                <td
                                    class="border-b border-rule px-4 py-4 align-top text-sm text-ink-mute"
                                >
                                    {{
                                        student.active_class?.name ??
                                        "Unassigned"
                                    }}
                                </td>
                                <td
                                    class="border-b border-rule px-4 py-4 align-top"
                                >
                                    <form
                                        class="flex flex-col gap-2 sm:flex-row sm:items-start"
                                        @submit.prevent="enrollStudent(student)"
                                    >
                                        <div class="min-w-48">
                                            <select
                                                v-model="
                                                    enrollmentFormFor(student)
                                                        .class_id
                                                "
                                                class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-oxblood focus:ring-oxblood"
                                            >
                                                <option disabled value="">
                                                    Select class
                                                </option>
                                                <option
                                                    v-for="schoolClass in classes"
                                                    :key="schoolClass.id"
                                                    :value="schoolClass.id"
                                                >
                                                    {{ schoolClass.name }}
                                                </option>
                                            </select>
                                            <InputError
                                                :message="
                                                    enrollmentFormFor(student)
                                                        .errors.class_id
                                                "
                                            />
                                        </div>
                                        <PrimaryButton
                                            type="submit"
                                            :disabled="
                                                enrollmentFormFor(student)
                                                    .processing
                                            "
                                        >
                                            Enroll
                                        </PrimaryButton>
                                    </form>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
