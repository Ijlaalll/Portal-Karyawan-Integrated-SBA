<?php

namespace App\Services;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeService
{
    /**
     * Generate an inline SVG QR code for a given text/token.
     * Uses BaconQrCode library with proper spec compliance, quiet zone, and high contrast.
     */
    public function generateSvg(string $text, int $size = 200): string
    {
        // Use margin of 2 modules (quiet zone) and white background with black modules
        $renderer = new ImageRenderer(
            new RendererStyle($size, 2),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);

        $svg = $writer->writeString($text);

        // Strip xml header so it inserts cleanly into HTML Blade templates
        $svg = preg_replace('/<\?xml.*?\?>/i', '', $svg);

        return trim($svg);
    }
}

