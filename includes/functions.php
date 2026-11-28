<?php

// Escape database and form values before displaying them in HTML.
function escape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Redirect visitors who have not logged in.
function requireLogin(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Extra security: each POST form includes a token stored in the session.
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Invalid security token.');
    }
}

// Normal form fields must contain strings, not arrays.
function validatePostFields(): void
{
    foreach ($_POST as $value) {
        if (!is_string($value)) {
            http_response_code(400);
            exit('Invalid form data.');
        }
    }
}

// Compare the parsed date with the input to reject impossible dates.
function isValidDate(string $date): bool
{
    if (!preg_match('/^[1-9][0-9]{3}-[0-9]{2}-[0-9]{2}$/D', $date)) {
        return false;
    }
    $parsed = DateTime::createFromFormat('!Y-m-d', $date);
    return $parsed !== false && $parsed->format('Y-m-d') === $date;
}

// Project links are optional, but must point to GitHub over HTTPS.
function isValidGithubUrl(string $url): bool
{
    if (mb_strlen($url) > 255 || !filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    $parts = parse_url($url);
    return is_array($parts) && strtolower($parts['scheme'] ?? '') === 'https' &&
        strtolower($parts['host'] ?? '') === 'github.com' &&
        !isset($parts['user']) && !isset($parts['pass']);
}

// Return a consistent result when an image cannot be accepted.
function uploadError(string $message): array
{
    return ['success' => false, 'path' => null, 'error' => $message];
}

// Validate the uploaded file, then create and save a real GD thumbnail.
function processAvatarUpload(array $file): array
{
    if (($file['error'] ?? null) !== UPLOAD_ERR_OK) {
        return uploadError('Please select a valid image.');
    }
    if (!isset($file['size']) || !is_int($file['size']) ||
        $file['size'] <= 0 || $file['size'] > 2097152) {
        return uploadError('Image must be no larger than 2 MB.');
    }

    $tmpName = $file['tmp_name'] ?? '';
    if (!is_string($tmpName) || !is_uploaded_file($tmpName)) {
        return uploadError('Invalid uploaded file.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if (!$finfo) {
        return uploadError('Unable to inspect the image.');
    }
    $mime = finfo_file($finfo, $tmpName);
    finfo_close($finfo);
    if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {
        return uploadError('Only JPEG and PNG images are allowed.');
    }

    // Limit image dimensions before decoding to reduce memory use.
    $dimensions = @getimagesize($tmpName);
    if (!$dimensions || $dimensions[0] > 4096 || $dimensions[1] > 4096) {
        return uploadError('Invalid image or image exceeds 4096 pixels.');
    }

    $uploadDirectory = dirname(__DIR__) . '/uploads/';
    if (!is_dir($uploadDirectory) || !is_writable($uploadDirectory)) {
        return uploadError('Upload directory is not writable.');
    }

    if ($mime === 'image/jpeg') {
        $source = @imagecreatefromjpeg($tmpName);
    } else {
        $source = @imagecreatefrompng($tmpName);
    }
    if (!$source) {
        return uploadError('Could not process the image.');
    }

    $thumbnail = imagecreatetruecolor(150, 150);
    if (!$thumbnail) {
        imagedestroy($source);
        return uploadError('Could not create thumbnail.');
    }

    // Crop the central square to avoid stretching the photo.
    $width = imagesx($source);
    $height = imagesy($source);
    $crop = min($width, $height);
    $x = (int) (($width - $crop) / 2);
    $y = (int) (($height - $crop) / 2);
    $white = imagecolorallocate($thumbnail, 255, 255, 255);
    imagefill($thumbnail, 0, 0, $white);
    $resized = imagecopyresampled($thumbnail, $source, 0, 0, $x, $y, 150, 150, $crop, $crop);

    // Never use the original filename supplied by the browser.
    $filename = bin2hex(random_bytes(16)) . '.jpg';
    $path = $uploadDirectory . $filename;
    $saved = $resized && @imagejpeg($thumbnail, $path, 90);
    imagedestroy($source);
    imagedestroy($thumbnail);

    if (!$saved || !is_file($path) || filesize($path) === 0) {
        deleteAvatar('uploads/' . $filename);
        return uploadError('Could not save thumbnail.');
    }
    return ['success' => true, 'path' => 'uploads/' . $filename, 'error' => ''];
}

// Only delete files matching the generated avatar naming pattern.
function deleteAvatar(?string $path): void
{
    if ($path === null || !preg_match('~^uploads/[a-f0-9]{32}\.jpg$~D', $path)) {
        return;
    }
    $file = dirname(__DIR__) . '/' . $path;
    if (is_file($file) && !is_link($file)) {
        @unlink($file);
    }
}
