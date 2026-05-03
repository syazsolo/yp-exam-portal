<script setup>
import { computed } from "vue";

const props = defineProps({
    status: { type: String, required: true },
    label: { type: String, default: null },
});

const variants = {
    active: "border-emerald-200 bg-emerald-50 text-emerald-700",
    closed: "border-red-200 bg-red-50 text-red-700",
    draft: "border-ink/10 bg-ink/5 text-ink-mute",
    invalid: "border-red-200 bg-red-50 text-red-700",
    pending: "border-amber-200 bg-amber-50 text-amber-700",
    pending_review: "border-amber-200 bg-amber-50 text-amber-700",
    scored: "border-emerald-200 bg-emerald-50 text-emerald-700",
    submitted: "border-sky-200 bg-sky-50 text-sky-700",
};

const variantClass = computed(
    () => variants[props.status] ?? "border-ink/10 bg-ink/5 text-ink-mute",
);

const displayLabel = computed(
    () => props.label ?? props.status.replaceAll("_", " "),
);
</script>

<template>
    <span
        class="inline-flex rounded-full border px-2.5 py-1 text-[11px] font-semibold uppercase leading-none"
        :class="variantClass"
    >
        {{ displayLabel }}
    </span>
</template>
