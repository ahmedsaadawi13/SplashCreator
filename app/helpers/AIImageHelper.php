<?php
// FILE: /app/helpers/AIImageHelper.php

class AIImageHelper {

    /**
     * Simulate AI image generation
     * In production, this would call DALL-E, Midjourney, Stable Diffusion API, etc.
     */
    public static function generate($prompt, $options = []) {
        $style = $options['style'] ?? 'realistic';
        $width = $options['width'] ?? 1024;
        $height = $options['height'] ?? 1024;
        $tenantId = $options['tenant_id'] ?? 1;

        // Create tenant directory if not exists
        $uploadDir = __DIR__ . '/../../storage/uploads/' . $tenantId . '/generated';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $filename = 'ai_image_' . time() . '_' . uniqid() . '.jpg';
        $filepath = $uploadDir . '/' . $filename;
        $relativePath = 'storage/uploads/' . $tenantId . '/generated/' . $filename;

        // Simulate image generation by creating a placeholder image
        self::createPlaceholderImage($filepath, $width, $height, $prompt);

        return [
            'success' => true,
            'image_path' => $relativePath,
            'width' => $width,
            'height' => $height,
            'style' => $style,
            'prompt' => $prompt
        ];
    }

    private static function createPlaceholderImage($filepath, $width, $height, $prompt) {
        // Create a simple placeholder image
        $image = imagecreatetruecolor($width, $height);

        // Create gradient background
        $colors = self::getColorPalette();
        $color1 = imagecolorallocate($image, $colors[0][0], $colors[0][1], $colors[0][2]);
        $color2 = imagecolorallocate($image, $colors[1][0], $colors[1][1], $colors[1][2]);

        // Fill with gradient
        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / $height;
            $r = (int)($colors[0][0] * (1 - $ratio) + $colors[1][0] * $ratio);
            $g = (int)($colors[0][1] * (1 - $ratio) + $colors[1][1] * $ratio);
            $b = (int)($colors[0][2] * (1 - $ratio) + $colors[1][2] * $ratio);
            $color = imagecolorallocate($image, $r, $g, $b);
            imageline($image, 0, $y, $width, $y, $color);
        }

        // Add text
        $white = imagecolorallocate($image, 255, 255, 255);
        $textLines = self::wrapText($prompt, 40);

        $y = ($height / 2) - (count($textLines) * 20);
        foreach ($textLines as $line) {
            $bbox = imagettfbbox(16, 0, self::getFont(), $line);
            $textWidth = $bbox[2] - $bbox[0];
            $x = ($width - $textWidth) / 2;
            imagettftext($image, 16, 0, $x, $y, $white, self::getFont(), $line);
            $y += 30;
        }

        // Add watermark
        $watermark = "AI Generated - SplashCreator";
        imagettftext($image, 10, 0, 10, $height - 20, $white, self::getFont(), $watermark);

        // Save image
        imagejpeg($image, $filepath, 85);
        imagedestroy($image);
    }

    private static function getColorPalette() {
        $palettes = [
            [[99, 102, 241], [139, 92, 246]], // Purple gradient
            [[244, 114, 182], [251, 146, 60]], // Pink-Orange gradient
            [[59, 130, 246], [147, 51, 234]], // Blue-Purple gradient
            [[16, 185, 129], [59, 130, 246]], // Green-Blue gradient
            [[239, 68, 68], [245, 158, 11]], // Red-Yellow gradient
        ];

        return $palettes[array_rand($palettes)];
    }

    private static function wrapText($text, $maxLength) {
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            if (strlen($currentLine . ' ' . $word) <= $maxLength) {
                $currentLine .= ($currentLine ? ' ' : '') . $word;
            } else {
                if ($currentLine) {
                    $lines[] = $currentLine;
                }
                $currentLine = $word;
            }
        }

        if ($currentLine) {
            $lines[] = $currentLine;
        }

        return array_slice($lines, 0, 4); // Max 4 lines
    }

    private static function getFont() {
        // Try to find a system font
        $fonts = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            '/System/Library/Fonts/Helvetica.ttc',
            __DIR__ . '/../../public/fonts/arial.ttf'
        ];

        foreach ($fonts as $font) {
            if (file_exists($font)) {
                return $font;
            }
        }

        // Return path to default font (will need to be handled)
        return $fonts[0];
    }

    public static function applyBrandColors($imagePath, $brandKit) {
        // In production, this would apply brand colors to the generated image
        // For simulation, we return the path as-is
        return $imagePath;
    }

    public static function resize($imagePath, $newWidth, $newHeight) {
        // Image resizing logic
        if (!file_exists($imagePath)) {
            return false;
        }

        $imageInfo = getimagesize($imagePath);
        $mime = $imageInfo['mime'];

        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($imagePath);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($imagePath);
                break;
            default:
                return false;
        }

        $resized = imagescale($image, $newWidth, $newHeight);
        imagejpeg($resized, $imagePath, 85);
        imagedestroy($image);
        imagedestroy($resized);

        return true;
    }
}
