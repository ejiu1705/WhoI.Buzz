@props(['tier' => 3])

@php
    $meta = match ((int) $tier) {
        1 => [
            'title' => 'Tier 1 • Campus Issued (Gold)',
            'cls' => 'bg-[#F2AA00] text-[#4C2500] border-[#CD7000]',
            'icon' => 'star',
        ],
        2 => [
            'title' => 'Tier 2 • Digitally Verified External',
            'cls' => 'bg-[#CD7000] text-[#FDF6DC] border-[#4C2500]/25',
            'icon' => 'badge-check',
        ],
        default => [
            'title' => 'Tier 3 • Administratively Reviewed',
            'cls' => 'bg-[#4C2500] text-[#F9E7A9] border-[#F2AA00]/40',
            'icon' => 'document-check',
        ],
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[11px] font-bold tracking-tight '.$meta['cls']]) }}>
    <x-icon :name="$meta['icon']" class="w-3.5 h-3.5 shrink-0" />
    <span>{{ $meta['title'] }}</span>
</span>
