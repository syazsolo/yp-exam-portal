<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
</script>

<template>
    <Head title="Playground" />
    <AuthenticatedLayout>
        <div class="mx-auto max-w-4xl px-5 py-8 sm:px-9">
            <div class="border border-rule bg-white px-6 py-6">
                <p
                    class="text-[11px] font-semibold uppercase tracking-label text-ink-mute"
                >
                    Playground
                </p>
                <h1 class="mt-2 font-display text-3xl font-medium text-ink">
                    Playground
                </h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-ink-soft">
                    A simple place to keep UI experiments and component
                    examples while the exam portal grows.
                </p>
                <Link
                    :href="route('playground.tables')"
                    class="mt-5 inline-flex items-center justify-center bg-ink px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.06em] text-ivory transition hover:bg-oxblood"
                >
                    Table examples
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
