@php
    $formatRupiah = fn ($value) => 'Rp '.number_format($value, 0, ',', '.');
    $width = 226;
    $contentWidth = 198;
    $x = 14;
    $right = 212;
    $logoX = 88;
    $currentY = 18;
    $lineGap = 14;

    $measureItemBlock = function ($item) {
        $lines = 1;
        if ($item->variantOptions->isNotEmpty()) {
            $lines += 1;
        }
        if ($item->addons->isNotEmpty()) {
            $lines += 1;
        }
        if ($item->note) {
            $lines += 1;
        }

        return 24 + ($lines * 12);
    };

    $bodyHeight = 0;
    foreach ($transaction->items as $item) {
        $bodyHeight += $measureItemBlock($item);
    }

    $summaryHeight = 92;
    $footerHeight = 54;
    $height = max(620, 160 + $bodyHeight + $summaryHeight + $footerHeight);
@endphp
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="58mm" height="{{ $height }}px" viewBox="0 0 {{ $width }} {{ $height }}">
    <rect width="{{ $width }}" height="{{ $height }}" fill="#ffffff"/>
    <style>
        .title { font: 700 14px Arial, sans-serif; fill: #111827; }
        .subtitle { font: 400 9px Arial, sans-serif; fill: #4b5563; }
        .label { font: 400 8px Arial, sans-serif; fill: #4b5563; }
        .value { font: 700 8px Arial, sans-serif; fill: #111827; }
        .item { font: 700 8px Arial, sans-serif; fill: #111827; }
        .small { font: 400 7px Arial, sans-serif; fill: #6b7280; }
        .line { stroke: #d1d5db; stroke-dasharray: 3 3; stroke-width: 1; }
        .navy { fill: #001356; }
    </style>

    <image href="{{ asset('images/keijora-bird-navy.png') }}" x="{{ $logoX }}" y="10" width="50" height="50" preserveAspectRatio="xMidYMid meet"/>
    <text x="113" y="76" text-anchor="middle" class="title">Keijora POS</text>
    <text x="113" y="90" text-anchor="middle" class="subtitle">{{ config('app.name', 'Keijora POS') }}</text>
    <text x="113" y="110" text-anchor="middle" class="value">Struk Pembayaran</text>

    <line x1="{{ $x }}" y1="120" x2="{{ $right }}" y2="120" class="line"/>

    <text x="{{ $x }}" y="138" class="label">Invoice</text>
    <text x="{{ $x }}" y="150" class="value">{{ $transaction->invoice_number }}</text>
    <text x="132" y="138" class="label">Kasir</text>
    <text x="132" y="150" class="value">{{ $transaction->cashier->name ?? '-' }}</text>

    <text x="{{ $x }}" y="170" class="label">Tanggal</text>
    <text x="{{ $x }}" y="182" class="value">{{ $transaction->paid_at?->format('d M Y H:i') }}</text>
    <text x="132" y="170" class="label">Metode</text>
    <text x="132" y="182" class="value">{{ $transaction->payment_method === 'cash' ? 'Tunai' : 'QRIS' }}</text>

    <line x1="{{ $x }}" y1="194" x2="{{ $right }}" y2="194" class="line"/>

    @php $currentY = 214; @endphp
    @foreach ($transaction->items as $item)
        @php
            $optionNames = $item->variantOptions->map(fn ($option) => $option->variant_group_name.': '.$option->option_name)->all();
            $addonNames = $item->addons->pluck('addon_name')->all();
            $metaParts = collect([
                $optionNames ? implode(', ', $optionNames) : null,
                $addonNames ? 'Addon: '.implode(', ', $addonNames) : null,
                $item->note ? 'Catatan: '.$item->note : null,
            ])->filter()->all();
            $blockHeight = $measureItemBlock($item);
        @endphp
        <text x="{{ $x }}" y="{{ $currentY }}" class="item">{{ $item->product_name }}</text>
        <text x="{{ $right }}" y="{{ $currentY }}" text-anchor="end" class="value">{{ $formatRupiah($item->line_total) }}</text>
        <text x="{{ $x }}" y="{{ $currentY + 12 }}" class="small">{{ $item->quantity }} x {{ $formatRupiah($item->unit_price) }}</text>
        @if (! empty($metaParts))
            <text x="{{ $x }}" y="{{ $currentY + 24 }}" class="small">{{ \Illuminate\Support\Str::limit(implode(' | ', $metaParts), 66) }}</text>
        @endif
        <line x1="{{ $x }}" y1="{{ $currentY + $blockHeight - 8 }}" x2="{{ $right }}" y2="{{ $currentY + $blockHeight - 8 }}" class="line"/>
        @php $currentY += $blockHeight; @endphp
    @endforeach

    @php
        $summaryTop = $currentY + 8;
    @endphp
    <line x1="{{ $x }}" y1="{{ $summaryTop }}" x2="{{ $right }}" y2="{{ $summaryTop }}" class="line"/>

    <text x="{{ $x }}" y="{{ $summaryTop + 20 }}" class="label">Subtotal</text>
    <text x="{{ $right }}" y="{{ $summaryTop + 20 }}" text-anchor="end" class="value">{{ $formatRupiah($transaction->subtotal) }}</text>

    <text x="{{ $x }}" y="{{ $summaryTop + 36 }}" class="label">Diskon</text>
    <text x="{{ $right }}" y="{{ $summaryTop + 36 }}" text-anchor="end" class="value">-{{ $formatRupiah($transaction->discount_amount) }}</text>

    <text x="{{ $x }}" y="{{ $summaryTop + 52 }}" class="label">Pajak</text>
    <text x="{{ $right }}" y="{{ $summaryTop + 52 }}" text-anchor="end" class="value">{{ $formatRupiah($transaction->tax_amount) }}</text>

    <line x1="{{ $x }}" y1="{{ $summaryTop + 62 }}" x2="{{ $right }}" y2="{{ $summaryTop + 62 }}" class="line"/>

    <text x="{{ $x }}" y="{{ $summaryTop + 80 }}" class="title">Total</text>
    <text x="{{ $right }}" y="{{ $summaryTop + 80 }}" text-anchor="end" class="title navy">{{ $formatRupiah($transaction->total) }}</text>

    <text x="{{ $x }}" y="{{ $summaryTop + 96 }}" class="label">Dibayar</text>
    <text x="{{ $right }}" y="{{ $summaryTop + 96 }}" text-anchor="end" class="value">{{ $formatRupiah($transaction->paid_amount) }}</text>

    <text x="{{ $x }}" y="{{ $summaryTop + 112 }}" class="label">Kembalian</text>
    <text x="{{ $right }}" y="{{ $summaryTop + 112 }}" text-anchor="end" class="value">{{ $formatRupiah($transaction->change_amount) }}</text>

    @if ($transaction->customer_name || $transaction->customer_phone)
        <line x1="{{ $x }}" y1="{{ $summaryTop + 124 }}" x2="{{ $right }}" y2="{{ $summaryTop + 124 }}" class="line"/>
        <text x="{{ $x }}" y="{{ $summaryTop + 140 }}" class="label">Pembeli</text>
        <text x="{{ $x }}" y="{{ $summaryTop + 152 }}" class="value">{{ $transaction->customer_name ?: '-' }}</text>
        <text x="{{ $x }}" y="{{ $summaryTop + 164 }}" class="small">{{ $transaction->customer_phone ?: '-' }}</text>
        @php $footerTop = $summaryTop + 184; @endphp
    @else
        @php $footerTop = $summaryTop + 140; @endphp
    @endif

    <line x1="{{ $x }}" y1="{{ $footerTop }}" x2="{{ $right }}" y2="{{ $footerTop }}" class="line"/>
    <text x="113" y="{{ $footerTop + 24 }}" text-anchor="middle" class="value">Terimakasih sudah memesan makanan kakak</text>
    <text x="113" y="{{ $footerTop + 38 }}" text-anchor="middle" class="small">Nota pemesanan kaka {{ $transaction->invoice_number }}</text>
</svg>
