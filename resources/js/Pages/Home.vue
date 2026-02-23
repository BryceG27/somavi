<template>
    <div class="min-h-screen">
        <Head :title="content.name">
            <meta name="description" :content="seoDescription" />
            <meta name="robots" content="index, follow" />
            <link v-if="canonicalUrl" rel="canonical" :href="canonicalUrl" />
            <meta property="og:title" :content="content.name" />
            <meta property="og:description" :content="seoDescription" />
            <meta property="og:type" content="website" />
            <meta v-if="canonicalUrl" property="og:url" :content="canonicalUrl" />
            <meta v-if="seoImage" property="og:image" :content="seoImage" />
            <meta name="twitter:card" content="summary_large_image" />
            <meta name="twitter:title" :content="content.name" />
            <meta name="twitter:description" :content="seoDescription" />
            <meta v-if="seoImage" name="twitter:image" :content="seoImage" />
        </Head>

        <header
            class="fixed top-0 left-0 right-0 z-20 transition-colors duration-300"
            :class="headerOpacity > 0.08 ? 'backdrop-blur-md' : ''"
            :style="{ backgroundColor: `rgba(0, 0, 0, ${headerOpacity})` }"
        >
            <div class="mx-auto w-[90%] border-b border-white/25">
                <nav class="flex items-center justify-between py-5 text-white">
                    <div class="flex items-center gap-4">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full border border-white/40 text-xs font-semibold uppercase tracking-[0.3em]">
                            SM
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.35em] text-white/70">
                                {{ content.address || 'Palermo' }}
                            </p>
                            <p class="text-lg font-semibold" style="font-family: var(--font-display);">
                                {{ content.name }}
                            </p>
                        </div>
                    </div>
                    <div class="hidden items-center gap-8 text-xs font-semibold uppercase tracking-[0.3em] md:flex">
                        <a href="#spaces" class="transition hover:text-white/70">{{ content.nav.spaces }}</a>
                        <a href="#gallery" class="transition hover:text-white/70">{{ content.nav.gallery }}</a>
                        <a href="#booking" class="transition hover:text-white/70">{{ content.nav.booking }}</a>
                        <a href="#contact" class="transition hover:text-white/70">{{ content.nav.contact }}</a>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <select
                                v-model="language"
                                class="appearance-none rounded-full border border-white/30 bg-white/10 px-4 py-2 pr-10 text-[11px] font-semibold uppercase tracking-[0.3em] text-white"
                            >
                                <option v-for="locale in supportedLocales" :key="locale" :value="locale">
                                    {{ localeOptionLabel(locale) }}
                                </option>
                            </select>
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-white/80">▾</span>
                        </div>
                        <a
                            :href="isAuthenticated ? privateAreaUrl : loginUrl"
                            class="flex h-10 w-10 items-center justify-center rounded-full border border-white/30 text-white/80"
                            aria-label="Area privata"
                        >
                            <i class="pi pi-user text-base"></i>
                        </a>
                    </div>
                </nav>
            </div>
        </header>

        <main class="relative z-10">
            <section class="relative flex min-h-screen items-center overflow-hidden pt-24">
                <div class="absolute inset-0">
                    <div
                        class="absolute inset-0 bg-cover bg-center"
                        :style="heroBackgroundStyle"
                    ></div>
                    <div class="absolute inset-0 bg-black/55"></div>
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.08),transparent_45%)]"></div>
                </div>
                <div class="relative z-10 mx-auto w-[90%] max-w-6xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-white/70">
                        {{ content.kicker }}
                    </p>
                    <h1
                        class="mt-6 max-w-3xl text-4xl font-semibold leading-tight text-white sm:text-5xl"
                        style="font-family: var(--font-display);"
                    >
                        {{ content.headline }}
                    </h1>
                    <p class="mt-6 max-w-2xl text-base leading-relaxed text-white/85">
                        {{ content.heroBody || content.description }}
                    </p>
                    <div class="mt-10 flex flex-wrap items-center gap-4">
                        <Button
                            v-if="content.primaryCta"
                            :label="content.primaryCta"
                            icon="pi pi-eye"
                            @click="openModal('spaces')"
                            class="!rounded-full !border-transparent !bg-white !px-6 !py-3 !text-black"
                        />
                        <a
                            v-if="content.secondaryCta"
                            href="#booking"
                            class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.3em] text-white"
                        >
                            {{ content.secondaryCta }}
                            <span class="text-base">→</span>
                        </a>
                    </div>
                    <div class="mt-12 grid gap-4 text-sm text-white/80 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-2xl border border-white/20 bg-white/10 p-5 backdrop-blur">
                            <p class="text-xs uppercase tracking-[0.3em] text-white/60">{{ content.labels.rooms }}</p>
                            <p class="mt-2 text-2xl font-semibold">{{ content.roomsLabel }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/20 bg-white/10 p-5 backdrop-blur">
                            <p class="text-xs uppercase tracking-[0.3em] text-white/60">{{ content.labels.beds }}</p>
                            <p class="mt-2 text-2xl font-semibold">{{ content.bedsLabel }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/20 bg-white/10 p-5 backdrop-blur">
                            <p class="text-xs uppercase tracking-[0.3em] text-white/60">{{ content.labels.bathrooms }}</p>
                            <p class="mt-2 text-2xl font-semibold">{{ content.bathroomsLabel }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/20 bg-white/10 p-5 backdrop-blur">
                            <p class="text-xs uppercase tracking-[0.3em] text-white/60">{{ content.labels.guests }}</p>
                            <p class="mt-2 text-2xl font-semibold">{{ content.guestsLabel }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="spaces" class="mx-auto w-[90%] max-w-6xl pb-20 mt-7">
                <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
                    <div class="rounded-[2.5rem] border border-black/10 bg-white p-10 text-[var(--ink)]">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.7)]">{{ content.spacesTitle }}</p>
                        <h2 class="mt-4 text-3xl font-semibold" style="font-family: var(--font-display);">
                            {{ content.spacesHeadline }}
                        </h2>
                        <p class="mt-6 text-sm text-[color:rgba(30,27,23,0.8)]">
                            {{ content.spacesBody }}
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3 text-xs uppercase tracking-[0.3em]">
                            <span class="rounded-full border border-black/15 px-4 py-2">
                                {{ content.roomsLabel }}
                            </span>
                            <span class="rounded-full border border-black/15 px-4 py-2">
                                {{ content.bedsLabel }}
                            </span>
                            <span class="rounded-full border border-black/15 px-4 py-2">
                                {{ content.guestsLabel }}
                            </span>
                        </div>
                    </div>
                    <div class="grid gap-6">
                        <div
                            v-for="card in infoCards"
                            :key="card.key"
                            class="rounded-3xl border border-black/10 bg-white p-6 text-[var(--ink)]"
                        >
                            <p class="text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.7)]">{{ card.title }}</p>
                            <p class="mt-3 text-lg font-semibold" style="font-family: var(--font-display);">{{ card.summary }}</p>
                            <button
                                type="button"
                                class="mt-6 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.9)]"
                                @click="openModal(card.key)"
                            >
                                {{ content.detailsCta }}
                                <span class="text-base">→</span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section id="gallery" class="mx-auto w-[90%] max-w-6xl pb-20">
                <div class="flex flex-wrap items-end justify-between gap-6 text-[var(--ink)]">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.7)]">{{ content.galleryTitle }}</p>
                        <h2 class="mt-3 text-3xl font-semibold" style="font-family: var(--font-display);">
                            {{ content.galleryHeadline }}
                        </h2>
                    </div>
                    <p class="max-w-lg text-sm text-[color:rgba(30,27,23,0.7)]">
                        {{ content.galleryBody }}
                    </p>
                </div>
                <div class="mt-10 grid gap-6 md:grid-cols-3">
                    <div
                        v-for="image in galleryImages"
                        :key="image.id"
                        class="group relative aspect-[4/3] overflow-hidden rounded-3xl border border-black/10"
                    >
                        <img
                            :src="image.url"
                            :alt="content.galleryImageAlt"
                            class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                            loading="lazy"
                        />
                        <div class="absolute inset-0 bg-black/20 opacity-0 transition group-hover:opacity-100"></div>
                    </div>
                    <div v-if="galleryImages.length === 0" class="rounded-3xl border border-black/10 bg-white p-8 text-[color:rgba(30,27,23,0.8)]">
                        {{ content.galleryEmpty }}
                    </div>
                </div>
            </section>

            <section id="booking" class="mx-auto w-[90%] max-w-6xl pb-20">
                <div class="rounded-[2.5rem] border border-black/10 bg-white p-10 text-[var(--ink)]">
                    <div class="flex flex-wrap items-start justify-between gap-6">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[rgba(30,27,23,0.7)]">{{ content.bookingTitle }}</p>
                            <h2 class="mt-3 text-3xl font-semibold" style="font-family: var(--font-display);">
                                {{ content.bookingHeadline }}
                            </h2>
                        <p class="mt-4 max-w-xl text-sm text-[rgba(30,27,23,0.8)]">
                            {{ content.bookingBody }}
                        </p>
                        </div>
                        <div class="flex flex-wrap gap-3" v-show="false">
                            <a
                                v-if="content.airbnbUrl"
                                :href="content.airbnbUrl"
                                target="_blank"
                                rel="noreferrer"
                                class="rounded-full border border-black/15 px-5 py-2 text-xs uppercase tracking-[0.3em] hover:bg-black/5 transition"
                            >
                                Airbnb
                            </a>
                            <a
                                v-if="content.bookingUrl"
                                :href="content.bookingUrl"
                                target="_blank"
                                rel="noreferrer"
                                class="rounded-full border border-black/15 px-5 py-2 text-xs uppercase tracking-[0.3em] hover:bg-black/5 transition"
                            >
                                Booking
                            </a>
                            <a
                                v-if="content.vrboUrl"
                                :href="content.vrboUrl"
                                target="_blank"
                                rel="noreferrer"
                                class="rounded-full border border-black/15 px-5 py-2 text-xs uppercase tracking-[0.3em] hover:bg-black/5 transition"
                            >
                                Vrbo
                            </a>

                        </div>
                    </div>
                    <div class="mt-8 grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
                        <form class="space-y-5" @submit.prevent="submitBooking">
                            <div
                                v-if="bookingErrorList.length"
                                class="rounded-2xl border border-[var(--terracotta)]/30 bg-[var(--terracotta)]/10 px-5 py-4 text-[var(--ink)]"
                            >
                                <p class="text-xs font-semibold uppercase tracking-[0.3em]">
                                    {{ content.validationErrorTitle }}
                                </p>
                                <ul class="mt-2 space-y-1 text-sm">
                                    <li v-for="(error, index) in bookingErrorList" :key="`${error}-${index}`">
                                        {{ error }}
                                    </li>
                                </ul>
                            </div>
                            <div
                                v-if="bookingNotice === 'guest_registration_required'"
                                class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-[var(--ink)]"
                            >
                                <p v-if="language === 'it'" class="text-sm leading-relaxed">
                                    Prenotazione effettuata correttamente. Termina la registrazione nell'<a :href="loginUrl" class="font-semibold italic underline underline-offset-2">area privata</a> per controllare le tue prenotazioni.
                                </p>
                                <p v-else class="text-sm leading-relaxed">
                                    Booking completed successfully. Complete your registration in the <a :href="loginUrl" class="font-semibold italic underline underline-offset-2">private area</a> to manage your bookings.
                                </p>
                            </div>
                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="flex flex-col gap-2 text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.7)]">
                                    {{ content.arrivalLabel }}
                                    <DatePicker
                                        v-model="startDateValue"
                                        :min-date="startMinDate"
                                        :disabled-dates="disabledStartDates"
                                        :manual-input="false"
                                        show-icon
                                        date-format="dd/mm/yy"
                                        :input-class="inputClass('start_date')"
                                        class="w-full"
                                    />
                                    <span v-if="bookingForm.errors.start_date" class="text-[11px] font-semibold uppercase tracking-[0.25em] text-[var(--terracotta)]">
                                        {{ bookingForm.errors.start_date }}
                                    </span>
                                </label>
                                <label class="flex flex-col gap-2 text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.7)]">
                                    {{ content.departureLabel }}
                                    <DatePicker
                                        v-model="endDateValue"
                                        :min-date="endMinDate"
                                        :disabled-dates="disabledEndDates"
                                        :manual-input="false"
                                        show-icon
                                        date-format="dd/mm/yy"
                                        :input-class="inputClass('end_date')"
                                        class="w-full"
                                    />
                                    <span v-if="bookingForm.errors.end_date" class="text-[11px] font-semibold uppercase tracking-[0.25em] text-[var(--terracotta)]">
                                        {{ bookingForm.errors.end_date }}
                                    </span>
                                </label>
                            </div>
                            <label class="flex flex-col gap-2 text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.7)]">
                                {{ content.guestsFormLabel }}
                                <select v-model.number="bookingForm.guests_count" :class="inputClass('guests_count')">
                                    <option v-for="count in guestOptions" :key="count" :value="count">
                                        {{ count }}
                                    </option>
                                </select>
                                <span v-if="bookingForm.errors.guests_count" class="text-[11px] font-semibold uppercase tracking-[0.25em] text-[var(--terracotta)]">
                                    {{ bookingForm.errors.guests_count }}
                                </span>
                            </label>
                            <div class="space-y-3 text-[color:rgba(30,27,23,0.75)]">
                                <p class="text-xs font-semibold uppercase tracking-[0.3em]">{{ content.paymentPlanLabel }}</p>
                                <label class="flex items-center gap-3 text-sm">
                                    <input v-model="bookingForm.payment_plan" type="radio" value="full" class="h-4 w-4 accent-[var(--terracotta)]" />
                                    <span>{{ content.paymentFullLabel }}</span>
                                </label>
                                <label class="flex items-center gap-3 text-sm">
                                    <input v-model="bookingForm.payment_plan" type="radio" value="split" class="h-4 w-4 accent-[var(--terracotta)]" />
                                    <span>{{ content.paymentSplitLabel }}</span>
                                </label>
                                <p v-if="bookingForm.payment_plan === 'split'" class="text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.6)]">
                                    {{ content.paymentSplitHint }}
                                </p>
                                <span v-if="bookingForm.errors.payment_plan" class="text-[11px] font-semibold uppercase tracking-[0.25em] text-[var(--terracotta)]">
                                    {{ bookingForm.errors.payment_plan }}
                                </span>
                            </div>
                            <div v-if="isAuthenticated" class="rounded-2xl border border-black/10 bg-[color:rgba(30,27,23,0.03)] px-5 py-6 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                    <i class="pi pi-check text-lg"></i>
                                </div>
                                <p class="mt-3 text-xs font-semibold uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.7)]">
                                    {{ content.authenticatedLabel }}
                                </p>
                            </div>
                            <div v-else class="space-y-4">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <label class="flex flex-col gap-2 text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.7)]">
                                        {{ content.nameLabel }}
                                        <input v-model="bookingForm.name" type="text" :class="inputClass('name')" />
                                        <span v-if="bookingForm.errors.name" class="text-[11px] font-semibold uppercase tracking-[0.25em] text-[var(--terracotta)]">
                                            {{ bookingForm.errors.name }}
                                        </span>
                                    </label>
                                    <label class="flex flex-col gap-2 text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.7)]">
                                        {{ content.surnameLabel }}
                                        <input v-model="bookingForm.surname" type="text" :class="inputClass('surname')" />
                                        <span v-if="bookingForm.errors.surname" class="text-[11px] font-semibold uppercase tracking-[0.25em] text-[var(--terracotta)]">
                                            {{ bookingForm.errors.surname }}
                                        </span>
                                    </label>
                                </div>
                                <label class="flex flex-col gap-2 text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.7)]">
                                    {{ content.emailLabel }}
                                    <input
                                        v-model="bookingForm.email"
                                        type="email"
                                        required
                                        :class="inputClass('email')"
                                    />
                                    <span v-if="bookingForm.errors.email" class="text-[11px] font-semibold uppercase tracking-[0.25em] text-[var(--terracotta)]">
                                        {{ bookingForm.errors.email }}
                                    </span>
                                </label>
                            </div>
                            <label class="flex flex-col gap-2 text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.7)]">
                                {{ content.notesLabel }}
                                <textarea v-model="bookingForm.notes" rows="4" :class="inputClass('notes')"></textarea>
                                <span v-if="bookingForm.errors.notes" class="text-[11px] font-semibold uppercase tracking-[0.25em] text-[var(--terracotta)]">
                                    {{ bookingForm.errors.notes }}
                                </span>
                            </label>
                            <span v-if="bookingForm.errors.apartment_id" class="block text-[11px] font-semibold uppercase tracking-[0.25em] text-[var(--terracotta)]">
                                {{ bookingForm.errors.apartment_id }}
                            </span>
                            <button
                                type="submit"
                                class="w-full rounded-full bg-[var(--ink)] px-6 py-3 text-xs font-semibold uppercase tracking-[0.3em] text-white"
                                :disabled="bookingForm.processing || !bookingForm.apartment_id"
                            >
                                {{ content.bookingSubmitLabel }}
                            </button>
                            <div
                                v-if="content.checkInText || content.checkOutText"
                                class="mt-3 grid gap-3 text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.8)] md:grid-cols-2"
                            >
                                <span v-if="content.checkInText" class="block w-full rounded-full border border-black/15 px-4 py-2">
                                    {{ content.checkInText }}
                                </span>
                                <span v-if="content.checkOutText" class="block w-full rounded-full border border-black/15 px-4 py-2">
                                    {{ content.checkOutText }}
                                </span>
                            </div>
                        </form>
                        <div class="space-y-4">
                            <div class="rounded-2xl border border-black/10 bg-[color:rgba(30,27,23,0.03)] p-5">
                                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.7)]">
                                    {{ content.pricingTitle }}
                                </p>
                                <ul class="mt-4 space-y-2 text-sm text-[var(--ink)]/80">
                                    <li class="flex items-center justify-between">
                                        <span>{{ content.basePriceLabel }}</span>
                                        <span>{{ formatCurrency(pricing.base) }}</span>
                                    </li>
                                    <li class="flex items-center justify-between">
                                        <span>{{ content.extraGuestLabel }} 2</span>
                                        <span>+ {{ formatCurrency(pricing.extra2) }}</span>
                                    </li>
                                    <li class="flex items-center justify-between">
                                        <span>{{ content.extraGuestLabel }} 3</span>
                                        <span>+ {{ formatCurrency(pricing.extra3) }}</span>
                                    </li>
                                    <li class="flex items-center justify-between">
                                        <span>{{ content.extraGuestLabel }} 4</span>
                                        <span>+ {{ formatCurrency(pricing.extra4) }}</span>
                                    </li>
                                </ul>
                                <div class="mt-4 border-t border-black/10 pt-4">
                                    <p class="text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.7)]">
                                        {{ content.totalLabel }}
                                    </p>
                                    <p class="mt-2 text-2xl font-semibold text-[var(--ink)]" style="font-family: var(--font-display);">
                                        {{ formatCurrency(pricing.totalForGuests) }}
                                    </p>
                                    <p class="mt-2 text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.6)]">
                                        {{ content.perNightLabel }}
                                    </p>
                                </div>
                            </div>
                            <div class="rounded-2xl border border-black/10 bg-[color:rgba(30,27,23,0.03)] px-5 py-4 text-[color:rgba(30,27,23,0.75)]">
                                <div class="flex items-center justify-between text-xs uppercase tracking-[0.3em]">
                                    <span>{{ content.nightsLabel }}</span>
                                    <span>{{ bookingNights }}</span>
                                </div>
                                <div class="mt-3 flex items-center justify-between text-xs uppercase tracking-[0.3em]">
                                    <span>{{ content.perNightLabel }}</span>
                                    <span>{{ formatCurrency(pricing.totalForGuests) }}</span>
                                </div>
                                <div class="mt-4 border-t border-black/10 pt-3">
                                    <div class="flex items-center justify-between text-xs uppercase tracking-[0.3em]">
                                        <span>{{ content.totalStayLabel }}</span>
                                        <span>{{ formatCurrency(bookingTotal) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="contact" class="mx-auto w-[90%] max-w-6xl pb-24">
                <div class="grid gap-8 rounded-[2.5rem] border border-black/10 bg-white p-10 text-[var(--ink)] lg:grid-cols-[1.2fr_0.8fr]">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.7)]">{{ content.contactTitle }}</p>
                        <h2 class="mt-3 text-3xl font-semibold" style="font-family: var(--font-display);">
                            {{ content.contactHeadline }}
                        </h2>
                        <p class="mt-4 text-sm text-[color:rgba(30,27,23,0.8)]">
                            {{ content.contactBody }}
                        </p>
                        <div v-if="content.airbnbUrl || content.bookingUrl || content.vrboUrl" class="mt-6">
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.6)]">
                                {{ content.portalsLabel }}
                            </p>
                            <div class="mt-4 flex flex-wrap gap-3">
                                <a
                                    v-if="content.airbnbUrl"
                                    :href="content.airbnbUrl"
                                    target="_blank"
                                    rel="noreferrer"
                                    class="rounded-full border border-black/15 px-4 py-2 text-xs uppercase tracking-[0.3em]"
                                >
                                    Airbnb
                                </a>
                                <a
                                    v-if="content.bookingUrl"
                                    :href="content.bookingUrl"
                                    target="_blank"
                                    rel="noreferrer"
                                    class="rounded-full border border-black/15 px-4 py-2 text-xs uppercase tracking-[0.3em]"
                                >
                                    Booking
                                </a>
                                <a
                                    v-if="content.vrboUrl"
                                    :href="content.vrboUrl"
                                    target="_blank"
                                    rel="noreferrer"
                                    class="rounded-full border border-black/15 px-4 py-2 text-xs uppercase tracking-[0.3em]"
                                >
                                    Vrbo
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-black/10 bg-white p-6">
                        <p class="text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.7)]">Info rapide</p>
                        <ul class="mt-4 space-y-3 text-sm text-[color:rgba(30,27,23,0.8)]">
                            <li>{{ content.address || 'Indirizzo disponibile su richiesta' }}</li>
                            <li v-if="content.contactEmail">{{ content.contactEmail }}</li>
                            <li v-if="content.contactPhone">{{ content.contactPhone }}</li>
                        </ul>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a
                                v-if="content.contactEmail"
                                :href="`mailto:${content.contactEmail}`"
                                class="rounded-full border border-black/15 px-4 py-2 text-xs uppercase tracking-[0.3em]"
                            >
                                Email
                            </a>
                            <a
                                v-if="content.whatsappUrl"
                                :href="content.whatsappUrl"
                                class="rounded-full border border-black/15 px-4 py-2 text-xs uppercase tracking-[0.3em]"
                            >
                                WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-black/10 py-8 text-center text-xs uppercase tracking-[0.3em] text-[color:rgba(30,27,23,0.6)]">
            {{ content.name }}
        </footer>

        <Dialog
            v-for="card in infoCards"
            :key="card.key"
            v-model:visible="modals[card.key]"
            modal
            :header="modalCopy[card.key].title"
            class="w-[100vw] max-w-lg"
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
import { computed, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';

const props = defineProps({
    apartment: {
        type: Object,
        default: null,
    },
    auth: {
        type: Object,
        default: null,
    },
    blocked_dates: {
        type: Array,
        default: () => [],
    },
    reservations: {
        type: Array,
        default: () => [],
    },
    app_url: {
        type: String,
        default: '',
    },
});

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
const routeUrls = computed(() => page.props.routes || {});
const scrollY = ref(0);
const headerOpacity = computed(() => Math.min(0.7, scrollY.value / 220));
const isAuthenticated = computed(() => Boolean(props.auth?.user));
const loginUrl = computed(() => routeUrls.value.login || '/login');
const privateAreaUrl = computed(() => routeUrls.value.private_area || '/private-area');
const bookingRequestUrl = computed(() => routeUrls.value.booking_request || '/booking-request');
const bookingNotice = computed(() => page.props.flash?.booking_notice ?? null);
const startDateValue = ref(null);
const endDateValue = ref(null);
const bookingForm = useForm({
    apartment_id: props.apartment?.id ?? null,
    start_date: '',
    end_date: '',
    guests_count: 1,
    name: '',
    surname: '',
    email: '',
    notes: '',
    payment_plan: 'full',
    payment_locale: '',
});
const inputClass = (field) => {
    const base = 'rounded-xl border bg-white px-4 py-3 text-base text-[var(--ink)]';
    return bookingForm.errors[field]
        ? `${base} border-[var(--terracotta)]`
        : `${base} border-black/15`;
};
const bookingErrorList = computed(() => {
    const errors = Object.values(bookingForm.errors || {}).filter(Boolean);
    return [...new Set(errors)];
});

const startOfDay = (value) => {
    const date = new Date(value);
    date.setHours(0, 0, 0, 0);
    return date;
};

const today = ref(startOfDay(new Date()));

const addDays = (date, amount) => {
    const next = new Date(date);
    next.setDate(next.getDate() + amount);
    return next;
};

const parseDate = (value) => {
    if (!value) {
        return null;
    }

    const [year, month, day] = String(value).split('-').map(Number);

    if (!year || !month || !day) {
        return null;
    }

    return new Date(year, month - 1, day);
};

const formatDateForApi = (date) => {
    if (!date) {
        return '';
    }

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
};

const startMinDate = computed(() => addDays(today.value, 1));
const endMinDate = computed(() => {
    if (startDateValue.value) {
        return addDays(startDateValue.value, 1);
    }

    return startMinDate.value;
});

const buildDisabledDates = (ranges, options = {}) => {
    const includeStart = options.includeStart ?? true;
    const includeEnd = options.includeEnd ?? false;
    const timestamps = new Set();

    ranges.forEach((range) => {
        const start = range?.start;
        const end = range?.end;

        if (!start || !end) {
            return;
        }

        let current = startOfDay(start);
        let last = startOfDay(end);

        if (!includeStart) {
            current = addDays(current, 1);
        }

        if (!includeEnd) {
            last = addDays(last, -1);
        }

        if (current > last) {
            return;
        }

        while (current <= last) {
            timestamps.add(current.getTime());
            current = addDays(current, 1);
        }
    });

    return Array.from(timestamps, (time) => new Date(time));
};

const bookingRanges = computed(() => {
    const ranges = [];
    const sources = [...(props.blocked_dates || []), ...(props.reservations || [])];

    sources.forEach((item) => {
        const start = parseDate(item?.start_date);
        const end = parseDate(item?.end_date);

        if (!start || !end) {
            return;
        }

        if (start >= end) {
            return;
        }

        ranges.push({ start, end });
    });

    return ranges;
});

const disabledStartDates = computed(() => buildDisabledDates(bookingRanges.value, { includeStart: true, includeEnd: false }));
const disabledEndDates = computed(() => buildDisabledDates(bookingRanges.value, { includeStart: false, includeEnd: true }));

const onScroll = () => {
    scrollY.value = window.scrollY || 0;
};

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
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
});

