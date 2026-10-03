@extends('layouts.public')

@section('title', $achievement->title)
@section('meta_description', str($achievement->description ?? '')->limit(155)->value() ?: 'Prestasi siswa '.$schoolName)

@section('content')
    <x-public.page-header
        :eyebrow="$achievement->category?->name ?? 'Prestasi'"
        :title="$achievement->title"
        :breadcrumb="[
            'Beranda' => route('public.home'),
            'Prestasi' => route('public.achievements.index'),
            $achievement->title => null,
        ]" />

    <section class="pt-8 pb-14 sm:pt-10 sm:pb-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="grid gap-6 lg:grid-cols-5">
                <div class="overflow-hidden rounded-3xl border border-bluelight bg-white shadow-xs lg:col-span-3">
                    <x-public.media :model="$achievement" :alt="$achievement->title" icon="award" class="aspect-[4/3]" />
                </div>

                <article class="h-fit rounded-3xl border border-bluelight bg-white p-6 shadow-xs sm:p-8 lg:col-span-2">
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full bg-bluelight px-3 py-1 text-xs font-heading font-semibold text-blueprim">
                            {{ $achievement->category?->name ?? 'Prestasi' }}
                        </span>
                        @if ($achievement->is_featured)
                            <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-heading font-semibold text-amber-700">
                                Unggulan
                            </span>
                        @endif
                    </div>

                    <h2 class="mt-4 font-heading text-xl font-semibold leading-snug text-bluedark">
                        {{ $achievement->title }}
                    </h2>

                    <dl class="mt-6 space-y-4 text-sm">
                        <div>
                            <dt class="text-xs text-bluedark/45">Tingkat</dt>
                            <dd class="mt-1 font-medium text-bluedark">{{ $achievement->level }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-bluedark/45">Lingkup</dt>
                            <dd class="mt-1 font-medium text-bluedark">{{ $achievement->scope }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-bluedark/45">Tanggal</dt>
                            <dd class="mt-1 font-medium text-bluedark">{{ $achievement->achievement_date->translatedFormat('d F Y') }}</dd>
                        </div>
                        @if (filled($achievement->rank))
                            <div>
                                <dt class="text-xs text-bluedark/45">Peringkat</dt>
                                <dd class="mt-1 font-medium text-bluedark">{{ $achievement->rank }}</dd>
                            </div>
                        @endif
                        @if (filled($achievement->organizer))
                            <div>
                                <dt class="text-xs text-bluedark/45">Penyelenggara</dt>
                                <dd class="mt-1 font-medium text-bluedark">{{ $achievement->organizer }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if (filled($achievement->description))
                        <div class="mt-6 border-t border-bluelight pt-5 text-sm leading-relaxed text-bluedark/70">
                            {{ $achievement->description }}
                        </div>
                    @endif
                </article>
            </div>

            @if ($achievement->participants->isNotEmpty())
                <section class="mt-8 rounded-3xl border border-bluelight bg-white p-6 shadow-xs sm:p-8">
                    <h2 class="font-heading text-lg font-semibold text-bluedark">Siswa yang berpartisipasi</h2>
                    <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ($achievement->participants as $participant)
                            <li class="rounded-2xl bg-[#F7FBFF] p-4">
                                <p class="font-heading text-sm font-medium text-bluedark">
                                    {{ $participant->student?->full_name ?? 'Siswa' }}
                                </p>
                                @if (filled($participant->role))
                                    <p class="mt-1 text-xs text-bluedark/55">{{ $participant->role }}</p>
                                @endif
                                @if (filled($participant->description))
                                    <p class="mt-2 text-xs leading-relaxed text-bluedark/65">{{ $participant->description }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <a href="{{ route('public.achievements.index') }}"
                class="mt-8 inline-flex items-center gap-2 font-heading text-sm font-medium text-bluedark transition-colors hover:text-blueprim">
                <span aria-hidden="true">←</span> Kembali ke daftar prestasi
            </a>
        </div>
    </section>
@endsection
