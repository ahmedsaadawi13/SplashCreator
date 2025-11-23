<!-- FILE: /app/views/auth/login.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SplashCreator</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Login to SplashCreator</h1>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo View::e($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo BASE_URL; ?>/auth/login">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
                </div>

                <p style="text-align: center; margin-top: 20px;">
                    Don't have an account? <a href="<?php echo BASE_URL; ?>/auth/register">Register</a>
                </p>

                <p style="text-align: center; margin-top: 20px; font-size: 12px; color: #999;">
                    Demo credentials:<br>
                    Email: admin@demo.com | creator@demo.com<br>
                    Password: password
                </p>
            </form>
        </div>
    </div>
</body>
</html>
