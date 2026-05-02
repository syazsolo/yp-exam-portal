<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";

const props = defineProps({ subjects: Array });
const form = useForm({ name: "", subject_ids: [] });
const submit = () => form.post(route("lecturer.classes.store"));

function toggleSubject(id) {
    const idx = form.subject_ids.indexOf(id);
    idx === -1 ? form.subject_ids.push(id) : form.subject_ids.splice(idx, 1);
}
</script>

<template>
    <Head title="New Class" />
    <AuthenticatedLayout>
        <template #header
            ><h2 class="text-xl font-semibold text-gray-800">
                New Class
            </h2></template
        >
        <div class="mx-auto max-w-lg px-4 py-8">
            <div class="space-y-4 rounded-lg border bg-white p-6">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700"
                        >Class Name</label
                    >
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full rounded border px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                    />
                    <p
                        v-if="form.errors.name"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700"
                        >Subjects</label
                    >
                    <div class="space-y-1">
                        <label
                            v-for="s in subjects"
                            :key="s.id"
                            class="flex cursor-pointer items-center gap-2 text-sm"
                        >
                            <input
                                type="checkbox"
                                :checked="form.subject_ids.includes(s.id)"
                                @change="toggleSubject(s.id)"
                                class="rounded"
                            />
                            {{ s.name }}
                        </label>
                        <p
                            v-if="subjects.length === 0"
                            class="text-xs text-gray-400"
                        >
                            No subjects yet.
                            <Link
                                :href="route('lecturer.subjects.create')"
                                class="underline"
                                >Create one first</Link
                            >.
                        </p>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button
                        @click="submit"
                        :disabled="form.processing"
                        class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700 disabled:opacity-50"
                    >
                        Save
                    </button>
                    <Link
                        :href="route('lecturer.classes.index')"
                        class="self-center text-sm text-gray-500 hover:underline"
                        >Cancel</Link
                    >
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
