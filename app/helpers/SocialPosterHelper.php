<?php
// FILE: /app/helpers/SocialPosterHelper.php

class SocialPosterHelper {

    /**
     * Simulate posting to social media platforms
     * In production, this would use actual API integrations
     */
    public static function post($platform, $content, $socialAccountId) {
        $logFile = __DIR__ . '/../../storage/logs/social_post_log.txt';

        // Ensure log directory exists
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $postId = uniqid('post_');

        // Simulate API call based on platform
        $result = self::simulatePlatformPost($platform, $content, $socialAccountId);

        // Log the post
        $logEntry = sprintf(
            "[%s] Platform: %s | Account ID: %s | Post ID: %s | Status: %s | Content: %s\n",
            $timestamp,
            strtoupper($platform),
            $socialAccountId,
            $postId,
            $result['status'],
            substr($content['text'] ?? 'media post', 0, 100)
        );

        file_put_contents($logFile, $logEntry, FILE_APPEND);

        // Simulate metrics generation
        if ($result['status'] === 'success') {
            self::generateSimulatedMetrics($content, $platform);
        }

        return $result;
    }

    private static function simulatePlatformPost($platform, $content, $socialAccountId) {
        // Simulate network delay
        usleep(100000); // 0.1 second

        // Random success (95% success rate)
        $success = (rand(1, 100) <= 95);

        if (!$success) {
            return [
                'status' => 'failed',
                'message' => 'Simulated API error: Rate limit exceeded',
                'platform' => $platform
            ];
        }

        $platformMessages = [
            'instagram' => 'Successfully posted to Instagram',
            'facebook' => 'Successfully posted to Facebook',
            'twitter' => 'Successfully posted to Twitter',
            'tiktok' => 'Successfully posted to TikTok',
            'linkedin' => 'Successfully posted to LinkedIn',
            'youtube' => 'Successfully posted to YouTube'
        ];

        return [
            'status' => 'success',
            'message' => $platformMessages[$platform] ?? 'Successfully posted',
            'platform' => $platform,
            'post_id' => uniqid($platform . '_'),
            'url' => self::generateMockUrl($platform, uniqid())
        ];
    }

    private static function generateMockUrl($platform, $postId) {
        $urls = [
            'instagram' => "https://instagram.com/p/{$postId}",
            'facebook' => "https://facebook.com/posts/{$postId}",
            'twitter' => "https://twitter.com/status/{$postId}",
            'tiktok' => "https://tiktok.com/@user/video/{$postId}",
            'linkedin' => "https://linkedin.com/posts/{$postId}",
            'youtube' => "https://youtube.com/shorts/{$postId}"
        ];

        return $urls[$platform] ?? '#';
    }

    private static function generateSimulatedMetrics($content, $platform) {
        // Generate random but realistic metrics based on platform
        $baseMetrics = self::getPlatformBaseMetrics($platform);

        $metrics = [
            'impressions' => rand($baseMetrics['impressions'][0], $baseMetrics['impressions'][1]),
            'likes' => rand($baseMetrics['likes'][0], $baseMetrics['likes'][1]),
            'comments' => rand($baseMetrics['comments'][0], $baseMetrics['comments'][1]),
            'shares' => rand($baseMetrics['shares'][0], $baseMetrics['shares'][1]),
            'clicks' => rand($baseMetrics['clicks'][0], $baseMetrics['clicks'][1])
        ];

        return $metrics;
    }

    private static function getPlatformBaseMetrics($platform) {
        $metrics = [
            'instagram' => [
                'impressions' => [500, 5000],
                'likes' => [20, 300],
                'comments' => [5, 50],
                'shares' => [2, 30],
                'clicks' => [10, 150]
            ],
            'facebook' => [
                'impressions' => [1000, 10000],
                'likes' => [50, 500],
                'comments' => [10, 100],
                'shares' => [5, 80],
                'clicks' => [20, 300]
            ],
            'twitter' => [
                'impressions' => [300, 3000],
                'likes' => [10, 150],
                'comments' => [3, 40],
                'shares' => [5, 60],
                'clicks' => [8, 100]
            ],
            'linkedin' => [
                'impressions' => [800, 8000],
                'likes' => [30, 400],
                'comments' => [8, 80],
                'shares' => [10, 100],
                'clicks' => [25, 250]
            ],
            'tiktok' => [
                'impressions' => [2000, 50000],
                'likes' => [100, 2000],
                'comments' => [20, 300],
                'shares' => [15, 200],
                'clicks' => [50, 500]
            ],
            'youtube' => [
                'impressions' => [1500, 20000],
                'likes' => [50, 800],
                'comments' => [10, 150],
                'shares' => [8, 100],
                'clicks' => [100, 1000]
            ]
        ];

        return $metrics[$platform] ?? $metrics['instagram'];
    }

    public static function validateContent($platform, $content) {
        $errors = [];

        // Platform-specific validation
        switch ($platform) {
            case 'twitter':
                if (isset($content['text']) && strlen($content['text']) > 280) {
                    $errors[] = 'Tweet exceeds 280 characters';
                }
                break;

            case 'instagram':
                if (isset($content['text']) && strlen($content['text']) > 2200) {
                    $errors[] = 'Instagram caption exceeds 2200 characters';
                }
                if (!isset($content['media_path'])) {
                    $errors[] = 'Instagram posts require an image or video';
                }
                break;

            case 'linkedin':
                if (isset($content['text']) && strlen($content['text']) > 3000) {
                    $errors[] = 'LinkedIn post exceeds 3000 characters';
                }
                break;

            case 'tiktok':
                if (!isset($content['media_path']) || !preg_match('/\.(mp4|mov)$/i', $content['media_path'])) {
                    $errors[] = 'TikTok requires a video file';
                }
                break;
        }

        return $errors;
    }

    public static function schedulePost($scheduledPost) {
        // This method is called by the cron scheduler
        // It processes a scheduled post and attempts to publish it

        try {
            $content = [
                'text' => $scheduledPost['content_text'],
                'media_path' => $scheduledPost['media_path']
            ];

            $result = self::post(
                $scheduledPost['platform'],
                $content,
                $scheduledPost['social_account_id']
            );

            return $result;

        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'message' => $e->getMessage()
            ];
        }
    }

    public static function getSupportedPlatforms() {
        return [
            'instagram' => 'Instagram',
            'facebook' => 'Facebook',
            'twitter' => 'Twitter / X',
            'tiktok' => 'TikTok',
            'linkedin' => 'LinkedIn',
            'youtube' => 'YouTube Shorts'
        ];
    }
}
