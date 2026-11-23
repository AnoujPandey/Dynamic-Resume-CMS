<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Get the profile
$stmt = $pdo->prepare("SELECT * FROM users ORDER BY id ASC LIMIT 1");
$stmt->execute();
$user = $stmt->fetch();

// Get resume entries and skills
$entries = [];
$skills = [];

if ($user) {
    $stmt = $pdo->prepare(
        "SELECT * FROM resume_entries
         WHERE user_id = :user_id
         ORDER BY start_date DESC"
    );
    $stmt->execute(['user_id' => $user['id']]);
    $entries = $stmt->fetchAll();

    $stmt = $pdo->prepare(
        "SELECT * FROM skills
         WHERE user_id = :user_id
         ORDER BY sort_order ASC"
    );
    $stmt->execute(['user_id' => $user['id']]);
    $skills = $stmt->fetchAll();
}

require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($user): ?>

<header>
    <div class="avatar-wrap">
    <img
        src="<?= !empty($user['avatar_path'])
            ? '../' . escape($user['avatar_path'])
            : '../my.jpeg' ?>"
        alt="Profile photo">
    </div>

    <div class="header-body">
        <h1><?= escape($user['full_name']) ?></h1>
        <p><?= escape($user['headline']) ?></p>

        <div class="contact-grid">
            <a class="contact-pill" href="mailto:<?= escape($user['email']) ?>">
                <?= escape($user['email']) ?>
            </a>
        </div>
    </div>
</header>

<nav>
    <a href="#objective">Objective</a>
    <a href="#education">Education</a>
    <a href="#experience">Experience</a>
    <a href="#projects">Projects</a>
    <a href="#skills">Skills</a>
</nav>

<?php else: ?>

<p>Profile information is not available yet.</p>

<?php endif; ?>

<?php if ($user): ?>

<div class="layout">
    <main>
        <section id="objective">
            <h2 class="sec-title">Career Objective</h2>

            <div class="objective-card">
                <?= nl2br(escape($user['summary'])) ?>
            </div>
        </section>

<section id="education">
    <h2 class="sec-title">Education</h2>

    <div class="timeline">
        <?php
        $hasEducation = false;

        foreach ($entries as $entry):
            if ($entry['entry_type'] !== 'education') {
                continue;
            }

            $hasEducation = true;
        ?>

            <div class="tl-item">
                <div class="tl-top">
                    <span class="tl-inst">
                        <?= escape($entry['organization']) ?>
                    </span>

                    <span class="tl-date">
                        <?= escape(date('Y', strtotime($entry['start_date']))) ?>
                        –
                        <?= $entry['end_date']
                            ? escape(date('Y', strtotime($entry['end_date'])))
                            : 'Present' ?>
                    </span>
                </div>

                <div class="tl-prog">
                    <?= escape($entry['title']) ?>
                </div>

                <p><?= nl2br(escape($entry['description'])) ?></p>
            </div>

        <?php endforeach; ?>

        <?php if (!$hasEducation): ?>
            <p>No education records available yet.</p>
        <?php endif; ?>
    </div>
</section>

<section id="experience">
    <h2 class="sec-title">Work Experience</h2>

    <?php
    $hasExperience = false;

    foreach ($entries as $entry):
        if ($entry['entry_type'] !== 'experience') {
            continue;
        }

        $hasExperience = true;
    ?>

        <div class="exp-card">
            <div class="exp-top">
                <span class="exp-title">
                    <?= escape($entry['title']) ?>
                </span>

                <span class="exp-date-badge">
                    <?= escape(date('M Y', strtotime($entry['start_date']))) ?>
                    –
                    <?= $entry['end_date']
                        ? escape(date('M Y', strtotime($entry['end_date'])))
                        : 'Present' ?>
                </span>
            </div>

            <p class="exp-org">
                <?= escape($entry['organization']) ?>
            </p>

            <p>
                <?= nl2br(escape($entry['description'])) ?>
            </p>
        </div>

    <?php endforeach; ?>

    <?php if (!$hasExperience): ?>
        <p>No work experience added yet.</p>
    <?php endif; ?>
</section>

<section id="projects">
    <h2 class="sec-title">My Projects</h2>

    <div class="project-grid">
        <?php
        $hasProjects = false;

        foreach ($entries as $entry):
            if ($entry['entry_type'] !== 'project') {
                continue;
            }

            $hasProjects = true;
        ?>

            <div class="proj-card">
                <h3 class="proj-title">
                    <?= escape($entry['title']) ?>
                </h3>

                <p class="proj-sub">
                    <?= escape($entry['organization']) ?>
                </p>

                <p>
                    <?= nl2br(escape($entry['description'])) ?>
                </p>

                <?php if (!empty($entry['github_url'])): ?>
                    <a
                        href="<?= escape($entry['github_url']) ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="github-link"
                    >
                        View on GitHub ↗
                    </a>
                <?php endif; ?>
            </div>

        <?php endforeach; ?>
    </div>

    <?php if (!$hasProjects): ?>
        <p>No projects added yet.</p>
    <?php endif; ?>
</section>

<section id="skills">
    <h2 class="sec-title">Technical Skills</h2>

    <?php if (!empty($skills)): ?>

        <?php foreach ($skills as $skill): ?>
            <div class="s-skill">
                <div class="s-skill-top">
                    <?= escape($skill['skill_name']) ?>

                    <span>
                        <?= (int) $skill['skill_level'] ?>%
                    </span>
                </div>

                <div class="s-bar">
                    <div
                        class="s-bar-fill"
                        style="width: <?= max(0, min(100, (int) $skill['skill_level'])) ?>%;">
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    <?php else: ?>
        <p>No technical skills added yet.</p>
    <?php endif; ?>
</section>

    </main>
</div>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>