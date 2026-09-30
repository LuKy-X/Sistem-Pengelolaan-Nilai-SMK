@php
    $contactPhone   = $schoolProfile?->phone;
    $contactEmail   = $schoolProfile?->email;
    $contactAddress = $schoolProfile?->address;
    $currentYear    = now()->year;
@endphp

<footer class="mt-24 sm:mt-28 md:mt-32 bg-bluedark text-white rounded-t-[2rem] sm:rounded-t-[2.5rem] md:rounded-t-[3rem] overflow-hidden">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 pt-12 sm:pt-14 md:pt-16 reveal">
        <div class="grid md:grid-cols-2 gap-10 md:gap-12 lg:gap-16">

            {{-- Identity, contact & social --}}
            <div class="flex flex-col">
                <div class="footer-brand-logo w-fit mb-6">
                    <img src="{{ asset('assets/images/logo/logo-smk-bisa-hebat.png') }}"
                        alt="Logo {{ $schoolName }}"
                        class="h-16 sm:h-20 md:h-24 w-auto object-contain"
                        loading="lazy">
                </div>

                <p class="text-sm text-white/60 mb-6 leading-relaxed max-w-md">
                    {{ $schoolProfile?->description ?? 'SMK Negeri 2 Karanganyar membekali siswa dengan kurikulum berbasis industri, sertifikasi kompetensi, dan jaringan kerja sama dunia usaha untuk masa depan karier yang nyata.' }}
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
                    @else
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-white/10 grid place-items-center shrink-0">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z" />
                                </svg>
                            </span>
                            <p class="font-heading font-semibold">0271-6498171</p>
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
                </div>

                {{-- Sponsor/event card: white mini card, sits under the contact block --}}
                <div class="mb-7">
                    <x-public.sponsor-bar variant="mini" />
                </div>

                {{-- Social media icons --}}
                <div class="mt-auto pt-5 border-t border-white/10 flex justify-start flex-wrap gap-3">
                    <a href="#" aria-label="Facebook"
                        class="w-10 h-10 rounded-xl bg-white/10 hover:bg-blueprim hover:-translate-y-1 flex items-center justify-center transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" aria-hidden="true">
                            <path d="M15 4h-2a4 4 0 00-4 4v3H7v3h2v6h3v-6h2.5l.5-3H12V8a1 1 0 011-1h2V4z"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="Instagram"
                        class="w-10 h-10 rounded-xl bg-white/10 hover:bg-blueprim hover:-translate-y-1 flex items-center justify-center transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="5"/>
                            <circle cx="12" cy="12" r="4"/>
                            <circle cx="17.5" cy="6.5" r="1"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="YouTube"
                        class="w-10 h-10 rounded-xl bg-white/10 hover:bg-blueprim hover:-translate-y-1 flex items-center justify-center transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" aria-hidden="true">
                            <rect x="2" y="5" width="20" height="14" rx="4"/>
                            <path d="M10 9l5 3-5 3V9z" fill="white" stroke="none"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="TikTok"
                        class="w-10 h-10 rounded-xl bg-white/10 hover:bg-blueprim hover:-translate-y-1 flex items-center justify-center transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M16 3c.3 2.1 1.7 3.6 4 3.9v2.7c-1.4 0-2.7-.4-4-1.2v6.4a5.3 5.3 0 11-4.7-5.3v2.8a2.5 2.5 0 102 2.5V3h2.7z" fill="white"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="LinkedIn"
                        class="w-10 h-10 rounded-xl bg-white/10 hover:bg-blueprim hover:-translate-y-1 flex items-center justify-center transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="white" aria-hidden="true">
                            <path d="M6.94 5a2 2 0 11-4-.02 2 2 0 014 .02zM7 8.48H3V21h4V8.48zm6.32 0H9.34V21h3.94v-6.57c0-3.66 4.77-3.96 4.77 0V21H22v-7.93c0-6.17-6.87-5.94-8.68-2.91V8.48z"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Google Maps embed --}}
            <div class="footer-map-card rounded-2xl overflow-hidden border border-white/10 min-h-[260px] sm:min-h-[300px] md:min-h-0 md:h-full">
                @if (filled($contactAddress))
                    <iframe
                        class="map-frame w-full h-full"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3954.857074950696!2d110.94792197505011!3d-7.5905309924241084!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e654b99ab219bfd%3A0x4e63f4d5cebe448a!2sSMK%20Negeri%202%20Karanganyar!5e0!3m2!1sid!2sid!4v1786770289292!5m2!1sid!2sid"
                        width="100%"
                        height="100%"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin"
                        title="Lokasi {{ $schoolName }}">
                    </iframe>
                @else
                    <iframe
                        class="map-frame w-full h-full"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3954.857074950696!2d110.94792197505011!3d-7.5905309924241084!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e654b99ab219bfd%3A0x4e63f4d5cebe448a!2sSMK%20Negeri%202%20Karanganyar!5e0!3m2!1sid!2sid!4v1786770289292!5m2!1sid!2sid"
                        width="100%"
                        height="100%"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin"
                        title="Lokasi {{ $schoolName }}">
                    </iframe>
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
