<x-skeletons.online-shell>
    <div class="space-y-4">
        <div class="skeleton-shimmer h-12 rounded-xl"></div>
        <div class="flex gap-2 overflow-hidden">
            <div class="skeleton-shimmer h-10 w-20 shrink-0 rounded-full"></div>
            <div class="skeleton-shimmer h-10 w-24 shrink-0 rounded-full"></div>
            <div class="skeleton-shimmer h-10 w-28 shrink-0 rounded-full"></div>
            <div class="skeleton-shimmer h-10 w-24 shrink-0 rounded-full"></div>
        </div>
        <div class="skeleton-shimmer h-32 rounded-[28px] sm:h-36"></div>
        <div class="grid grid-cols-2 gap-2 sm:gap-3">
            @foreach (range(1, 6) as $item)
                <div class="overflow-hidden rounded-[20px] border border-[#dfe3e9] bg-white p-0">
                    <div class="skeleton-shimmer h-28 w-full rounded-none"></div>
                    <div class="space-y-2 p-3">
                        <div class="skeleton-shimmer h-4 w-4/5 rounded-full"></div>
                        <div class="skeleton-shimmer h-4 w-1/2 rounded-full"></div>
                        <div class="flex gap-2">
                            <div class="skeleton-shimmer h-6 w-14 rounded-full"></div>
                            <div class="skeleton-shimmer h-6 w-16 rounded-full"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-skeletons.online-shell>
