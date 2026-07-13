@props([
    'href',
    'label' => 'Kembali',
])

<a
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' => 'inline-flex shrink-0 items-center justify-center gap-2 rounded-full border border-[#c6c5d2] bg-white text-[#001356] shadow-sm transition hover:bg-[#eef3ff] active:scale-[0.98]',
    ]) }}
    aria-label="{{ $label }}"
>
    <span class="material-symbols-outlined text-[22px]">arrow_back</span>
    <span class="hidden text-sm font-bold sm:inline">{{ $label }}</span>
</a>
