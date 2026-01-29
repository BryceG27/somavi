<template>
    <div class="min-h-screen">
        <Head title="Casa Kalsa" />
        <div class="relative overflow-hidden">
            <div class="absolute -top-32 -left-24 h-72 w-72 rounded-full bg-[radial-gradient(circle_at_top,#f4d3b4,#f6f0e6)] opacity-80 blur-3xl float-slow"></div>
            <div class="absolute top-24 -right-28 h-80 w-80 rounded-full bg-[radial-gradient(circle_at_top,#b8d8d3,#f6f0e6)] opacity-90 blur-3xl float-fast"></div>
        </div>

        <header class="relative z-10">
            <nav class="mx-auto flex max-w-6xl items-center justify-between px-6 py-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[var(--terracotta)] text-sm font-semibold text-white">
                        CK
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.4em] text-[var(--sea)]">Palermo</p>
                        <p class="text-lg font-semibold" style="font-family: var(--font-display);">Casa Kalsa</p>
                    </div>
                </div>
                <div class="hidden items-center gap-8 text-sm font-semibold uppercase tracking-[0.2em] text-[var(--ink)] md:flex">
                    <a href="#spazi" class="transition hover:text-[var(--terracotta)]">Spazi</a>
                    <a href="#servizi" class="transition hover:text-[var(--terracotta)]">Servizi</a>
                    <a href="#prenota" class="transition hover:text-[var(--terracotta)]">Prenota</a>
                    <a href="#contatti" class="transition hover:text-[var(--terracotta)]">Contatti</a>
                </div>
            </nav>
        </header>

        <main class="relative z-10">
            <section class="mx-auto grid max-w-6xl items-center gap-12 px-6 pb-20 pt-8 lg:grid-cols-[1.1fr_0.9fr]">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[var(--terracotta)]">Due camere, cucina e giardino privato</p>
                    <h1 class="mt-4 text-4xl font-semibold leading-tight text-[var(--ink)] sm:text-5xl" style="font-family: var(--font-display);">
                        Una casa autentica alla Kalsa, tra storia e vita locale.
                    </h1>
                    <p class="mt-6 text-lg leading-relaxed text-[var(--ink)]/80">
                        Piccola struttura in un contesto tipicamente siciliano, accogliente e completa di cucina e giardino privato.
                        Due camere da letto e un bagno per vivere Palermo con calma, a pochi minuti dalla stazione e dal porto.
                    </p>
                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <PButton
                            label="Scopri la camera"
                            icon="pi pi-eye"
                            @click="openModal('spazi')"
                            class="!rounded-full !border-transparent !bg-[var(--terracotta)] !px-6 !py-3 !text-white"
                        />
                        <a href="#prenota" class="inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-[0.2em] text-[var(--sea)]">
                            Verifica disponibilita
                            <span class="text-base">→</span>
                        </a>
                    </div>
                    <div class="mt-10 flex flex-wrap gap-6 text-sm text-[var(--ink)]/70">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">•</span>
                            <span>Giardino privato</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-lg">•</span>
                            <span>Due camere da letto</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-lg">•</span>
                            <span>Centro storico a piedi</span>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <div class="grain rounded-[2rem] border border-black/10 bg-[linear-gradient(135deg,#f6f0e6_0%,#f0d7c6_45%,#cfe3de_100%)] p-6 shadow-xl">
                        <div class="relative h-[340px] overflow-hidden rounded-[1.5rem] bg-[radial-gradient(circle_at_top,#f4ede5,#e9d6c6)]">
                            <div class="absolute inset-0 bg-[linear-gradient(120deg,rgba(198,91,60,0.15),rgba(43,111,107,0.15))]"></div>
                            <div class="absolute bottom-6 left-6 right-6 rounded-2xl bg-white/80 p-4 backdrop-blur">
                                <p class="text-xs uppercase tracking-[0.3em] text-[var(--sea)]">Casa privata</p>
                                <p class="mt-2 text-lg font-semibold" style="font-family: var(--font-display);">Spazi completi e atmosfera siciliana</p>
                                <p class="mt-1 text-sm text-[var(--ink)]/70">Placeholder fotografico in attesa delle immagini reali.</p>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -bottom-10 -left-10 h-24 w-24 rounded-full bg-[var(--terracotta)]/20 blur-2xl"></div>
                </div>
            </section>

            <section id="spazi" class="mx-auto max-w-6xl px-6 pb-20">
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[var(--sea)]">Spazi e servizi</p>
                        <h2 class="mt-3 text-3xl font-semibold" style="font-family: var(--font-display);">Tre dettagli che fanno la differenza.</h2>
                    </div>
                    <p class="max-w-lg text-sm text-[var(--ink)]/70">
                        Ogni scelta e pensata per restare leggera: materiali naturali, colori caldi e un ritmo rilassato per chi vuole staccare.
                    </p>
                </div>
                <div class="mt-10 grid gap-6 md:grid-cols-3">
                    <div v-for="card in infoCards" :key="card.key" class="rounded-3xl border border-black/10 bg-white/70 p-6 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[var(--terracotta)]">{{ card.title }}</p>
                        <h3 class="mt-4 text-xl font-semibold" style="font-family: var(--font-display);">{{ card.summary }}</h3>
                        <button
                            type="button"
                            class="mt-6 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.3em] text-[var(--sea)]"
                            @click="openModal(card.key)"
                        >
                            Dettagli
                            <span class="text-base">→</span>
                        </button>
                    </div>
                </div>
            </section>

            <section id="servizi" class="mx-auto max-w-6xl px-6 pb-20">
                <div class="grid gap-12 lg:grid-cols-[0.9fr_1.1fr]">
                    <div class="rounded-[2rem] border border-black/10 bg-white/70 p-8 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[var(--terracotta)]">Posizione strategica</p>
                            <h3 class="mt-4 text-2xl font-semibold" style="font-family: var(--font-display);">A pochi minuti dalla stazione e dal porto.</h3>
                        <ul class="mt-6 space-y-4 text-sm text-[var(--ink)]/80">
                            <li>5 min a piedi dalla stazione e dal porto.</li>
                            <li>10 min dal centro storico passeggiando per le vie piu caratteristiche.</li>
                            <li>Chiese e monumenti storici a due passi.</li>
                        </ul>
                        <div class="mt-8 flex items-center gap-3 text-xs uppercase tracking-[0.3em] text-[var(--sea)]">
                            <span class="h-2 w-2 rounded-full bg-[var(--sea)]"></span>
                            Quartiere storico con atmosfera autentica
                        </div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-3xl border border-black/10 bg-[linear-gradient(160deg,#f8efe7,#f2dac8)] p-6">
                            <p class="text-xs uppercase tracking-[0.3em] text-[var(--sea)]">Giardino</p>
                            <p class="mt-3 text-lg font-semibold" style="font-family: var(--font-display);">Spazio esterno privato.</p>
                            <p class="mt-2 text-sm text-[var(--ink)]/70">Perfetto per serate miti.</p>
                        </div>
                        <div class="rounded-3xl border border-black/10 bg-[linear-gradient(160deg,#e6f1ee,#d3e6e2)] p-6">
                            <p class="text-xs uppercase tracking-[0.3em] text-[var(--terracotta)]">Camere</p>
                            <p class="mt-3 text-lg font-semibold" style="font-family: var(--font-display);">Due camere da letto.</p>
                            <p class="mt-2 text-sm text-[var(--ink)]/70">Spazi comodi e accoglienti.</p>
                        </div>
                        <div class="rounded-3xl border border-black/10 bg-[linear-gradient(160deg,#f5efe9,#f3e6dd)] p-6">
                            <p class="text-xs uppercase tracking-[0.3em] text-[var(--sea)]">Cucina</p>
                            <p class="mt-3 text-lg font-semibold" style="font-family: var(--font-display);">Cucina attrezzata.</p>
                            <p class="mt-2 text-sm text-[var(--ink)]/70">Vivi la casa come se fosse tua.</p>
                        </div>
                        <div class="rounded-3xl border border-black/10 bg-[linear-gradient(160deg,#f0e6db,#e8d4c4)] p-6">
                            <p class="text-xs uppercase tracking-[0.3em] text-[var(--terracotta)]">Quartiere</p>
                            <p class="mt-3 text-lg font-semibold" style="font-family: var(--font-display);">Contesto tipicamente siciliano.</p>
                            <p class="mt-2 text-sm text-[var(--ink)]/70">Vicinanza a chiese e monumenti.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mx-auto max-w-6xl px-6 pb-20">
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[var(--sea)]">Galleria</p>
                        <h2 class="mt-3 text-3xl font-semibold" style="font-family: var(--font-display);">Immagini in arrivo.</h2>
                    </div>
                    <p class="max-w-lg text-sm text-[var(--ink)]/70">
                        In attesa del materiale fotografico definitivo, abbiamo preparato alcune anteprime per definire il mood.
                    </p>
                </div>
                <div class="mt-10 grid gap-6 md:grid-cols-3">
                    <div class="aspect-[4/3] rounded-3xl border border-black/10 bg-[linear-gradient(140deg,#f4e7dc,#f0d2c1)] p-6">
                        <p class="text-xs uppercase tracking-[0.3em] text-[var(--sea)]">Camera</p>
                    </div>
                    <div class="aspect-[4/3] rounded-3xl border border-black/10 bg-[linear-gradient(140deg,#e6efec,#cfe1dc)] p-6">
                        <p class="text-xs uppercase tracking-[0.3em] text-[var(--terracotta)]">Dettagli</p>
                    </div>
                    <div class="aspect-[4/3] rounded-3xl border border-black/10 bg-[linear-gradient(140deg,#f3ece4,#e9d7c6)] p-6">
                        <p class="text-xs uppercase tracking-[0.3em] text-[var(--sea)]">Kalsa</p>
                    </div>
                </div>
            </section>

            <section id="prenota" class="mx-auto max-w-6xl px-6 pb-20">
                <div class="rounded-[2.5rem] border border-black/10 bg-white/80 p-10 shadow-lg">
                    <div class="flex flex-wrap items-center justify-between gap-6">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[var(--terracotta)]">Prenota</p>
                            <h2 class="mt-3 text-3xl font-semibold" style="font-family: var(--font-display);">Verifica disponibilita in tempo reale.</h2>
                            <p class="mt-4 max-w-xl text-sm text-[var(--ink)]/70">
                                Disponibilita e prezzi saranno sincronizzati con Booking.com e Airbnb tramite provider dedicato.
                                Per ora puoi lasciare una richiesta e ti risponderemo subito.
                            </p>
                        </div>
                        <div class="flex items-center gap-2 text-xs uppercase tracking-[0.3em] text-[var(--sea)]">
                            <span class="h-2 w-2 rounded-full bg-[var(--sea)]"></span>
                            Integrazione in corso
                        </div>
                    </div>
                    <div class="mt-8 grid gap-4 md:grid-cols-[1fr_1fr_auto]">
                        <label class="flex flex-col gap-2 text-xs uppercase tracking-[0.3em] text-[var(--ink)]/60">
                            Check-in
                            <input type="date" class="rounded-xl border border-black/10 bg-white px-4 py-3 text-base" />
                        </label>
                        <label class="flex flex-col gap-2 text-xs uppercase tracking-[0.3em] text-[var(--ink)]/60">
                            Check-out
                            <input type="date" class="rounded-xl border border-black/10 bg-white px-4 py-3 text-base" />
                        </label>
                        <div class="flex items-end">
                            <PButton
                                label="Richiedi disponibilita"
                                icon="pi pi-send"
                                class="!w-full !rounded-xl !border-transparent !bg-[var(--sea)] !px-6 !py-3 !text-white"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <section id="contatti" class="mx-auto max-w-6xl px-6 pb-24">
                <div class="grid gap-8 rounded-[2.5rem] border border-black/10 bg-[linear-gradient(130deg,#f6efe7,#e9d9c8)] p-10 lg:grid-cols-[1.2fr_0.8fr]">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[var(--sea)]">Contatti</p>
                        <h2 class="mt-3 text-3xl font-semibold" style="font-family: var(--font-display);">Parliamo del tuo soggiorno.</h2>
                        <p class="mt-4 text-sm text-[var(--ink)]/70">
                            Scrivici per confermare le date o per ricevere un consiglio su misura. Siamo una struttura piccola,
                            e rispondiamo rapidamente a ogni richiesta.
                        </p>
                    </div>
                    <div class="rounded-2xl bg-white/70 p-6">
                        <p class="text-xs uppercase tracking-[0.3em] text-[var(--terracotta)]">Info rapide</p>
                        <ul class="mt-4 space-y-3 text-sm text-[var(--ink)]/70">
                            <li>Via della Kalsa, Palermo</li>
                            <li>info@casakalsa.it</li>
                            <li>+39 000 000 000</li>
                        </ul>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="mailto:info@casakalsa.it" class="rounded-full border border-black/10 bg-white px-4 py-2 text-xs uppercase tracking-[0.3em]">Email</a>
                            <a href="#" class="rounded-full border border-black/10 bg-white px-4 py-2 text-xs uppercase tracking-[0.3em]">WhatsApp</a>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-black/10 py-8 text-center text-xs uppercase tracking-[0.3em] text-[var(--ink)]/50">
            Casa Kalsa - Palermo
        </footer>

        <Dialog
            v-for="card in infoCards"
            :key="card.key"
            v-model:visible="modals[card.key]"
            modal
            :header="modalCopy[card.key].title"
            class="w-[90vw] max-w-lg"
        >
            <div class="space-y-4">
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-[var(--terracotta)]">
                    {{ modalCopy[card.key].subtitle }}
                </p>
                <p class="text-sm text-[var(--ink)]/80">
                    {{ modalCopy[card.key].body }}
                </p>
                <ul class="space-y-2 text-sm text-[var(--ink)]/70">
                    <li v-for="item in modalCopy[card.key].list" :key="item">- {{ item }}</li>
                </ul>
            </div>
        </Dialog>
    </div>
</template>

<script setup>
import { reactive } from 'vue';
import { Head } from '@inertiajs/vue3';

const modals = reactive({
    spazi: false,
    servizi: false,
    quartiere: false,
});

const infoCards = [
    {
        key: 'spazi',
        title: 'La Casa',
        summary: 'Due camere da letto, un bagno e spazi accoglienti per sentirsi a casa.',
    },
    {
        key: 'servizi',
        title: 'Cucina e comfort',
        summary: 'Cucina attrezzata e dotazioni essenziali per vivere Palermo in autonomia.',
    },
    {
        key: 'quartiere',
        title: 'La Kalsa',
        summary: 'Immersa nella parte piu antica e storica della citta, a pochi minuti dal centro.',
    },
];

const modalCopy = {
    spazi: {
        title: 'La Casa',
        subtitle: 'Spazi semplici, caldi, tipicamente siciliani.',
        body: 'Due camere da letto, un bagno e una zona giorno accogliente. Una soluzione ideale per chi vuole vivere Palermo con calma e sentirsi subito a casa.',
        list: ['Due camere da letto', 'Un bagno completo', 'Spazi pensati per il riposo'],
    },
    servizi: {
        title: 'Cucina e comfort',
        subtitle: 'Cucina attrezzata e dettagli funzionali.',
        body: 'Una cucina comoda per organizzare i pasti e un giardino privato per rilassarsi la sera, con temperature miti per gran parte dell anno.',
        list: ['Cucina completa', 'Giardino privato', 'Spazi funzionali e accoglienti'],
    },
    quartiere: {
        title: 'La Kalsa',
        subtitle: 'La parte piu antica e storica della citta.',
        body: 'A 5 minuti dalla stazione e dal porto, 10 minuti a piedi dal centro storico passando per le strade piu caratteristiche. Chiese e monumenti sono a pochi passi.',
        list: ['5 min da stazione e porto', '10 min dal centro storico', 'Monumenti e chiese vicini'],
    },
};

const openModal = (key) => {
    modals[key] = true;
};
</script>
