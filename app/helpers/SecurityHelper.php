<?php
// FILE: /app/helpers/SecurityHelper.php

class SecurityHelper {

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public static function generateApiKey() {
        return hash('sha256', uniqid('api_', true) . random_bytes(32));
    }

    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }

    public static function sanitizeFilename($filename) {
        // Remove any path info
        $filename = basename($filename);

        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        return $filename;
    }

    public static function generateUniqueFilename($originalFilename, $prefix = '') {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $basename = pathinfo($originalFilename, PATHINFO_FILENAME);
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '', $basename);

        $unique = $prefix . time() . '_' . uniqid() . '_' . substr($basename, 0, 20);

        return $extension ? $unique . '.' . $extension : $unique;
    }

    public static function rateLimit($key, $maxAttempts = 10, $decayMinutes = 1) {
        if (!isset($_SESSION['rate_limit'])) {
            $_SESSION['rate_limit'] = [];
        }

        $now = time();
        $decaySeconds = $decayMinutes * 60;

        if (!isset($_SESSION['rate_limit'][$key])) {
            $_SESSION['rate_limit'][$key] = [
                'attempts' => 1,
                'reset_at' => $now + $decaySeconds
            ];
            return true;
        }

        $limit = &$_SESSION['rate_limit'][$key];

        // Reset if time has passed
        if ($now >= $limit['reset_at']) {
            $limit['attempts'] = 1;
            $limit['reset_at'] = $now + $decaySeconds;
            return true;
        }

        // Check if limit exceeded
        if ($limit['attempts'] >= $maxAttempts) {
            return false;
        }

        $limit['attempts']++;
        return true;
    }

    public static function getClientIp() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];

        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER)) {
                $ip = $_SERVER[$key];
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    public static function getUserAgent() {
        return isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : '';
    }

    public static function escapeOutput($value) {
        if (is_array($value)) {
            return array_map([self::class, 'escapeOutput'], $value);
        }
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public static function preventClickjacking() {
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
    }
}
