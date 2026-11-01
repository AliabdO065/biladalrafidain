<?php
namespace App\Services;

use App\Models\LandingShipment;
use App\Support\ShipmentStages;
use ArPHP\I18N\Arabic;
use Illuminate\Support\Str;

/** Private PHP/GD rendering: no browser, external request or shell process. */
class ShipmentBillImage
{
    private Arabic $arabic;
    private \GdImage $image;
    private string $arabicFont;
    private string $latinFont;

    public function render(LandingShipment $shipment, string $path, string $locale): void
    {
        if (!function_exists('imagettftext') || !function_exists('imagepng')) {
            throw new \RuntimeException('PHP GD with FreeType is required.');
        }
        $this->arabic = new Arabic();
        $this->arabicFont = resource_path('fonts/NotoNaskhArabic-Regular.ttf');
        $this->latinFont = resource_path('fonts/NotoSans-Regular.ttf');
        if (!is_readable($this->arabicFont) || !is_readable($this->latinFont)) {
            throw new \RuntimeException('Document fonts are missing.');
        }
        $this->image = imagecreatetruecolor(1130, 1600);
        imagefill($this->image, 0, 0, $this->color('#f4f7fc'));
        $ar = $locale === 'ar';
        $align = $ar ? 'right' : 'left';
        $labels = __('trade.bill');
        try {
            $this->box(44, 36, 1042, 204, '#0b2b51', 24);
            $this->box($ar ? 1070 : 44, 36, 16, 204, '#087eef', 8);
            $this->text($labels['company'], 31, $ar ? 370 : 80, 73, 640, '#ffffff', $align, 2);
            $this->text($labels['tagline'], 18, $ar ? 370 : 80, 163, 640, '#cadbed', $align, 2);
            $this->text('BILL OF LADING', 15, $ar ? 80 : 775, 78, 275, '#b8d2ef', 'left', 1);
            $this->text($labels['title'], 27, $ar ? 80 : 775, 121, 275, '#ffffff', 'left', 2);

            foreach ([[$labels['code'], $shipment->tracking_code, true], [$shipment->receipt_date ? $labels['receipt_date'] : $labels['registered'], ($shipment->receipt_date ?? $shipment->created_at)?->format('d/m/Y') ?? '—', false]] as $i => [$label, $value, $primary]) {
                $x = ($ar ? 1-$i : $i) * 531 + 44;
                $this->box($x, 266, 511, 122, $primary ? '#087eef' : '#ffffff', 18, $primary ? null : '#d8e4f1');
                $this->text($label, 18, $x+22, 283, 467, $primary ? '#d7eaff' : '#087adf', $align, 1);
                $this->text($value, $primary ? 19 : 27, $x+22, 323, 467, $primary ? '#ffffff' : '#0b2b51', $primary ? 'left' : $align, 2, true);
            }
            $this->box(44, 414, 1042, 194, '#ffffff', 15, '#d8e4f1');
            imageline($this->image, 565, 414, 565, 608, $this->color('#d8e4f1'));
            imageline($this->image, 44, 511, 1086, 511, $this->color('#d8e4f1'));
            $cells = [[$labels['client'], $shipment->receiver_name ?: '—'], [$labels['number'], $shipment->merchant_number ?: '—'], [$labels['supplier'], $shipment->sender_name ?: '—'], [$labels['route'], ($shipment->trip?->reference ?? '—').' · '.($shipment->city ?: $shipment->destination ?: '—')]];
            foreach ($cells as $i => [$label, $value]) {
                $col = $ar ? 1-($i % 2) : $i % 2;
                $x = 44 + $col * 521; $y = 414 + intdiv($i, 2) * 97;
                $this->text($label, 17, $x+22, $y+12, 477, '#087adf', $align, 1);
                $this->text($value, 22, $x+22, $y+44, 477, '#0b2b51', $align, 2, true);
            }
            $metrics = [[$labels['parcels'], (string)($shipment->packages_count ?? '—'), 'PARCELS'], [$labels['weight'], $shipment->weight_kg !== null ? rtrim(rtrim($shipment->weight_kg, '0'), '.') : '—', 'KG'], [$labels['status'], ShipmentStages::label($shipment->current_status), 'STATUS']];
            foreach ($metrics as $i => [$label, $value, $unit]) {
                $x = 44 + ($ar ? 2-$i : $i) * 353;
                $this->box($x, 634, 336, 156, '#ffffff', 18, '#d8e4f1');
                $this->text($label, 17, $x+14, 651, 308, '#748397', 'center', 1);
                $this->text($value, $i === 2 ? 22 : 32, $x+14, 692, 308, '#087eef', 'center', 2, true);
                $this->text($unit, 12, $x+14, 758, 308, '#748397', 'center', 1);
            }
            $this->box(44, 816, 1042, 55, '#0b2b51', 9);
            $labelX = $ar ? 575 : 69; $valueX = $ar ? 69 : 575;
            $this->text($labels['description'], 20, $labelX, 829, 470, '#ffffff', $align, 1);
            $this->text($labels['details'], 20, $valueX, 829, 470, '#ffffff', $ar ? 'left' : 'right', 1);
            $rows = [[$labels['fee'], trim(($shipment->shipping_fee ?? '—').' '.$shipment->currency)], [$labels['paid'], trim(($shipment->paid_amount ?? '—').' '.$shipment->currency)], [$labels['reference'], $shipment->receipt_number ?: '—'], [$labels['notes'], Str::limit($shipment->notes ?: '—', 170)]];
            $y = 871;
            foreach ($rows as $i => [$label, $value]) {
                $height = $i === 3 ? 112 : 80;
                $this->box(44, $y, 1042, $height, $i % 2 ? '#eff5fc' : '#ffffff', 0, '#d8e4f1');
                $this->text($label, 21, $labelX, $y+23, 470, '#0b2b51', $align, 1);
                $this->text($value, $i === 3 ? 18 : 22, $valueX, $y+23, 470, '#0b2b51', $ar ? 'left' : 'right', $i === 3 ? 3 : 2, true);
                $y += $height;
            }
            $this->box(44, 1251, 1042, 112, '#0b2b51', 22);
            $this->text($labels['balance'], 22, $labelX, 1281, 470, '#cadbed', $align, 2);
            $this->text(trim(($shipment->balance ?? '—').' '.($shipment->currency ?: $labels['no_currency'])), 34, $valueX, 1280, 470, '#06c69f', $ar ? 'left' : 'right', 2, true);
            $this->text($labels['disclaimer'], 15, 55, 1382, 1020, '#6c8097', $align, 3);
            imageline($this->image, 44, 1490, 1086, 1490, $this->color('#d8e4f1'));
            $this->text($labels['company'], 15, 55, 1514, 750, '#6c8097', $align, 2);
            $this->text($labels['thanks'], 15, 825, 1514, 250, '#087adf', 'right', 2);
            if (!imagepng($this->image, $path, 6)) throw new \RuntimeException('Document image could not be saved.');
        } finally {
            imagedestroy($this->image);
        }
    }

