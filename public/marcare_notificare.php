<?php
require_once '../includes/init.php';
if (!isset($_SESSION)) session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toate']) && $_POST['toate'] == '1' && isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("UPDATE notificare SET este_citita = 1 WHERE utilizator_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        echo 'OK';
    } elseif (isset($_POST['id'], $_POST['este_citita'], $_SESSION['user_id'])) {
        $id = (int)$_POST['id'];
        $este_citita = (int)$_POST['este_citita'];
        $user_id = $_SESSION['user_id'];

        // validare pt user
        $stmt = $pdo->prepare("SELECT id FROM notificare WHERE id = ? AND utilizator_id = ?");
        $stmt->execute([$id, $user_id]);
        if ($stmt->rowCount() === 1) {
            $update = $pdo->prepare("UPDATE notificare SET este_citita = ? WHERE id = ?");
            $update->execute([$este_citita, $id]);
            echo 'OK';
        } else {
            echo 'Notificare invalidă';
        }
    } else {
        echo 'Date lipsă';
    }
} else {
    echo 'Metodă invalidă';
}
