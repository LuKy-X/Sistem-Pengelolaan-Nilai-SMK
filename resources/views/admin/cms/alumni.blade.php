@extends('layouts.admin')

@section('title', 'Manajemen Alumni')

@section('content')
    <div class="space-y-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="font-heading text-xl font-bold text-bluedark md:text-2xl">Manajemen Alumni</h1>
                    <span class="inline-flex rounded-full border border-blue-200 bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800">
                        CMS Publik
                    </span>
                </div>
                <p class="mt-1 max-w-3xl text-sm text-bluedark/60">
                    Pilih siswa yang telah lulus untuk mengisi profil dan kisah yang ingin dipublikasikan di halaman alumni.
                </p>
            </div>
            <a href="{{ route('public.alumni.index') }}" target="_blank" rel="noopener noreferrer"
                class="btn btn-outline btn-sm inline-flex items-center gap-2 text-xs font-semibold">
                Lihat halaman alumni
            </a>
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            <div class="panel border border-bluelight bg-white p-4 shadow-xs">
                <p class="text-xs text-bluedark/55">Siswa lulus</p>
                <p class="mt-1 font-heading text-2xl font-bold text-bluedark">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="panel border border-bluelight bg-white p-4 shadow-xs">
                <p class="text-xs text-bluedark/55">Kisah tampil di publik</p>
                <p class="mt-1 font-heading text-2xl font-bold text-emerald-700">{{ number_format($stats['featured']) }}</p>
            </div>
            <div class="panel border border-bluelight bg-white p-4 shadow-xs">
                <p class="text-xs text-bluedark/55">Kisah tersimpan</p>
                <p class="mt-1 font-heading text-2xl font-bold text-blueprim">{{ number_format($stats['stories']) }}</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.cms.alumni.index') }}"
            class="panel flex flex-col gap-3 border border-bluelight bg-white p-4 shadow-xs sm:flex-row">
            <label for="alumniSearch" class="sr-only">Cari siswa lulusan</label>
            <input id="alumniSearch" type="search" name="search" value="{{ $search }}"
                placeholder="Cari nama, NIS, pekerjaan, atau perusahaan"
                class="f-input min-w-0 flex-1 text-xs">
            <button type="submit" class="btn btn-primary btn-sm text-xs font-semibold">Cari alumni</button>
            @if ($search !== '')
                <a href="{{ route('admin.cms.alumni.index') }}" class="btn btn-outline btn-sm text-center text-xs">Reset</a>
            @endif
        </form>

        @if ($graduates->isEmpty())
            <div class="panel border border-bluelight bg-white p-10 text-center shadow-xs">
                <h2 class="font-heading font-semibold text-bluedark">
                    {{ $search !== '' ? 'Siswa lulusan tidak ditemukan' : 'Belum ada siswa yang lulus' }}
                </h2>
                <p class="mt-2 text-sm text-bluedark/55">Siswa berstatus lulus akan muncul di daftar ini.</p>
            </div>
        @else
            <div class="panel overflow-hidden border border-bluelight bg-white shadow-xs">
                <div class="overflow-x-auto">
                    <table class="tbl w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-bluelight bg-slate-50/70">
                                <th class="w-14 px-4 py-3 text-center">No</th>
                                <th class="px-4 py-3">Nama alumni</th>
                                <th class="px-4 py-3">Tahun lulus</th>
                                <th class="px-4 py-3">Pekerjaan / Perusahaan</th>
                                <th class="px-4 py-3">Status publikasi</th>
                                <th class="w-20 px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($graduates as $index => $graduate)
                                @php
                                    $alumniProfile = $graduate->alumniProfile;
                                    $story = $alumniProfile?->stories->first();
                                    $year = $alumniProfile?->graduation_year ?? $graduate->graduation_date?->format('Y');
                                    $status = $story?->is_featured ? 'Tampil publik' : ($alumniProfile ? 'Draf' : 'Belum diisi');
                                    $statusClass = $story?->is_featured
                                        ? 'bg-emerald-50 text-emerald-800'
                                        : ($alumniProfile ? 'bg-slate-100 text-slate-600' : 'bg-amber-50 text-amber-800');
                                    $photoUrl = $story
                                        ? app(\App\Services\PublicMediaService::class)->forModel($story)
                                        : null;
                                    $modalData = [
                                        'id' => $graduate->id,
                                        'name' => $graduate->full_name,
                                        'action' => route('admin.cms.alumni.update', $graduate),
                                        'graduation_year' => $year ?? now()->year,
                                        'current_occupation' => $alumniProfile?->current_occupation,
                                        'current_company' => $alumniProfile?->current_company,
                                        'city' => $alumniProfile?->city,
                                        'social_link' => $alumniProfile?->social_link,
                                        'story_title' => $story?->title,
                                        'story' => $story?->story,
                                        'career_story' => $story?->career_story,
                                        'quote' => $story?->quote,
                                        'is_featured' => (bool) $story?->is_featured,
                                        'photo_url' => $photoUrl,
                                        'has_photo' => $story?->media->isNotEmpty() ?? false,
                                    ];
                                @endphp
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-4 py-3.5 text-center font-mono text-bluedark/50">
                                        {{ $graduates->firstItem() + $index }}
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="font-heading font-semibold text-bluedark">{{ $graduate->full_name }}</div>
                                        <div class="mt-1 text-[11px] text-bluedark/50">NIS {{ $graduate->nis }}</div>
                                    </td>
                                    <td class="px-4 py-3.5 text-bluedark/70">{{ $year ?: 'Belum dilengkapi' }}</td>
                                    <td class="px-4 py-3.5">
                                        <div class="text-bluedark/80">{{ $alumniProfile?->current_occupation ?: '—' }}</div>
                                        @if (filled($alumniProfile?->current_company))
                                            <div class="mt-1 text-[11px] text-bluedark/50">{{ $alumniProfile->current_company }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-semibold {{ $statusClass }}">
                                            {{ $status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <button type="button"
                                            data-alumni="{{ json_encode($modalData, JSON_UNESCAPED_SLASHES) }}"
                                            onclick="openAlumniModal(JSON.parse(this.dataset.alumni))"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-blueprim transition-colors hover:bg-blue-50 hover:text-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blueprim"
                                            aria-label="{{ $alumniProfile ? 'Edit data '.$graduate->full_name : 'Isi data '.$graduate->full_name }}"
                                            title="{{ $alumniProfile ? 'Edit data alumni' : 'Isi data alumni' }}">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M12 20h9" />
                                                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($graduates->hasPages())
                <div>{{ $graduates->links() }}</div>
            @endif
        @endif
    </div>

    <div id="alumniModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-950/50 p-4"
        role="dialog" aria-modal="true" aria-labelledby="alumniModalTitle">
        <div class="flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-bluelight bg-white shadow-2xl">
            <div class="flex items-center justify-between gap-4 border-b border-bluelight bg-slate-50 px-5 py-4">
                <div class="min-w-0">
                    <h2 id="alumniModalTitle" class="truncate font-heading text-base font-bold text-bluedark">Data Alumni</h2>
                    <p class="mt-1 text-xs text-bluedark/55">Lengkapi profil dan kisah yang ingin ditampilkan.</p>
                </div>
                <button type="button" onclick="closeAlumniModal()"
                    class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-xl text-bluedark/50 hover:bg-slate-200 hover:text-bluedark"
                    aria-label="Tutup form alumni">&times;</button>
            </div>

            <form id="alumniForm" method="POST" action="" enctype="multipart/form-data"
                class="grid gap-5 overflow-y-auto p-4 sm:p-5">
                @csrf
                @method('PUT')
                <input id="alumniStudentId" type="hidden" name="student_id">

                <section class="space-y-4">
                    <h3 class="font-heading text-sm font-semibold text-bluedark">Profil dan pekerjaan</h3>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label for="alumniGraduationYear" class="f-label text-xs">Tahun lulus</label>
                            <input id="alumniGraduationYear" type="number" name="graduation_year" min="1900" max="2100" required
                                class="f-input w-full text-xs">
                        </div>
                        <div>
                            <label for="alumniOccupation" class="f-label text-xs">Pekerjaan</label>
                            <input id="alumniOccupation" type="text" name="current_occupation" maxlength="150"
                                class="f-input w-full text-xs">
                        </div>
                        <div>
                            <label for="alumniCompany" class="f-label text-xs">Perusahaan</label>
                            <input id="alumniCompany" type="text" name="current_company" maxlength="150"
                                class="f-input w-full text-xs">
                        </div>
                        <div>
                            <label for="alumniCity" class="f-label text-xs">Kota</label>
                            <input id="alumniCity" type="text" name="city" maxlength="100"
                                class="f-input w-full text-xs">
                        </div>
                    </div>
                    <div>
                        <label for="alumniSocialLink" class="f-label text-xs">Tautan profil (opsional)</label>
                        <input id="alumniSocialLink" type="url" name="social_link" maxlength="255" placeholder="https://"
                            class="f-input w-full text-xs">
                    </div>
                </section>

                <section class="space-y-4">
                    <h3 class="font-heading text-sm font-semibold text-bluedark">Kisah alumni</h3>
                    <div>
                        <label for="alumniStoryTitle" class="f-label text-xs">Judul kisah</label>
                        <input id="alumniStoryTitle" type="text" name="story[title]" maxlength="200"
                            class="f-input w-full text-xs">
                    </div>
                    <div>
                        <label for="alumniStory" class="f-label text-xs">Cerita alumni</label>
                        <textarea id="alumniStory" name="story[story]" rows="3" class="f-textarea w-full text-xs"></textarea>
                    </div>
                    <div>
                        <label for="alumniCareerStory" class="f-label text-xs">Ringkasan perjalanan karier</label>
                        <textarea id="alumniCareerStory" name="story[career_story]" rows="2" class="f-textarea w-full text-xs"></textarea>
                    </div>
                    <div>
                        <label for="alumniQuote" class="f-label text-xs">Kutipan</label>
                        <textarea id="alumniQuote" name="story[quote]" rows="2" class="f-textarea w-full text-xs"></textarea>
                    </div>
                    <div class="flex flex-col gap-3 rounded-xl border border-bluelight p-3 sm:flex-row sm:items-center">
                        <div class="h-24 w-20 shrink-0 overflow-hidden rounded-lg bg-bluelight">
                            <img id="alumniPhotoPreview" src="" alt="Pratinjau foto kisah alumni"
                                class="hidden h-full w-full object-cover">
                            <span id="alumniPhotoPlaceholder" class="grid h-full w-full place-items-center text-blueprim/50" aria-hidden="true">
                                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <circle cx="12" cy="8" r="4" />
                                    <path d="M5 21v-2a7 7 0 0 1 14 0v2" />
                                </svg>
                            </span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <label for="alumniPhoto" class="f-label text-xs">Foto kisah (opsional)</label>
                            <input id="alumniPhoto" type="file" name="story[photo]" accept="image/jpeg,image/png,image/webp"
                                class="mt-1 block w-full text-xs text-bluedark/70 file:mr-3 file:rounded-lg file:border-0 file:bg-bluelight file:px-3 file:py-2 file:font-semibold file:text-blueprim">
                            <p class="mt-1 text-[11px] text-bluedark/50">JPG, PNG, atau WEBP; maksimal 4 MB.</p>
                            <label id="alumniRemovePhotoLabel" class="mt-2 hidden items-center gap-2 text-xs text-rose-700">
                                <input type="checkbox" name="story[remove_photo]" value="1"
                                    class="rounded border-rose-200 text-rose-600 focus:ring-rose-500">
                                Hapus foto saat disimpan
                            </label>
                        </div>
                    </div>
                </section>

                <label class="flex cursor-pointer items-start gap-2 rounded-xl border border-bluelight bg-bluelight/30 p-3">
                    <input type="hidden" name="is_featured" value="0">
                    <input id="alumniFeatured" type="checkbox" name="is_featured" value="1"
                        class="mt-0.5 rounded border-bluelight text-blueprim focus:ring-blueprim">
                    <span>
                        <span class="block text-xs font-semibold text-bluedark">Tampilkan kisah ini di halaman alumni dan beranda</span>
                        <span class="mt-0.5 block text-[11px] text-bluedark/55">Hanya kisah yang dicentang yang diterbitkan ke halaman publik.</span>
                    </span>
                </label>

                <div class="flex justify-end border-t border-bluelight pt-4">
                    <button type="submit" class="btn btn-primary btn-sm text-xs font-semibold">Simpan data alumni</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const alumniModal = document.getElementById('alumniModal');
        const alumniForm = document.getElementById('alumniForm');

        function openAlumniModal(data) {
            alumniForm.action = data.action;
            document.getElementById('alumniStudentId').value = data.id;
            document.getElementById('alumniModalTitle').textContent = `Data Alumni — ${data.name}`;
            document.getElementById('alumniGraduationYear').value = data.graduation_year || '';
            document.getElementById('alumniOccupation').value = data.current_occupation || '';
            document.getElementById('alumniCompany').value = data.current_company || '';
            document.getElementById('alumniCity').value = data.city || '';
            document.getElementById('alumniSocialLink').value = data.social_link || '';
            document.getElementById('alumniStoryTitle').value = data.story_title || '';
            document.getElementById('alumniStory').value = data.story || '';
            document.getElementById('alumniCareerStory').value = data.career_story || '';
            document.getElementById('alumniQuote').value = data.quote || '';
            document.getElementById('alumniFeatured').checked = !!data.is_featured;
            document.getElementById('alumniPhoto').value = '';

            const photoPreview = document.getElementById('alumniPhotoPreview');
            const photoPlaceholder = document.getElementById('alumniPhotoPlaceholder');
            const removePhotoLabel = document.getElementById('alumniRemovePhotoLabel');
            if (data.photo_url) {
                photoPreview.src = data.photo_url;
                photoPreview.classList.remove('hidden');
                photoPlaceholder.classList.add('hidden');
            } else {
                photoPreview.src = '';
                photoPreview.classList.add('hidden');
                photoPlaceholder.classList.remove('hidden');
            }
            removePhotoLabel.classList.toggle('hidden', !data.has_photo);
            removePhotoLabel.classList.toggle('inline-flex', !!data.has_photo);

            alumniModal.classList.remove('hidden');
            alumniModal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeAlumniModal() {
            alumniModal.classList.add('hidden');
            alumniModal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }

        alumniModal.addEventListener('click', (event) => {
            if (event.target === alumniModal) {
                closeAlumniModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !alumniModal.classList.contains('hidden')) {
                closeAlumniModal();
            }
        });

        @if ($errors->any() && old('student_id'))
            openAlumniModal({
                id: @json((int) old('student_id')),
                action: @json(route('admin.cms.alumni.index')),
                name: @json(old('full_name', 'Data Alumni')),
                graduation_year: @json(old('graduation_year')),
                current_occupation: @json(old('current_occupation')),
                current_company: @json(old('current_company')),
                city: @json(old('city')),
                social_link: @json(old('social_link')),
                story_title: @json(old('story.title')),
                story: @json(old('story.story')),
                career_story: @json(old('story.career_story')),
                quote: @json(old('story.quote')),
                is_featured: @json((bool) old('is_featured')),
                has_photo: false
            });
            alumniForm.action = `${@json(url('/admin/cms/alumni'))}/${@json((int) old('student_id'))}`;
        @endif
    </script>
@endpush
