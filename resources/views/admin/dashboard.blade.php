@php
    $formatRupiah = fn ($value) => 'Rp '.number_format($value, 0, ',', '.');
    $growthClass = fn ($value) => $value >= 0 ? 'text-[#005236]' : 'text-[#ba1a1a]';
    $growthIcon = fn ($value) => $value >= 0 ? 'trending_up' : 'trending_down';
@endphp

<x-pos-layout active="dashboard" title="Dashboard" subtitle="Halo {{ auth()->user()->name }}, berikut ringkasan performa penjualan hari ini.">
    <div class="space-y-6">
        <section class="grid grid-cols-2 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-xl border border-[#c6c5d2] bg-white p-4 shadow-[0_4px_12px_rgba(27,43,107,0.04)] sm:p-6">
                <div class="mb-5 flex items-start justify-between">
                    <span class="rounded-lg bg-[#1b2b6b]/10 p-2 text-[#001356]">
                        <span class="material-symbols-outlined">payments</span>
                    </span>
                    <span class="{{ $growthClass($salesGrowth) }} flex items-center gap-1 text-sm font-bold">
                        <span class="material-symbols-outlined text-[18px]">{{ $growthIcon($salesGrowth) }}</span>
                        {{ abs($salesGrowth) }}%
                    </span>
                </div>
                <p class="text-sm font-bold text-[#454650]">Total Penjualan Hari Ini</p>
                <h3 class="mt-1 text-2xl font-extrabold text-[#001356]">{{ $formatRupiah($todaySales) }}</h3>
            </article>

            <article class="rounded-xl border border-[#c6c5d2] bg-white p-4 shadow-[0_4px_12px_rgba(27,43,107,0.04)] sm:p-6">
                <div class="mb-5 flex items-start justify-between">
                    <span class="rounded-lg bg-[#d5e3fc] p-2 text-[#57657a]">
                        <span class="material-symbols-outlined">receipt_long</span>
                    </span>
                    <span class="{{ $growthClass($transactionGrowth) }} flex items-center gap-1 text-sm font-bold">
                        <span class="material-symbols-outlined text-[18px]">{{ $growthIcon($transactionGrowth) }}</span>
                        {{ abs($transactionGrowth) }}%
                    </span>
                </div>
                <p class="text-sm font-bold text-[#454650]">Jumlah Transaksi</p>
                <h3 class="mt-1 text-2xl font-extrabold text-[#001356]">{{ number_format($todayTransactionCount, 0, ',', '.') }}</h3>
            </article>

            <article class="rounded-xl border border-[#c6c5d2] bg-white p-4 shadow-[0_4px_12px_rgba(27,43,107,0.04)] sm:p-6">
                <div class="mb-5 flex items-start justify-between">
                    <span class="rounded-lg bg-[#6ffbbe]/20 p-2 text-[#005236]">
                        <span class="material-symbols-outlined">star</span>
                    </span>
                </div>
                <p class="text-sm font-bold text-[#454650]">Produk Terlaris Hari Ini</p>
                <h3 class="mt-1 line-clamp-1 text-2xl font-extrabold text-[#001356]">{{ $topProduct?->product_name ?? '-' }}</h3>
                <p class="mt-1 text-xs font-bold text-[#005236]">{{ $topProduct ? number_format($topProduct->total_quantity, 0, ',', '.').' terjual' : 'Belum ada penjualan' }}</p>
            </article>

            <article class="rounded-xl border border-[#c6c5d2] bg-white p-4 shadow-[0_4px_12px_rgba(27,43,107,0.04)] sm:p-6">
                <div class="mb-5 flex items-start justify-between">
                    <span class="rounded-lg bg-[#dde1ff] p-2 text-[#334283]">
                        <span class="material-symbols-outlined">calculate</span>
                    </span>
                </div>
                <p class="text-sm font-bold text-[#454650]">Rata-rata Transaksi</p>
                <h3 class="mt-1 text-2xl font-extrabold text-[#001356]">{{ $formatRupiah($averageBasket) }}</h3>
            </article>
        </section>

        <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <article class="rounded-xl border border-[#c6c5d2] bg-white p-4 shadow-[0_4px_12px_rgba(27,43,107,0.04)] sm:p-6 xl:col-span-2">
                <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold text-[#001356]">Penjualan 7 Hari Terakhir</h2>
                        <p class="mt-1 text-sm text-[#454650]">Tren pendapatan harian dari database POS.</p>
                    </div>
                    <span class="w-fit rounded-lg border border-[#c6c5d2] bg-[#f0f4fa] px-3 py-2 text-sm font-bold text-[#454650]">Minggu Ini</span>
                </div>

                <div class="flex h-72 items-end justify-between gap-3 rounded-xl bg-[#f6faff] px-3 pb-4 pt-8 sm:gap-4 sm:px-5">
                    @foreach ($chartData as $day)
                        @php
                            $height = max(8, (int) round(($day['total'] / $maxChartValue) * 100));
                        @endphp
                        <div class="flex min-w-0 flex-1 flex-col items-center gap-2">
                            <div class="group relative flex h-52 w-full items-end">
                                <div class="w-full rounded-t-lg transition-all duration-700 {{ $day['is_today'] ? 'bg-[#001356]' : 'bg-[#dfe3e9]' }}" style="height: {{ $height }}%;">
                                    <div class="absolute -top-7 left-1/2 hidden -translate-x-1/2 whitespace-nowrap rounded-md bg-[#1b2b6b] px-2 py-1 text-[10px] font-bold text-white group-hover:block">
                                        {{ $formatRupiah($day['total']) }}
                                    </div>
                                </div>
                            </div>
                            <span class="truncate text-xs font-bold {{ $day['is_today'] ? 'text-[#001356]' : 'text-[#454650]' }}">{{ $day['label'] }}</span>
                            <span class="hidden text-[10px] text-[#767681] sm:block">{{ $day['date'] }}</span>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="rounded-xl border border-[#c6c5d2] bg-white p-4 shadow-[0_4px_12px_rgba(27,43,107,0.04)] sm:p-6">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold text-[#001356]">Transaksi Terbaru</h2>
                        <p class="mt-1 text-sm text-[#454650]">Aktivitas kasir paling baru.</p>
                    </div>
                    <a href="{{ route('transactions.index') }}" class="rounded-lg px-3 py-2 text-sm font-bold text-[#001356] hover:bg-[#eef3ff]">Lihat</a>
                </div>

                <div class="space-y-4">
                    @forelse ($recentTransactions as $transaction)
                        <div class="flex items-center gap-3 border-b border-[#dfe3e9] pb-3 last:border-b-0 last:pb-0">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#e4e8ee] text-[#001356]">
                                <span class="material-symbols-outlined text-[20px]">receipt_long</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-[#171c20]">
                                    {{ $transaction->items->take(2)->pluck('product_name')->join(' + ') ?: $transaction->invoice_number }}
                                </p>
                                <p class="text-[11px] text-[#454650]">
                                    {{ $transaction->paid_at?->format('H:i') }} - {{ $transaction->payment_method === 'cash' ? 'Tunai' : 'QRIS' }}
                                </p>
                            </div>
                            <p class="shrink-0 text-sm font-extrabold text-[#001356]">{{ $formatRupiah($transaction->total) }}</p>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-[#c6c5d2] bg-[#f6faff] p-5 text-center text-sm text-[#454650]">
                            Belum ada transaksi hari ini.
                        </div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <article class="rounded-xl border border-[#c6c5d2] bg-white p-4 shadow-[0_4px_12px_rgba(27,43,107,0.04)] sm:p-6 xl:col-span-2">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold text-[#001356]">Stok Perlu Dicek</h2>
                        <p class="mt-1 text-sm text-[#454650]">Produk dengan stok 10 atau kurang.</p>
                    </div>
                    <a href="{{ route('admin.products.index') }}" class="rounded-lg px-3 py-2 text-sm font-bold text-[#001356] hover:bg-[#eef3ff]">Kelola Produk</a>
                </div>

                <div class="overflow-hidden rounded-xl border border-[#dfe3e9]">
                    <table class="w-full min-w-[620px] text-left">
                        <thead class="bg-[#f0f4fa]">
                            <tr>
                                <th class="px-4 py-3 text-xs font-extrabold uppercase tracking-wide text-[#454650]">Produk</th>
                                <th class="px-4 py-3 text-xs font-extrabold uppercase tracking-wide text-[#454650]">Kategori</th>
                                <th class="px-4 py-3 text-xs font-extrabold uppercase tracking-wide text-[#454650]">Stok</th>
                                <th class="px-4 py-3 text-right text-xs font-extrabold uppercase tracking-wide text-[#454650]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#dfe3e9] bg-white">
                            @forelse ($lowStockProducts as $product)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-bold text-[#171c20]">{{ $product->name }}</td>
                                    <td class="px-4 py-3 text-sm text-[#454650]">{{ $product->category?->name ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-3 py-1 text-xs font-extrabold {{ $product->stock <= 0 ? 'bg-[#ffdad6] text-[#93000a]' : 'bg-[#fff3cd] text-[#7a4b00]' }}">
                                            {{ $product->stock }} tersisa
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="text-sm font-bold text-[#001356] hover:underline">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-sm text-[#454650]">Semua stok masih aman.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="relative overflow-hidden rounded-xl border border-[#c6c5d2] bg-[#001356] p-4 text-white shadow-[0_4px_12px_rgba(27,43,107,0.08)] sm:p-6">
                <div class="relative z-10">
                    <span class="material-symbols-outlined mb-8 rounded-xl bg-white/10 p-3 text-3xl">bolt</span>
                    <h2 class="text-2xl font-extrabold">Fokus Hari Ini</h2>
                    <p class="mt-3 text-sm leading-6 text-white/80">Pantau stok rendah dan transaksi terbaru secara rutin. Kalau angka penjualan mulai naik, pastikan produk favorit tetap tersedia di kasir.</p>
                    <a href="{{ route('transactions.index') }}" class="mt-6 inline-flex rounded-xl bg-white px-4 py-3 text-sm font-extrabold text-[#001356]">Buka Laporan</a>
                </div>
                <span class="material-symbols-outlined absolute -bottom-8 -right-5 text-[140px] text-white/10">analytics</span>
            </article>
        </section>
    </div>
</x-pos-layout>
