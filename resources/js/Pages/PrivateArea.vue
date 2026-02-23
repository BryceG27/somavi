<template>
    <div class="min-h-screen bg-[color:var(--sand)]">
        <Head :title="copy.pageTitle" />

        <header class="sticky top-0 z-20 border-b border-black/10 bg-[color:rgba(246,240,230,0.9)] backdrop-blur">
            <div class="mx-auto flex w-[90%] max-w-6xl items-center justify-between py-5 text-[var(--ink)]">
                <div class="flex items-center gap-4">
                    <div class="flex h-11 w-11 items-center justify-center rounded-full border border-black/20 text-xs font-semibold uppercase tracking-[0.3em]">
                        AV
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.35em] text-[color:rgba(30,27,23,0.6)]">La Kalsa di Somavi</p>
                        <p class="text-lg font-semibold" style="font-family: var(--font-display);">{{ copy.pageTitle }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.3em]">
                    <div class="relative">
                        <select
                            v-model="language"
                            class="appearance-none rounded-full border border-black/15 bg-white px-4 py-2 pr-9 text-[11px] font-semibold uppercase tracking-[0.3em] text-(--ink) hover:opacity-70"
                            style="cursor: pointer"
                        >
                            <option v-for="locale in supportedLocales" :key="locale" :value="locale">
                                {{ localeOptionLabel(locale) }}
                            </option>
                        </select>
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-[color:rgba(30,27,23,0.6)]">▾</span>
                    </div>
                    <a :href="homeUrl" class="rounded-full border border-black/15 px-4 py-2 hover:opacity-80">{{ copy.backHome }}</a>
                    <button type="button" class="rounded-full bg-(--ink) px-4 py-2 text-white" @click="logout">
                        {{ copy.logout }}
                    </button>
                </div>
            </div>
        </header>

        <main class="mx-auto w-[90%] max-w-6xl pb-24 pt-14">
            <section
                v-if="paymentNotice"
                class="mb-8 rounded-[2.5rem] p-6 text-[color:rgba(30,27,23,0.8)]"
                :class="paymentNotice.classes"
            >
                <p class="text-xs font-semibold uppercase tracking-[0.3em]" :class="paymentNotice.kickerClass">
                    {{ paymentNotice.kicker }}
                </p>
                <p class="mt-3 text-sm">
                    {{ paymentNotice.body }}
                </p>
            </section>
            <section
                v-if="paymentActionError"
                class="mb-8 rounded-[2.5rem] border border-rose-200 bg-rose-50 p-6 text-[color:rgba(30,27,23,0.8)]"
            >
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-rose-700">
                    {{ copy.paymentActionErrorKicker }}
                </p>
                <p class="mt-3 text-sm">
                    {{ paymentActionError }}
                </p>
            </section>
            <section
                v-if="profileSavedSuccess"
                class="mb-8 rounded-[2.5rem] border border-emerald-200 bg-emerald-50 p-6 text-[color:rgba(30,27,23,0.8)]"
            >
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-emerald-700">
                    {{ copy.profileSavedKicker }}
                </p>
                <p class="mt-3 text-sm">
                    {{ copy.profileSavedBody }}
                </p>
            </section>
            <section class="rounded-[2.5rem] border border-black/10 bg-white p-10">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.6)]">{{ copy.detailsKicker }}</p>
                <h1 class="mt-4 text-3xl font-semibold" style="font-family: var(--font-display);">{{ copy.profileTitle }}</h1>
                <form class="mt-6 grid gap-4 md:grid-cols-2" @submit.prevent="submitProfile">
                    <label class="flex flex-col gap-2 text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.6)]">
                        {{ copy.nameLabel }}
                        <input v-model="profileForm.name" type="text" class="rounded-xl border border-black/15 bg-white px-4 py-3 text-base text-[var(--ink)]" />
                        <span v-if="profileForm.errors.name" class="text-[11px] font-semibold uppercase tracking-[0.25em] text-[var(--terracotta)]">
                            {{ profileForm.errors.name }}
                        </span>
                    </label>
                    <label class="flex flex-col gap-2 text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.6)]">
                        {{ copy.surnameLabel }}
                        <input v-model="profileForm.surname" type="text" class="rounded-xl border border-black/15 bg-white px-4 py-3 text-base text-[var(--ink)]" />
                        <span v-if="profileForm.errors.surname" class="text-[11px] font-semibold uppercase tracking-[0.25em] text-[var(--terracotta)]">
                            {{ profileForm.errors.surname }}
                        </span>
                    </label>
                    <label class="flex flex-col gap-2 text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.6)]">
                        {{ copy.emailLabel }}
                        <input v-model="profileForm.email" type="email" class="rounded-xl border border-black/15 bg-white px-4 py-3 text-base text-[var(--ink)]" />
                        <span v-if="profileForm.errors.email" class="text-[11px] font-semibold uppercase tracking-[0.25em] text-[var(--terracotta)]">
                            {{ profileForm.errors.email }}
                        </span>
                    </label>
                    <label class="flex flex-col gap-2 text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.6)]">
                        {{ copy.phoneLabel }}
                        <input v-model="profileForm.phone" type="text" class="rounded-xl border border-black/15 bg-white px-4 py-3 text-base text-[var(--ink)]" />
                        <span v-if="profileForm.errors.phone" class="text-[11px] font-semibold uppercase tracking-[0.25em] text-[var(--terracotta)]">
                            {{ profileForm.errors.phone }}
                        </span>
                    </label>
                    <label class="flex flex-col gap-2 text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.6)]">
                        {{ copy.preferredLanguageLabel }}
                        <select v-model="profileForm.preferred_locale" class="rounded-xl border border-black/15 bg-white px-4 py-3 text-base text-[var(--ink)]">
                            <option v-for="locale in supportedLocales" :key="locale" :value="locale">
                                {{ localeOptionLabel(locale) }}
                            </option>
                        </select>
                        <span v-if="profileForm.errors.preferred_locale" class="text-[11px] font-semibold uppercase tracking-[0.25em] text-[var(--terracotta)]">
                            {{ profileForm.errors.preferred_locale }}
                        </span>
                    </label>
                    <div class="md:col-span-2">
                        <button type="submit" class="rounded-full border border-black/15 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] hover:bg-[color:rgba(0,0,0,0.05)]">
                            {{ copy.updateProfile }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="mt-10 rounded-[2.5rem] border border-black/10 bg-white p-8">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.6)]">{{ copy.bookingsKicker }}</p>
                <div class="mt-4 grid gap-6 lg:grid-cols-2">
                    <div>
                        <h2 class="text-2xl font-semibold" style="font-family: var(--font-display);">{{ copy.bookingsTitle }}</h2>
                        <div v-if="upcomingReservations.length" class="mt-6 space-y-4">
                            <div
                                v-for="reservation in upcomingReservations"
                                :key="reservation.id"
                                class="rounded-2xl border border-black/10 bg-[color:rgba(30,27,23,0.03)] p-5"
                            >
                                <p class="text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.6)]">
                                    {{ reservationTitle(reservation) }}
                                </p>
                                <p class="mt-3 text-sm text-[color:rgba(30,27,23,0.75)]">
                                    {{ formatDate(reservation.start_date) }} → {{ formatDate(reservation.end_date) }}
                                </p>
                                <p class="mt-2 text-sm text-[color:rgba(30,27,23,0.75)]">
                                    {{ formatCurrency(reservation.total) }}
                                </p>
                                <p class="mt-2 text-sm text-[color:rgba(30,27,23,0.75)]">
                                    {{ copy.totalPaidLabel }}: {{ formatCurrency(reservation.total_paid) }}
                                </p>
                                <p v-if="reservation.total_authorized > 0" class="mt-1 text-xs uppercase tracking-[0.25em] text-[color:rgba(30,27,23,0.6)]">
                                    {{ copy.totalAuthorizedLabel }}: {{ formatCurrency(reservation.total_authorized) }}
                                </p>
                                <p v-if="reservation.amount_due > 0" class="mt-1 text-sm font-semibold text-[color:rgba(30,27,23,0.85)]">
                                    {{ copy.amountDueLabel }}: {{ formatCurrency(reservation.amount_due) }}
                                </p>
                                <span
                                    class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em]"
                                    :class="reservationStatusClass(reservation.status)"
                                >
                                    {{ reservationStatusLabel(reservation.status) }}
                                </span>
                                <div
                                    v-if="canPayReservation(reservation)"
                                    class="mt-4 rounded-xl border border-black/10 bg-white px-4 py-3 text-[color:rgba(30,27,23,0.8)]"
                                >
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.6)]">
                                        {{ copy.nextPaymentLabel }}: {{ paymentStepLabel(reservation.next_pending_payment.step) }}
                                    </p>
                                    <p class="mt-2 text-sm">
                                        {{ formatCurrency(reservation.next_pending_payment.amount, reservation.next_pending_payment.currency) }}
                                    </p>
                                    <p v-if="reservation.next_pending_payment.due_at" class="mt-1 text-xs uppercase tracking-[0.25em] text-[color:rgba(30,27,23,0.6)]">
                                        {{ copy.dueDateLabel }}: {{ formatDate(reservation.next_pending_payment.due_at) }}
                                    </p>
                                    <button
                                        type="button"
                                        class="mt-3 rounded-full bg-(--ink) px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-white disabled:opacity-60"
                                        :disabled="isPayingReservation(reservation.id)"
                                        @click="payReservation(reservation)"
                                    >
                                        {{ isPayingReservation(reservation.id) ? copy.redirectingToCheckout : paymentButtonLabel(reservation) }}
                                    </button>
                                </div>
                                <button
                                    v-if="reservation.status !== 'cancelled'"
                                    type="button"
                                    class="mt-4 rounded-full border border-black/15 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] hover:bg-[color:rgba(0,0,0,0.05)]"
                                    @click="cancelReservation(reservation.id)"
                                >
                                    {{ copy.cancelReservation }}
                                </button>
                            </div>
                        </div>
                        <div v-else class="mt-6 space-y-4">
                            <div class="rounded-2xl border border-black/10 bg-[color:rgba(30,27,23,0.03)] p-5">
                                <p class="text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.6)]">{{ copy.noBookingTitle }}</p>
                                <p class="mt-3 text-sm text-[color:rgba(30,27,23,0.75)]">
                                    {{ copy.noBookingBody }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-2xl font-semibold" style="font-family: var(--font-display);">{{ copy.staysTitle }}</h2>
                        <div v-if="pastReservations.length" class="mt-6 space-y-4">
                            <div
                                v-for="reservation in pastReservations"
                                :key="reservation.id"
                                class="rounded-2xl border border-black/10 bg-[color:rgba(30,27,23,0.03)] p-5"
                            >
                                <p class="text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.6)]">
                                    {{ reservationTitle(reservation) }}
                                </p>
                                <p class="mt-3 text-sm text-[color:rgba(30,27,23,0.75)]">
                                    {{ formatDate(reservation.start_date) }} → {{ formatDate(reservation.end_date) }}
                                </p>
                                <p class="mt-2 text-sm text-[color:rgba(30,27,23,0.75)]">
                                    {{ formatCurrency(reservation.total) }}
                                </p>
                                <p class="mt-2 text-sm text-[color:rgba(30,27,23,0.75)]">
                                    {{ copy.totalPaidLabel }}: {{ formatCurrency(reservation.total_paid) }}
                                </p>
                                <p v-if="reservation.total_authorized > 0" class="mt-1 text-xs uppercase tracking-[0.25em] text-[color:rgba(30,27,23,0.6)]">
                                    {{ copy.totalAuthorizedLabel }}: {{ formatCurrency(reservation.total_authorized) }}
                                </p>
                                <p v-if="reservation.amount_due > 0" class="mt-1 text-sm font-semibold text-[color:rgba(30,27,23,0.85)]">
                                    {{ copy.amountDueLabel }}: {{ formatCurrency(reservation.amount_due) }}
                                </p>
                                <span
                                    class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em]"
                                    :class="reservationStatusClass(reservation.status)"
                                >
                                    {{ reservationStatusLabel(reservation.status) }}
                                </span>
                                <div
                                    v-if="canPayReservation(reservation)"
                                    class="mt-4 rounded-xl border border-black/10 bg-white px-4 py-3 text-[color:rgba(30,27,23,0.8)]"
                                >
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.6)]">
                                        {{ copy.nextPaymentLabel }}: {{ paymentStepLabel(reservation.next_pending_payment.step) }}
                                    </p>
                                    <p class="mt-2 text-sm">
                                        {{ formatCurrency(reservation.next_pending_payment.amount, reservation.next_pending_payment.currency) }}
                                    </p>
                                    <p v-if="reservation.next_pending_payment.due_at" class="mt-1 text-xs uppercase tracking-[0.25em] text-[color:rgba(30,27,23,0.6)]">
                                        {{ copy.dueDateLabel }}: {{ formatDate(reservation.next_pending_payment.due_at) }}
                                    </p>
                                    <button
                                        type="button"
                                        class="mt-3 rounded-full bg-(--ink) px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-white disabled:opacity-60"
                                        :disabled="isPayingReservation(reservation.id)"
                                        @click="payReservation(reservation)"
                                    >
                                        {{ isPayingReservation(reservation.id) ? copy.redirectingToCheckout : paymentButtonLabel(reservation) }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="mt-6 space-y-4">
                            <div class="rounded-2xl border border-black/10 bg-[color:rgba(30,27,23,0.03)] p-5">
                                <p class="text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.6)]">{{ copy.noStaysTitle }}</p>
                                <p class="mt-3 text-sm text-[color:rgba(30,27,23,0.75)]">
                                    {{ copy.noStaysBody }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    auth: {
        type: Object,
        default: null,
    },
    reservations: {
        type: Array,
        default: () => [],
    },
});

