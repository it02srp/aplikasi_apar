<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GeneratePwaIcons extends Command
{
    protected $signature   = 'pwa:icons';
    protected $description = 'Generate PWA PNG icons (192x192 dan 512x512) dari icon.svg';

    public function handle(): int
    {
        if (!extension_loaded('gd')) {
            $this->error('PHP GD extension tidak aktif. Aktifkan di php.ini terlebih dahulu.');
            return self::FAILURE;
        }

        $sizes = [192, 512];

        foreach ($sizes as $size) {
            $img = imagecreatetruecolor($size, $size);
            imagealphablending($img, true);
            imagesavealpha($img, true);

            // Background rounded feel — fill green #15803d
            $bg   = imagecolorallocate($img, 21, 128, 61);
            $white = imagecolorallocate($img, 255, 255, 255);
            $trans = imagecolorallocatealpha($img, 0, 0, 0, 127);

            imagefill($img, 0, 0, $trans);

            // Draw rounded rectangle background
            $radius = (int)($size * 0.18);
            imagefilledrectangle($img, $radius, 0, $size - $radius, $size, $bg);
            imagefilledrectangle($img, 0, $radius, $size, $size - $radius, $bg);
            imagefilledellipse($img, $radius,          $radius,          $radius * 2, $radius * 2, $bg);
            imagefilledellipse($img, $size - $radius,  $radius,          $radius * 2, $radius * 2, $bg);
            imagefilledellipse($img, $radius,          $size - $radius,  $radius * 2, $radius * 2, $bg);
            imagefilledellipse($img, $size - $radius,  $size - $radius,  $radius * 2, $radius * 2, $bg);

            // Draw a simple "A" letter centered (represents APAR)
            $cx   = (int)($size / 2);
            $cy   = (int)($size / 2);
            $thick = max(4, (int)($size * 0.04));

            // Scale factor
            $s = $size / 512;
            // Left leg of A
            imagefilledpolygon($img, [
                (int)(($cx - 110) * $s + 110 * $s), (int)(($cy + 130) * $s),
                (int)(($cx - 60) * $s + 60 * $s),   (int)(($cy + 130) * $s),
                (int)($cx * $s),                      (int)(($cy - 140) * $s),
                (int)(($cx - 50) * $s + 50 * $s),    (int)(($cy - 140) * $s),
            ], $white);

            // Simpler: just draw letter with imagestring if font permits
            // Overwrite with simpler block shapes
            imagefill($img, 0, 0, $trans);

            // Re-draw bg
            imagefilledrectangle($img, $radius, 0, $size - $radius, $size, $bg);
            imagefilledrectangle($img, 0, $radius, $size, $size - $radius, $bg);
            imagefilledellipse($img, $radius,         $radius,         $radius * 2, $radius * 2, $bg);
            imagefilledellipse($img, $size - $radius, $radius,         $radius * 2, $radius * 2, $bg);
            imagefilledellipse($img, $radius,         $size - $radius, $radius * 2, $radius * 2, $bg);
            imagefilledellipse($img, $size - $radius, $size - $radius, $radius * 2, $radius * 2, $bg);

            // Fire extinguisher: cylinder body
            $bx = (int)($size * 0.35);
            $by = (int)($size * 0.38);
            $bw = (int)($size * 0.30);
            $bh = (int)($size * 0.42);
            $br = (int)($size * 0.04);
            imagefilledrectangle($img, $bx + $br, $by, $bx + $bw - $br, $by + $bh, $white);
            imagefilledrectangle($img, $bx, $by + $br, $bx + $bw, $by + $bh - $br, $white);
            imagefilledellipse($img, $bx + $br,        $by + $br,        $br * 2, $br * 2, $white);
            imagefilledellipse($img, $bx + $bw - $br,  $by + $br,        $br * 2, $br * 2, $white);
            imagefilledellipse($img, $bx + $br,        $by + $bh - $br,  $br * 2, $br * 2, $white);
            imagefilledellipse($img, $bx + $bw - $br,  $by + $bh - $br,  $br * 2, $br * 2, $white);

            // Neck
            $nx = (int)($size * 0.44);
            $ny = (int)($size * 0.25);
            $nw = (int)($size * 0.12);
            $nh = (int)($size * 0.14);
            imagefilledrectangle($img, $nx, $ny, $nx + $nw, $ny + $nh, $white);

            // Valve
            $vx = (int)($size * 0.37);
            $vy = (int)($size * 0.22);
            $vw = (int)($size * 0.26);
            $vh = (int)($size * 0.06);
            imagefilledrectangle($img, $vx, $vy, $vx + $vw, $vy + $vh, $white);

            // Handle (horizontal bar to the right)
            $hx = (int)($size * 0.63);
            $hy = (int)($size * 0.23);
            $hw = (int)($size * 0.14);
            $hh = (int)($size * 0.025);
            imagefilledrectangle($img, $hx, $hy, $hx + $hw, $hy + $hh, $white);

            $path = public_path("icons/icon-{$size}.png");
            imagepng($img, $path);
            imagedestroy($img);

            $this->info("✓ Dibuat: public/icons/icon-{$size}.png");
        }

        $this->info('PWA icons berhasil dibuat!');
        return self::SUCCESS;
    }
}
