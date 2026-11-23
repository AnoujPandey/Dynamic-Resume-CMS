<?php

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$message = '';
$error = '';

// Get logged-in user profile
$stmt = $pdo->prepare(
    "SELECT *
     FROM users
     WHERE id = :id"
);

$stmt->execute([
    'id' => $_SESSION['user_id']
]);

$user = $stmt->fetch();

if (!$user) {
    die('User profile not found.');
}

// Handle profile update
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['update_profile'])
) {
    $fullName = trim($_POST['full_name'] ?? '');
    $headline = trim($_POST['headline'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($fullName === '') {
        $error = 'Full name is required.';
    } elseif ($headline === '') {
        $error = 'Headline is required.';
    } elseif ($summary === '') {
        $error = 'Summary is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare(
            "UPDATE users
             SET full_name = :full_name,
                 headline = :headline,
                 summary = :summary,
                 email = :email
             WHERE id = :id"
        );

        $stmt->execute([
            'full_name' => $fullName,
            'headline' => $headline,
            'summary' => $summary,
            'email' => $email,
            'id' => $_SESSION['user_id']
        ]);

        $user['full_name'] = $fullName;
        $user['headline'] = $headline;
        $user['summary'] = $summary;
        $user['email'] = $email;

        $_SESSION['full_name'] = $fullName;

        $message = 'Profile updated successfully!';
    }
}

// Handle avatar upload
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['upload_avatar'])
    && isset($_FILES['avatar'])
) {
    $file = $_FILES['avatar'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select a valid image.';
    } elseif ($file['size'] > 2097152) {
        $error = 'Image must be smaller than 2 MB.';
    } elseif (!is_uploaded_file($file['tmp_name'])) {
        $error = 'Invalid uploaded file.';
    } else {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $mimeType = finfo_file(
            $finfo,
            $file['tmp_name']
        );

        finfo_close($finfo);

        $allowedTypes = [
            'image/jpeg',
            'image/png'
        ];

        if (!in_array($mimeType, $allowedTypes, true)) {
            $error = 'Only JPEG and PNG images are allowed.';
        } else {
            if ($mimeType === 'image/jpeg') {
                $sourceImage = imagecreatefromjpeg(
                    $file['tmp_name']
                );
            } else {
                $sourceImage = imagecreatefrompng(
                    $file['tmp_name']
                );
            }

            if (!$sourceImage) {
                $error = 'Could not process the image.';
            } else {
                $sourceWidth = imagesx($sourceImage);
                $sourceHeight = imagesy($sourceImage);

                // Create a real 150 x 150 thumbnail
                $thumbnail = imagecreatetruecolor(150, 150);

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

                // Safe random filename
                $filename = bin2hex(
                    random_bytes(16)
                ) . '.jpg';

                $uploadDirectory =
                    __DIR__ . '/../uploads/';

                $fullPath =
                    $uploadDirectory . $filename;

                if (
                    imagejpeg(
                        $thumbnail,
                        $fullPath,
                        90
                    )
                ) {
                    // Remove previous uploaded avatar
                    if (!empty($user['avatar_path'])) {
                        $oldFile =
                            __DIR__
                            . '/../'
                            . $user['avatar_path'];

                        $uploadRealPath =
                            realpath($uploadDirectory);

                        $oldRealPath =
                            realpath($oldFile);

                        if (
                            $oldRealPath !== false
                            && $uploadRealPath !== false
                            && str_starts_with(
                                $oldRealPath,
                                $uploadRealPath
                            )
                            && is_file($oldFile)
                        ) {
                            unlink($oldFile);
                        }
                    }

                    $relativePath =
                        'uploads/' . $filename;

                    $stmt = $pdo->prepare(
                        "UPDATE users
                         SET avatar_path = :avatar_path
                         WHERE id = :id"
                    );

                    $stmt->execute([
                        'avatar_path' => $relativePath,
                        'id' => $_SESSION['user_id']
                    ]);

                    $user['avatar_path'] =
                        $relativePath;

                    $message =
                        'Profile image updated successfully!';
                } else {
                    $error =
                        'Could not save the image.';
                }

                imagedestroy($sourceImage);
                imagedestroy($thumbnail);
            }
        }
    }
}

