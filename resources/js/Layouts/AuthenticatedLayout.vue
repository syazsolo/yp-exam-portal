<script setup>
import { ref, computed, watch } from "vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import BrandLogo from "@/Components/BrandLogo.vue";

defineProps({
    title: String,
});

const page = usePage();
const user = computed(() => page.props.auth.user || {});
const initials = computed(() => {
    const n = (user.value.name || "").trim();
    if (!n) return "·";
    const parts = n.split(/\s+/).slice(0, 2);
    return (
        parts.map((p) => p[0]?.toUpperCase() ?? "").join("") ||
        n[0].toUpperCase()
    );
});

const navItems = computed(() => page.props.app?.nav ?? []);
const homeHref = computed(() => navItems.value[0]?.href ?? route("dashboard"));

const isActive = (m) => {
    try {
        return route().current(m);
    } catch {
        return false;
    }
};

const drawerOpen = ref(false);
watch(
    () => page.url,
    () => {
        drawerOpen.value = false;
    },
);

const logout = () => router.post(route("logout"));
</script>

<template>
    <div class="fixed inset-0 flex overflow-hidden bg-ivory text-ink">
        <!-- Mobile drawer scrim -->
        <div
            v-if="drawerOpen"
            class="fixed inset-0 z-30 bg-ink/50 backdrop-blur-sm lg:hidden"
            @click="drawerOpen = false"
        />

        <!-- Sidebar (drawer on mobile, static on lg+) -->
        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-ink text-ivory transition-transform duration-200 ease-out lg:static lg:w-[220px] lg:translate-x-0"
            :class="
                drawerOpen
                    ? 'translate-x-0'
                    : '-translate-x-full lg:translate-x-0'
            "
        >
            <!-- Brand -->
            <div
                class="flex items-center justify-between border-b border-white/10 px-6 py-7"
            >
                <Link :href="homeHref">
                    <BrandLogo tone="ivory" />
                </Link>
                <button
                    type="button"
                    @click="drawerOpen = false"
                    class="-m-2 rounded p-2 text-ivory/50 transition hover:text-ivory lg:hidden"
                    aria-label="Close menu"
                >
                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M18 6L6 18M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Nav -->
            <nav class="flex-1 px-3 py-4">
                <Link
                    v-for="n in navItems"
                    :key="n.label"
                    :href="n.href"
                    class="mb-0.5 flex items-center gap-3 rounded-sm px-3 py-2.5 text-[13px] transition"
                    :class="
                        isActive(n.match)
                            ? 'bg-white/10 font-semibold text-ivory'
                            : 'font-normal text-ivory/55 hover:text-ivory'
                    "
                >
                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.75"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="flex-shrink-0"
                    >
                        <template v-if="n.icon === 'home'">
                            <path
                                d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"
                            />
                        </template>
                        <template v-else-if="n.icon === 'doc'">
                            <path
                                d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"
                            />
                        </template>
                        <template v-else-if="n.icon === 'users'">
                            <path
                                d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"
                            />
                        </template>
                        <template v-else-if="n.icon === 'book'">
                            <path
                                d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 19.5A2.5 2.5 0 0 0 6.5 22H20V2H6.5A2.5 2.5 0 0 0 4 4.5v15z"
                            />
                        </template>
                    </svg>
                    <span class="truncate">{{ n.label }}</span>
                </Link>
            </nav>

            <!-- User -->
            <div
                class="flex items-center gap-3 border-t border-white/10 px-5 py-4"
            >
                <Link
                    :href="route('profile.edit')"
                    class="flex min-w-0 flex-1 items-center gap-3"
                >
                    <span
                        class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-oxblood text-[12px] font-bold text-ivory"
                        >{{ initials }}</span
                    >
                    <span class="min-w-0">
                        <span
                            class="block truncate text-[12px] font-semibold leading-tight text-ivory"
                            >{{ user.name }}</span
                        >
                        <span
                            class="block truncate text-[10px] capitalize text-ivory/40"
                            >{{ user.role || "User" }}</span
                        >
                    </span>
                </Link>
                <button
                    type="button"
                    @click="logout"
                    title="Sign out"
                    class="flex-shrink-0 text-ivory/30 transition hover:text-ivory"
                >
                    <svg
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path
                            d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"
                        />
                    </svg>
                </button>
            </div>
        </aside>

        <!-- Main column -->
        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            <!-- Mobile top bar -->
            <div
                class="flex flex-shrink-0 items-center gap-3 border-b border-rule bg-ivory px-4 py-3 lg:hidden"
            >
                <button
                    type="button"
                    @click="drawerOpen = true"
                    class="-m-2 rounded p-2 text-ink-soft transition hover:text-ink"
                    aria-label="Open menu"
                >
                    <svg
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <Link :href="homeHref">
                    <BrandLogo />
                </Link>
            </div>

            <!-- Optional page header -->
            <header
                v-if="title || $slots.header"
                class="flex-shrink-0 border-b border-rule bg-ivory px-6 py-5 lg:px-9"
            >
                <slot name="header">
                    <h2 class="text-xl font-semibold text-gray-800">
                        {{ title }}
                    </h2>
                </slot>
            </header>

            <!-- Flash -->
            <div
                v-if="page.props.flash?.success"
                class="flex-shrink-0 border-b border-emerald-200/60 bg-emerald-50 px-6 py-2.5 text-sm text-emerald-800 lg:px-9"
            >
                {{ page.props.flash.success }}
            </div>
            <div
                v-if="page.props.flash?.error"
                class="flex-shrink-0 border-b border-red-200/60 bg-red-50 px-6 py-2.5 text-sm text-red-800 lg:px-9"
            >
                {{ page.props.flash.error }}
            </div>

            <!-- Content -->
            <main class="min-w-0 flex-1 overflow-y-auto overflow-x-hidden">
                <slot />
            </main>
        </div>
    </div>
</template>
