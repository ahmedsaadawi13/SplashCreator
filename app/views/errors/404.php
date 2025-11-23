<!-- FILE: /app/views/errors/404.php -->
<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="card" style="text-align: center; padding: 60px 20px;">
        <h1 style="font-size: 72px; color: #667eea;">404</h1>
        <h2>Page Not Found</h2>
        <p><?php echo isset($message) ? View::e($message) : 'The page you are looking for does not exist.'; ?></p>
        <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-primary" style="margin-top: 20px;">Go to Dashboard</a>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