// Get resume entries
$stmt = $pdo->prepare(
    "SELECT *
     FROM resume_entries
     WHERE user_id = :user_id
     ORDER BY start_date DESC"
);

$stmt->execute([
    'user_id' => $_SESSION['user_id']
]);

$entries = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<main>

    <section>
        <h2 class="sec-title">
            Admin Dashboard
        </h2>

        <p>
            Welcome,
            <?= escape(
                $_SESSION['full_name'] ?? 'Admin'
            ) ?>
        </p>

        <p>
            <a href="entry_form.php">
                Add New Entry
            </a>
            |
            <a href="index.php">
                View Public Resume
            </a>
            |
            <a href="logout.php">
                Logout
            </a>
        </p>
    </section>

    <?php if ($message !== ''): ?>
        <p>
            <?= escape($message) ?>
        </p>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <p>
            <?= escape($error) ?>
        </p>
    <?php endif; ?>

    <section>
        <h2 class="sec-title">
            Edit Profile
        </h2>

        <form method="POST">

            <input
                type="hidden"
                name="update_profile"
                value="1"
            >

            <div>
                <label for="full_name">
                    Full Name
                </label>

                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    value="<?= escape(
                        $user['full_name']
                    ) ?>"
                    required
                >
            </div>

            <div>
                <label for="headline">
                    Headline
                </label>

                <input
                    type="text"
                    id="headline"
                    name="headline"
                    value="<?= escape(
                        $user['headline']
                    ) ?>"
                    required
                >
            </div>

            <div>
                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= escape(
                        $user['email']
                    ) ?>"
                    required
                >
            </div>

            <div>
                <label for="summary">
                    Summary
                </label>

                <textarea
                    id="summary"
                    name="summary"
                    rows="5"
                    required
                ><?= escape(
                    $user['summary']
                ) ?></textarea>
            </div>

            <button type="submit">
                Update Profile
            </button>

        </form>
    </section>

    <section>
        <h2 class="sec-title">
            Profile Image
        </h2>

        <?php if (!empty($user['avatar_path'])): ?>

            <p>
                <img
                    src="../<?= escape(
                        $user['avatar_path']
                    ) ?>"
                    alt="Profile photo"
                    width="150"
                    height="150"
                >
            </p>

        <?php endif; ?>

        <form
            method="POST"
            enctype="multipart/form-data"
        >

            <input
                type="hidden"
                name="upload_avatar"
                value="1"
            >

            <div>
                <label for="avatar">
                    Upload JPEG or PNG
                </label>

                <input
                    type="file"
                    id="avatar"
                    name="avatar"
                    accept="image/jpeg,image/png"
                    required
                >
            </div>

            <button type="submit">
                Upload Profile Image
            </button>

        </form>
    </section>

    <section>
        <h2 class="sec-title">
            Resume Entries
        </h2>

        <?php if (empty($entries)): ?>

            <p>
                No resume entries available.
            </p>

        <?php else: ?>

            <table>

                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Title</th>
                        <th>Organization</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($entries as $entry): ?>

                        <tr>

                            <td>
                                <?= escape(
                                    $entry['entry_type']
                                ) ?>
                            </td>

                            <td>
                                <?= escape(
                                    $entry['title']
                                ) ?>
                            </td>

                            <td>
                                <?= escape(
                                    $entry['organization']
                                ) ?>
                            </td>

                            <td>

                                <a
                                    href="entry_form.php?id=<?= (int) $entry['id'] ?>"
                                >
                                    Edit
                                </a>

                                |

                                <form
                                    method="POST"
                                    action="delete_entry.php"
                                    style="display:inline;"
                                    onsubmit="return confirm('Delete this entry?');"
                                >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int) $entry['id'] ?>"
                                    >

                                    <button type="submit">
                                        Delete
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        <?php endif; ?>

    </section>

</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>