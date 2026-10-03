@extends('layouts.public')

@section('title', $story->title)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($story->career_story ?: $story->story), 160))

@section('content')
    <x-public.page-header
        eyebrow="Kisah Alumni"
        :title="$story->title"
        :description="$alumnus->student->full_name"
        :breadcrumb="[
            'Beranda' => route('public.home'),
            'Alumni' => route('public.alumni.index'),
            $story->title => null,
        ]" />

    <section class="pb-14 pt-8 sm:pb-16 sm:pt-10">
        <div class="mx-auto grid max-w-5xl gap-6 px-4 sm:px-6 md:grid-cols-5 md:px-8">
            <div class="overflow-hidden rounded-3xl border border-bluelight bg-white shadow-xs md:col-span-2">
                <x-public.media :model="$story" :alt="$alumnus->student->full_name" icon="user" class="aspect-[4/5]" />
            </div>

            <article class="rounded-3xl border border-bluelight bg-white p-6 shadow-xs sm:p-8 md:col-span-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-bluelight px-3 py-1 text-xs font-heading font-semibold text-blueprim">
                        Lulusan {{ $alumnus->graduation_year }}
                    </span>
                    @if (filled($alumnus->current_occupation))
                        <span class="rounded-full border border-bluelight px-3 py-1 text-xs font-medium text-bluedark/70">
                            {{ $alumnus->current_occupation }}
                        </span>
                    @endif
                </div>

                <h2 class="mt-5 font-heading text-xl font-semibold text-bluedark">{{ $alumnus->student->full_name }}</h2>
                @if (filled($alumnus->current_company) || filled($alumnus->city))
                    <p class="mt-1 text-sm text-bluedark/55">
                        {{ collect([$alumnus->current_company, $alumnus->city])->filter()->implode(' · ') }}
                    </p>
                @endif

                @if (filled($story->career_story))
                    <section class="mt-7">
                        <h3 class="font-heading text-base font-semibold text-bluedark">Perjalanan karier</h3>
                        <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-bluedark/70">{{ $story->career_story }}</p>
                    </section>
                @endif

                @if (filled($story->story))
                    <section class="mt-7 border-t border-bluelight pt-6">
                        <h3 class="font-heading text-base font-semibold text-bluedark">Kisah alumni</h3>
                        <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-bluedark/70">{{ $story->story }}</p>
                    </section>
                @endif

                @if (filled($story->quote))
                    <blockquote class="mt-7 border-l-4 border-blueprim pl-4 text-sm italic leading-relaxed text-bluedark/65">
                        &ldquo;{{ $story->quote }}&rdquo;
                    </blockquote>
                @endif

                @if (filled($alumnus->social_link))
                    <a href="{{ $alumnus->social_link }}" target="_blank" rel="noopener noreferrer"
                        class="mt-7 inline-flex items-center gap-2 rounded-full bg-bluedark px-5 py-3 font-heading text-sm font-semibold text-white transition-colors hover:bg-blueprim">
                        Kunjungi profil alumni
                    </a>
                @endif
            </article>

            <a href="{{ route('public.alumni.index') }}"
                class="inline-flex items-center gap-2 font-heading text-sm font-medium text-bluedark transition-colors hover:text-blueprim md:col-span-5">
                <span aria-hidden="true">←</span> Kembali ke daftar alumni
            </a>
        </div>
    </section>
@endsection
