@props([
    'balance' => null,
    'standing' => ['label' => 'Aman', 'badge' => 'badge-green', 'tone' => 'safe'],
    'showBalance' => true,
])

<span {{ $attributes->merge(['class' => 'badge '.($standing['badge'] ?? 'badge-gray')]) }}>
    @if($showBalance && $balance !== null)
        {{ $balance }} &middot;
    @endif
    {{ $standing['label'] ?? '—' }}
</span>
