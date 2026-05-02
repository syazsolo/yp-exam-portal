<script setup>
import { Head } from '@inertiajs/vue3';
import GuestHeader from '@/Components/GuestHeader.vue';
</script>

<template>
    <Head title="YP Exam Portal" />

    <div class="relative min-h-[100svh] overflow-hidden bg-ivory text-ink">
        <!-- Subtle paper texture / vignette -->
        <div
            aria-hidden="true"
            class="pointer-events-none absolute inset-0"
            style="
                background:
                    radial-gradient(
                        120% 80% at 50% -10%,
                        rgba(26, 24, 20, 0) 0%,
                        rgba(26, 24, 20, 0.06) 80%
                    ),
                    radial-gradient(
                        60% 40% at 100% 100%,
                        rgba(122, 31, 43, 0.05),
                        transparent 60%
                    );
            "
        />

        <!-- ── Header ─────────────────────────────────────────── -->
        <GuestHeader />

        <!-- ── Hero ───────────────────────────────────────────── -->
        <main
            class="relative mx-auto max-w-6xl px-5 pb-32 pt-12 sm:px-8 sm:pt-16 lg:pt-28"
        >
            <div class="grid grid-cols-12 gap-8">
                <div class="col-span-12 lg:col-span-10">
                    <h1
                        class="reveal reveal-2 font-display font-light leading-[0.92] tracking-[-0.03em] text-[clamp(2.75rem,10vw,7.5rem)]"
                        style="font-variation-settings: 'opsz' 144"
                    >
                        Examinations,
                        <span class="italic text-oxblood">redesigned.</span>
                    </h1>

                    <div
                        class="reveal reveal-3 mt-10 grid grid-cols-12 gap-8 sm:mt-12"
                    >
                        <p
                            class="col-span-12 max-w-xl font-sans text-base leading-relaxed text-ink-soft sm:text-lg md:col-span-7"
                        >
                            A workspace for lecturers to organize exams, and for
                            students to sit them.
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <!-- ── Footer ─────────────────────────────────────────── -->
        <footer
            class="reveal reveal-4 absolute inset-x-0 bottom-0 mx-auto max-w-6xl px-5 pb-6 sm:px-8 sm:pb-8"
        >
            <div class="rule mb-3 sm:mb-4" />
            <div
                class="flex items-center justify-end font-sans text-xs text-ink-mute"
            >
                <span class="eyebrow">Mohamad Syazani · 2026</span>
            </div>
        </footer>
    </div>
</template>