watch(
    () => props.apartment?.id ?? null,
    (value) => {
        bookingForm.apartment_id = value;
    },
    { immediate: true },
);

watch(
    () => props.auth?.user?.email,
    (value) => {
        if (value) {
            bookingForm.email = value;
        }
    },
    { immediate: true },
);

watch(
    () => language.value,
    (value) => {
        bookingForm.payment_locale = value;
    },
    { immediate: true },
);

watch(
    () => bookingForm.start_date,
    (value) => {
        const parsed = parseDate(value);
        const parsedTime = parsed ? startOfDay(parsed).getTime() : null;
        const currentTime = startDateValue.value ? startOfDay(startDateValue.value).getTime() : null;

        if (parsedTime !== currentTime) {
            startDateValue.value = parsed;
        }
    },
    { immediate: true },
);

watch(
    () => bookingForm.end_date,
    (value) => {
        const parsed = parseDate(value);
        const parsedTime = parsed ? startOfDay(parsed).getTime() : null;
        const currentTime = endDateValue.value ? startOfDay(endDateValue.value).getTime() : null;

        if (parsedTime !== currentTime) {
            endDateValue.value = parsed;
        }
    },
    { immediate: true },
);

watch(startDateValue, (value) => {
    const formatted = formatDateForApi(value);

    if (formatted !== bookingForm.start_date) {
        bookingForm.start_date = formatted;
    }

    if (value && endDateValue.value && endDateValue.value <= value) {
        endDateValue.value = null;
    }
});

