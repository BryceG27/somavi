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
                            autocomplete="current-password"
                        />
                        <span v-if="form.errors.password" class="text-[11px] font-semibold uppercase tracking-[0.25em] text-[var(--terracotta)]">
                            {{ form.errors.password }}
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
                    <p>{{ copy.registerPrompt }}</p>
                    <a href="/register" class="mt-3 inline-flex rounded-full border border-black/15 px-4 py-2 text-[11px] font-semibold">
                        {{ copy.registerCta }}
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
    email: '',
    password: '',
});

const submit = () => {
    form.post('/login');
};

const copy = computed(() => (language.value === 'it'
    ? {
        pageTitle: 'Accedi',
        backHome: 'Torna alla home',
        kicker: 'Area privata',
        title: 'Accedi con i tuoi dati.',
        subtitle: 'Usa email e password per gestire le tue prenotazioni.',
        emailLabel: 'Email',
        passwordLabel: 'Password',
        submitLabel: 'Accedi',
        registerPrompt: 'Non hai un account?',
        registerCta: 'Registrati',
    }
    : {
        pageTitle: 'Sign in',
        backHome: 'Back to home',
        kicker: 'Private area',
        title: 'Sign in with your details.',
        subtitle: 'Use email and password to manage your bookings.',
        emailLabel: 'Email',
        passwordLabel: 'Password',
        submitLabel: 'Sign in',
        registerPrompt: "Don't have an account?",
        registerCta: 'Create account',
    }
));
</script>
