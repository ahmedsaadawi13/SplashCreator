<!-- FILE: /app/views/errors/403.php -->
<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="card" style="text-align: center; padding: 60px 20px;">
        <h1 style="font-size: 72px; color: #f56565;">403</h1>
        <h2>Access Denied</h2>
        <p><?php echo isset($message) ? View::e($message) : 'You do not have permission to access this resource.'; ?></p>
        <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-primary" style="margin-top: 20px;">Go to Dashboard</a>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