watch(endDateValue, (value) => {
    const formatted = formatDateForApi(value);

    if (formatted !== bookingForm.end_date) {
        bookingForm.end_date = formatted;
    }
});

const pickLocalized = (base, fallback = '') => {
    if (!props.apartment) {
        return fallback;
    }

    const key = `${base}_${language.value}`;

    return props.apartment[key] || props.apartment[`${base}_it`] || fallback;
};

const submitBooking = () => {
    bookingForm.clearErrors();

    if (!bookingForm.apartment_id) {
        bookingForm.setError('apartment_id', content.value.apartmentRequiredError);
        return;
    }

    if (props.auth?.user?.email) {
        bookingForm.email = props.auth.user.email;
    }

    bookingForm.post(bookingRequestUrl.value, {
        preserveScroll: true,
        onSuccess: () => {
            const fields = ['start_date', 'end_date', 'guests_count', 'name', 'surname', 'notes'];
            if (!props.auth?.user?.email) {
                fields.push('email');
            }
            bookingForm.reset(...fields);
            bookingForm.payment_plan = 'full';
            startDateValue.value = null;
            endDateValue.value = null;
        },
    });
};

const canonicalUrl = computed(() => {
    const base = (props.app_url || '').trim();
    if (!base) {
        return '';
    }
    return `${base.replace(/\/$/, '')}/`;
});

