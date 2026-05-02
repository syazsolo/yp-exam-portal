<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, router, Link } from "@inertiajs/vue3";

const props = defineProps({ schoolClass: Object, subjects: Array });
const addForm = useForm({ email: "" });
const addStudent = () =>
    addForm.post(route("lecturer.classes.students.add", props.schoolClass.id), {
        onSuccess: () => addForm.reset(),
    });

function removeStudent(userId) {
    if (confirm("Remove student?"))
        router.delete(
            route("lecturer.classes.students.remove", [
                props.schoolClass.id,
                userId,
            ]),
        );
}
</script>

<template>
    <Head :title="schoolClass.name" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('lecturer.classes.index')"
                    class="text-sm text-gray-400 hover:text-gray-600"
                    >← Classes</Link
                >
                <h2 class="text-xl font-semibold text-gray-800">
                    {{ schoolClass.name }}
                </h2>
            </div>
        </template>
        <div class="mx-auto max-w-4xl space-y-6 px-4 py-8">
            <div
                v-if="$page.props.flash?.success"
                class="rounded border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800"
            >
                {{ $page.props.flash.success }}
            </div>

            <!-- subjects -->
            <div class="rounded-lg border bg-white p-5">
                <h3 class="mb-3 font-semibold text-gray-700">Subjects</h3>
                <div class="flex flex-wrap gap-2">
                    <span
                        v-for="s in schoolClass.subjects"
                        :key="s.id"
                        class="rounded bg-gray-100 px-3 py-1 text-sm text-gray-700"
                        >{{ s.name }}</span
                    >
                    <span
                        v-if="schoolClass.subjects.length === 0"
                        class="text-sm text-gray-400"
                        >None assigned.</span
                    >
                </div>
            </div>

            <!-- add student -->
            <div class="rounded-lg border bg-white p-5">
                <h3 class="mb-3 font-semibold text-gray-700">
                    Add Student by Email
                </h3>
                <div class="flex gap-2">
                    <input
                        v-model="addForm.email"
                        type="email"
                        placeholder="student@example.com"
                        class="flex-1 rounded border px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
                    />
                    <button
                        @click="addStudent"
                        :disabled="addForm.processing"
                        class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700 disabled:opacity-50"
                    >
                        Add
                    </button>
                </div>
                <p
                    v-if="addForm.errors.email"
                    class="mt-1 text-xs text-red-600"
                >
                    {{ addForm.errors.email }}
                </p>
            </div>

            <!-- students list -->
            <div class="overflow-hidden rounded-lg border bg-white">
                <div class="border-b bg-gray-50 px-5 py-3">
                    <h3 class="font-semibold text-gray-700">
                        Students ({{ schoolClass.students.length }})
                    </h3>
                </div>
                <table class="w-full text-sm">
                    <thead
                        class="bg-gray-50 text-xs uppercase tracking-wide text-gray-400"
                    >
                        <tr>
                            <th class="px-4 py-3 text-left">Name</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="schoolClass.students.length === 0">
                            <td
                                colspan="3"
                                class="px-4 py-6 text-center text-gray-400"
                            >
                                No students yet.
                            </td>
                        </tr>
                        <tr
                            v-for="s in schoolClass.students"
                            :key="s.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-4 py-3 font-medium">{{ s.name }}</td>
                            <td class="px-4 py-3 text-gray-500">
                                {{ s.email }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button
                                    @click="removeStudent(s.id)"
                                    class="text-xs text-red-500 hover:underline"
                                >
                                    Remove
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
