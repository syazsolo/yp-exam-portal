<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
    stats: Object,
    recentExams: Array,
    classes: Array,
    pendingReviews: Number,
})

const statusStyle = {
    active: { dot: 'bg-emerald-600', text: 'text-emerald-700', bg: 'bg-emerald-50' },
    draft:  { dot: 'bg-ink-mute',    text: 'text-ink-mute',    bg: 'bg-ivory-200' },
    closed: { dot: 'bg-oxblood',     text: 'text-oxblood',     bg: 'bg-oxblood/5' },
}
const badgeFor = (s) => statusStyle[s] || statusStyle.draft
</script>

<template>
    <Head title="Overview" />
    <AuthenticatedLayout>

        <!-- Top bar -->
        <div class="flex flex-col gap-3 border-b border-rule px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-9 sm:py-6">
            <div>
                <h1 class="font-display text-2xl font-medium tracking-tight text-ink">Overview</h1>
                <p class="mt-0.5 text-xs text-ink-mute">Welcome back, {{ $page.props.auth.user.name?.split(' ')[0] }}.</p>
            </div>
            <div class="flex gap-2">
                <Link
                    :href="route('lecturer.exams.index')"
                    class="inline-flex items-center justify-center border border-rule bg-transparent px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.06em] text-ink-soft transition hover:border-ink hover:text-ink"
                >
                    All exams
                </Link>
                <Link
                    :href="route('lecturer.exams.create')"
                    class="inline-flex items-center justify-center bg-ink px-5 py-2.5 text-[11px] font-semibold uppercase tracking-[0.06em] text-ivory transition hover:bg-oxblood"
                >
                    + New exam
                </Link>
            </div>
        </div>

        <div class="px-5 py-6 sm:px-9 sm:py-8 space-y-8">

            <!-- Pending reviews -->
            <div
                v-if="pendingReviews > 0"
                class="flex items-center gap-3 border border-amber-300/60 bg-amber-50/70 px-4 py-3 text-sm text-amber-900"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                <span>{{ pendingReviews }} session{{ pendingReviews === 1 ? '' : 's' }} awaiting open-text review.</span>
            </div>

            <!-- Stats -->
            <section class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div
                    v-for="(item, i) in [
                        { label: 'Active Exams', value: stats.active_exams, sub: 'Currently running', accent: true },
                        { label: 'Total Exams',  value: stats.total_exams,  sub: 'All time' },
                        { label: 'Classes',      value: stats.classes,      sub: 'Active groups' },
                        { label: 'Students',     value: stats.students,     sub: 'Across all classes' },
                    ]"
                    :key="i"
                    class="border border-rule bg-white px-5 py-5"
                >
                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.1em] text-ink-mute">{{ item.label }}</p>
                    <p
                        class="text-3xl font-bold leading-none sm:text-4xl"
                        :class="item.accent ? 'text-oxblood' : 'text-ink'"
                    >{{ item.value }}</p>
                    <p class="mt-2 text-xs text-ink-mute">{{ item.sub }}</p>
                </div>
            </section>

            <!-- Recent exams -->
            <section>
                <header class="mb-3.5 flex items-center justify-between">
                    <h2 class="text-[13px] font-bold uppercase tracking-[0.06em] text-ink">Recent Exams</h2>
                    <Link :href="route('lecturer.exams.index')" class="text-[11px] font-semibold tracking-wide text-ink-mute transition hover:text-ink">
                        View all →
                    </Link>
                </header>
                <div class="border border-rule bg-white">
                    <Link
                        v-for="(ex, i) in recentExams"
                        :key="ex.id"
                        :href="route('lecturer.exams.show', ex.id)"
                        class="flex items-center gap-4 px-5 py-3.5 transition hover:bg-ivory/60"
                        :class="i < recentExams.length - 1 ? 'border-b border-rule' : ''"
                    >
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-[13px] font-semibold text-ink">{{ ex.title }}</p>
                            <p class="truncate text-[11px] text-ink-mute">{{ ex.subject }}</p>
                        </div>
                        <span
                            class="hidden flex-shrink-0 items-center gap-1.5 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.08em] sm:inline-flex"
                            :class="[badgeFor(ex.status).bg, badgeFor(ex.status).text]"
                        >
                            <span class="h-1.5 w-1.5 rounded-full" :class="badgeFor(ex.status).dot" />
                            {{ ex.status }}
                        </span>
                        <span class="hidden w-16 flex-shrink-0 text-right text-xs text-ink-mute md:block">{{ ex.time_limit }}min</span>
                        <span
                            v-if="ex.status === 'active'"
                            class="hidden w-20 flex-shrink-0 text-right text-xs font-semibold text-ink md:block"
                        >{{ ex.sessions_count }} taken</span>
                        <span v-else class="hidden w-20 md:block" />
                    </Link>
                    <div v-if="recentExams.length === 0" class="px-5 py-12 text-center text-sm text-ink-mute">
                        No exams yet.
                        <Link :href="route('lecturer.exams.create')" class="ml-1 underline underline-offset-2 hover:text-ink">Create one</Link>
                    </div>
                </div>
            </section>

            <!-- Classes -->
            <section>
                <header class="mb-3.5 flex items-center justify-between">
                    <h2 class="text-[13px] font-bold uppercase tracking-[0.06em] text-ink">Classes</h2>
                    <Link :href="route('lecturer.classes.index')" class="text-[11px] font-semibold tracking-wide text-ink-mute transition hover:text-ink">
                        Manage →
                    </Link>
                </header>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="cl in classes"
                        :key="cl.id"
                        class="border border-rule bg-white px-5 py-4"
                    >
                        <p class="text-[14px] font-bold text-ink">{{ cl.name }}</p>
                        <p class="mt-1 text-[11px] text-ink-mute">{{ cl.students }} student{{ cl.students === 1 ? '' : 's' }}</p>
                        <div class="mt-3 flex flex-wrap gap-1">
                            <span
                                v-for="s in cl.subjects"
                                :key="s"
                                class="bg-ivory-200 px-2 py-0.5 text-[10px] font-medium text-ink-soft"
                            >{{ s }}</span>
                            <span v-if="!cl.subjects?.length" class="text-[10px] italic text-ink-mute">No subjects</span>
                        </div>
                    </div>
                    <div
                        v-if="classes.length === 0"
                        class="col-span-full border border-dashed border-rule px-6 py-10 text-center text-sm text-ink-mute"
                    >
                        No classes yet.
                        <Link :href="route('lecturer.classes.create')" class="underline underline-offset-2 hover:text-ink">Create one</Link>.
                    </div>
                </div>
            </section>
        </div>

    </AuthenticatedLayout>
</template>