const seoDescription = computed(() => {
    const raw = (content.value.description || content.value.heroBody || '').trim();
    return raw.replace(/\s+/g, ' ').slice(0, 160);
});

const seoImage = computed(() => heroImage.value || '');

const content = computed(() => {
    const fallbackName = 'Somavi';
    const contactEmail = props.apartment?.contact_email?.trim() || '';
    const contactPhone = props.apartment?.contact_phone?.trim() || '';
    const whatsappUrl = props.apartment?.whatsapp_url?.trim() || '';
    const formatCount = (count, one, many) => {
        if (!Number.isFinite(count)) {
            return `0 ${many}`;
        }

        return `${count} ${count === 1 ? one : many}`;
    };

    return {
        name: pickLocalized('name', fallbackName) || fallbackName,
        address: pickLocalized('address', ''),
        description: pickLocalized('description', ''),
        rooms: props.apartment?.rooms_count ?? 0,
        beds: props.apartment?.beds_count ?? 0,
        bathrooms: props.apartment?.bathrooms_count ?? 0,
        guests: props.apartment?.max_guests ?? 0,
        roomsLabel: formatCount(props.apartment?.rooms_count ?? 0, language.value === 'it' ? 'stanza' : 'room', language.value === 'it' ? 'stanze' : 'rooms'),
        bedsLabel: formatCount(props.apartment?.beds_count ?? 0, language.value === 'it' ? 'letto' : 'bed', language.value === 'it' ? 'letti' : 'beds'),
        bathroomsLabel: formatCount(props.apartment?.bathrooms_count ?? 0, language.value === 'it' ? 'bagno' : 'bathroom', language.value === 'it' ? 'bagni' : 'bathrooms'),
        guestsLabel: formatCount(props.apartment?.max_guests ?? 0, language.value === 'it' ? 'ospite' : 'guest', language.value === 'it' ? 'ospiti' : 'guests'),
        airbnbUrl: props.apartment?.airbnb_url ?? null,
        bookingUrl: props.apartment?.booking_url ?? null,
        vrboUrl: props.apartment?.vrbo_url ?? null,
        contactEmail: contactEmail || null,
        contactPhone: contactPhone || null,
        whatsappUrl: whatsappUrl || null,
        kicker: pickLocalized('hero_kicker', language.value === 'it'
            ? 'La tua casa privata a Palermo'
            : 'Your private home in Palermo'),
        headline: pickLocalized('hero_headline', language.value === 'it'
            ? 'Vivi la citta con lentezza, comfort e luce mediterranea.'
            : 'Live the city with calm, comfort, and Mediterranean light.'),
        heroBody: pickLocalized('hero_body', language.value === 'it'
            ? 'Un soggiorno essenziale e luminoso, pensato per chi ama muoversi a piedi e respirare la citta.'
            : 'A bright, essential stay designed for those who love to walk and breathe the city.'),
        primaryCta: pickLocalized('hero_primary_cta', language.value === 'it'
            ? 'Scopri la casa'
            : 'Discover the apartment'),
        secondaryCta: pickLocalized('hero_secondary_cta', language.value === 'it'
            ? 'Verifica disponibilita'
            : 'Check availability'),
        checkInText: props.apartment?.check_in_text ?? null,
        checkOutText: props.apartment?.check_out_text ?? null,
        spacesTitle: language.value === 'it' ? 'Spazi essenziali' : 'Essential spaces',
        spacesHeadline: language.value === 'it'
            ? 'Ogni dettaglio e pensato per restare leggeri.'
            : 'Every detail is designed for a light stay.',
        spacesBody: language.value === 'it'
            ? 'L appartamento offre ambienti funzionali e ariosi, perfetti per un soggiorno indipendente e rilassato.'
            : 'The apartment offers functional, airy rooms perfect for a relaxed, independent stay.',
        detailsCta: language.value === 'it' ? 'Dettagli' : 'Details',
        galleryTitle: language.value === 'it' ? 'Galleria' : 'Gallery',
        galleryHeadline: language.value === 'it' ? 'Atmosfera e luce naturale.' : 'Atmosphere and natural light.',
        galleryBody: language.value === 'it'
            ? 'Le immagini vengono dall appartamento reale, aggiornate in tempo reale dal gestionale.'
            : 'Images come from the real apartment, updated directly from the back office.',
        galleryEmpty: language.value === 'it'
            ? 'Le immagini saranno disponibili a breve.'
            : 'Images will be available soon.',
        galleryImageAlt: language.value === 'it'
            ? `Foto di ${pickLocalized('name', fallbackName) || fallbackName}`
            : `Photo of ${pickLocalized('name', fallbackName) || fallbackName}`,
        bookingTitle: language.value === 'it' ? 'Prenota' : 'Book',
        bookingHeadline: language.value === 'it'
            ? 'Prenota in modo semplice e diretto.'
            : 'Book simply and directly.',
        bookingBody: language.value === 'it'
            ? 'Puoi usare i link ufficiali oppure contattarci per una richiesta dedicata.'
            : 'Use the official links or contact us for a tailored request.',
        arrivalLabel: language.value === 'it' ? 'Arrivo' : 'Arrival date',
        departureLabel: language.value === 'it' ? 'Partenza' : 'Departure date',
        nameLabel: language.value === 'it' ? 'Nome' : 'Name',
        emailLabel: language.value === 'it' ? 'Email' : 'Email',
        notesLabel: language.value === 'it' ? 'Note' : 'Notes',
        surnameLabel: language.value === 'it' ? 'Cognome' : 'Last name',
        guestsFormLabel: language.value === 'it' ? 'Ospiti' : 'Guests',
        bookingSubmitLabel: language.value === 'it' ? 'Invia richiesta' : 'Send request',
        validationErrorTitle: language.value === 'it' ? 'Correggi i campi evidenziati' : 'Please review the highlighted fields',
        apartmentRequiredError: language.value === 'it'
            ? 'Nessun appartamento configurato. Riprova piu tardi.'
            : 'No apartment is configured right now. Please try again later.',
        authenticatedLabel: language.value === 'it' ? 'Autenticato' : 'Authenticated',
        loginCta: language.value === 'it' ? 'Accedi' : 'Sign in',
        paymentPlanLabel: language.value === 'it' ? 'Pagamento' : 'Payment',
        paymentFullLabel: language.value === 'it' ? 'Paga tutto subito' : 'Pay in full now',
        paymentSplitLabel: language.value === 'it' ? '30% ora, 70% piu avanti' : '30% now, 70% later',
        paymentSplitHint: language.value === 'it'
            ? 'Saldo entro 7 giorni prima del check-in.'
            : 'Balance due 7 days before check-in.',
        pricingTitle: language.value === 'it' ? 'Prezzi per notte' : 'Nightly pricing',
        basePriceLabel: language.value === 'it' ? 'Prezzo base (1 ospite)' : 'Base price (1 guest)',
        extraGuestLabel: language.value === 'it' ? 'Supplemento ospite' : 'Extra guest',
        totalLabel: language.value === 'it' ? 'Totale ospiti' : 'Guest total',
        nightsLabel: language.value === 'it' ? 'Notti' : 'Nights',
        totalStayLabel: language.value === 'it' ? 'Totale soggiorno' : 'Stay total',
        perNightLabel: language.value === 'it' ? 'Per notte' : 'Per night',
        pricingNote: language.value === 'it'
            ? 'Il prezzo finale puo variare in base al periodo.'
            : 'Final pricing may vary depending on the season.',
        contactTitle: language.value === 'it' ? 'Contatti' : 'Contact',
        contactHeadline: language.value === 'it'
            ? 'Scrivici per il tuo soggiorno.'
            : 'Reach out for your stay.',
        contactBody: language.value === 'it'
            ? 'Rispondiamo rapidamente per aiutarti con disponibilita e consigli locali.'
            : 'We reply quickly with availability and local tips.',
        portalsLabel: language.value === 'it' ? 'Portali' : 'Portals',
        nav: language.value === 'it'
            ? {
                spaces: 'Spazi',
                gallery: 'Galleria',
                booking: 'Prenota',
                contact: 'Contatti',
            }
            : {
                spaces: 'Spaces',
                gallery: 'Gallery',
                booking: 'Book',
                contact: 'Contact',
            },
        labels: language.value === 'it'
            ? {
                rooms: 'Stanze',
                beds: 'Letti',
                bathrooms: 'Bagni',
                guests: 'Ospiti',
            }
            : {
                rooms: 'Rooms',
                beds: 'Beds',
                bathrooms: 'Bathrooms',
                guests: 'Guests',
            },
    };
});

