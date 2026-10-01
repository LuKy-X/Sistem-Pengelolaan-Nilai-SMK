@props([
    'label',
    'tone' => 'gray',
    'dot' => false,
])

@php
    $tones = [
        'green' => 'badge-green',
        'yellow' => 'badge-yellow',
        'red' => 'badge-red',
        'blue' => 'badge-blue',
        'gray' => 'badge-gray',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'badge '.($tones[$tone] ?? $tones['gray'])]) }}>
    @if($dot)
        <span class="dot mr-1"></span>
    @endif
    {{ $label }}
</span>