    private function color(string $hex): int
    {
        [$r,$g,$b] = sscanf($hex, '#%02x%02x%02x');
        return imagecolorallocate($this->image, $r, $g, $b);
    }

    private function box(int $x, int $y, int $w, int $h, string $fill, int $radius = 0, ?string $border = null): void
    {
        if ($border) { $this->box($x,$y,$w,$h,$border,$radius); $this->box($x+2,$y+2,$w-4,$h-4,$fill,max(0,$radius-2)); return; }
        $color = $this->color($fill); $r = min($radius, intdiv($w,2), intdiv($h,2));
        imagefilledrectangle($this->image,$x+$r,$y,$x+$w-$r,$y+$h,$color);
        imagefilledrectangle($this->image,$x,$y+$r,$x+$w,$y+$h-$r,$color);
        if ($r) foreach ([[$x+$r,$y+$r],[$x+$w-$r,$y+$r],[$x+$r,$y+$h-$r],[$x+$w-$r,$y+$h-$r]] as [$cx,$cy]) imagefilledellipse($this->image,$cx,$cy,2*$r,2*$r,$color);
    }

    private function runs(string $text): array
    {
        $isArabic = (bool)preg_match('/\p{Arabic}/u', $text);
        $glyphs = $isArabic ? $this->arabic->utf8Glyphs($text, 10000, false) : $text;
        $glyphs = preg_replace('/\p{Cf}/u', '', $glyphs);
        $runs = preg_split('/(?<=\p{Arabic})(?=\P{Arabic})|(?<=\P{Arabic})(?=\p{Arabic})/u', $glyphs, -1, PREG_SPLIT_NO_EMPTY);
        return array_map(fn ($run) => [$run, preg_match('/\p{Arabic}/u', $run) ? $this->arabicFont : $this->latinFont], $runs);
    }

