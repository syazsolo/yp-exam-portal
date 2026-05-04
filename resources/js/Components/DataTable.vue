<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import StatusPill from "@/Components/StatusPill.vue";

const props = defineProps({
    columns: { type: Array, default: () => [] },
    rows: { type: Array, default: () => [] },
    actions: { type: Array, default: () => [] },
    rowKey: { type: [String, Function], default: "id" },
    emptyMessage: { type: String, default: "Nothing to show yet." },
});

const emit = defineEmits(["action", "delete"]);

const hasActions = computed(() => props.actions.length > 0);
const visibleColumns = computed(() =>
    props.columns.filter((column) => column.show !== false),
);
const emptyColspan = computed(
    () => visibleColumns.value.length + (hasActions.value ? 1 : 0),
);

const alignClass = (align) =>
    ({
        center: "text-center",
        right: "text-right",
    })[align] ?? "text-left";

function valueFromPath(row, path) {
    if (!path) return undefined;

    return String(path)
        .split(".")
        .reduce((value, segment) => value?.[segment], row);
}

function rowIdentity(row, index) {
    return typeof props.rowKey === "function"
        ? props.rowKey(row, index)
        : (valueFromPath(row, props.rowKey) ?? index);
}

function columnKey(column, index) {
    return column.key ?? column.id ?? column.label ?? index;
}

function rawValue(row, column) {
    if (typeof column.value === "function") return column.value(row);

    return valueFromPath(row, column.key);
}

function displayValue(row, column) {
    const value = rawValue(row, column);
    const fallback = column.fallback ?? "None";

    if (typeof column.format === "function") return column.format(value, row);
    if (value === null || value === undefined || value === "") return fallback;

    return value;
}

function linkFor(row, column) {
    if (typeof column.href === "function") return column.href(row);

    return column.href;
}

function tagsFor(row, column) {
    const value =
        typeof column.items === "function"
            ? column.items(row)
            : rawValue(row, column);

    return Array.isArray(value) ? value : [];
}

function tagLabel(tag, column) {
    if (typeof column.tagLabel === "function") return column.tagLabel(tag);

    return tag?.[column.tagLabel ?? "name"] ?? tag;
}

function isActionVisible(action, row) {
    return typeof action.show === "function"
        ? action.show(row)
        : action.show !== false;
}

function actionLabel(action, row) {
    if (typeof action.label === "function") return action.label(row);

    if (action.type === "delete") return "Delete";
    if (action.type === "edit") return "Edit";

    return action.label ?? action.type ?? "Action";
}

function actionHref(action, row) {
    if (typeof action.href === "function") return action.href(row);

    return action.href;
}

function actionVariant(action, row) {
    return typeof action.variant === "function"
        ? action.variant(row)
        : action.variant;
}

function actionClass(action, row) {
    const variant = actionVariant(action, row);

    if (variant === "primary") {
        return "text-oxblood hover:bg-oxblood/10 hover:text-oxblood-dark";
    }

    if (variant === "danger" || action.type === "delete") {
        return "text-red-600 hover:bg-red-50 hover:text-red-700";
    }

    return "text-ink-mute hover:bg-ink/5 hover:text-ink";
}

function actionIcon(action) {
    if (action.icon) return action.icon;

    const name = action.name ?? action.type ?? action.label;

    if (name === "delete") return "trash";
    if (name === "edit") return "pencil";

    return "ellipsis";
}

function onAction(action, row) {
    const name = action.name ?? action.type ?? action.label;

    emit("action", { action: name, row });

    if (action.type === "delete" || action.name === "delete") {
        emit("delete", row);
    }
}
</script>