const pricing = computed(() => {
    const base = Number(props.apartment?.base_price ?? 0);
    const extra2 = Number(props.apartment?.extra_guest_price_2 ?? 0);
    const extra3 = Number(props.apartment?.extra_guest_price_3 ?? 0);
    const extra4 = Number(props.apartment?.extra_guest_price_4 ?? 0);
    const extraPrices = [extra2, extra3, extra4];
    const extras = extraPrices.slice(0, Math.max(0, bookingForm.guests_count - 1));
    const totalForGuests = base + extras.reduce((sum, price) => sum + price, 0);

    return {
        base,
        extra2,
        extra3,
        extra4,
        totalForGuests,
    };
});

const bookingNights = computed(() => {
    if (!bookingForm.start_date || !bookingForm.end_date) {
        return 0;
    }

    const start = new Date(bookingForm.start_date);
    const end = new Date(bookingForm.end_date);
    const diffMs = end.getTime() - start.getTime();
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

    return diffDays > 0 ? diffDays : 0;
});

const bookingTotal = computed(() => pricing.value.totalForGuests * bookingNights.value);

const guestOptions = computed(() => {
    const maxGuests = Math.min(Number(props.apartment?.max_guests ?? 4), 4);
    return Array.from({ length: maxGuests }, (_, index) => index + 1);
});

