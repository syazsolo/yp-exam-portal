<script setup>
import { Head } from "@inertiajs/vue3";
import EditorialLabel from "@/Components/EditorialLabel.vue";
import GuestHeader from "@/Components/GuestHeader.vue";
</script>

<template>
    <Head title="YP Exam Portal" />

    <div class="relative min-h-[100svh] overflow-hidden bg-ivory text-ink">
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

        <GuestHeader class="welcome-reveal welcome-reveal-1" />

        <main
            class="relative mx-auto max-w-6xl px-5 pb-32 pt-12 sm:px-8 sm:pt-16 lg:pt-28"
        >
            <div class="grid grid-cols-12 gap-8">
                <div class="col-span-12 lg:col-span-10">
                    <h1
                        class="welcome-display welcome-reveal welcome-reveal-2 font-display text-[clamp(2.75rem,10vw,7.5rem)] font-light leading-[0.92] tracking-[-0.03em]"
                    >
                        Examinations,
                        <span class="italic text-oxblood">redesigned.</span>
                    </h1>

                    <div
                        class="welcome-reveal welcome-reveal-3 mt-10 grid grid-cols-12 gap-8 sm:mt-12"
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

        <footer
            class="welcome-reveal welcome-reveal-4 absolute inset-x-0 bottom-0 mx-auto max-w-6xl px-5 pb-6 sm:px-8 sm:pb-8"
        >
            <div
                class="mb-3 h-px w-full bg-[color:var(--color-rule)] sm:mb-4"
            />
            <div
                class="flex items-center justify-end font-sans text-xs text-ink-mute"
            >
                <EditorialLabel>Mohamad Syazani &middot; 2026</EditorialLabel>
            </div>
        </footer>
    </div>
</template>

<style>
.welcome-display {
    font-variation-settings: "opsz" 144;
}

.welcome-reveal {
    opacity: 0;
    transform: translateY(8px);
    animation: welcome-reveal 700ms cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
}

.welcome-reveal-1 {
    animation-delay: 80ms;
}

.welcome-reveal-2 {
    animation-delay: 200ms;
}

.welcome-reveal-3 {
    animation-delay: 360ms;
}

.welcome-reveal-4 {
    animation-delay: 520ms;
}

@keyframes welcome-reveal {
    to {
        opacity: 1;
        transform: none;
    }
}
</style>