const language = ref('en');
const paymentState = ref('');
const payingReservationId = ref(null);
const paymentActionError = ref('');
const profileSavedSuccess = ref(false);
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
const routeUrls = computed(() => page.props.routes || {});
const homeUrl = computed(() => routeUrls.value.home || '/');
const logoutUrl = computed(() => routeUrls.value.logout || '/logout');
const profileUpdateUrl = computed(() => routeUrls.value.private_area_profile_update || '/private-area/profile');
const reservationCancelTemplate = computed(() => routeUrls.value.reservation_cancel_template || '/reservations/__reservation__/cancel');
const stripeCheckoutTemplate = computed(() => routeUrls.value.stripe_checkout_template || '/payments/stripe/checkout/__reservation__');

const withReservationId = (template, reservationId) => (
    String(template).replace('__reservation__', encodeURIComponent(String(reservationId)))
);

const pickDefaultLanguage = () => {
    const preferredLocale = normalizeLocaleCode(props.auth?.user?.preferred_locale);

    if (supportedLocales.value.includes(preferredLocale)) {
        return preferredLocale;
    }

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

    if (typeof window !== 'undefined') {
        const params = new URLSearchParams(window.location.search);
        paymentState.value = params.get('payment') || '';
    }
});

const logout = () => {
    router.post(logoutUrl.value);
};

