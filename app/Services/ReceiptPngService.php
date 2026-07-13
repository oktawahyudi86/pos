<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ReceiptPngService
{
    private int $width = 580;
    private int $padding = 30;
    private int $headerTop = 24;
    private int $logoWidth = 106;
    private int $lineHeight = 24;

    public function ensure(Transaction $transaction): string
    {
        $path = $this->path($transaction);

        File::ensureDirectoryExists(dirname($path));
        $this->render($transaction, $path);

        return $path;
    }

    public function publicRelativePath(Transaction $transaction): string
    {
        return 'receipts/'.$transaction->tenant_id.'/'.$this->fileName($transaction).'.png';
    }

    public function path(Transaction $transaction): string
    {
        return Storage::disk('public')->path($this->publicRelativePath($transaction));
    }

    private function render(Transaction $transaction, string $path): void
    {
        $transaction->loadMissing(['cashier', 'items.variantOptions', 'items.addons']);
        $receipt = $this->receiptSettings($transaction->tenant_id);
        $metrics = $this->measure($transaction, $receipt);
        $height = max(820, $metrics['height']);

        $image = imagecreatetruecolor($this->width, $height);
        if (! $image) {
            throw new RuntimeException('Gagal membuat canvas receipt.');
        }

        imageantialias($image, true);
        $white = imagecolorallocate($image, 255, 255, 255);
        $navy = imagecolorallocate($image, 0, 19, 86);
        $text = imagecolorallocate($image, 23, 28, 32);
        $muted = imagecolorallocate($image, 102, 112, 133);
        $line = imagecolorallocate($image, 228, 232, 239);
        imagefilledrectangle($image, 0, 0, $this->width, $height, $white);

        $y = $this->headerTop;
        $logoPath = $this->resolveLogoPath($receipt['logo_path'] ?? null);
        if ($logoPath && File::exists($logoPath)) {
            $this->drawCenteredLogo($image, $logoPath, $this->logoWidth, $y);
            $y += 92;
        } else {
            $y += 14;
        }

        $this->drawCenteredText($image, $receipt['cafe_name'] ?: config('app.name', 'Keijora POS'), $y + 10, 5, $navy);
        $y += 32;
        if (! empty($receipt['address'])) {
            $y = $this->drawCenteredWrappedText($image, $receipt['address'], $y + 2, 3, $muted, 420, 16);
        }
        if (! empty($receipt['phone'])) {
            $this->drawCenteredText($image, $receipt['phone'], $y + 4, 3, $muted);
            $y += 22;
        }

        $y += 8;
        $this->drawCenteredText($image, 'Struk Pembayaran', $y + 4, 4, $text);
        $y += 30;
        $this->drawLine($image, $this->padding, $y, $this->width - $this->padding, $y, $line);

        $y += 24;
        $y = $this->drawMetaRow($image, 'Invoice', $transaction->invoice_number, 'Kasir', $transaction->cashier->name ?? '-', $y, $text, $muted);
        $y = $this->drawMetaRow($image, 'Tanggal', $transaction->paid_at?->format('d M Y H:i') ?? '-', 'Metode', $transaction->payment_method === 'cash' ? 'Tunai' : 'QRIS', $y + 2, $text, $muted);

        $y += 10;
        $this->drawLine($image, $this->padding, $y, $this->width - $this->padding, $y, $line);
        $y += 18;

        foreach ($transaction->items as $item) {
            $metaParts = [];
            $variantText = $item->variantOptions->map(fn ($option) => $option->variant_group_name.': '.$option->option_name)->filter()->implode(', ');
            if ($variantText !== '') {
                $metaParts[] = $variantText;
            }
            $addonText = $item->addons->pluck('addon_name')->filter()->implode(', ');
            if ($addonText !== '') {
                $metaParts[] = 'Addon: '.$addonText;
            }
            if ($item->note) {
                $metaParts[] = 'Catatan: '.$item->note;
            }

            $blockHeight = $this->measureItemBlock($item, $metaParts);
            $this->drawText($image, $this->padding, $y, $item->product_name, 4, $text);
            $this->drawRightText($image, $this->width - $this->padding, $y, $this->formatRupiah($item->line_total), 3, $text);
            $this->drawText($image, $this->padding, $y + 22, $item->quantity.' x '.$this->formatRupiah($item->unit_price), 3, $muted);
            if (! empty($metaParts)) {
                $this->drawWrappedText($image, implode(' | ', $metaParts), $this->padding, $y + 38, 3, $muted, $this->width - ($this->padding * 2), 16);
            }
            $this->drawLine($image, $this->padding, $y + $blockHeight - 9, $this->width - $this->padding, $y + $blockHeight - 9, $line);
            $y += $blockHeight;
        }

        $y += 10;
        $this->drawLine($image, $this->padding, $y, $this->width - $this->padding, $y, $line);
        $y += 18;

        $this->drawSummaryRow($image, 'Subtotal', $this->formatRupiah($transaction->subtotal), $y, $text, $muted, false);
        $y += 18;
        $this->drawSummaryRow($image, 'Diskon', '-'.$this->formatRupiah($transaction->discount_amount), $y, $text, $muted, false);
        $y += 18;
        $this->drawSummaryRow($image, 'Pajak', $this->formatRupiah($transaction->tax_amount), $y, $text, $muted, false);
        $y += 18;
        $this->drawLine($image, $this->padding, $y, $this->width - $this->padding, $y, $line);
        $y += 18;

        $this->drawSummaryRow($image, 'Total', $this->formatRupiah($transaction->total), $y, $navy, $muted, true);
        $y += 20;
        $this->drawSummaryRow($image, 'Dibayar', $this->formatRupiah($transaction->paid_amount), $y, $text, $muted, false);
        $y += 18;
        $this->drawSummaryRow($image, 'Kembalian', $this->formatRupiah($transaction->change_amount), $y, $text, $muted, false);
        $y += 20;

        if ($transaction->customer_name || $transaction->customer_phone) {
            $this->drawLine($image, $this->padding, $y, $this->width - $this->padding, $y, $line);
            $y += 16;
            $this->drawText($image, $this->padding, $y, 'Pembeli', 3, $muted);
            $y += 14;
            $this->drawText($image, $this->padding, $y, $transaction->customer_name ?: '-', 4, $text);
            $y += 12;
            $this->drawText($image, $this->padding, $y, $transaction->customer_phone ?: '-', 3, $muted);
            $y += 18;
        }

        $footerY = max($y + 18, $height - 110);
        $this->drawLine($image, $this->padding, $footerY, $this->width - $this->padding, $footerY, $line);
        $this->drawCenteredText($image, $receipt['cafe_name'] ?: config('app.name', 'Keijora POS'), $footerY + 18, 4, $navy);
        $this->drawCenteredText($image, 'Terimakasih sudah memesan makanan kakak', $footerY + 34, 3, $navy);
        $footerNote = $receipt['footer_note'] ?: ('Nota pemesanan kaka '.$transaction->invoice_number);
        $this->drawCenteredText($image, $footerNote, $footerY + 50, 2, $muted);

        imagepng($image, $path, 6);
        imagedestroy($image);
    }

    private function receiptSettings(int $tenantId): array
    {
        return Setting::getValue('receipt', [
            'logo_path' => null,
            'cafe_name' => config('app.name', 'Keijora POS'),
            'address' => '',
            'phone' => '',
            'footer_note' => 'Terima kasih atas kunjungan Anda.',
        ], $tenantId);
    }

    private function resolveLogoPath(?string $logoPath): ?string
    {
        if ($logoPath) {
            $publicPath = Storage::disk('public')->path($logoPath);
            if (File::exists($publicPath)) {
                return $publicPath;
            }
        }

        $fallback = public_path('images/keijora-bird-navy.png');

        return File::exists($fallback) ? $fallback : null;
    }

    private function drawCenteredLogo($image, string $path, int $targetWidth, int $y): void
    {
        $logo = $this->loadImageResource($path);
        if (! $logo) {
            return;
        }

        $srcWidth = imagesx($logo);
        $srcHeight = imagesy($logo);
        if ($srcWidth <= 0 || $srcHeight <= 0) {
            imagedestroy($logo);
            return;
        }

        $targetHeight = (int) round($srcHeight * ($targetWidth / $srcWidth));
        $canvasX = (int) round(($this->width - $targetWidth) / 2);
        imagecopyresampled($image, $logo, $canvasX, $y, 0, 0, $targetWidth, $targetHeight, $srcWidth, $srcHeight);
        imagedestroy($logo);
    }

    private function loadImageResource(string $path)
    {
        $mime = File::mimeType($path);

        return match ($mime) {
            'image/png' => imagecreatefrompng($path),
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : null,
            'image/gif' => imagecreatefromgif($path),
            default => null,
        };
    }

    private function measure(Transaction $transaction, array $receipt): array
    {
        $height = 240;
        $height += $receipt['address'] ? $this->countWrappedLines($receipt['address'], 420, 3) * 16 : 0;
        $height += $receipt['phone'] ? 22 : 0;
        $height += 32; // title
        $height += 24; // separator
        $height += 2 * 18; // meta rows

        foreach ($transaction->items as $item) {
            $metaParts = [];
            $variantText = $item->variantOptions->map(fn ($option) => $option->variant_group_name.': '.$option->option_name)->filter()->implode(', ');
            if ($variantText !== '') {
                $metaParts[] = $variantText;
            }
            $addonText = $item->addons->pluck('addon_name')->filter()->implode(', ');
            if ($addonText !== '') {
                $metaParts[] = 'Addon: '.$addonText;
            }
            if ($item->note) {
                $metaParts[] = 'Catatan: '.$item->note;
            }

            $height += $this->measureItemBlock($item, $metaParts);
        }

        $height += 20; // summary separator
        $height += 4 * 18; // summary rows
        $height += ($transaction->customer_name || $transaction->customer_phone) ? 54 : 0;
        $height += 116; // footer

        return ['height' => $height];
    }

    private function measureItemBlock($item, array $metaParts): int
    {
        $lines = 2;
        if (! empty($metaParts)) {
            $wrapped = $this->countWrappedLines(implode(' | ', $metaParts), $this->width - ($this->padding * 2), 3);
            $lines += max(1, $wrapped);
        }

        return 22 + ($lines * 16);
    }

    private function drawSummaryRow($image, string $label, string $value, int $y, int $labelColor, int $mutedColor, bool $emphasize): void
    {
        $labelSize = $emphasize ? 5 : 3;
        $valueSize = $emphasize ? 5 : 3;
        $this->drawText($image, $this->padding, $y, $label, $labelSize, $labelColor);
        $this->drawRightText($image, $this->width - $this->padding, $y, $value, $valueSize, $labelColor);
    }

    private function drawMetaRow($image, string $leftLabel, string $leftValue, string $rightLabel, string $rightValue, int $y, int $textColor, int $mutedColor): int
    {
        $this->drawText($image, $this->padding, $y, $leftLabel, 3, $mutedColor);
        $this->drawText($image, $this->padding, $y + 15, $leftValue, 4, $textColor);
        $this->drawText($image, 322, $y, $rightLabel, 3, $mutedColor);
        $this->drawText($image, 322, $y + 15, $rightValue, 4, $textColor);

        return $y + 34;
    }

    private function drawCenteredText($image, string $text, int $y, int $font, int $color): void
    {
        $x = (int) round(($this->width - $this->textWidth($text, $font)) / 2);
        imagestring($image, $font, max($this->padding, $x), $y, $text, $color);
    }

    private function drawCenteredWrappedText($image, string $text, int $y, int $font, int $color, int $maxWidth, int $lineHeight): int
    {
        $lines = $this->wrapText($text, $maxWidth, $font);
        foreach ($lines as $line) {
            $this->drawCenteredText($image, $line, $y, $font, $color);
            $y += $lineHeight;
        }

        return $y;
    }

    private function drawWrappedText($image, string $text, int $x, int $y, int $font, int $color, int $maxWidth, int $lineHeight): int
    {
        $lines = $this->wrapText($text, $maxWidth, $font);
        foreach ($lines as $line) {
            imagestring($image, $font, $x, $y, $line, $color);
            $y += $lineHeight;
        }

        return $y;
    }

    private function drawText($image, int $x, int $y, string $text, int $font, int $color): void
    {
        imagestring($image, $font, $x, $y, $text, $color);
    }

    private function drawRightText($image, int $rightX, int $y, string $text, int $font, int $color): void
    {
        $x = max($this->padding, $rightX - $this->textWidth($text, $font));
        imagestring($image, $font, $x, $y, $text, $color);
    }

    private function drawLine($image, int $x1, int $y1, int $x2, int $y2, int $color): void
    {
        imageline($image, $x1, $y1, $x2, $y2, $color);
    }

    private function wrapText(string $text, int $maxWidth, int $font): array
    {
        $words = preg_split('/\s+/u', trim($text)) ?: [];
        $lines = [];
        $line = '';

        foreach ($words as $word) {
            $candidate = $line === '' ? $word : $line.' '.$word;
            if ($this->textWidth($candidate, $font) <= $maxWidth || $line === '') {
                $line = $candidate;
            } else {
                $lines[] = $line;
                $line = $word;
            }
        }

        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines ?: [''];
    }

    private function countWrappedLines(string $text, int $maxWidth, int $font): int
    {
        return count($this->wrapText($text, $maxWidth, $font));
    }

    private function textWidth(string $text, int $font): int
    {
        return imagefontwidth($font) * max(1, mb_strwidth($text, 'UTF-8'));
    }

    private function formatRupiah(int|float $value): string
    {
        return 'Rp '.number_format($value, 0, ',', '.');
    }

    private function fileName(Transaction $transaction): string
    {
        return Str::slug($transaction->invoice_number, '_');
    }
}
