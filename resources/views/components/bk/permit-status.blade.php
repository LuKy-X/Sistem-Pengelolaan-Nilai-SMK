@props([
    'status',
])

@php
    $map = [
        'PENDING' => ['label' => 'Menunggu', 'tone' => 'yellow'],
        'APPROVED' => ['label' => 'Disetujui', 'tone' => 'green'],
        'REJECTED' => ['label' => 'Ditolak', 'tone' => 'red'],
        'COMPLETED' => ['label' => 'Sudah Kembali', 'tone' => 'blue'],
        'LATE' => ['label' => 'Kembali Terlambat', 'tone' => 'red'],
        'CANCELLED' => ['label' => 'Dibatalkan', 'tone' => 'gray'],
    ];

    $current = $status instanceof \BackedEnum ? $status->value : (is_object($status) ? class_basename($status) : (string) $status);
    $meta = $map[$current] ?? ['label' => $current, 'tone' => 'gray'];
@endphp

<x-bk.status-badge :label="$meta['label']" :tone="$meta['tone']" :dot="true" {{ $attributes }} />
