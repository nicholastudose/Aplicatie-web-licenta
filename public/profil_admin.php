<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}

include '../includes/header_admin.php';

$stmt = $pdo->prepare("SELECT * FROM utilizator WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Profil Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: #f7f7f7;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding-top:19px;
        }

        .profil-container {
            max-width: 600px;
            margin: 100px auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.08);
            text-align: center;
        }

        h2 {
            margin-bottom: 25px;
            color: #00796b;
        }

        .profil-info p {
            font-size: 16px;
            margin: 10px 0;
        }

        .logout-btn {
            margin-top: 30px;
            background-color: #b71c1c;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .logout-btn:hover {
            background-color: #a21717;
        }
    </style>
</head>
<body>

<div class="profil-container">
    <h2>Profilul administratorului</h2>

    <div class="profil-info">
        <p><strong>Prenume:</strong> <?= htmlspecialchars($admin['prenume']) ?></p>
        <p><strong>Nume:</strong> <?= htmlspecialchars($admin['nume']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($admin['email']) ?></p>
        <p><strong>Rol:</strong> <?= htmlspecialchars($admin['rol']) ?></p>
        <p><strong>Data înregistrării:</strong> <?= date('d.m.Y H:i', strtotime($admin['data_inregistrare'])) ?></p>
    </div>

    <form action="logout.php" method="post">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