const profileForm = useForm({
    name: props.auth?.user?.name ?? '',
    surname: props.auth?.user?.surname ?? '',
    email: props.auth?.user?.email ?? '',
    phone: props.auth?.user?.phone ?? '',
    preferred_locale: normalizeLocaleCode(props.auth?.user?.preferred_locale) || defaultLocale.value,
});

const submitProfile = () => {
    profileSavedSuccess.value = false;

    profileForm.patch(profileUpdateUrl.value, {
        preserveScroll: true,
        onSuccess: () => {
            profileSavedSuccess.value = true;
            language.value = normalizeLocaleCode(profileForm.preferred_locale) || language.value;
        },
        onError: () => {
            profileSavedSuccess.value = false;
        },
    });
};

const cancelReservation = (reservationId) => {
    if (!window.confirm(copy.value.cancelConfirm)) {
        return;
    }

    router.post(withReservationId(reservationCancelTemplate.value, reservationId));
};

const canPayReservation = (reservation) => (
    reservation?.status !== 'cancelled' && !!reservation?.next_pending_payment
);

const isPayingReservation = (reservationId) => payingReservationId.value === reservationId;

const paymentStepLabel = (step) => {
    if (step === 'deposit') {
        return copy.value.paymentStepDeposit;
    }

    if (step === 'balance') {
        return copy.value.paymentStepBalance;
    }

    return copy.value.paymentStepFull;
};

