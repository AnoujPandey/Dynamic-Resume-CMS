<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';


// Protect admin page
requireLogin();


$userId = (int) $_SESSION['user_id'];

$success = '';
$error = '';


// Update profile
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['update_profile'])
) {

    $fullName = trim(
        $_POST['full_name'] ?? ''
    );

    $headline = trim(
        $_POST['headline'] ?? ''
    );

    $summary = trim(
        $_POST['summary'] ?? ''
    );

    $email = trim(
        $_POST['email'] ?? ''
    );


    if ($fullName === '') {

        $error =
            'Full name is required.';

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            'Please enter a valid email address.';

    } else {

        try {

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
                'id' => $userId
            ]);


            $_SESSION['full_name'] =
                $fullName;

            $success =
                'Profile updated successfully.';

        } catch (PDOException $e) {

            $error =
                'Unable to update profile.';
        }
    }
}


// Upload avatar
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['upload_avatar'])
) {

    // Get current avatar
    $stmt = $pdo->prepare(
        "SELECT avatar_path
         FROM users
         WHERE id = :id
         LIMIT 1"
    );

    $stmt->execute([
        'id' => $userId
    ]);

    $currentUser =
        $stmt->fetch();


    $oldAvatar =
        $currentUser['avatar_path']
        ?? null;


    if (!isset($_FILES['avatar'])) {

        $error =
            'Please choose an image.';

    } else {

        $result =
            processAvatarUpload(
                $_FILES['avatar'],
                $oldAvatar
            );


        if ($result['success']) {

            $stmt = $pdo->prepare(
                "UPDATE users
                 SET avatar_path = :avatar_path
                 WHERE id = :id"
            );

            $stmt->execute([
                'avatar_path' =>
                    $result['path'],

                'id' =>
                    $userId
            ]);


            $success =
                'Profile image updated successfully.';

        } else {

            $error =
                $result['error'];
        }
    }
}


// Get profile
$stmt = $pdo->prepare(
    "SELECT *
     FROM users
     WHERE id = :id
     LIMIT 1"
);

$stmt->execute([
    'id' => $userId
]);

$user = $stmt->fetch();


// Get resume entries
$stmt = $pdo->prepare(
    "SELECT *
     FROM resume_entries
     WHERE user_id = :user_id
     ORDER BY start_date DESC"
);

$stmt->execute([
    'user_id' => $userId
]);

$entries =
    $stmt->fetchAll();


$pageTitle =
    'Admin Dashboard';

require_once __DIR__ . '/../includes/header.php';

?>