    private function width(string $text, int $size): int
    {
        $width = 0;
        foreach ($this->runs($text) as [$glyphs, $font]) {
            $box = imagettfbbox($size,0,$font,$glyphs);
            $width += max($box[0],$box[2],$box[4],$box[6])-min($box[0],$box[2],$box[4],$box[6]);
        }
        return $width;
    }

    private function wrap(string $text, int $size, int $width): array
    {
        $lines=[]; $line='';
        foreach (preg_split('/\s+/u',$text,-1,PREG_SPLIT_NO_EMPTY) as $word) {
            if ($line !== '' && $this->width($line.' '.$word,$size)>$width) { $lines[]=$line; $line=''; }
            // Long tracking codes and names cannot draw outside their cell.
            foreach (mb_str_split($word) as $char) {
                $candidate = $line.$char;
                if ($this->width($candidate,$size)>$width && $line !== '') { $lines[]=$line; $line=''; }
                $line.=$char;
            }
            $line.=' ';
        }
        if (trim($line)!=='')$lines[]=trim($line);
        return array_map('trim',$lines);
    }

    private function text(string $text, int $size, int $x, int $y, int $width, string $color, string $align, int $maxLines = 1, bool $fit = false): void
    {
        $text=mb_substr(preg_replace('/[\x00-\x1F\x{202A}-\x{202E}\x{2066}-\x{2069}]/u',' ',$text),0,512);
        $lines=$this->wrap($text,$size,$width);
        while($fit && count($lines)>$maxLines && $size>12){$size--; $lines=$this->wrap($text,$size,$width);}
        if(count($lines)>$maxLines){$lines=array_slice($lines,0,$maxLines); $last=array_pop($lines);while($last!==''&&$this->width($last.'…',$size)>$width)$last=mb_substr($last,0,-1);$lines[]=$last.'…';}
        $lineHeight=(int)ceil($size*1.5);
        foreach($lines as $index=>$line){
            $lineWidth=$this->width($line,$size);$left=$align==='right'?$x+$width-$lineWidth:($align==='center'?$x+intdiv($width-$lineWidth,2):$x);
            $runs=$this->runs($line);$minY=0;
            foreach($runs as [$glyphs,$font]){$box=imagettfbbox($size,0,$font,$glyphs);$minY=min($minY,$box[1],$box[3],$box[5],$box[7]);}
            foreach($runs as [$glyphs,$font]){
                $box=imagettfbbox($size,0,$font,$glyphs);$minX=min($box[0],$box[2],$box[4],$box[6]);
                imagettftext($this->image,$size,0,$left-$minX,$y+$index*$lineHeight-$minY,$this->color($color),$font,$glyphs);
                $left+=max($box[0],$box[2],$box[4],$box[6])-$minX;
            }
        }
    }
}
