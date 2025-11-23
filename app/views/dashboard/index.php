<!-- FILE: /app/views/dashboard/index.php -->
<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <h1>Dashboard</h1>
    <p>Welcome, <?php echo View::e($user['full_name']); ?>!</p>

    <?php if ($usage_stats): ?>
    <h2 style="margin-top: 30px;">Usage Statistics - <?php echo View::e($usage_stats['plan_name']); ?> Plan</h2>
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Text Generations</h3>
            <div class="stat-value"><?php echo $usage_stats['text_generations']['used']; ?> / <?php echo $usage_stats['text_generations']['limit']; ?></div>
            <div class="progress">
                <div class="progress-bar" style="width: <?php echo $usage_stats['text_generations']['percentage']; ?>%;"></div>
            </div>
        </div>

        <div class="stat-card">
            <h3>Image Generations</h3>
            <div class="stat-value"><?php echo $usage_stats['image_generations']['used']; ?> / <?php echo $usage_stats['image_generations']['limit']; ?></div>
            <div class="progress">
                <div class="progress-bar" style="width: <?php echo $usage_stats['image_generations']['percentage']; ?>%;"></div>
            </div>
        </div>

        <div class="stat-card">
            <h3>Video Generations</h3>
            <div class="stat-value"><?php echo $usage_stats['video_generations']['used']; ?> / <?php echo $usage_stats['video_generations']['limit']; ?></div>
            <div class="progress">
                <div class="progress-bar" style="width: <?php echo $usage_stats['video_generations']['percentage']; ?>%;"></div>
            </div>
        </div>

        <div class="stat-card">
            <h3>Scheduled Posts</h3>
            <div class="stat-value"><?php echo $usage_stats['scheduled_posts']['used']; ?> / <?php echo $usage_stats['scheduled_posts']['limit']; ?></div>
            <div class="progress">
                <div class="progress-bar" style="width: <?php echo $usage_stats['scheduled_posts']['percentage']; ?>%;"></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($analytics): ?>
    <h2 style="margin-top: 30px;">Analytics Overview</h2>
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Impressions</h3>
            <div class="stat-value"><?php echo number_format($analytics['total_impressions'] ?? 0); ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Likes</h3>
            <div class="stat-value"><?php echo number_format($analytics['total_likes'] ?? 0); ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Comments</h3>
            <div class="stat-value"><?php echo number_format($analytics['total_comments'] ?? 0); ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Shares</h3>
            <div class="stat-value"><?php echo number_format($analytics['total_shares'] ?? 0); ?></div>
        </div>
    </div>
    <?php endif; ?>

    <div class="grid-2" style="margin-top: 30px;">
        <div class="card">
            <h3>Recent Content</h3>
            <?php if (!empty($recent_content)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_content as $content): ?>
                    <tr>
                        <td><a href="<?php echo BASE_URL; ?>/content/view/<?php echo $content['id']; ?>"><?php echo View::e($content['title']); ?></a></td>
                        <td><?php echo View::e(ucfirst($content['type'])); ?></td>
                        <td><?php echo date('M d, Y', strtotime($content['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>No content yet. <a href="<?php echo BASE_URL; ?>/content/create">Create your first content</a></p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>Upcoming Posts</h3>
            <?php if (!empty($upcoming_posts)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Content</th>
                        <th>Platform</th>
                        <th>Scheduled</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($upcoming_posts as $post): ?>
                    <tr>
                        <td><?php echo View::e($post['title']); ?></td>
                        <td><?php echo View::e(ucfirst($post['platform'])); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($post['scheduled_time_utc'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>No upcoming posts. <a href="<?php echo BASE_URL; ?>/schedule/create">Schedule a post</a></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
