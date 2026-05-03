<script setup>
import { Link } from "@inertiajs/vue3";
import BrandLogo from "@/Components/BrandLogo.vue";

defineProps({
    altLink: { type: Object, default: null }, // { href, label }
});
</script>

<template>
    <div class="flex min-h-screen flex-col bg-ivory text-ink lg:flex-row">
        <!-- Mobile-only mini brand bar -->
        <div
            class="flex items-center justify-between border-b border-rule bg-ivory px-6 py-4 lg:hidden"
        >
            <Link href="/" class="inline-flex">
                <BrandLogo />
            </Link>
            <Link
                v-if="altLink"
                :href="altLink.href"
                class="text-[11px] font-medium tracking-wide text-ink-mute transition hover:text-ink"
                >{{ altLink.label }} →</Link
            >
        </div>

        <!-- Brand panel (desktop only) -->
        <aside
            class="relative hidden overflow-hidden bg-ink px-7 py-10 text-ivory lg:flex lg:min-h-screen lg:w-[40%] lg:flex-col lg:justify-between lg:px-[52px] lg:py-12"
        >
            <div
                aria-hidden="true"
                class="pointer-events-none absolute inset-0"
                style="
                    background-image: linear-gradient(
                        rgba(255, 255, 255, 0.025) 1px,
                        transparent 1px
                    );
                    background-size: 100% 60px;
                "
            />

            <Link href="/" class="relative inline-flex">
                <BrandLogo tone="ivory" />
            </Link>

            <div class="relative my-10 lg:my-0">
                <p
                    class="font-display text-[36px] font-light leading-[1.08] tracking-tight text-ivory sm:text-[44px] lg:text-[52px]"
                >
                    Examinations,<br />
                    <em class="not-italic text-oxblood-light">redesigned.</em>
                </p>
                <p
                    class="mt-5 max-w-[280px] text-[13px] font-light leading-[1.8] text-ivory/45"
                >
                    A workspace for lecturers to organize exams, and for
                    students to sit them.
                </p>
            </div>

            <div
                class="relative text-[9px] font-normal uppercase tracking-[0.18em] text-ivory/20"
            >
                Mohamad Syazani — 2026
            </div>
        </aside>

        <!-- Form panel -->
        <div class="flex flex-1 flex-col bg-ivory">
            <div
                v-if="altLink"
                class="hidden justify-end px-7 pt-7 sm:px-[52px] sm:pt-9 lg:flex"
            >
                <Link
                    :href="altLink.href"
                    class="text-[12px] font-medium tracking-wide text-ink-mute transition hover:text-ink"
                    >{{ altLink.label }} →</Link
                >
            </div>

            <div
                class="flex flex-1 items-center justify-center px-6 py-10 sm:px-[52px] sm:py-[60px]"
            >
                <div class="w-full max-w-[420px]">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