<template>
    <div
        class="overflow-hidden rounded-md border border-rule bg-ivory-50 shadow-[var(--shadow-paper)]"
    >
        <div
            v-if="$slots.caption"
            class="flex items-center justify-between gap-4 border-b border-rule bg-ivory-50 px-5 py-4"
        >
            <slot name="caption" />
        </div>
        <div class="overflow-x-auto">
            <table
                class="app-data-table min-w-full border-separate border-spacing-0 text-sm text-ink-soft"
            >
                <thead>
                    <tr>
                        <th
                            v-for="(column, index) in visibleColumns"
                            :key="columnKey(column, index)"
                            :class="alignClass(column.align)"
                        >
                            {{ column.label ?? column.header ?? column.key }}
                        </th>
                        <th v-if="hasActions" class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="rows.length === 0">
                        <td
                            :colspan="emptyColspan"
                            class="px-5 py-12 text-center text-sm text-ink-mute"
                        >
                            {{ emptyMessage }}
                        </td>
                    </tr>
                    <tr
                        v-for="(row, rowIndex) in rows"
                        :key="rowIdentity(row, rowIndex)"
                    >
                        <td
                            v-for="(column, columnIndex) in visibleColumns"
                            :key="columnKey(column, columnIndex)"
                            :class="[
                                alignClass(column.align),
                                column.cellClass,
                            ]"
                        >
                            <slot
                                :name="`cell-${columnKey(column, columnIndex)}`"
                                :row="row"
                                :value="rawValue(row, column)"
                                :display-value="displayValue(row, column)"
                            >
                                <Link
                                    v-if="column.type === 'link'"
                                    :href="linkFor(row, column)"
                                    class="font-semibold text-ink underline decoration-ink/25 underline-offset-4 transition hover:text-oxblood hover:decoration-oxblood focus:outline-none focus-visible:rounded-sm focus-visible:ring-2 focus-visible:ring-oxblood/30"
                                >
                                    {{ displayValue(row, column) }}
                                </Link>
                                <StatusPill
                                    v-else-if="column.type === 'status'"
                                    :status="rawValue(row, column)"
                                    :label="column.statusLabel?.(row)"
                                />
                                <div
                                    v-else-if="column.type === 'tags'"
                                    class="flex flex-wrap gap-1.5"
                                >
                                    <span
                                        v-if="tagsFor(row, column).length === 0"
                                        class="text-ink-mute"
                                    >
                                        {{ column.empty ?? "None" }}
                                    </span>
                                    <span
                                        v-for="tag in tagsFor(row, column)"
                                        :key="tag?.id ?? tagLabel(tag, column)"
                                        class="rounded-full border border-ink/10 bg-ink/5 px-2.5 py-1 text-xs font-medium text-ink-mute"
                                    >
                                        {{ tagLabel(tag, column) }}
                                    </span>
                                </div>
                                <span
                                    v-else
                                    :class="
                                        column.type === 'primary'
                                            ? 'font-semibold text-ink'
                                            : 'text-ink-mute'
                                    "
                                >
                                    {{ displayValue(row, column) }}
                                </span>
                            </slot>
                        </td>
                        <td v-if="hasActions">
                            <div
                                class="flex items-center justify-end gap-1.5 whitespace-nowrap"
                            >
                                <template
                                    v-for="action in actions"
                                    :key="
                                        action.name ??
                                        action.type ??
                                        action.label
                                    "
                                >
                                    <Link
                                        v-if="
                                            isActionVisible(action, row) &&
                                            actionHref(action, row)
                                        "
                                        :href="actionHref(action, row)"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md transition"
                                        :class="actionClass(action, row)"
                                        :aria-label="actionLabel(action, row)"
                                        :title="actionLabel(action, row)"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            aria-hidden="true"
                                        >
                                            <template
                                                v-if="
                                                    actionIcon(action) ===
                                                    'pencil'
                                                "
                                            >
                                                <path d="M12 20h9" />
                                                <path
                                                    d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"
                                                />
                                            </template>
                                            <template
                                                v-else-if="
                                                    actionIcon(action) ===
                                                    'trash'
                                                "
                                            >
                                                <path d="M3 6h18" />
                                                <path d="M8 6V4h8v2" />
                                                <path d="M6 6l1 15h10l1-15" />
                                                <path d="M10 11v6" />
                                                <path d="M14 11v6" />
                                            </template>
                                            <template
                                                v-else-if="
                                                    actionIcon(action) ===
                                                    'settings'
                                                "
                                            >
                                                <path d="M4 7h16" />
                                                <path d="M4 12h16" />
                                                <path d="M4 17h16" />
                                                <path d="M8 5v4" />
                                                <path d="M16 10v4" />
                                                <path d="M11 15v4" />
                                            </template>
                                            <template
                                                v-else-if="
                                                    actionIcon(action) === 'eye'
                                                "
                                            >
                                                <path
                                                    d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6z"
                                                />
                                                <path
                                                    d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"
                                                />
                                            </template>
                                            <template v-else>
                                                <path d="M5 12h.01" />
                                                <path d="M12 12h.01" />
                                                <path d="M19 12h.01" />
                                            </template>
                                        </svg>
                                        <span class="sr-only">{{
                                            actionLabel(action, row)
                                        }}</span>
                                    </Link>
                                    <button
                                        v-else-if="isActionVisible(action, row)"
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md transition"
                                        :class="actionClass(action, row)"
                                        :aria-label="actionLabel(action, row)"
                                        :title="actionLabel(action, row)"
                                        @click="onAction(action, row)"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            aria-hidden="true"
                                        >
                                            <template
                                                v-if="
                                                    actionIcon(action) ===
                                                    'pencil'
                                                "
                                            >
                                                <path d="M12 20h9" />
                                                <path
                                                    d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"
                                                />
                                            </template>
                                            <template
                                                v-else-if="
                                                    actionIcon(action) ===
                                                    'trash'
                                                "
                                            >
                                                <path d="M3 6h18" />
                                                <path d="M8 6V4h8v2" />
                                                <path d="M6 6l1 15h10l1-15" />
                                                <path d="M10 11v6" />
                                                <path d="M14 11v6" />
                                            </template>
                                            <template
                                                v-else-if="
                                                    actionIcon(action) ===
                                                    'settings'
                                                "
                                            >
                                                <path d="M4 7h16" />
                                                <path d="M4 12h16" />
                                                <path d="M4 17h16" />
                                                <path d="M8 5v4" />
                                                <path d="M16 10v4" />
                                                <path d="M11 15v4" />
                                            </template>
                                            <template
                                                v-else-if="
                                                    actionIcon(action) === 'eye'
                                                "
                                            >
                                                <path
                                                    d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6z"
                                                />
                                                <path
                                                    d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"
                                                />
                                            </template>
                                            <template v-else>
                                                <path d="M5 12h.01" />
                                                <path d="M12 12h.01" />
                                                <path d="M19 12h.01" />
                                            </template>
                                        </svg>
                                        <span class="sr-only">{{
                                            actionLabel(action, row)
                                        }}</span>
                                    </button>
                                </template>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style>
.app-data-table thead {
    @apply bg-white/70;
}

.app-data-table th {
    @apply whitespace-nowrap border-b border-rule px-5 py-3 font-sans text-[11px] font-semibold uppercase tracking-label text-ink-mute;
}

.app-data-table td {
    @apply border-b border-rule px-5 py-4 align-middle;
}

.app-data-table tbody tr {
    @apply bg-white/80 transition-colors duration-150;
}

.app-data-table tbody tr:hover {
    @apply bg-ivory-50;
}

.app-data-table tbody tr:last-child td {
    @apply border-b-0;
}
</style>
