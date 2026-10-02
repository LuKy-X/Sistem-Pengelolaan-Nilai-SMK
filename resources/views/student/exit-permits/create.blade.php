@extends('layouts.student')

@section('title', 'Ajukan Izin Keluar')

@section('content')
<div class="space-y-4 lg:space-y-5 max-w-2xl">

  <div>
    <a href="{{ route('student.exit-permits.index') }}" class="text-[11px] font-semibold text-blueprim hover:underline">&larr; Riwayat izin</a>
    <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark mt-1">Ajukan Izin Keluar</h1>
    <p class="text-sm text-bluedark/60 mt-1">Pengajuan akan diverifikasi oleh Guru BK sebelum Anda diperbolehkan keluar lingkungan sekolah.</p>
  </div>

  <div class="panel p-4 lg:p-5">
    <form method="POST" action="{{ route('student.exit-permits.store') }}" class="space-y-4">
      @csrf

      <div>
        <label class="f-label" for="reason_id">Alasan Izin <span class="text-red-500">*</span></label>
        <select id="reason_id" name="reason_id" class="f-select" required>
          <option value="">Pilih alasan...</option>
          @foreach($reasons as $reason)
            <option value="{{ $reason->id }}" @selected((string) old('reason_id') === (string) $reason->id)>{{ $reason->name }}</option>
          @endforeach
        </select>
        @error('reason_id')<p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>

      <div>
        <label class="f-label" for="detail_kebutuhan">Detail Keperluan <span class="text-red-500">*</span></label>
        <textarea id="detail_kebutuhan" name="reason_detail" rows="4" class="f-textarea" placeholder="Contoh: Mengambil obat ke apotek bersama orang tua, dijemput di gerbang sekolah." required minlength="10" maxlength="1000">{{ old('reason_detail') }}</textarea>
        <p class="text-[10px] text-bluedark/45 mt-1">Minimal 10 karakter, maksimal 1000 karakter.</p>
        @error('reason_detail')<p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>@enderror
      </div>

      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="f-label" for="exit_period_id">Keluar pada Jam Pelajaran <span class="text-red-500">*</span></label>
          <select id="exit_period_id" name="exit_period_id" class="f-select" required>
            <option value="">Pilih jam...</option>
            @foreach($periods as $period)
              <option value="{{ $period->id }}" @selected((string) old('exit_period_id') === (string) $period->id)>
                {{ $period->displayLabel() }}
              </option>
            @endforeach
          </select>
          <p class="text-[10px] text-bluedark/45 mt-1">Jam saat Anda meninggalkan sekolah.</p>
          @error('exit_period_id')<p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="f-label" for="return_period_id">Harus Kembali Pada Jam <span class="text-red-500">*</span></label>
          <select id="return_period_id" name="return_period_id" class="f-select" required>
            <option value="">Pilih jam...</option>
            @foreach($periods as $period)
              <option value="{{ $period->id }}" @selected((string) old('return_period_id') === (string) $period->id)>
                {{ $period->displayLabel() }}
              </option>
            @endforeach
          </select>
          <p class="text-[10px] text-bluedark/45 mt-1">Wajib sudah kembali paling lambat di awal jam ini.</p>
          @error('return_period_id')<p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
      </div>

      <div class="p-3 rounded-xl bg-bluelight/50 border border-bluelight text-[11px] text-bluedark/75">
        Durasi izin dihitung otomatis dari kedua jam di atas, jadi Anda tidak perlu menulis jam.
        Setelah izin disetujui, timer kepulangan berjalan dan keterlambatan tercatat otomatis.
        Jika terlambat karena hal mendesak, Anda dapat mengajukan banding kepada Guru BK.
      </div>

      <div class="flex gap-2">
        <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
        <a href="{{ route('student.exit-permits.index') }}" class="btn btn-outline">Batal</a>
      </div>
    </form>
  </div>

</div>
@endsection