const formatCurrency = (value) => new Intl.NumberFormat(language.value === 'it' ? 'it-IT' : 'en-US', {
    style: 'currency',
    currency: 'EUR',
}).format(Number.isFinite(value) ? value : 0);

const heroImage = computed(() => props.apartment?.cover_image_url ?? null);

const heroBackgroundStyle = computed(() => {
    if (heroImage.value) {
        return { backgroundImage: `url(${heroImage.value})` };
    }

    return {
        backgroundImage: 'radial-gradient(circle at top, #f6e6d6, #caa28b 45%, #2b6f6b 100%)',
    };
});

const galleryImages = computed(() => props.apartment?.images ?? []);

const infoCards = computed(() => [
    {
        key: 'spaces',
        title: language.value === 'it' ? 'La Casa' : 'The Home',
        summary: language.value === 'it'
            ? 'Spazi curati, pensati per vivere Palermo con calma.'
            : 'Thoughtful spaces designed for a calm Palermo stay.',
    },
    {
        key: 'comfort',
        title: language.value === 'it' ? 'Comfort' : 'Comfort',
        summary: language.value === 'it'
            ? 'Cucina, aree comuni e dettagli essenziali.'
            : 'Kitchen, shared areas, and essential details.',
    },
    {
        key: 'neighborhood',
        title: language.value === 'it' ? 'Quartiere' : 'Neighborhood',
        summary: language.value === 'it'
            ? 'Indirizzo strategico, tutto raggiungibile a piedi.'
            : 'Strategic address, everything within walking distance.',
    },
]);

