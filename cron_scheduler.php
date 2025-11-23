<?php
// FILE: /cron_scheduler.php
// Run this script via cron every minute: * * * * * php /path/to/splashcreator/cron_scheduler.php

echo "SplashCreator Scheduler Running at " . date('Y-m-d H:i:s') . "\n";

// Load core files
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/helpers/SocialPosterHelper.php';
require_once __DIR__ . '/app/helpers/ActivityHelper.php';
require_once __DIR__ . '/app/helpers/SecurityHelper.php';
require_once __DIR__ . '/config/config.php';

// Initialize database
$db = Database::getInstance();

// Get all pending posts that are due
$sql = "SELECT sp.*, ci.title, ci.content_text, ci.media_path, ci.type,
               sa.platform, sa.username
        FROM scheduled_posts sp
        JOIN content_items ci ON sp.content_item_id = ci.id
        JOIN social_accounts sa ON sp.social_account_id = sa.id
        WHERE sp.status = 'scheduled'
        AND sp.scheduled_time_utc <= NOW()
        ORDER BY sp.scheduled_time_utc ASC
        LIMIT 100";

$pendingPosts = $db->fetchAll($sql);

echo "Found " . count($pendingPosts) . " posts to process\n";

foreach ($pendingPosts as $post) {
    echo "Processing post ID: {$post['id']} - {$post['title']} on {$post['platform']}\n";

    // Prepare content for posting
    $content = [
        'text' => $post['content_text'],
        'media_path' => $post['media_path']
    ];

    try {
        // Attempt to post
        $result = SocialPosterHelper::post(
            $post['platform'],
            $content,
            $post['social_account_id']
        );

        if ($result['status'] === 'success') {
            // Update status to posted
            $updateSql = "UPDATE scheduled_posts
                         SET status = 'posted',
                             log_message = :log_message,
                             posted_at = NOW()
                         WHERE id = :id";

            $db->execute($updateSql, [
                'log_message' => $result['message'],
                'id' => $post['id']
            ]);

            // Generate simulated metrics
            $metricsHelper = [
                'instagram' => ['impressions' => rand(500, 5000), 'likes' => rand(20, 300), 'comments' => rand(5, 50), 'shares' => rand(2, 30), 'clicks' => rand(10, 150)],
                'facebook' => ['impressions' => rand(1000, 10000), 'likes' => rand(50, 500), 'comments' => rand(10, 100), 'shares' => rand(5, 80), 'clicks' => rand(20, 300)],
                'twitter' => ['impressions' => rand(300, 3000), 'likes' => rand(10, 150), 'comments' => rand(3, 40), 'shares' => rand(5, 60), 'clicks' => rand(8, 100)],
                'linkedin' => ['impressions' => rand(800, 8000), 'likes' => rand(30, 400), 'comments' => rand(8, 80), 'shares' => rand(10, 100), 'clicks' => rand(25, 250)],
                'tiktok' => ['impressions' => rand(2000, 50000), 'likes' => rand(100, 2000), 'comments' => rand(20, 300), 'shares' => rand(15, 200), 'clicks' => rand(50, 500)],
                'youtube' => ['impressions' => rand(1500, 20000), 'likes' => rand(50, 800), 'comments' => rand(10, 150), 'shares' => rand(8, 100), 'clicks' => rand(100, 1000)]
            ];

            $metrics = $metricsHelper[$post['platform']] ?? $metricsHelper['instagram'];

            $metricsSql = "INSERT INTO social_metrics (tenant_id, platform, content_item_id, impressions, likes, comments, shares, clicks)
                          VALUES (:tenant_id, :platform, :content_item_id, :impressions, :likes, :comments, :shares, :clicks)";

            $db->execute($metricsSql, [
                'tenant_id' => $post['tenant_id'],
                'platform' => $post['platform'],
                'content_item_id' => $post['content_item_id'],
                'impressions' => $metrics['impressions'],
                'likes' => $metrics['likes'],
                'comments' => $metrics['comments'],
                'shares' => $metrics['shares'],
                'clicks' => $metrics['clicks']
            ]);

            echo "  ✓ Posted successfully\n";
        } else {
            // Update status to failed
            $updateSql = "UPDATE scheduled_posts
                         SET status = 'failed',
                             log_message = :log_message
                         WHERE id = :id";

            $db->execute($updateSql, [
                'log_message' => $result['message'],
                'id' => $post['id']
            ]);

            echo "  ✗ Failed: {$result['message']}\n";
        }

        // Log activity
        $logSql = "INSERT INTO activity_logs (tenant_id, entity_type, entity_id, action, description, ip_address)
                  VALUES (:tenant_id, 'social_post', :entity_id, :action, :description, '0.0.0.0')";

        $db->execute($logSql, [
            'tenant_id' => $post['tenant_id'],
            'entity_id' => $post['id'],
            'action' => $result['status'],
            'description' => "Scheduler: " . $result['message']
        ]);

    } catch (Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";

        // Update status to failed
        $updateSql = "UPDATE scheduled_posts
                     SET status = 'failed',
                         log_message = :log_message
                     WHERE id = :id";

        $db->execute($updateSql, [
            'log_message' => 'Exception: ' . $e->getMessage(),
            'id' => $post['id']
        ]);
    }
}

echo "Scheduler finished at " . date('Y-m-d H:i:s') . "\n";
echo str_repeat('-', 50) . "\n";
