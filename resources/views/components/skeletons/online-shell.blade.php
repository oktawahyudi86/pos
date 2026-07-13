<div class="mx-auto flex h-full w-full max-w-5xl flex-col px-4 pb-32 pt-0 sm:px-6">
    <div class="flex h-20 items-center justify-between">
        <div class="skeleton-shimmer h-11 w-11 rounded-full"></div>
        <div class="flex items-center gap-2">
            <div class="skeleton-shimmer h-9 w-9 rounded-lg"></div>
            <div class="skeleton-shimmer h-4 w-28 rounded-full"></div>
        </div>
        <div class="skeleton-shimmer h-11 w-11 rounded-full"></div>
    </div>
    <div class="mt-4 flex-1">
        {{ $slot }}
    </div>
</div>
<div class="fixed inset-x-0 bottom-0 border-t border-[#d7dde8] bg-white px-2 py-1 sm:px-4">
    <div class="mx-auto grid w-full max-w-5xl grid-cols-3 gap-2">
        <div class="skeleton-shimmer h-16 rounded-xl"></div>
        <div class="skeleton-shimmer h-16 rounded-xl"></div>
        <div class="skeleton-shimmer h-16 rounded-xl"></div>
    </div>
</div>
