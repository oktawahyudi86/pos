<x-skeletons.online-shell>
    <div class="space-y-4">
        <div class="space-y-2">
            <div class="skeleton-shimmer h-3 w-28 rounded-full"></div>
            <div class="skeleton-shimmer h-8 w-56 rounded-full"></div>
        </div>
        <div class="rounded-xl border border-[#dfe3e9] bg-white p-4">
            <div class="skeleton-shimmer mb-3 h-4 w-32 rounded-full"></div>
            <div class="flex gap-2">
                <div class="skeleton-shimmer h-12 flex-1 rounded-xl"></div>
                <div class="skeleton-shimmer h-12 w-12 rounded-xl"></div>
            </div>
        </div>
        @foreach (range(1, 2) as $item)
            <div class="rounded-xl border border-[#dfe3e9] bg-white p-5 space-y-4">
                <div class="flex justify-between gap-3">
                    <div class="space-y-2">
                        <div class="skeleton-shimmer h-3 w-36 rounded-full"></div>
                        <div class="skeleton-shimmer h-5 w-40 rounded-full"></div>
                    </div>
                    <div class="skeleton-shimmer h-7 w-28 rounded-full"></div>
                </div>
                @foreach (range(1, 5) as $step)
                    <div class="flex items-center gap-3">
                        <div class="skeleton-shimmer h-8 w-8 rounded-full"></div>
                        <div class="skeleton-shimmer h-4 w-40 rounded-full"></div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</x-skeletons.online-shell>
