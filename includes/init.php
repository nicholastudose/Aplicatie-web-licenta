<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=aplicatie_licenta', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Eroare conexiune: " . $e->getMessage());
}
function adaugaNotificare(PDO $pdo, int $user_id, string $mesaj): void {
    $stmt = $pdo->prepare("INSERT INTO notificare (utilizator_id, mesaj) VALUES (?, ?)");
    $stmt->execute([$user_id, $mesaj]);
}

