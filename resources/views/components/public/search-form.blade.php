@props([
    'action',
    'id',
    'label' => 'Cari',
    'placeholder' => 'Cari berdasarkan kata kunci',
    'searchName' => 'q',
    'searchValue' => '',
    'filterName' => null,
    'filterLabel' => 'Semua kategori',
    'filterValue' => '',
    'filters' => [],
    'hiddenFields' => [],
])

<form action="{{ $action }}" method="GET" role="search"
    class="grid w-full min-w-0 gap-2 {{ $filterName ? 'grid-cols-1 min-[400px]:grid-cols-[minmax(0,1fr)_auto] sm:grid-cols-[minmax(0,1fr)_minmax(10rem,0.7fr)_auto] lg:max-w-3xl' : 'grid-cols-[minmax(0,1fr)_auto] lg:max-w-md' }}">
    @foreach ($hiddenFields as $name => $value)
        @if (filled($value))
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endif
    @endforeach

    <label for="{{ $id }}" class="sr-only">{{ $label }}</label>
    <input
        id="{{ $id }}"
        type="search"
        name="{{ $searchName }}"
        value="{{ $searchValue }}"
        maxlength="100"
        placeholder="{{ $placeholder }}"
        class="min-h-11 min-w-0 {{ $filterName ? 'min-[400px]:col-span-2 sm:col-span-1' : '' }} rounded-xl border border-bluesoft/60 bg-white px-4 text-sm text-bluedark placeholder:text-bluedark/40 focus:border-blueprim focus:outline-none focus:ring-2 focus:ring-blueprim/20">

    @if ($filterName)
        <label for="{{ $filterName }}-{{ $id }}" class="sr-only">{{ $filterLabel }}</label>
        <select
            id="{{ $filterName }}-{{ $id }}"
            name="{{ $filterName }}"
            class="min-h-11 min-w-0 rounded-xl border border-bluesoft/60 bg-white px-4 text-sm text-bluedark focus:border-blueprim focus:outline-none focus:ring-2 focus:ring-blueprim/20">
            <option value="">{{ $filterLabel }}</option>
            @foreach ($filters as $filter)
                <option value="{{ $filter['value'] }}" @selected((string) $filterValue === (string) $filter['value'])>
                    {{ $filter['label'] }}
                </option>
            @endforeach
        </select>
    @endif

    <button type="submit" class="min-h-11 rounded-xl bg-blueprim px-5 font-heading text-sm font-semibold text-white transition-colors hover:bg-bluedark">
        Filter
    </button>
</form>
