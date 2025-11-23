<?php
// FILE: /app/helpers/AIVideoHelper.php

class AIVideoHelper {

    /**
     * Simulate AI video generation
     * In production, this would call video generation APIs like Synthesia, D-ID, etc.
     */
    public static function generate($scriptText, $options = []) {
        $resolution = $options['resolution'] ?? '1920x1080';
        $voiceType = $options['voice'] ?? 'professional';
        $tenantId = $options['tenant_id'] ?? 1;

        // Calculate estimated duration (rough estimate based on words)
        $wordCount = str_word_count($scriptText);
        $duration = (int)($wordCount / 2.5); // Assume 150 words per minute

        // Create tenant directory if not exists
        $uploadDir = __DIR__ . '/../../storage/uploads/' . $tenantId . '/generated';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $filename = 'ai_video_' . time() . '_' . uniqid() . '.mp4';
        $filepath = $uploadDir . '/' . $filename;
        $relativePath = 'storage/uploads/' . $tenantId . '/generated/' . $filename;

        // Simulate video generation by creating a placeholder video file
        self::createPlaceholderVideo($filepath, $scriptText, $resolution);

        return [
            'success' => true,
            'video_path' => $relativePath,
            'duration_seconds' => $duration,
            'resolution' => $resolution,
            'script' => $scriptText
        ];
    }

    private static function createPlaceholderVideo($filepath, $scriptText, $resolution) {
        // Create a simple text file placeholder
        // In production, this would generate an actual video file
        // For now, we create a metadata file

        $metadata = [
            'type' => 'AI Generated Video',
            'script' => $scriptText,
            'resolution' => $resolution,
            'generated_at' => date('Y-m-d H:i:s'),
            'status' => 'simulated',
            'note' => 'This is a simulated video generation. In production, this would be an actual MP4 file.'
        ];

        // Create a dummy file to represent the video
        file_put_contents($filepath, json_encode($metadata, JSON_PRETTY_PRINT));

        // Also create a companion text file with the script
        $scriptFile = str_replace('.mp4', '_script.txt', $filepath);
        file_put_contents($scriptFile, $scriptText);
    }

    public static function addSubtitles($videoPath, $scriptText) {
        // In production, this would generate and add SRT subtitles to the video
        // For simulation, we create a subtitle file

        $srtPath = str_replace('.mp4', '.srt', $videoPath);

        $lines = explode("\n", $scriptText);
        $srtContent = '';
        $index = 1;
        $startTime = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $duration = max(3, strlen($line) / 15); // Rough estimate
            $endTime = $startTime + $duration;

            $srtContent .= $index . "\n";
            $srtContent .= self::formatSrtTime($startTime) . ' --> ' . self::formatSrtTime($endTime) . "\n";
            $srtContent .= $line . "\n\n";

            $startTime = $endTime;
            $index++;
        }

        file_put_contents($srtPath, $srtContent);

        return [
            'success' => true,
            'subtitle_path' => $srtPath
        ];
    }

    private static function formatSrtTime($seconds) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        $millis = ($secs - floor($secs)) * 1000;

        return sprintf('%02d:%02d:%02d,%03d', $hours, $minutes, floor($secs), $millis);
    }

    public static function generateThumbnail($videoPath, $timePosition = 0) {
        // In production, this would extract a frame from the video
        // For simulation, we create a placeholder thumbnail

        $thumbnailPath = str_replace('.mp4', '_thumb.jpg', $videoPath);

        // Create a simple thumbnail image
        $image = imagecreatetruecolor(1280, 720);
        $bg = imagecolorallocate($image, 41, 128, 185);
        imagefill($image, 0, 0, $bg);

        $white = imagecolorallocate($image, 255, 255, 255);
        $text = "Video Thumbnail";

        // Try to add text if font available
        $font = self::findFont();
        if ($font) {
            imagettftext($image, 48, 0, 400, 360, $white, $font, $text);
        }

        imagejpeg($image, $thumbnailPath, 85);
        imagedestroy($image);

        return [
            'success' => true,
            'thumbnail_path' => $thumbnailPath
        ];
    }

    private static function findFont() {
        $fonts = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf'
        ];

        foreach ($fonts as $font) {
            if (file_exists($font)) {
                return $font;
            }
        }

        return null;
    }

    public static function estimateDuration($scriptText) {
        $wordCount = str_word_count($scriptText);
        return (int)($wordCount / 2.5); // 150 words per minute
    }
}
