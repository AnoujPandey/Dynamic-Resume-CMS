<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php');
    exit;
}
verifyCsrfToken();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
    http_response_code(400);
    exit('Invalid entry ID.');
}

try {
    $stmt = $pdo->prepare('DELETE FROM resume_entries WHERE id = :id AND user_id = :user_id');
    $stmt->execute(['id' => $id, 'user_id' => $_SESSION['user_id']]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Unable to delete the entry.');
}
header('Location: admin.php');
exit;
