@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
        <p class="text-xs font-medium text-[#4C2500]/80">
            Menampilkan
            <span class="font-extrabold text-[#4C2500]">{{ $paginator->firstItem() }}</span>
            –
            <span class="font-extrabold text-[#4C2500]">{{ $paginator->lastItem() }}</span>
            dari
            <span class="font-extrabold text-[#4C2500]">{{ $paginator->total() }}</span>
            data
        </p>

        <div class="inline-flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="px-3 py-1.5 rounded-xl bg-[#4C2500]/10 text-[#4C2500]/50 text-xs font-bold cursor-not-allowed">&laquo;</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="px-3 py-1.5 rounded-xl bg-[#FDF6DC] hover:bg-[#F2AA00] border border-[#4C2500]/20 text-[#4C2500] text-xs font-extrabold transition">&laquo;</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 py-1.5 text-xs font-bold text-[#4C2500]/65">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page"
                                  class="px-3 py-1.5 rounded-xl bg-[#4C2500] text-[#F2AA00] text-xs font-extrabold shadow-sm">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}"
                               class="px-3 py-1.5 rounded-xl bg-[#FDF6DC] hover:bg-[#F9E7A9] border border-[#4C2500]/15 text-[#4C2500] text-xs font-bold transition">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="px-3 py-1.5 rounded-xl bg-[#FDF6DC] hover:bg-[#F2AA00] border border-[#4C2500]/20 text-[#4C2500] text-xs font-extrabold transition">&raquo;</a>
            @else
                <span class="px-3 py-1.5 rounded-xl bg-[#4C2500]/10 text-[#4C2500]/50 text-xs font-bold cursor-not-allowed">&raquo;</span>
            @endif
        </div>
    </nav>
@endif
