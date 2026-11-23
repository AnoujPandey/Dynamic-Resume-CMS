<?php

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$entry = [
    'entry_type' => 'experience',
    'title' => '',
    'organization' => '',
    'start_date' => '',
    'end_date' => '',
    'description' => '',
    'github_url' => ''
];

$isEdit = false;
$error = '';

if ($id) {
    $stmt = $pdo->prepare(
        "SELECT *
         FROM resume_entries
         WHERE id = :id
         AND user_id = :user_id"
    );

    $stmt->execute([
        'id' => $id,
        'user_id' => $_SESSION['user_id']
    ]);

    $foundEntry = $stmt->fetch();

    if (!$foundEntry) {
        die('Entry not found.');
    }

    $entry = $foundEntry;
    $isEdit = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $entryType = $_POST['entry_type'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $organization = trim($_POST['organization'] ?? '');
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $githubUrl = trim($_POST['github_url'] ?? '');

    $allowedTypes = ['experience', 'education', 'project'];

    if (!in_array($entryType, $allowedTypes, true)) {
        $error = 'Please select a valid entry type.';
    } elseif ($title === '') {
        $error = 'Title is required.';
    } elseif ($organization === '') {
        $error = 'Organization is required.';
    } elseif ($startDate === '') {
        $error = 'Start date is required.';
    } elseif ($description === '') {
        $error = 'Description is required.';
    } else {

        $endDate = $endDate !== '' ? $endDate : null;
        $githubUrl = $githubUrl !== '' ? $githubUrl : null;

        if ($isEdit) {

            $stmt = $pdo->prepare(
                "UPDATE resume_entries
                 SET entry_type = :entry_type,
                     title = :title,
                     organization = :organization,
                     start_date = :start_date,
                     end_date = :end_date,
                     description = :description,
                     github_url = :github_url
                 WHERE id = :id
                 AND user_id = :user_id"
            );

            $stmt->execute([
                'entry_type' => $entryType,
                'title' => $title,
                'organization' => $organization,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'description' => $description,
                'github_url' => $githubUrl,
                'id' => $id,
                'user_id' => $_SESSION['user_id']
            ]);

        } else {

            $stmt = $pdo->prepare(
                "INSERT INTO resume_entries
                 (user_id, entry_type, title, organization,
                  start_date, end_date, description, github_url)
                 VALUES
                 (:user_id, :entry_type, :title, :organization,
                  :start_date, :end_date, :description, :github_url)"
            );

            $stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'entry_type' => $entryType,
                'title' => $title,
                'organization' => $organization,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'description' => $description,
                'github_url' => $githubUrl
            ]);
        }

        header('Location: admin.php');
        exit;
    }

    $entry = [
        'entry_type' => $entryType,
        'title' => $title,
        'organization' => $organization,
        'start_date' => $startDate,
        'end_date' => $endDate ?? '',
        'description' => $description,
        'github_url' => $githubUrl ?? ''
    ];
}

require_once __DIR__ . '/../includes/header.php';
?>

<main>
    <section>

        <h2 class="sec-title">
            <?= $isEdit ? 'Edit Entry' : 'Add New Entry' ?>
        </h2>

        <?php if ($error !== ''): ?>
            <p><?= escape($error) ?></p>
        <?php endif; ?>

        <form method="POST">

            <div>
                <label for="entry_type">Entry Type</label>

                <select id="entry_type" name="entry_type" required>
                    <option
                        value="experience"
                        <?= $entry['entry_type'] === 'experience' ? 'selected' : '' ?>
                    >
                        Experience
                    </option>

                    <option
                        value="education"
                        <?= $entry['entry_type'] === 'education' ? 'selected' : '' ?>
                    >
                        Education
                    </option>

                    <option
                        value="project"
                        <?= $entry['entry_type'] === 'project' ? 'selected' : '' ?>
                    >
                        Project
                    </option>
                </select>
            </div>

            <div>
                <label for="title">Title</label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    value="<?= escape($entry['title']) ?>"
                    required
                >
            </div>

            <div>
                <label for="organization">Organization</label>

                <input
                    type="text"
                    id="organization"
                    name="organization"
                    value="<?= escape($entry['organization']) ?>"
                    required
                >
            </div>

            <div>
                <label for="start_date">Start Date</label>

                <input
                    type="date"
                    id="start_date"
                    name="start_date"
                    value="<?= escape($entry['start_date']) ?>"
                    required
                >
            </div>

            <div>
                <label for="end_date">End Date</label>

                <input
                    type="date"
                    id="end_date"
                    name="end_date"
                    value="<?= escape($entry['end_date'] ?? '') ?>"
                >
            </div>

            <div>
                <label for="description">Description</label>

                <textarea
                    id="description"
                    name="description"
                    rows="6"
                    required
                ><?= escape($entry['description']) ?></textarea>
            </div>

            <div>
                <label for="github_url">
                    GitHub URL
                </label>

                <input
                    type="url"
                    id="github_url"
                    name="github_url"
                    value="<?= escape($entry['github_url'] ?? '') ?>"
                >
            </div>

            <button type="submit">
                <?= $isEdit ? 'Update Entry' : 'Create Entry' ?>
            </button>

            <a href="admin.php">Cancel</a>

        </form>

    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>