const paymentButtonLabel = (reservation) => {
    if (reservation?.next_pending_payment?.step === 'deposit') {
        return copy.value.payDeposit;
    }

    if (reservation?.next_pending_payment?.step === 'balance') {
        return copy.value.payBalance;
    }

    return copy.value.payFull;
};

const payReservation = async (reservation) => {
    if (!canPayReservation(reservation) || isPayingReservation(reservation.id)) {
        return;
    }

    paymentActionError.value = '';
    payingReservationId.value = reservation.id;

    try {
        const response = await window.axios.post(withReservationId(stripeCheckoutTemplate.value, reservation.id));
        const redirectUrl = response?.data?.url;

        if (!redirectUrl) {
            paymentActionError.value = copy.value.paymentRedirectError;
            return;
        }

        window.location.assign(redirectUrl);
    } catch (error) {
        paymentActionError.value = error?.response?.data?.message || copy.value.paymentActionErrorBody;
    } finally {
        payingReservationId.value = null;
    }
};

const formatDate = (value) => {
    if (!value) {
        return '-';
    }

    const locale = language.value === 'it' ? 'it-IT' : 'en-US';
    return new Intl.DateTimeFormat(locale, { dateStyle: 'medium' }).format(new Date(value));
};

const formatCurrency = (value, currency = 'EUR') => {
    const numericValue = Number(value);
    const currencyCode = String(currency || 'EUR').toUpperCase();

    return new Intl.NumberFormat(language.value === 'it' ? 'it-IT' : 'en-US', {
        style: 'currency',
        currency: currencyCode,
    }).format(Number.isFinite(numericValue) ? numericValue : 0);
};

