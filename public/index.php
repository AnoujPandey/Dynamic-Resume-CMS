<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// This is a single-owner portfolio.
$profileId = 1;
$stmt = $pdo->prepare('SELECT id, full_name, headline, summary, email, avatar_path FROM users WHERE id = :id');
$stmt->execute(['id' => $profileId]);
$user = $stmt->fetch();

$entries = [];
$skills = [];
if ($user) {
    $stmt = $pdo->prepare('SELECT * FROM resume_entries WHERE user_id = :user_id ORDER BY start_date DESC');
    $stmt->execute(['user_id' => $user['id']]);
    $entries = $stmt->fetchAll();

    $stmt = $pdo->prepare('SELECT * FROM skills WHERE user_id = :user_id ORDER BY sort_order ASC');
    $stmt->execute(['user_id' => $user['id']]);
    $skills = $stmt->fetchAll();
}

// Group entries using a classroom-style foreach loop.
$education = [];
$experience = [];
$projects = [];
foreach ($entries as $entry) {
    if ($entry['entry_type'] === 'education') {
        $education[] = $entry;
    } elseif ($entry['entry_type'] === 'experience') {
        $experience[] = $entry;
    } elseif ($entry['entry_type'] === 'project') {
        $projects[] = $entry;
    }
}

$avatar = 'my.jpeg';
if ($user && !empty($user['avatar_path']) && is_file(dirname(__DIR__) . '/' . $user['avatar_path'])) {
    $avatar = $user['avatar_path'];
}

$pageTitle = $user ? $user['full_name'] . ' - Portfolio Resume' : 'Portfolio Resume';
require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($user): ?>
<header class="resume-header">
    <div class="avatar-wrap">
        <img src="../<?= escape($avatar) ?>" alt="Profile photo">
    </div>
    <div class="header-body">
        <h1><?= escape($user['full_name']) ?></h1>
        <p class="headline"><?= escape($user['headline']) ?></p>
        <div class="contact-grid">
            <a class="contact-pill" href="mailto:<?= escape($user['email']) ?>"><?= escape($user['email']) ?></a>
        </div>
    </div>
</header>
<?php renderNavigation(true); ?>

<div class="layout">
    <aside class="resume-sidebar">
        <div class="side-block" id="skills">
            <div class="side-heading">Technical Skills</div>
            <?php foreach ($skills as $skill): ?>
                <?php $level = max(0, min(100, (int) $skill['skill_level'])); ?>
                <div class="s-skill">
                    <div class="s-skill-top">
                        <?= escape($skill['skill_name']) ?> <span><?= $level ?>%</span>
                    </div>
                    <div class="s-bar"><div class="s-bar-fill" style="width: <?= $level ?>%;"></div></div>
                </div>
            <?php endforeach; ?>
            <?php if (!$skills): ?>
                <p class="sidebar-empty">No technical skills added yet.</p>
            <?php endif; ?>
        </div>
    </aside>

    <main class="resume-main">
        <section id="objective">
            <h2 class="sec-title">Career Objective</h2>
            <div class="objective-card"><?= nl2br(escape($user['summary'])) ?></div>
        </section>

        <section id="education">
            <h2 class="sec-title">Education</h2>
            <div class="timeline">
                <?php foreach ($education as $entry): ?>
                    <article class="tl-item">
                        <div class="tl-top">
                            <span class="tl-inst"><?= escape($entry['organization']) ?></span>
                            <span class="tl-date">
                                <?= date('Y', strtotime($entry['start_date'])) ?> –
                                <?= $entry['end_date'] ? date('Y', strtotime($entry['end_date'])) : 'Present' ?>
                            </span>
                        </div>
                        <div class="tl-prog"><?= escape($entry['title']) ?></div>
                        <p><?= nl2br(escape($entry['description'])) ?></p>
                    </article>
                <?php endforeach; ?>
                <?php if (!$education): ?>
                    <p class="empty-state">No education records available yet.</p>
                <?php endif; ?>
            </div>
        </section>

        <section id="experience">
            <h2 class="sec-title">Experience</h2>
            <?php foreach ($experience as $entry): ?>
                <article class="exp-card">
                    <div class="exp-top">
                        <span class="exp-title"><?= escape($entry['title']) ?></span>
                        <span class="exp-date-badge">
                            <?= date('M Y', strtotime($entry['start_date'])) ?> –
                            <?= $entry['end_date'] ? date('M Y', strtotime($entry['end_date'])) : 'Present' ?>
                        </span>
                    </div>
                    <p class="exp-org"><?= escape($entry['organization']) ?></p>
                    <p><?= nl2br(escape($entry['description'])) ?></p>
                </article>
            <?php endforeach; ?>
            <?php if (!$experience): ?>
                <p class="empty-state">No experience added yet.</p>
            <?php endif; ?>
        </section>

        <section id="projects">
            <h2 class="sec-title">Academic Projects</h2>
            <div class="project-grid">
                <?php foreach ($projects as $entry): ?>
                    <article class="proj-card">
                        <div class="proj-icon">&lt;/&gt;</div>
                        <h3 class="proj-title"><?= escape($entry['title']) ?></h3>
                        <p class="proj-sub"><?= escape($entry['organization']) ?> · <?= date('Y', strtotime($entry['start_date'])) ?></p>
                        <p class="proj-description"><?= nl2br(escape($entry['description'])) ?></p>
                        <?php if (!empty($entry['github_url']) && isValidGithubUrl($entry['github_url'])): ?>
                            <a class="github-link" href="<?= escape($entry['github_url']) ?>" target="_blank" rel="noopener noreferrer">View on GitHub ↗</a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php if (!$projects): ?>
                <p class="empty-state">No projects added yet.</p>
            <?php endif; ?>
        </section>
    </main>
</div>
<?php else: ?>
    <main class="resume-main">
        <p class="empty-state">Profile information is not available yet.</p>
    </main>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
