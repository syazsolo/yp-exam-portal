<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    roles: { type: Array, default: () => [] },
});

const fallbackRoles = [
    { value: 'lecturer', label: 'Lecturer', description: 'Create exams, review submissions, manage classes and subjects.' },
    { value: 'student',  label: 'Student',  description: 'Take exams, track your attempts, stay on top of upcoming assessments.' },
];
const roleOptions = computed(() => props.roles.length ? props.roles : fallbackRoles);

const form = useForm({
    name: '',
    email: '',
    role: '',
    password: '',
    password_confirmation: '',
});

const showPass    = ref(false);
const showConfirm = ref(false);
const focused     = ref('');

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const fieldClass = (key, error) => ({
    'border-oxblood': error,
    'border-ink bg-ivory-50': !error && focused.value === key,
    'border-rule bg-transparent': !error && focused.value !== key,
});
</script>

<template>
    <GuestLayout :alt-link="{ href: route('login'), label: 'Sign in instead' }">
        <Head title="Create account" />

        <header class="mb-9">
            <div class="mb-2.5 text-[11px] font-semibold uppercase tracking-[0.12em] text-ink-mute">Create account</div>
            <h1 class="font-logo text-[40px] font-light leading-[1.08] tracking-tight text-ink">
                Join the portal.
            </h1>
        </header>

        <form @submit.prevent="submit" novalidate>
            <!-- Name -->
            <div class="mb-[18px]">
                <label for="name" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.08em]" :class="form.errors.name ? 'text-oxblood' : 'text-ink-mute'">Full name</label>
                <div class="flex items-center border-[1.5px] transition" :class="fieldClass('name', form.errors.name)">
                    <input
                        id="name" v-model="form.name" required autofocus autocomplete="name"
                        @focus="focused = 'name'" @blur="focused = ''"
                        class="flex-1 border-0 bg-transparent px-3.5 py-2.5 text-sm text-ink outline-none focus:ring-0"
                    />
                </div>
                <p v-if="form.errors.name" class="mt-1.5 text-[11px] text-oxblood">{{ form.errors.name }}</p>
            </div>

            <!-- Email -->
            <div class="mb-[18px]">
                <label for="email" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.08em]" :class="form.errors.email ? 'text-oxblood' : 'text-ink-mute'">Email address</label>
                <div class="flex items-center border-[1.5px] transition" :class="fieldClass('email', form.errors.email)">
                    <input
                        id="email" type="email" v-model="form.email" required autocomplete="username"
                        @focus="focused = 'email'" @blur="focused = ''"
                        class="flex-1 border-0 bg-transparent px-3.5 py-2.5 text-sm text-ink outline-none focus:ring-0"
                    />
                </div>
                <p v-if="form.errors.email" class="mt-1.5 text-[11px] text-oxblood">{{ form.errors.email }}</p>
            </div>

            <!-- Role -->
            <div class="mb-[18px]">
                <div class="mb-2 text-[11px] font-semibold uppercase tracking-[0.08em]" :class="form.errors.role ? 'text-oxblood' : 'text-ink-mute'">I am a</div>
                <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                    <button
                        v-for="r in roleOptions"
                        :key="r.value"
                        type="button"
                        @click="form.role = r.value"
                        class="border-[1.5px] px-4 py-3.5 text-left transition"
                        :class="form.role === r.value
                            ? 'border-ink bg-ink text-ivory'
                            : 'border-rule bg-transparent text-ink hover:border-ink-soft'"
                    >
                        <div
                            class="mb-1 text-[12px] font-semibold uppercase tracking-[0.06em]"
                            :class="form.role === r.value ? 'text-ivory' : 'text-ink'"
                        >{{ r.label }}</div>
                        <div
                            class="text-[12px] font-light leading-[1.55]"
                            :class="form.role === r.value ? 'text-ivory/60' : 'text-ink-mute'"
                        >{{ r.description }}</div>
                    </button>
                </div>
                <p v-if="form.errors.role" class="mt-1.5 text-[11px] text-oxblood">{{ form.errors.role }}</p>
            </div>

            <!-- Password -->
            <div class="mb-[18px]">
                <label for="password" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.08em]" :class="form.errors.password ? 'text-oxblood' : 'text-ink-mute'">Password</label>
                <div class="flex items-center border-[1.5px] transition" :class="fieldClass('password', form.errors.password)">
                    <input
                        id="password" :type="showPass ? 'text' : 'password'" v-model="form.password" required autocomplete="new-password"
                        @focus="focused = 'password'" @blur="focused = ''"
                        class="flex-1 border-0 bg-transparent px-3.5 py-2.5 text-sm text-ink outline-none focus:ring-0"
                    />
                    <button type="button" @click="showPass = !showPass"
                        class="px-3.5 text-[11px] font-semibold uppercase tracking-[0.06em] text-ink-mute transition hover:text-ink"
                    >{{ showPass ? 'Hide' : 'Show' }}</button>
                </div>
                <p v-if="form.errors.password" class="mt-1.5 text-[11px] text-oxblood">{{ form.errors.password }}</p>
            </div>

            <!-- Confirm -->
            <div class="mb-[18px]">
                <label for="password_confirmation" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.08em]" :class="form.errors.password_confirmation ? 'text-oxblood' : 'text-ink-mute'">Confirm password</label>
                <div class="flex items-center border-[1.5px] transition" :class="fieldClass('confirm', form.errors.password_confirmation)">
                    <input
                        id="password_confirmation" :type="showConfirm ? 'text' : 'password'" v-model="form.password_confirmation" required autocomplete="new-password"
                        @focus="focused = 'confirm'" @blur="focused = ''"
                        class="flex-1 border-0 bg-transparent px-3.5 py-2.5 text-sm text-ink outline-none focus:ring-0"
                    />
                    <button type="button" @click="showConfirm = !showConfirm"
                        class="px-3.5 text-[11px] font-semibold uppercase tracking-[0.06em] text-ink-mute transition hover:text-ink"
                    >{{ showConfirm ? 'Hide' : 'Show' }}</button>
                </div>
                <p v-if="form.errors.password_confirmation" class="mt-1.5 text-[11px] text-oxblood">{{ form.errors.password_confirmation }}</p>
            </div>

            <div class="mt-7 flex items-center justify-between">
                <Link
                    :href="route('login')"
                    class="text-[12px] font-normal text-ink-mute underline underline-offset-[3px] transition hover:text-ink"
                >Already registered?</Link>
                <button
                    type="submit"
                    :disabled="form.processing"
                    :class="form.processing ? 'opacity-40' : ''"
                    class="bg-ink px-8 py-3.5 text-[11px] font-semibold uppercase tracking-[0.1em] text-ivory transition hover:bg-oxblood disabled:cursor-not-allowed"
                >Register</button>
            </div>
        </form>
    </GuestLayout>
</template>