<main>

    <h1>
        Admin Dashboard
    </h1>


    <p>
        Welcome,
        <?= escape(
            $_SESSION['full_name']
            ?? 'Admin'
        ) ?>.
    </p>


    <div class="admin-actions">

        <a href="index.php">
            View Portfolio
        </a>

        <a href="entry_form.php">
            Add Resume Entry
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>


    <?php if ($success !== ''): ?>

        <div class="message success">

            <?= escape($success) ?>

        </div>

    <?php endif; ?>


    <?php if ($error !== ''): ?>

        <div class="message error">

            <?= escape($error) ?>

        </div>

    <?php endif; ?>


    <section>

        <h2>
            Profile Information
        </h2>


        <form
            method="post"
            action="admin.php"
            class="cms-form"
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
                    Career Summary
                </label>

                <textarea
                    id="summary"
                    name="summary"
                    rows="6"
                ><?= escape(
                    $user['summary']
                ) ?></textarea>

            </div>


            <button
                type="submit"
                name="update_profile"
            >
                Update Profile
            </button>

        </form>

    </section>


    <section>

        <h2>
            Profile Image
        </h2>


        <?php if (
            !empty(
                $user['avatar_path']
            )
        ): ?>

            <img
                src="../<?= escape(
                    $user['avatar_path']
                ) ?>"
                alt="Current profile image"
                class="admin-avatar-preview"
            >

        <?php endif; ?>


        <form
            method="post"
            action="admin.php"
            enctype="multipart/form-data"
            class="cms-form"
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


            <button
                type="submit"
                name="upload_avatar"
            >
                Upload Avatar
            </button>

        </form>

    </section>


    <section>

    <h2>
        Resume Entries
    </h2>


    <?php

    $experienceEntries = [];
    $educationEntries = [];
    $projectEntries = [];


    foreach ($entries as $entry) {

        if ($entry['entry_type'] === 'experience') {
            $experienceEntries[] = $entry;
        }

        if ($entry['entry_type'] === 'education') {
            $educationEntries[] = $entry;
        }

        if ($entry['entry_type'] === 'project') {
            $projectEntries[] = $entry;
        }
    }

    ?>


    <div class="admin-actions">

        <a href="entry_form.php">
            Add New Entry
        </a>

    </div>


    <!-- EXPERIENCE -->

    <h3 class="admin-section-title">
        Experience
    </h3>


    <?php if (!empty($experienceEntries)): ?>

        <div class="table-wrap">

            <table>

                <thead>

                    <tr>

                        <th>
                            Job Title
                        </th>

                        <th>
                            Company
                        </th>

                        <th>
                            Start Date
                        </th>

                        <th>
                            End Date
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php foreach ($experienceEntries as $entry): ?>

                    <tr>

                        <td>
                            <?= escape($entry['title']) ?>
                        </td>

                        <td>
                            <?= escape($entry['organization']) ?>
                        </td>

                        <td>
                            <?= escape($entry['start_date']) ?>
                        </td>

                        <td>
                            <?= !empty($entry['end_date'])
                                ? escape($entry['end_date'])
                                : 'Present'
                            ?>
                        </td>

                        <td>

                            <a
                                href="entry_form.php?id=<?= (int) $entry['id'] ?>"
                            >
                                Edit
                            </a>


                            <form
                                method="post"
                                action="delete_entry.php"
                                class="inline-form"
                                onsubmit="return confirm('Are you sure you want to delete this experience?');"
                            >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $entry['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    class="delete-button"
                                >
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>


    <?php else: ?>

        <p class="empty-state">
            No experience entries available.
        </p>

    <?php endif; ?>



    <!-- EDUCATION -->

    <h3 class="admin-section-title">
        Education
    </h3>


    <?php if (!empty($educationEntries)): ?>

        <div class="table-wrap">

            <table>

                <thead>

                    <tr>

                        <th>
                            Degree / Program
                        </th>

                        <th>
                            School / University
                        </th>

                        <th>
                            Start Date
                        </th>

                        <th>
                            End Date
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php foreach ($educationEntries as $entry): ?>

                    <tr>

                        <td>
                            <?= escape($entry['title']) ?>
                        </td>

                        <td>
                            <?= escape($entry['organization']) ?>
                        </td>

                        <td>
                            <?= escape($entry['start_date']) ?>
                        </td>

                        <td>
                            <?= !empty($entry['end_date'])
                                ? escape($entry['end_date'])
                                : 'Present'
                            ?>
                        </td>

                        <td>

                            <a
                                href="entry_form.php?id=<?= (int) $entry['id'] ?>"
                            >
                                Edit
                            </a>


                            <form
                                method="post"
                                action="delete_entry.php"
                                class="inline-form"
                                onsubmit="return confirm('Are you sure you want to delete this education entry?');"
                            >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $entry['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    class="delete-button"
                                >
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>


    <?php else: ?>

        <p class="empty-state">
            No education entries available.
        </p>

    <?php endif; ?>



    <!-- PROJECTS -->

    <h3 class="admin-section-title">
        Projects
    </h3>


    <?php if (!empty($projectEntries)): ?>

        <div class="table-wrap">

            <table>

                <thead>

                    <tr>

                        <th>
                            Project Name
                        </th>

                        <th>
                            Technologies / Institution
                        </th>

                        <th>
                            Start Date
                        </th>

                        <th>
                            GitHub
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php foreach ($projectEntries as $entry): ?>

                    <tr>

                        <td>
                            <?= escape($entry['title']) ?>
                        </td>

                        <td>
                            <?= escape($entry['organization']) ?>
                        </td>

                        <td>
                            <?= escape($entry['start_date']) ?>
                        </td>

                        <td>

                            <?php if (!empty($entry['github_url'])): ?>

                                <a
                                    href="<?= escape($entry['github_url']) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    GitHub
                                </a>

                            <?php else: ?>

                                —

                            <?php endif; ?>

                        </td>

                        <td>

                            <a
                                href="entry_form.php?id=<?= (int) $entry['id'] ?>"
                            >
                                Edit
                            </a>


                            <form
                                method="post"
                                action="delete_entry.php"
                                class="inline-form"
                                onsubmit="return confirm('Are you sure you want to delete this project?');"
                            >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $entry['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    class="delete-button"
                                >
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>


    <?php else: ?>

        <p class="empty-state">
            No project entries available.
        </p>

    <?php endif; ?>

</section>

</main>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>