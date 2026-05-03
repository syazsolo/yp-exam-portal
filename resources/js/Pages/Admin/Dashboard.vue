<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
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

        <div class="px-5 py-6 sm:px-9 sm:py-8">
            <section class="border border-rule bg-white px-5 py-5">
                <p
                    class="text-[11px] font-semibold uppercase tracking-label text-ink-mute"
                >
                    Dashboard
                </p>
                <h2 class="mt-2 text-lg font-semibold text-ink">
                    Student enrollment
                </h2>
                <p class="mt-1 max-w-2xl text-sm leading-6 text-ink-soft">
                    Class assignments and student enrollment controls.
                </p>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
