<script setup>
/**
 * GuestHeader
 * ──────────
 * Marketing/landing header. Guests only — authed users are
 * redirected away from this page server-side.
 *
 * Responsive:
 *   - >= md: inline ghost links
 *   - <  md: hamburger toggles a floating fold-down panel
 */
import { ref } from "vue";
import { Link } from "@inertiajs/vue3";
import BrandLogo from "@/Components/BrandLogo.vue";

const open = ref(false);
const toggle = () => (open.value = !open.value);
const close = () => (open.value = false);
</script>

<template>
    <header class="reveal reveal-1 relative z-20">
        <div
            class="mx-auto flex max-w-6xl items-center justify-between px-5 py-5 sm:px-8 sm:py-6"
        >
            <!-- Brand -->
            <Link href="/" @click="close">
                <BrandLogo />
            </Link>

            <!-- Desktop nav -->
            <nav class="hidden items-center gap-1 md:flex">
                <Link :href="route('login')" class="btn-ghost">Sign in</Link>
                <Link :href="route('register')" class="btn-ghost">
                    Register
                </Link>
            </nav>

            <!-- Mobile hamburger -->
            <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center text-ink transition hover:text-oxblood focus:outline-none focus-visible:ring-2 focus-visible:ring-oxblood focus-visible:ring-offset-4 focus-visible:ring-offset-ivory md:hidden"
                :aria-expanded="open"
                aria-controls="site-header-panel"
                aria-label="Toggle navigation"
                @click="toggle"
            >
                <span class="relative block h-3 w-5">
                    <span
                        class="absolute left-0 right-0 h-px bg-ink transition-transform duration-300"
                        :class="
                            open
                                ? 'top-1/2 -translate-y-1/2 rotate-45'
                                : 'top-0'
                        "
                    />
                    <!-- h-[0.5px] is a hack but it works, without it appears fat -->
                    <span
                        class="absolute left-0 right-0 h-[0.5px] bg-ink transition-transform duration-300"
                        :class="
                            open
                                ? 'top-1/2 -translate-y-1/2 -rotate-45'
                                : 'bottom-0'
                        "
                    />
                </span>
            </button>
        </div>

        <!-- Mobile panel — floats above content -->
        <transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div
                v-if="open"
                id="site-header-panel"
                class="absolute inset-x-0 top-full z-30 border-t border-[color:var(--color-rule)] bg-ivory/95 shadow-[var(--shadow-paper)] backdrop-blur-sm md:hidden"
            >
                <nav class="mx-auto flex max-w-6xl flex-col px-5 py-3 sm:px-8">
                    <Link
                        :href="route('login')"
                        class="flex items-center justify-between border-b border-[color:var(--color-rule)] py-4 font-sans text-base text-ink transition hover:text-oxblood"
                        @click="close"
                    >
                        <span>Sign in</span>
                    </Link>
                    <Link
                        :href="route('register')"
                        class="flex items-center justify-between py-4 font-sans text-base text-ink transition hover:text-oxblood"
                        @click="close"
                    >
                        <span>Register</span>
                    </Link>
                </nav>
            </div>
        </transition>

        <!-- Bottom hairline -->
        <div class="h-px bg-[color:var(--color-rule)]" />
    </header>
</template>
