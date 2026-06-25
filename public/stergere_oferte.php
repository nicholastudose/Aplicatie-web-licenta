<?php
require_once '../includes/init.php';

if (!isset($_GET['id'])) {
    die('ID invalid!');
}

$id = intval($_GET['id']);
$stmt = $pdo->prepare("DELETE FROM oferta WHERE id = ?");
$stmt->execute([$id]);

header('Location: oferte_admin.php');
exit;
