<div class="mx-auto max-w-7xl">
        @if (session('status'))
            <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($items as $item)
                <article class="rounded-2xl border border-[#c6c5d2] bg-white p-6">
                    <div class="mb-5 flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-[#171c20]">{{ $item->{$titleField} }}</h3>
                            <p class="mt-1 text-sm text-[#454650]">{{ $subtitleCallback ? $subtitleCallback($item) : '' }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $item->is_active ? 'bg-[#6ffbbe] text-[#002113]' : 'bg-[#ffdad6] text-[#93000a]' }}">
                            {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    @if ($priceCallback)
                        <p class="mb-5 text-xl font-bold text-[#001356]">{{ $priceCallback($item) }}</p>
                    @endif

                    <div class="flex items-center justify-end gap-3 border-t border-[#dfe3e9] pt-4 text-sm font-semibold">
                        <a href="{{ route($editRoute, $item) }}" class="text-[#001356] hover:underline">Edit</a>
                        <form action="{{ route($destroyRoute, $item) }}" method="POST" onsubmit="return confirm('Hapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-[#ba1a1a] hover:underline">Hapus</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-[#c6c5d2] bg-white p-12 text-center">
                    <span class="material-symbols-outlined mb-3 text-5xl text-[#767681]">{{ $emptyIcon }}</span>
                    <h3 class="text-lg font-bold text-[#171c20]">{{ $emptyTitle }}</h3>
                    <p class="mt-1 text-sm text-[#454650]">{{ $emptyText }}</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $items->links() }}
        </div>
</div>
