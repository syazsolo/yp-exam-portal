<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

defineProps({ status: String });

const form = useForm({ email: "" });
const focused = ref("");

const submit = () => form.post(route("password.email"));
</script>

<template>
    <GuestLayout :alt-link="{ href: route('login'), label: 'Back to sign in' }">
        <Head title="Forgot password" />

        <header class="mb-7">
            <div
                class="mb-2.5 text-[11px] font-semibold uppercase tracking-[0.12em] text-ink-mute"
            >
                Reset password
            </div>
            <h1
                class="font-logo text-[40px] font-light leading-[1.08] tracking-tight text-ink"
            >
                Forgot it? No worry.
            </h1>
            <p
                class="mt-5 max-w-[380px] text-[13px] font-light leading-[1.75] text-ink-mute"
            >
                Enter the email tied to your account and we'll send you a secure
                link to choose a new password.
            </p>
        </header>

        <p
            v-if="status"
            class="mb-5 border border-emerald-200/60 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800"
        >
            {{ status }}
        </p>

        <form @submit.prevent="submit" novalidate>
            <div class="mb-6">
                <label
                    for="email"
                    class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.08em]"
                    :class="
                        form.errors.email ? 'text-oxblood' : 'text-ink-mute'
                    "
                >
                    Email address
                </label>
                <div
                    class="flex items-center border-[1.5px] transition"
                    :class="
                        form.errors.email
                            ? 'border-oxblood'
                            : focused === 'email'
                              ? 'border-ink bg-ivory-50'
                              : 'border-rule bg-transparent'
                    "
                >
                    <input
                        id="email"
                        type="email"
                        v-model="form.email"
                        @focus="focused = 'email'"
                        @blur="focused = ''"
                        required
                        autofocus
                        autocomplete="username"
                        class="flex-1 border-0 bg-transparent px-3.5 py-2.5 text-sm text-ink outline-none focus:ring-0"
                    />
                </div>
                <p
                    v-if="form.errors.email"
                    class="mt-1.5 text-[11px] text-oxblood"
                >
                    {{ form.errors.email }}
                </p>
            </div>

            <div class="flex items-center justify-between">
                <Link
                    :href="route('login')"
                    class="text-[12px] font-normal text-ink-mute underline underline-offset-[3px] transition hover:text-ink"
                    >Remembered it?</Link
                >
                <button
                    type="submit"
                    :disabled="form.processing"
                    :class="form.processing ? 'opacity-40' : ''"
                    class="bg-ink px-8 py-3.5 text-[11px] font-semibold uppercase tracking-[0.1em] text-ivory transition hover:bg-oxblood disabled:cursor-not-allowed"
                >
                    Send reset link
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
