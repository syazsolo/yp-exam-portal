<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPass = ref(false);
const focused  = ref('');

const submit = () => {
    form.post(route('login'), { onFinish: () => form.reset('password') });
};
</script>

<template>
    <GuestLayout :alt-link="{ href: route('register'), label: 'Create an account' }">
        <Head title="Sign in" />

        <header class="mb-9">
            <div class="mb-2.5 text-[11px] font-semibold uppercase tracking-[0.12em] text-ink-mute">Sign in</div>
            <h1 class="font-logo text-[40px] font-light leading-[1.08] tracking-tight text-ink">
                Welcome back.
            </h1>
        </header>

        <p v-if="status" class="mb-5 border border-emerald-200/60 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">
            {{ status }}
        </p>

        <form @submit.prevent="submit" novalidate>
            <!-- Email -->
            <div class="mb-[18px]">
                <label for="email" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.08em]" :class="form.errors.email ? 'text-oxblood' : 'text-ink-mute'">
                    Email address
                </label>
                <div
                    class="flex items-center border-[1.5px] transition"
                    :class="form.errors.email
                        ? 'border-oxblood'
                        : focused === 'email' ? 'border-ink bg-ivory-50' : 'border-rule bg-transparent'"
                >
                    <input
                        id="email" type="email" v-model="form.email"
                        @focus="focused = 'email'" @blur="focused = ''"
                        required autofocus autocomplete="username"
                        class="flex-1 border-0 bg-transparent px-3.5 py-2.5 text-sm text-ink outline-none focus:ring-0"
                    />
                </div>
                <p v-if="form.errors.email" class="mt-1.5 text-[11px] text-oxblood">{{ form.errors.email }}</p>
            </div>

            <!-- Password -->
            <div class="mb-[18px]">
                <div class="mb-1.5 flex items-baseline justify-between">
                    <label for="password" class="text-[11px] font-semibold uppercase tracking-[0.08em]" :class="form.errors.password ? 'text-oxblood' : 'text-ink-mute'">
                        Password
                    </label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-[11px] text-ink-mute underline underline-offset-[3px] transition hover:text-ink"
                    >Forgot password?</Link>
                </div>
                <div
                    class="flex items-center border-[1.5px] transition"
                    :class="form.errors.password
                        ? 'border-oxblood'
                        : focused === 'password' ? 'border-ink bg-ivory-50' : 'border-rule bg-transparent'"
                >
                    <input
                        id="password" :type="showPass ? 'text' : 'password'" v-model="form.password"
                        @focus="focused = 'password'" @blur="focused = ''"
                        required autocomplete="current-password"
                        class="flex-1 border-0 bg-transparent px-3.5 py-2.5 text-sm text-ink outline-none focus:ring-0"
                    />
                    <button
                        type="button" @click="showPass = !showPass"
                        class="px-3.5 text-[11px] font-semibold uppercase tracking-[0.06em] text-ink-mute transition hover:text-ink"
                    >{{ showPass ? 'Hide' : 'Show' }}</button>
                </div>
                <p v-if="form.errors.password" class="mt-1.5 text-[11px] text-oxblood">{{ form.errors.password }}</p>
            </div>

            <!-- Remember -->
            <label class="mb-6 flex cursor-pointer items-center gap-2 text-[12px] text-ink-soft">
                <input type="checkbox" v-model="form.remember" class="h-4 w-4 rounded-none border-[1.5px] border-rule bg-transparent text-ink focus:ring-0 focus:ring-offset-0" />
                Remember me on this device
            </label>

            <div class="mt-6 flex items-center justify-between">
                <Link
                    :href="route('register')"
                    class="text-[12px] font-normal text-ink-mute underline underline-offset-[3px] transition hover:text-ink"
                >Don't have an account?</Link>
                <button
                    type="submit"
                    :disabled="form.processing"
                    :class="form.processing ? 'opacity-40' : ''"
                    class="bg-ink px-8 py-3.5 text-[11px] font-semibold uppercase tracking-[0.1em] text-ivory transition hover:bg-oxblood disabled:cursor-not-allowed"
                >Sign in</button>
            </div>
        </form>
    </GuestLayout>
</template>
