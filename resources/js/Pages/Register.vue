<template>
    <div class="min-h-screen bg-[color:var(--sand)]">
        <Head :title="copy.pageTitle" />

        <header class="sticky top-0 z-20 border-b border-black/10 bg-[color:rgba(246,240,230,0.9)] backdrop-blur">
            <div class="mx-auto flex w-[90%] max-w-5xl items-center justify-between py-5 text-[var(--ink)]">
                <div>
                    <p class="text-xs uppercase tracking-[0.35em] text-[color:rgba(30,27,23,0.6)]">Somavi</p>
                    <p class="text-lg font-semibold" style="font-family: var(--font-display);">{{ copy.pageTitle }}</p>
                </div>
                <div class="flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.3em]">
                    <div class="relative">
                        <select
                            v-model="language"
                            class="appearance-none rounded-full border border-black/15 bg-white px-4 py-2 pr-9 text-[11px] font-semibold uppercase tracking-[0.3em] text-[var(--ink)]"
                        >
                            <option value="it">IT</option>
                            <option value="en">EN</option>
                        </select>
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-[color:rgba(30,27,23,0.6)]">▾</span>
                    </div>
                    <a href="/" class="rounded-full border border-black/15 px-4 py-2">{{ copy.backHome }}</a>
                </div>
            </div>
        </header>

        <main class="mx-auto flex w-[90%] max-w-5xl flex-col items-center pb-24 pt-16">
            <section class="w-full max-w-xl rounded-[2.5rem] border border-black/10 bg-white p-10">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.6)]">{{ copy.kicker }}</p>
                <h1 class="mt-4 text-3xl font-semibold" style="font-family: var(--font-display);">
                    {{ copy.title }}
                </h1>
                <p class="mt-3 text-sm text-[color:rgba(30,27,23,0.75)]">
                    {{ copy.subtitle }}
                </p>

                <form class="mt-8 space-y-5" @submit.prevent="submit">
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="flex flex-col gap-2 text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.6)]">
                            {{ copy.nameLabel }}
                            <input
                                v-model="form.name"
                                type="text"
                                class="rounded-xl border border-black/15 bg-white px-4 py-3 text-base text-[var(--ink)]"
                                autocomplete="given-name"
                            />
                            <span v-if="form.errors.name" class="text-[11px] font-semibold uppercase tracking-[0.25em] text-[var(--terracotta)]">
                                {{ form.errors.name }}
                            </span>
                        </label>
                        <label class="flex flex-col gap-2 text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.6)]">
                            {{ copy.surnameLabel }}
                            <input
                                v-model="form.surname"
                                type="text"
                                class="rounded-xl border border-black/15 bg-white px-4 py-3 text-base text-[var(--ink)]"
                                autocomplete="family-name"
                            />
                            <span v-if="form.errors.surname" class="text-[11px] font-semibold uppercase tracking-[0.25em] text-[var(--terracotta)]">
                                {{ form.errors.surname }}
                            </span>
                        </label>
                    </div>
                    <label class="flex flex-col gap-2 text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.6)]">
                        {{ copy.emailLabel }}
                        <input
                            v-model="form.email"
                            type="email"
                            class="rounded-xl border border-black/15 bg-white px-4 py-3 text-base text-[var(--ink)]"
                            autocomplete="email"
                        />
                        <span v-if="form.errors.email" class="text-[11px] font-semibold uppercase tracking-[0.25em] text-[var(--terracotta)]">
                            {{ form.errors.email }}
                        </span>
                    </label>
                    <label class="flex flex-col gap-2 text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.6)]">
                        {{ copy.passwordLabel }}
                        <input
                            v-model="form.password"
                            type="password"
                            class="rounded-xl border border-black/15 bg-white px-4 py-3 text-base text-[var(--ink)]"
                            autocomplete="new-password"
                        />
                        <span v-if="form.errors.password" class="text-[11px] font-semibold uppercase tracking-[0.25em] text-[var(--terracotta)]">
                            {{ form.errors.password }}
                        </span>
                    </label>
                    <label class="flex flex-col gap-2 text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.6)]">
                        {{ copy.passwordConfirmLabel }}
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            class="rounded-xl border border-black/15 bg-white px-4 py-3 text-base text-[var(--ink)]"
                            autocomplete="new-password"
                        />
                        <span v-if="form.errors.password_confirmation" class="text-[11px] font-semibold uppercase tracking-[0.25em] text-[var(--terracotta)]">
                            {{ form.errors.password_confirmation }}
                        </span>
                    </label>
                    <button
                        type="submit"
                        class="w-full rounded-full bg-[var(--ink)] px-4 py-3 text-xs font-semibold uppercase tracking-[0.3em] text-white"
                        :disabled="form.processing"
                    >
                        {{ copy.submitLabel }}
                    </button>
                </form>

                <div class="mt-6 rounded-2xl border border-black/10 bg-[color:rgba(30,27,23,0.03)] px-5 py-4 text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.7)]">
                    <p>{{ copy.loginPrompt }}</p>
                    <a href="/login" class="mt-3 inline-flex rounded-full border border-black/15 px-4 py-2 text-[11px] font-semibold">
                        {{ copy.loginCta }}
                    </a>
                </div>
            </section>
        </main>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const language = ref('en');

const pickDefaultLanguage = () => {
    if (typeof window === 'undefined') {
        return 'en';
    }

    const candidates = [
        document.documentElement?.lang,
        ...(navigator.languages || []),
        navigator.language,
    ].filter(Boolean);

    return candidates.some((value) => String(value).toLowerCase().startsWith('it'))
        ? 'it'
        : 'en';
};

onMounted(() => {
    language.value = pickDefaultLanguage();
});

const form = useForm({
    name: '',
    surname: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register');
};

const copy = computed(() => (language.value === 'it'
    ? {
        pageTitle: 'Registrati',
        backHome: 'Torna alla home',
        kicker: 'Area privata',
        title: 'Crea il tuo accesso.',
        subtitle: 'Ti bastano email e password per gestire le prenotazioni.',
        nameLabel: 'Nome',
        surnameLabel: 'Cognome',
        emailLabel: 'Email',
        passwordLabel: 'Password',
        passwordConfirmLabel: 'Conferma password',
        submitLabel: 'Crea account',
        loginPrompt: 'Hai gia un account?',
        loginCta: 'Accedi',
    }
    : {
        pageTitle: 'Create account',
        backHome: 'Back to home',
        kicker: 'Private area',
        title: 'Create your access.',
        subtitle: 'You only need email and password to manage bookings.',
        nameLabel: 'First name',
        surnameLabel: 'Last name',
        emailLabel: 'Email',
        passwordLabel: 'Password',
        passwordConfirmLabel: 'Confirm password',
        submitLabel: 'Create account',
        loginPrompt: 'Already have an account?',
        loginCta: 'Sign in',
    }
));
</script>
