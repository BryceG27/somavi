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
                            <option value="it">IT</option>
                            <option value="en">EN</option>
                        </select>
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-[color:rgba(30,27,23,0.6)]">▾</span>
                    </div>
                    <a href="/" class="rounded-full border border-black/15 px-4 py-2 hover:opacity-80">{{ copy.backHome }}</a>
                    <button type="button" class="rounded-full bg-(--ink) px-4 py-2 text-white" @click="logout">
                        {{ copy.logout }}
                    </button>
                </div>
            </div>
        </header>

        <main class="mx-auto w-[90%] max-w-6xl pb-24 pt-14">
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
                                <span
                                    class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em]"
                                    :class="reservationStatusClass(reservation.status)"
                                >
                                    {{ reservationStatusLabel(reservation.status) }}
                                </span>
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
                                <span
                                    class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em]"
                                    :class="reservationStatusClass(reservation.status)"
                                >
                                    {{ reservationStatusLabel(reservation.status) }}
                                </span>
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
import { Head, router, useForm } from '@inertiajs/vue3';

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

const logout = () => {
    router.post('/logout');
};

const profileForm = useForm({
    name: props.auth?.user?.name ?? '',
    surname: props.auth?.user?.surname ?? '',
    email: props.auth?.user?.email ?? '',
    phone: props.auth?.user?.phone ?? '',
});

const submitProfile = () => {
    profileForm.post('/area-privata/profile');
};

const cancelReservation = (reservationId) => {
    if (!window.confirm(copy.value.cancelConfirm)) {
        return;
    }

    router.post(`/reservations/${reservationId}/cancel`);
};

const formatDate = (value) => {
    if (!value) {
        return '-';
    }

    const locale = language.value === 'it' ? 'it-IT' : 'en-US';
    return new Intl.DateTimeFormat(locale, { dateStyle: 'medium' }).format(new Date(value));
};

const formatCurrency = (value) => new Intl.NumberFormat(language.value === 'it' ? 'it-IT' : 'en-US', {
    style: 'currency',
    currency: 'EUR',
}).format(Number.isFinite(value) ? value : 0);

const isFutureReservation = (reservation) => {
    if (!reservation?.start_date) {
        return false;
    }

    return new Date(reservation.start_date) >= new Date();
};

const upcomingReservations = computed(() => props.reservations.filter((reservation) => isFutureReservation(reservation)));
const pastReservations = computed(() => props.reservations.filter((reservation) => !isFutureReservation(reservation)));

const reservationTitle = (reservation) => {
    if (!reservation?.apartment) {
        return '';
    }

    return language.value === 'it'
        ? reservation.apartment.name_it
        : reservation.apartment.name_en || reservation.apartment.name_it;
};

const reservationStatusLabel = (status) => {
    if (status === 'confirmed') {
        return language.value === 'it' ? 'Confermata' : 'Confirmed';
    }

    if (status === 'cancelled') {
        return language.value === 'it' ? 'Cancellata' : 'Cancelled';
    }

    return language.value === 'it' ? 'In attesa' : 'Pending';
};

const reservationStatusClass = (status) => {
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
    }
));
</script>

<style scoped>
button {
    cursor: pointer;
}
</style>
