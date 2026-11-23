<?php

session_start();

require_once __DIR__
    . '/../includes/functions.php';

require_once __DIR__
    . '/../config/database.php';

requireLogin();


$message = '';
$error = '';


// Get logged-in profile
$stmt = $pdo->prepare(
    "SELECT *
     FROM users
     WHERE id = :id
     LIMIT 1"
);

$stmt->execute([
    'id' => $_SESSION['user_id']
]);

$user = $stmt->fetch();


if (!$user) {
    die('User profile not found.');
}


// Update profile
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['update_profile'])
) {

    $fullName =
        trim(
            $_POST['full_name'] ?? ''
        );

    $headline =
        trim(
            $_POST['headline'] ?? ''
        );

    $summary =
        trim(
            $_POST['summary'] ?? ''
        );

    $email =
        trim(
            $_POST['email'] ?? ''
        );


    if ($fullName === '') {

        $error =
            'Full name is required.';

    } elseif ($headline === '') {

        $error =
            'Headline is required.';

    } elseif ($summary === '') {

        $error =
            'Summary is required.';

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            'Please enter a valid email address.';

    } else {

        $stmt = $pdo->prepare(
            "UPDATE users
             SET
                 full_name = :full_name,
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
            'id' => $_SESSION[
                'user_id'
            ]
        ]);


        $user['full_name'] =
            $fullName;

        $user['headline'] =
            $headline;

        $user['summary'] =
            $summary;

        $user['email'] =
            $email;

        $_SESSION['full_name'] =
            $fullName;


        $message =
            'Profile updated successfully!';
    }
}


// Upload avatar
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['upload_avatar'])
) {

    $result =
        processAvatarUpload(
            $_FILES['avatar'] ?? [],
            $user['avatar_path']
                ?? null
        );


    if ($result['success']) {

        $stmt = $pdo->prepare(
            "UPDATE users
             SET avatar_path =
                 :avatar_path
             WHERE id = :id"
        );

        $stmt->execute([
            'avatar_path' =>
                $result['path'],

            'id' =>
                $_SESSION[
                    'user_id'
                ]
        ]);

        $user['avatar_path'] =
            $result['path'];

        $message =
            'Profile image updated successfully!';

    } else {

        $error =
            $result['error'];
    }
}


// Resume entries
$stmt = $pdo->prepare(
    "SELECT *
     FROM resume_entries
     WHERE user_id = :user_id
     ORDER BY start_date DESC"
);

$stmt->execute([
    'user_id' =>
        $_SESSION['user_id']
]);

$entries =
    $stmt->fetchAll();


$pageTitle = 'Admin Dashboard';

require_once __DIR__
    . '/../includes/header.php';

?>


<main>


<section>

    <h2 class="sec-title">
        Admin Dashboard
    </h2>

    <p>
        Welcome,
        <?= escape(
            $_SESSION[
                'full_name'
            ] ?? 'Admin'
        ) ?>
    </p>

    <div class="admin-actions">

        <a href="entry_form.php">
            Add New Entry
        </a>

        <a href="index.php">
            View Public Resume
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>

</section>


<?php if ($message !== ''): ?>

    <p class="message success">
        <?= escape($message) ?>
    </p>

<?php endif; ?>


<?php if ($error !== ''): ?>

    <p class="message error">
        <?= escape($error) ?>
    </p>

<?php endif; ?>


<section>

<h2 class="sec-title">
    Edit Profile
</h2>


<form
    method="POST"
    class="cms-form"
>

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


<?php if (
    !empty(
        $user['avatar_path']
    )
): ?>

<img
    class="admin-avatar-preview"
    src="../<?= escape(
        $user['avatar_path']
    ) ?>"
    alt="Profile photo"
>

<?php endif; ?>


<form
    method="POST"
    enctype="multipart/form-data"
    class="cms-form"
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


<div class="table-wrap">

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

<?php
foreach ($entries as $entry):
?>

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
        $entry[
            'organization'
        ]
    ) ?>
</td>

<td>

<a
    href="entry_form.php?id=<?= (int)
        $entry['id']
    ?>"
>
    Edit
</a>


<form
    method="POST"
    action="delete_entry.php"
    class="inline-form"
    onsubmit="return confirm(
        'Delete this entry?'
    );"
>

<input
    type="hidden"
    name="id"
    value="<?= (int)
        $entry['id']
    ?>"
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


<?php endif; ?>

</section>


</main>


<?php

require_once __DIR__
    . '/../includes/footer.php';

?>