const isFutureReservation = (reservation) => {
    if (!reservation?.start_date) {
        return false;
    }

    return new Date(reservation.start_date) >= new Date();
};

const upcomingReservations = computed(() => props.reservations.filter((reservation) => isFutureReservation(reservation)));
const pastReservations = computed(() => props.reservations.filter((reservation) => !isFutureReservation(reservation)));
const paymentNotice = computed(() => {
    if (paymentState.value === 'authorized') {
        return {
            kicker: copy.value.paymentAuthorizedKicker,
            body: copy.value.paymentAuthorizedBody,
            classes: 'border border-amber-200 bg-amber-50',
            kickerClass: 'text-amber-700',
        };
    }

    if (paymentState.value === 'paid') {
        return {
            kicker: copy.value.paymentPaidKicker,
            body: copy.value.paymentPaidBody,
            classes: 'border border-emerald-200 bg-emerald-50',
            kickerClass: 'text-emerald-700',
        };
    }

    if (paymentState.value === 'cancelled') {
        return {
            kicker: copy.value.paymentCancelledKicker,
            body: copy.value.paymentCancelledBody,
            classes: 'border border-rose-200 bg-rose-50',
            kickerClass: 'text-rose-700',
        };
    }

    if (paymentState.value === 'failed') {
        return {
            kicker: copy.value.paymentFailedKicker,
            body: copy.value.paymentFailedBody,
            classes: 'border border-rose-200 bg-rose-50',
            kickerClass: 'text-rose-700',
        };
    }

    return null;
});

