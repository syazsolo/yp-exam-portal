<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const role = computed(() => page.props.auth.user?.role);
const isLecturer = computed(() => role.value === 'lecturer');
const isStudent = computed(() => role.value === 'student');
const roleLabel = computed(() =>
    role.value ? role.value.charAt(0).toUpperCase() + role.value.slice(1) : '',
);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <p class="text-sm uppercase tracking-wide text-gray-500">
                            Signed in as
                        </p>
                        <p class="mt-1 text-lg font-semibold">
                            {{ page.props.auth.user.name }}
                            <span
                                class="ms-2 inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800"
                            >
                                {{ roleLabel }}
                            </span>
                        </p>
                    </div>
                </div>

                <div
                    v-if="isLecturer"
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                    data-testid="lecturer-panel"
                >
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Lecturer tools
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Create exams, review submissions, and manage your
                            classes.
                        </p>
                    </div>
                </div>

                <div
                    v-if="isStudent"
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                    data-testid="student-panel"
                >
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Your exams
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            See upcoming exams assigned to your class and your
                            past attempts.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