const modalCopy = computed(() => ({
    spaces: {
        title: language.value === 'it' ? 'La Casa' : 'The Home',
        subtitle: language.value === 'it'
            ? 'Spazi semplici, caldi, autentici.'
            : 'Simple, warm, authentic spaces.',
        body: language.value === 'it'
            ? 'La casa offre ambienti ordinati e luminosi, con una distribuzione pratica per chi viaggia in autonomia.'
            : 'The home offers clean, bright rooms with a practical layout for independent travelers.',
        list: language.value === 'it'
            ? ['Ambienti luminosi', 'Layout funzionale', 'Atmosfera rilassata']
            : ['Bright rooms', 'Functional layout', 'Relaxed atmosphere'],
    },
    comfort: {
        title: language.value === 'it' ? 'Comfort' : 'Comfort',
        subtitle: language.value === 'it'
            ? 'Dettagli che semplificano il soggiorno.'
            : 'Details that simplify the stay.',
        body: language.value === 'it'
            ? 'Cucina attrezzata e spazi pratici per vivere la citta con autonomia e ritmo personale.'
            : 'Equipped kitchen and practical spaces to enjoy the city at your own pace.',
        list: language.value === 'it'
            ? ['Cucina completa', 'Spazi funzionali', 'Dettagli essenziali']
            : ['Full kitchen', 'Functional spaces', 'Essential details'],
    },
    neighborhood: {
        title: language.value === 'it' ? 'Quartiere' : 'Neighborhood',
        subtitle: language.value === 'it'
            ? 'Cuore storico e ritmi locali.'
            : 'Historic heart and local rhythms.',
        body: language.value === 'it'
            ? 'Posizione comoda per muoversi a piedi tra centro storico, porto e stazione.'
            : 'Convenient location for walking to the historic center, port, and station.',
        list: language.value === 'it'
            ? ['Centro storico vicino', 'Porto e stazione a pochi minuti', 'Atmosfera autentica']
            : ['Historic center nearby', 'Port and station within minutes', 'Authentic atmosphere'],
    },
}));

const modals = reactive({
    spaces: false,
    comfort: false,
    neighborhood: false,
});

const openModal = (key) => {
    modals[key] = true;
};
</script>

<style scoped>
button {
    cursor: pointer;
}
</style>
