<!-- FILE: /app/views/layouts/header.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? View::e($title) . ' - ' : ''; ?>SplashCreator</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
    <header>
        <div class="header-content">
            <a href="<?php echo BASE_URL; ?>/dashboard" class="logo">SplashCreator</a>
            <nav>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/dashboard">Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/ai/text">AI Text</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/ai/image">AI Image</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/ai/video">AI Video</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/content">Content</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/schedule">Schedule</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/social">Social</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/analytics">Analytics</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/auth/logout">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <?php endif; ?>
