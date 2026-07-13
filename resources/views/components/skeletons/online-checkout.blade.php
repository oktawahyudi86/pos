<x-skeletons.online-shell>
    <div class="space-y-4">
        <div class="space-y-2">
            <div class="skeleton-shimmer h-3 w-24 rounded-full"></div>
            <div class="skeleton-shimmer h-8 w-48 rounded-full"></div>
        </div>
        @foreach (range(1, 2) as $item)
            <div class="rounded-xl border border-[#dfe3e9] bg-white p-4">
                <div class="flex justify-between gap-3">
                    <div class="min-w-0 flex-1 space-y-2">
                        <div class="skeleton-shimmer h-4 w-3/4 rounded-full"></div>
                        <div class="skeleton-shimmer h-3 w-1/2 rounded-full"></div>
                    </div>
                    <div class="skeleton-shimmer h-4 w-16 rounded-full"></div>
                </div>
            </div>
        @endforeach
        <div class="rounded-xl border border-[#dfe3e9] bg-[#f6faff] p-4 space-y-3">
            <div class="skeleton-shimmer h-4 w-40 rounded-full"></div>
            <div class="skeleton-shimmer h-12 rounded-xl"></div>
            <div class="skeleton-shimmer h-24 rounded-xl"></div>
            <div class="skeleton-shimmer h-11 rounded-xl"></div>
        </div>
        <div class="rounded-xl border border-dashed border-[#dfe3e9] p-4 space-y-3">
            <div class="skeleton-shimmer h-4 w-36 rounded-full"></div>
            <div class="grid grid-cols-2 gap-2">
                <div class="skeleton-shimmer h-12 rounded-xl"></div>
                <div class="skeleton-shimmer h-12 rounded-xl"></div>
            </div>
            <div class="skeleton-shimmer h-40 rounded-xl"></div>
        </div>
        <div class="skeleton-shimmer h-14 rounded-xl"></div>
    </div>
</x-skeletons.online-shell>
