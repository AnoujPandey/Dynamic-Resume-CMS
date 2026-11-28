<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: admin.php');
    exit;
}

$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    validatePostFields();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 120) {
        $error = 'Please enter a valid email address.';
    } elseif ($password === '') {
        $error = 'Please enter your password.';
    } else {
        $stmt = $pdo->prepare('SELECT id, full_name, password FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            unset($_SESSION['csrf_token']);
            header('Location: admin.php');
            exit;
        }
        $error = 'Invalid email or password.';
    }
}

$pageTitle = 'Admin Login';
require_once __DIR__ . '/../includes/header.php';
?>
<main>
    <h1>Admin Login</h1>
    <p>Sign in to manage your portfolio.</p>
    <?php if ($error !== ''): ?>
        <div class="message error"><?= escape($error) ?></div>
    <?php endif; ?>
    <form method="post" action="login.php" class="cms-form">
        <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
        <div>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= escape($email) ?>" maxlength="120" required>
        </div>
        <div>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
    <?php renderNavigation(); ?>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
