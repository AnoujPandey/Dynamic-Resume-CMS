<?php

session_start();

require_once __DIR__
    . '/../config/database.php';

require_once __DIR__
    . '/../includes/functions.php';


if (!empty($_SESSION['user_id'])) {
    header('Location: admin.php');
    exit;
}


$error = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email =
        trim($_POST['email'] ?? '');

    $password =
        $_POST['password'] ?? '';


    if (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {
        $error =
            'Please enter a valid email address.';

    } elseif ($password === '') {

        $error =
            'Please enter your password.';

    } else {

        $stmt = $pdo->prepare(
            "SELECT
                id,
                full_name,
                email,
                password
             FROM users
             WHERE email = :email
             LIMIT 1"
        );

        $stmt->execute([
            'email' => $email
        ]);

        $user = $stmt->fetch();


        if (
            $user
            && password_verify(
                $password,
                $user['password']
            )
        ) {

            session_regenerate_id(true);

            $_SESSION['user_id'] =
                $user['id'];

            $_SESSION['full_name'] =
                $user['full_name'];

            header(
                'Location: admin.php'
            );

            exit;
        }

        $error =
            'Invalid email or password.';
    }
}


$pageTitle = 'Admin Login';

require_once __DIR__
    . '/../includes/header.php';

?>


<main>

<section>

    <h2 class="sec-title">
        Admin Login
    </h2>


    <?php if ($error !== ''): ?>

        <p class="message error">

            <?= escape($error) ?>

        </p>

    <?php endif; ?>


    <form
        method="POST"
        class="cms-form"
    >

        <div>

            <label for="email">
                Email
            </label>

            <input
                type="email"
                id="email"
                name="email"
                value="<?= escape(
                    $_POST[
                        'email'
                    ] ?? ''
                ) ?>"
                required
            >

        </div>


        <div>

            <label for="password">
                Password
            </label>

            <input
                type="password"
                id="password"
                name="password"
                required
            >

        </div>


        <button type="submit">
            Login
        </button>

    </form>

</section>

</main>


<?php

require_once __DIR__
    . '/../includes/footer.php';

?>