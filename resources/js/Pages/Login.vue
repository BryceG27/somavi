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
                            <option v-for="locale in supportedLocales" :key="locale" :value="locale">
                                {{ localeOptionLabel(locale) }}
                            </option>
                        </select>
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-[color:rgba(30,27,23,0.6)]">▾</span>
                    </div>
                    <a :href="routes.home" class="rounded-full border border-black/15 px-4 py-2">{{ copy.backHome }}</a>
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
                    <a :href="routes.register" class="mt-3 inline-flex rounded-full border border-black/15 px-4 py-2 text-[11px] font-semibold">
                        {{ copy.registerCta }}
                    </a>
                </div>
            </section>
        </main>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const language = ref('en');
const page = usePage();
const localization = computed(() => page.props.localization || {});
const normalizeLocaleCode = (value) => String(value || '')
    .toLowerCase()
    .replace('_', '-')
    .split('-')[0];
const supportedLocales = computed(() => {
    const locales = Array.isArray(localization.value.supported_locales)
        ? localization.value.supported_locales.map((value) => normalizeLocaleCode(value)).filter(Boolean)
        : [];

    return locales.length ? [...new Set(locales)] : ['it', 'en'];
});
const localeLabels = computed(() => {
    const labels = localization.value.locale_labels;

    return labels && typeof labels === 'object' ? labels : {};
});
const localeOptionLabel = (locale) => localeLabels.value[locale] || locale.toUpperCase();
const defaultLocale = computed(() => {
    const locale = normalizeLocaleCode(localization.value.default_locale);

    return supportedLocales.value.includes(locale)
        ? locale
        : (supportedLocales.value[0] || 'en');
});

const pickDefaultLanguage = () => {
    if (typeof window === 'undefined') {
        return defaultLocale.value;
    }

    const candidates = [
        normalizeLocaleCode(document.documentElement?.lang),
        ...(navigator.languages || []),
        navigator.language,
    ].map((value) => normalizeLocaleCode(value))
        .filter(Boolean);

    for (const candidate of candidates) {
        if (supportedLocales.value.includes(candidate)) {
            return candidate;
        }
    }

    return defaultLocale.value;
};

onMounted(() => {
    language.value = pickDefaultLanguage();
    form.preferred_locale = language.value;
});

const form = useForm({
    email: '',
    password: '',
    preferred_locale: '',
});
const routes = computed(() => ({
    home: page.props.routes?.home || '/',
    login: page.props.routes?.login || '/login',
    register: page.props.routes?.register || '/register',
}));

const submit = () => {
    form.post(routes.value.login);
};

watch(
    () => language.value,
    (value) => {
        form.preferred_locale = normalizeLocaleCode(value);
    },
    { immediate: true },
);

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
