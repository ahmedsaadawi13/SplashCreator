<!-- FILE: /app/views/auth/register.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SplashCreator</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Create Account</h1>

            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo View::e($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo BASE_URL; ?>/auth/register">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="form-group">
                    <label for="company_name">Company Name</label>
                    <input type="text" name="company_name" id="company_name" class="form-control"
                           value="<?php echo isset($old['company_name']) ? View::e($old['company_name']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="subdomain">Subdomain</label>
                    <input type="text" name="subdomain" id="subdomain" class="form-control"
                           value="<?php echo isset($old['subdomain']) ? View::e($old['subdomain']) : ''; ?>"
                           pattern="[a-z0-9-]+" required>
                    <small>Only lowercase letters, numbers, and hyphens</small>
                </div>

                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" name="full_name" id="full_name" class="form-control"
                           value="<?php echo isset($old['full_name']) ? View::e($old['full_name']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control"
                           value="<?php echo isset($old['email']) ? View::e($old['email']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required minlength="8">
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirm Password</label>
                    <input type="password" name="password_confirm" id="password_confirm" class="form-control" required>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
                </div>

                <p style="text-align: center; margin-top: 20px;">
                    Already have an account? <a href="<?php echo BASE_URL; ?>/auth/login">Login</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>