const reservationTitle = (reservation) => {
    if (!reservation?.apartment) {
        return '';
    }

    return language.value === 'it'
        ? reservation.apartment.name_it
        : reservation.apartment.name_en || reservation.apartment.name_it;
};

const reservationStatusLabel = (status) => {
    if (status === 'awaiting_payment') {
        return language.value === 'it' ? 'In attesa di pagamento' : 'Awaiting payment';
    }

    if (status === 'confirmed') {
        return language.value === 'it' ? 'Confermata' : 'Confirmed';
    }

    if (status === 'cancelled') {
        return language.value === 'it' ? 'Cancellata' : 'Cancelled';
    }

    return language.value === 'it' ? 'In verifica disponibilita' : 'Availability check';
};

const reservationStatusClass = (status) => {
    if (status === 'awaiting_payment') {
        return 'bg-slate-200 text-slate-700';
    }

    if (status === 'confirmed') {
        return 'bg-emerald-100 text-emerald-700';
    }

    if (status === 'cancelled') {
        return 'bg-rose-100 text-rose-700';
    }

    return 'bg-amber-100 text-amber-700';
};

const copy = computed(() => (language.value === 'it'
    ? {
        pageTitle: 'Area Privata',
        backHome: 'Torna alla home',
        logout: 'Esci',
        heroTitle: 'Gestisci i tuoi dati e le tue prenotazioni.',
        heroBody: 'Questa area ti permette di monitorare le richieste, aggiornare i tuoi dettagli e restare in contatto con noi.',
        privateAreaKicker: 'Area privata',
        quickAccess: 'Accesso rapido',
        emailLabel: 'Email',
        bookingCodeLabel: 'Codice prenotazione',
        recoverBooking: 'Recupera prenotazione',
        detailsKicker: 'I tuoi dati',
        profileTitle: 'Profilo cliente',
        nameLabel: 'Nome',
        surnameLabel: 'Cognome',
        phoneLabel: 'Telefono',
        preferredLanguageLabel: 'Lingua preferita',
        pendingLabel: 'Da completare',
        updateProfile: 'Aggiorna dati',
        bookingsKicker: 'Prenotazioni',
        bookingsTitle: 'Il tuo prossimo soggiorno',
        staysTitle: 'I tuoi soggiorni',
        cancelReservation: 'Annulla prenotazione',
        cancelConfirm: 'Vuoi annullare questa prenotazione?',
        noBookingTitle: 'Nessuna prenotazione attiva',
        noBookingBody: 'Quando effettuerai una prenotazione, la vedrai apparire qui con i dettagli del soggiorno.',
        noStaysTitle: 'Nessun soggiorno passato',
        noStaysBody: 'Quando avrai soggiornato, troverai qui il riepilogo delle tue esperienze.',
        paymentAuthorizedKicker: 'Pagamento autorizzato',
        paymentAuthorizedBody: 'Il pagamento e stato autorizzato. Stiamo verificando la disponibilita e ti confermeremo a breve.',
        paymentPaidKicker: 'Pagamento completato',
        paymentPaidBody: 'Pagamento registrato correttamente.',
        paymentCancelledKicker: 'Pagamento annullato',
        paymentCancelledBody: 'Hai annullato il pagamento. Puoi riprovare quando vuoi.',
        paymentFailedKicker: 'Pagamento non riuscito',
        paymentFailedBody: 'Non siamo riusciti a verificare il pagamento. Riprova o contattaci.',
        profileSavedKicker: 'Profilo aggiornato',
        profileSavedBody: 'I tuoi dati cliente sono stati salvati correttamente.',
        totalPaidLabel: 'Coperto',
        totalAuthorizedLabel: 'Di cui autorizzato',
        amountDueLabel: 'Residuo',
        nextPaymentLabel: 'Prossimo pagamento',
        dueDateLabel: 'Scadenza',
        paymentStepFull: 'Totale',
        paymentStepDeposit: 'Caparra',
        paymentStepBalance: 'Saldo',
        payFull: 'Paga totale',
        payDeposit: 'Paga caparra',
        payBalance: 'Paga saldo',
        redirectingToCheckout: 'Reindirizzamento...',
        paymentRedirectError: 'Non siamo riusciti ad aprire Stripe. Riprova.',
        paymentActionErrorKicker: 'Errore pagamento',
        paymentActionErrorBody: 'Non siamo riusciti ad avviare il pagamento. Riprova o contattaci.',
    }
    : {
        pageTitle: 'Private Area',
        backHome: 'Back to home',
        logout: 'Sign out',
        heroTitle: 'Manage your data and reservations.',
        heroBody: 'This area lets you track requests, update your details, and stay in touch with us.',
        privateAreaKicker: 'Private area',
        quickAccess: 'Quick access',
        emailLabel: 'Email',
        bookingCodeLabel: 'Booking code',
        recoverBooking: 'Retrieve booking',
        detailsKicker: 'Your details',
        profileTitle: 'Customer profile',
        nameLabel: 'Name',
        surnameLabel: 'Last name',
        phoneLabel: 'Phone',
        preferredLanguageLabel: 'Preferred language',
        pendingLabel: 'To complete',
        updateProfile: 'Update details',
        bookingsKicker: 'Bookings',
        bookingsTitle: 'Your next stay',
        staysTitle: 'Your stays',
        cancelReservation: 'Cancel reservation',
        cancelConfirm: 'Do you want to cancel this reservation?',
        noBookingTitle: 'No active bookings',
        noBookingBody: 'When you book a stay, it will appear here with all the details.',
        noStaysTitle: 'No past stays',
        noStaysBody: 'After your stay, you will find a summary of your experiences here.',
        paymentAuthorizedKicker: 'Payment authorized',
        paymentAuthorizedBody: 'Your payment has been authorized. We are checking availability and will confirm shortly.',
        paymentPaidKicker: 'Payment completed',
        paymentPaidBody: 'Your payment was recorded successfully.',
        paymentCancelledKicker: 'Payment cancelled',
        paymentCancelledBody: 'You cancelled the payment. You can try again at any time.',
        paymentFailedKicker: 'Payment failed',
        paymentFailedBody: 'We could not verify your payment. Please retry or contact us.',
        profileSavedKicker: 'Profile updated',
        profileSavedBody: 'Your customer details were saved successfully.',
        totalPaidLabel: 'Covered',
        totalAuthorizedLabel: 'Authorized portion',
        amountDueLabel: 'Remaining',
        nextPaymentLabel: 'Next payment',
        dueDateLabel: 'Due date',
        paymentStepFull: 'Total',
        paymentStepDeposit: 'Deposit',
        paymentStepBalance: 'Balance',
        payFull: 'Pay total',
        payDeposit: 'Pay deposit',
        payBalance: 'Pay balance',
        redirectingToCheckout: 'Redirecting...',
        paymentRedirectError: 'We could not open Stripe. Please retry.',
        paymentActionErrorKicker: 'Payment error',
        paymentActionErrorBody: 'We could not start the payment. Please retry or contact us.',
    }
));
</script>

<style scoped>
button {
    cursor: pointer;
}
</style>
