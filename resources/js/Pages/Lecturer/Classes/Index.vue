<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";

defineProps({ classes: Array });
function destroy(id) {
    if (confirm("Delete this class?"))
        router.delete(route("lecturer.classes.destroy", id));
}
</script>

<template>
    <Head title="Classes" />
    <AuthenticatedLayout>
        <template #header
            ><h2 class="text-xl font-semibold text-gray-800">
                Classes
            </h2></template
        >
        <div class="mx-auto max-w-5xl space-y-4 px-4 py-8">
            <div
                v-if="$page.props.flash?.success"
                class="rounded border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800"
            >
                {{ $page.props.flash.success }}
            </div>
            <div class="flex justify-end">
                <Link
                    :href="route('lecturer.classes.create')"
                    class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700"
                    >+ New Class</Link
                >
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div
                    v-if="classes.length === 0"
                    class="col-span-2 text-sm text-gray-400"
                >
                    No classes yet.
                </div>
                <div
                    v-for="c in classes"
                    :key="c.id"
                    class="rounded-lg border bg-white p-5"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-semibold text-gray-800">
                                {{ c.name }}
                            </p>
                            <p class="mt-0.5 text-xs text-gray-400">
                                {{ c.students }} students
                            </p>
                        </div>
                        <div class="flex gap-2 text-sm">
                            <Link
                                :href="route('lecturer.classes.show', c.id)"
                                class="text-gray-500 hover:underline"
                                >Manage</Link
                            >
                            <Link
                                :href="route('lecturer.classes.edit', c.id)"
                                class="text-gray-500 hover:underline"
                                >Edit</Link
                            >
                            <button
                                @click="destroy(c.id)"
                                class="text-red-500 hover:underline"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-1">
                        <span
                            v-for="s in c.subjects"
                            :key="s.id"
                            class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600"
                            >{{ s.name }}</span
                        >
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
