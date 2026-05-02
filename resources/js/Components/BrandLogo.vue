<script setup>
defineProps({
    tone: { type: String, default: "ink" },
});
</script>

<template>
    <span class="inline-flex items-end gap-2.5 sm:gap-3">
        <span
            class="font-logo text-xl font-light italic leading-none tracking-tight"
            :class="tone === 'ivory' ? 'text-ivory' : 'text-ink'"
            aria-hidden="true"
        >
            yp
        </span>
        <span
            class="font-sans text-[11px] font-medium uppercase tracking-label"
            :class="tone === 'ivory' ? 'text-ivory/40' : 'text-ink-soft'"
        >
            Exam Portal
        </span>
        <span class="sr-only">YP Examination Portal</span>
    </span>
</template>
