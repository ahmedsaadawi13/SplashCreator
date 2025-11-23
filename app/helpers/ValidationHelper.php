<?php
// FILE: /app/helpers/ValidationHelper.php

class ValidationHelper {

    public static function email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    public static function required($value) {
        if (is_string($value)) {
            return trim($value) !== '';
        }
        return !empty($value);
    }

    public static function minLength($value, $min) {
        return strlen($value) >= $min;
    }

    public static function maxLength($value, $max) {
        return strlen($value) <= $max;
    }

    public static function inEnum($value, $allowed) {
        return in_array($value, $allowed, true);
    }

    public static function numeric($value) {
        return is_numeric($value);
    }

    public static function integer($value) {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    public static function float($value) {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }

    public static function date($date, $format = 'Y-m-d H:i:s') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public static function sanitizeString($value) {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    public static function sanitizeEmail($email) {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }

    public static function sanitizeUrl($url) {
        return filter_var(trim($url), FILTER_SANITIZE_URL);
    }

    public static function validateFileUpload($file, $allowedTypes = [], $maxSize = 10485760) {
        $errors = [];

        if (!isset($file['error']) || is_array($file['error'])) {
            $errors[] = 'Invalid file parameters';
            return $errors;
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = 'File size exceeds limit';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errors[] = 'No file was uploaded';
                break;
            default:
                $errors[] = 'Unknown upload error';
                break;
        }

        if ($file['size'] > $maxSize) {
            $errors[] = 'File size exceeds maximum allowed size';
        }

        if (!empty($allowedTypes)) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);

            if (!in_array($mimeType, $allowedTypes)) {
                $errors[] = 'Invalid file type';
            }
        }

        return $errors;
    }
}
