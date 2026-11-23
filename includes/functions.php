<?php

function escape($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}


function requireLogin(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}


function processAvatarUpload(
    array $file,
    ?string $oldAvatarPath = null
): array {
    $maxSize = 2097152;

    if (
        !isset($file['error'])
        || $file['error'] !== UPLOAD_ERR_OK
    ) {
        return [
            'success' => false,
            'path' => null,
            'error' => 'Please select a valid image.'
        ];
    }

    if (($file['size'] ?? 0) > $maxSize) {
        return [
            'success' => false,
            'path' => null,
            'error' => 'Image must be smaller than 2 MB.'
        ];
    }

    $tmpName = $file['tmp_name'] ?? '';

    if (
        $tmpName === ''
        || !is_uploaded_file($tmpName)
    ) {
        return [
            'success' => false,
            'path' => null,
            'error' => 'Invalid uploaded file.'
        ];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);

    if (!$finfo) {
        return [
            'success' => false,
            'path' => null,
            'error' => 'Unable to inspect the image.'
        ];
    }

    $mimeType = finfo_file(
        $finfo,
        $tmpName
    );

    finfo_close($finfo);

    if (
        !in_array(
            $mimeType,
            ['image/jpeg', 'image/png'],
            true
        )
    ) {
        return [
            'success' => false,
            'path' => null,
            'error' => 'Only JPEG and PNG images are allowed.'
        ];
    }

    if ($mimeType === 'image/jpeg') {
        $sourceImage =
            imagecreatefromjpeg($tmpName);
    } else {
        $sourceImage =
            imagecreatefrompng($tmpName);
    }

    if (!$sourceImage) {
        return [
            'success' => false,
            'path' => null,
            'error' => 'Could not process the image.'
        ];
    }

    $sourceWidth = imagesx($sourceImage);
    $sourceHeight = imagesy($sourceImage);

    $thumbnail =
        imagecreatetruecolor(150, 150);

    if (!$thumbnail) {
        imagedestroy($sourceImage);

        return [
            'success' => false,
            'path' => null,
            'error' => 'Could not create thumbnail.'
        ];
    }

    // White background for PNG transparency
    $white = imagecolorallocate(
        $thumbnail,
        255,
        255,
        255
    );

    imagefill(
        $thumbnail,
        0,
        0,
        $white
    );

    $cropSize = min(
        $sourceWidth,
        $sourceHeight
    );

    $sourceX = (int) (
        ($sourceWidth - $cropSize) / 2
    );

    $sourceY = (int) (
        ($sourceHeight - $cropSize) / 2
    );

    imagecopyresampled(
        $thumbnail,
        $sourceImage,
        0,
        0,
        $sourceX,
        $sourceY,
        150,
        150,
        $cropSize,
        $cropSize
    );

    $filename =
        bin2hex(random_bytes(16))
        . '.jpg';

    $projectRoot = dirname(__DIR__);

    $uploadDirectory =
        $projectRoot . '/uploads/';

    if (
        !is_dir($uploadDirectory)
        || !is_writable($uploadDirectory)
    ) {
        imagedestroy($sourceImage);
        imagedestroy($thumbnail);

        return [
            'success' => false,
            'path' => null,
            'error' => 'Upload directory is not writable.'
        ];
    }

    $fullPath =
        $uploadDirectory . $filename;

    $saved = imagejpeg(
        $thumbnail,
        $fullPath,
        90
    );

    imagedestroy($sourceImage);
    imagedestroy($thumbnail);

    if (!$saved) {
        return [
            'success' => false,
            'path' => null,
            'error' => 'Could not save thumbnail.'
        ];
    }

    // Delete previous uploaded avatar
    if (
        !empty($oldAvatarPath)
        && str_starts_with(
            $oldAvatarPath,
            'uploads/'
        )
    ) {
        $oldFile =
            $projectRoot
            . '/'
            . $oldAvatarPath;

        $uploadRealPath =
            realpath($uploadDirectory);

        $oldRealPath =
            realpath($oldFile);

        if (
            $uploadRealPath !== false
            && $oldRealPath !== false
            && str_starts_with(
                $oldRealPath,
                $uploadRealPath
            )
            && is_file($oldFile)
        ) {
            unlink($oldFile);
        }
    }

    return [
        'success' => true,
        'path' => 'uploads/' . $filename,
        'error' => ''
    ];
}