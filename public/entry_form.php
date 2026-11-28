<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
require_once __DIR__ . '/../config/database.php';

$userId = (int) $_SESSION['user_id'];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
// Missing ID means Create. An invalid ID is an error, not a new entry.
if ($id === false) {
    http_response_code(400);
    exit('Invalid entry ID.');
}
$isEdit = $id !== null;
$entry = [
    'entry_type' => 'experience', 'title' => '', 'organization' => '',
    'start_date' => '', 'end_date' => '', 'description' => '', 'github_url' => ''
];
$errors = [];
$types = ['experience' => 'Experience', 'education' => 'Education', 'project' => 'Project'];

if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM resume_entries WHERE id = :id AND user_id = :user_id');
    $stmt->execute(['id' => $id, 'user_id' => $userId]);
    $entry = $stmt->fetch();
    if (!$entry) {
        http_response_code(404);
        exit('Entry not found.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    validatePostFields();

    // Retain submitted values if validation fails.
    $entry = [
        'entry_type' => $_POST['entry_type'] ?? '',
        'title' => trim($_POST['title'] ?? ''),
        'organization' => trim($_POST['organization'] ?? ''),
        'start_date' => $_POST['start_date'] ?? '',
        'end_date' => $_POST['end_date'] ?? '',
        'description' => trim($_POST['description'] ?? ''),
        'github_url' => trim($_POST['github_url'] ?? '')
    ];

    if (!isset($types[$entry['entry_type']])) {
        $errors[] = 'Select a valid entry type.';
    }
    if ($entry['title'] === '' || mb_strlen($entry['title']) > 120) {
        $errors[] = 'Enter a title of at most 120 characters.';
    }
    if ($entry['organization'] === '' || mb_strlen($entry['organization']) > 120) {
        $errors[] = 'Enter an organization of at most 120 characters.';
    }
    if (!isValidDate($entry['start_date'])) {
        $errors[] = 'Enter a valid start date.';
    }
    if ($entry['end_date'] !== '') {
        if (!isValidDate($entry['end_date'])) {
            $errors[] = 'Enter a valid end date.';
        } elseif ($entry['end_date'] < $entry['start_date']) {
            $errors[] = 'End date cannot be before the start date.';
        }
    }
    if ($entry['description'] === '' || strlen($entry['description']) > 65535) {
        $errors[] = 'Enter a description of at most 65,535 bytes.';
    }
    if ($entry['entry_type'] === 'project' && $entry['github_url'] !== '' &&
        !isValidGithubUrl($entry['github_url'])) {
        $errors[] = 'Enter a valid HTTPS GitHub URL.';
    }

    if (!$errors) {
        // Empty optional fields are stored as SQL NULL.
        $data = $entry;
        $data['end_date'] = $entry['end_date'] === '' ? null : $entry['end_date'];
        $data['github_url'] = $entry['entry_type'] === 'project' && $entry['github_url'] !== ''
            ? $entry['github_url'] : null;
        $data['user_id'] = $userId;

        try {
            if ($isEdit) {
                $sql = 'UPDATE resume_entries SET entry_type = :entry_type, title = :title,
                    organization = :organization, start_date = :start_date, end_date = :end_date,
                    description = :description, github_url = :github_url
                    WHERE id = :id AND user_id = :user_id';
                $data['id'] = $id;
            } else {
                $sql = 'INSERT INTO resume_entries
                    (user_id, entry_type, title, organization, start_date, end_date, description, github_url)
                    VALUES (:user_id, :entry_type, :title, :organization, :start_date, :end_date, :description, :github_url)';
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            $_SESSION['success'] = $isEdit ? 'Entry updated successfully.' : 'Entry created successfully.';
            header('Location: admin.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Unable to save the entry. Please try again.';
        }
    }
}

$pageTitle = $isEdit ? 'Edit Entry' : 'Add New Entry';
require_once __DIR__ . '/../includes/header.php';
?>
<main>
    <section>
        <h2 class="sec-title"><?= escape($pageTitle) ?></h2>
        <?php foreach ($errors as $error): ?>
            <p class="message error"><?= escape($error) ?></p>
        <?php endforeach; ?>
        <form method="post" action="entry_form.php<?= $isEdit ? '?id=' . (int) $id : '' ?>" class="cms-form">
            <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
            <div>
                <label for="entry_type">Entry Type</label>
                <select id="entry_type" name="entry_type" required>
                    <?php foreach ($types as $value => $label): ?>
                        <option value="<?= escape($value) ?>" <?= $entry['entry_type'] === $value ? 'selected' : '' ?>><?= escape($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="title">Title</label>
                <input type="text" id="title" name="title" maxlength="120" value="<?= escape($entry['title']) ?>" required>
            </div>
            <div>
                <label for="organization">Organization / Institution</label>
                <input type="text" id="organization" name="organization" maxlength="120" value="<?= escape($entry['organization']) ?>" required>
            </div>
            <div>
                <label for="start_date">Start Date</label>
                <input type="date" id="start_date" name="start_date" value="<?= escape($entry['start_date']) ?>" required>
            </div>
            <div>
                <label for="end_date">End Date (optional)</label>
                <input type="date" id="end_date" name="end_date" value="<?= escape($entry['end_date']) ?>">
            </div>
            <div>
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="6" required><?= escape($entry['description']) ?></textarea>
            </div>
            <div>
                <label for="github_url">GitHub URL (optional, projects only)</label>
                <input type="url" id="github_url" name="github_url" maxlength="255" value="<?= escape($entry['github_url']) ?>">
            </div>
            <button type="submit"><?= $isEdit ? 'Update Entry' : 'Create Entry' ?></button>
            <a class="cancel-link" href="admin.php">Cancel</a>
        </form>
    </section>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
