<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";

const form = useForm({ name: "", description: "" });
const submit = () => form.post(route("lecturer.subjects.store"));
</script>

<template>
    <Head title="New Subject" />
    <AuthenticatedLayout>
        <template #header
            ><h2 class="text-xl font-semibold text-gray-800">
                New Subject
            </h2></template
        >
        <div class="mx-auto max-w-lg px-4 py-8">
            <div class="space-y-4 rounded-lg border bg-white p-6">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700"
                        >Name</label
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
                    <label class="mb-1 block text-sm font-medium text-gray-700"
                        >Description
                        <span class="font-normal text-gray-400"
                            >(optional)</span
                        ></label
                    >
                    <textarea
                        v-model="form.description"
                        rows="3"
                        class="w-full rounded border px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                    />
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
                        :href="route('lecturer.subjects.index')"
                        class="self-center text-sm text-gray-500 hover:underline"
                        >Cancel</Link
                    >
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
