<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
require_once __DIR__ . '/../config/database.php';

$userId = (int) $_SESSION['user_id'];
$success = '';
$error = '';

$stmt = $pdo->prepare('SELECT id, full_name, headline, summary, email, avatar_path FROM users WHERE id = :id');
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();
if (!$user) {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}

// The two forms are handled on the same dashboard.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    validatePostFields();

    if (isset($_POST['update_profile'])) {
        $fullName = trim($_POST['full_name'] ?? '');
        $headline = trim($_POST['headline'] ?? '');
        $summary = trim($_POST['summary'] ?? '');
        $email = trim($_POST['email'] ?? '');

        // Retain submitted values when validation fails (sticky form).
        $user['full_name'] = $fullName;
        $user['headline'] = $headline;
        $user['summary'] = $summary;
        $user['email'] = $email;

        if ($fullName === '' || mb_strlen($fullName) > 100) {
            $error = 'Enter a full name of at most 100 characters.';
        } elseif (mb_strlen($headline) > 150) {
            $error = 'Headline cannot exceed 150 characters.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 120) {
            $error = 'Enter a valid email address of at most 120 characters.';
        } elseif (strlen($summary) > 65535) {
            $error = 'Summary is too long.';
        } else {
            try {
                $stmt = $pdo->prepare('UPDATE users SET full_name = :full_name, headline = :headline,
                    summary = :summary, email = :email WHERE id = :id');
                $stmt->execute([
                    'full_name' => $fullName, 'headline' => $headline,
                    'summary' => $summary, 'email' => $email, 'id' => $userId
                ]);
                $_SESSION['full_name'] = $fullName;
                $_SESSION['success'] = 'Profile updated successfully.';
                header('Location: admin.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Unable to update profile. The email may already be in use.';
            }
        }
    } elseif (isset($_POST['upload_avatar'])) {
        $result = processAvatarUpload($_FILES['avatar'] ?? []);
        if (!$result['success']) {
            $error = $result['error'];
        } else {
            // Keep the old image until the new path is saved successfully.
            try {
                $stmt = $pdo->prepare('UPDATE users SET avatar_path = :avatar_path WHERE id = :id');
                $stmt->execute(['avatar_path' => $result['path'], 'id' => $userId]);
                deleteAvatar($user['avatar_path']);
                $_SESSION['success'] = 'Profile image updated successfully.';
                header('Location: admin.php');
                exit;
            } catch (PDOException $e) {
                deleteAvatar($result['path']);
                $error = 'Unable to update profile image.';
            }
        }
    }
}

// Display a success message once, after a redirect.
$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

$stmt = $pdo->prepare('SELECT * FROM resume_entries WHERE user_id = :user_id ORDER BY start_date DESC');
$stmt->execute(['user_id' => $userId]);
$entries = $stmt->fetchAll();

// Use one table template instead of three nearly identical tables.
$groups = ['experience' => [], 'education' => [], 'project' => []];
foreach ($entries as $entry) {
    $groups[$entry['entry_type']][] = $entry;
}
$sections = ['experience' => 'Experience', 'education' => 'Education', 'project' => 'Projects'];
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<main>
    <h1>Admin Dashboard</h1>
    <p>Welcome, <?= escape($_SESSION['full_name'] ?? 'Admin') ?>.</p>
    <?php renderNavigation(); ?>
    <?php if ($success !== ''): ?>
        <div class="message success"><?= escape($success) ?></div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <div class="message error"><?= escape($error) ?></div>
    <?php endif; ?>

    <section>
        <h2>Profile Information</h2>
        <form method="post" action="admin.php" class="cms-form">
            <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
            <div>
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?= escape($user['full_name']) ?>" maxlength="100" required>
            </div>
            <div>
                <label for="headline">Headline</label>
                <input type="text" id="headline" name="headline" value="<?= escape($user['headline']) ?>" maxlength="150">
            </div>
            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= escape($user['email']) ?>" maxlength="120" required>
            </div>
            <div>
                <label for="summary">Career Summary</label>
                <textarea id="summary" name="summary" rows="6"><?= escape($user['summary']) ?></textarea>
            </div>
            <button type="submit" name="update_profile">Update Profile</button>
        </form>
    </section>

    <section>
        <h2>Profile Image</h2>
        <?php if (!empty($user['avatar_path']) && is_file(dirname(__DIR__) . '/' . $user['avatar_path'])): ?>
            <img src="../<?= escape($user['avatar_path']) ?>" alt="Current profile image" class="admin-avatar-preview">
        <?php endif; ?>
        <form method="post" action="admin.php" enctype="multipart/form-data" class="cms-form">
            <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
            <div>
                <label for="avatar">Upload JPEG or PNG (maximum 2 MB, 4096 × 4096)</label>
                <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png" required>
            </div>
            <button type="submit" name="upload_avatar">Upload Avatar</button>
        </form>
    </section>

    <section>
        <h2>Resume Entries</h2>
        <div class="admin-actions"><a href="entry_form.php">Add New Entry</a></div>
        <?php foreach ($sections as $type => $heading): ?>
            <h3 class="admin-section-title"><?= escape($heading) ?></h3>
            <?php if ($groups[$type]): ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th><th>Organization</th><th>Start Date</th>
                                <th><?= $type === 'project' ? 'GitHub' : 'End Date' ?></th><th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($groups[$type] as $entry): ?>
                            <tr>
                                <td><?= escape($entry['title']) ?></td>
                                <td><?= escape($entry['organization']) ?></td>
                                <td><?= escape($entry['start_date']) ?></td>
                                <td>
                                    <?php if ($type === 'project'): ?>
                                        <?php if (!empty($entry['github_url']) && isValidGithubUrl($entry['github_url'])): ?>
                                            <a href="<?= escape($entry['github_url']) ?>" target="_blank" rel="noopener noreferrer">GitHub</a>
                                        <?php else: ?>—<?php endif; ?>
                                    <?php else: ?>
                                        <?= escape($entry['end_date'] ?? 'Present') ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="entry_form.php?id=<?= (int) $entry['id'] ?>">Edit</a>
                                    <form method="post" action="delete_entry.php" class="inline-form" onsubmit="return confirm('Delete this entry?');">
                                        <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $entry['id'] ?>">
                                        <button type="submit" class="delete-button">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="empty-state">No <?= escape(strtolower($heading)) ?> entries available.</p>
            <?php endif; ?>
        <?php endforeach; ?>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
