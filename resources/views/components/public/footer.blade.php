@php
    $contactPhone   = $schoolProfile?->phone;
    $contactEmail   = $schoolProfile?->email;
    $contactAddress = $schoolProfile?->address;
    $currentYear    = now()->year;
@endphp

<footer class="mt-24 sm:mt-28 md:mt-32 bg-bluedark text-white rounded-t-[2rem] sm:rounded-t-[2.5rem] md:rounded-t-[3rem] overflow-hidden">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 pt-12 sm:pt-14 md:pt-16 reveal">
        <div class="grid md:grid-cols-2 gap-10 md:gap-12 lg:gap-16">

            {{-- School identity and contact --}}
            <div class="flex flex-col">
                <div class="footer-brand-logo w-fit mb-6">
                    <img src="{{ asset('assets/images/logo/logo-smk-bisa-hebat.png') }}"
                        alt="SMK Bisa Hebat"
                        class="h-16 sm:h-20 md:h-24 w-auto object-contain"
                        loading="lazy">
                </div>

                <p class="text-sm text-white/60 mb-6 leading-relaxed max-w-md">
                    {{ $schoolProfile?->description ?? 'Informasi resmi, layanan, dan kontak sekolah dapat ditemukan melalui kanal berikut.' }}
                </p>

                <div class="space-y-4 text-sm mb-7">
                    @if (filled($contactPhone))
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-white/10 grid place-items-center shrink-0">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z" />
                                </svg>
                            </span>
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhone) }}"
                                class="font-heading font-semibold hover:text-bluesoft transition-colors">
                                {{ $contactPhone }}
                            </a>
                        </div>
                    @endif

                    @if (filled($contactEmail))
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-white/10 grid place-items-center shrink-0">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="2" y="4" width="20" height="16" rx="2" />
                                    <path d="m22 6-10 7L2 6" />
                                </svg>
                            </span>
                            <a href="mailto:{{ $contactEmail }}"
                                class="font-heading font-semibold hover:text-bluesoft transition-colors break-all">
                                {{ $contactEmail }}
                            </a>
                        </div>
                    @endif

                    @if (filled($contactAddress))
                        <p class="max-w-md pl-11 text-white/60">{{ $contactAddress }}</p>
                    @endif

                    @if (filled($schoolProfile?->website))
                        <a href="{{ $schoolProfile->website }}" target="_blank" rel="noopener noreferrer"
                            class="inline-flex pl-11 font-heading font-semibold text-white transition-colors hover:text-bluesoft">
                            Situs resmi sekolah
                        </a>
                    @endif
                </div>

                {{-- Sponsor/event card: white mini card, sits under the contact block --}}
                <div class="mb-7">
                    <x-public.sponsor-bar variant="mini" />
                </div>

                <div class="mt-auto border-t border-white/10 pt-5">
                    <a href="{{ route('public.profile') }}"
                        class="inline-flex items-center gap-2 font-heading text-sm font-semibold text-white transition-colors hover:text-bluesoft">
                        Profil &amp; kontak sekolah <span aria-hidden="true">→</span>
                    </a>
                </div>
            </div>

            {{-- School location --}}
            <div class="footer-map-card rounded-2xl overflow-hidden border border-white/10 min-h-[260px] sm:min-h-[300px] md:min-h-0 md:h-full">
                @if (filled($contactAddress))
                    <iframe
                        class="map-frame w-full h-full"
                        src="https://www.google.com/maps?q={{ urlencode(collect([$schoolProfile->school_name, $contactAddress])->filter()->implode(', ')) }}&amp;output=embed"
                        width="100%"
                        height="100%"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin"
                        title="Lokasi {{ $schoolName }}">
                    </iframe>
                @else
                    <div class="flex h-full min-h-[260px] flex-col items-center justify-center gap-3 bg-white/5 px-6 text-center">
                        <p class="font-heading font-semibold">Lokasi sekolah belum dicantumkan.</p>
                        <a href="{{ route('public.profile') }}"
                            class="text-sm font-medium text-white/70 underline underline-offset-4 transition-colors hover:text-white">
                            Lihat profil sekolah
                        </a>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <div class="border-t border-white/10 mt-12 sm:mt-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 py-5 flex flex-col sm:flex-row justify-between gap-2 text-xs text-white/50">
            <p>&copy; <span id="year">{{ $currentYear }}</span> {{ $schoolName }}. Seluruh hak cipta dilindungi.</p>
            <p>Dibuat dengan bangga untuk pendidikan vokasi Indonesia.</p>
        </div>
    </div>
</footer>
