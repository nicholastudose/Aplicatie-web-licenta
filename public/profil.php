<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'user') {
    header('Location: login.php');
    exit;
}

include '../includes/header.php';

$stmt = $pdo->prepare("SELECT * FROM utilizator WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$poza = !empty($user['poza']) ? '../' . $user['poza'] : '../assets/img/default.png';
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Profilul meu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: #f7f7f7;
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding-top: 20px;
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

        .profil-img {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            border: 4px solid #00796b;
            object-fit: cover;
            margin-bottom: 15px;
        }

        .profil-info p {
            font-size: 16px;
            margin: 8px 0;
        }

        .btn-profil, .logout-btn {
            font-family: inherit; 
        }

        .btn-container {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn-profil {
            background-color: #00796b;
            color: white;
            padding: 8px 22px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-profil:hover {
            background-color: #005f56;
        }

        .logout-btn {
            background-color: #b71c1c;
            color: white;
            padding: 10px 22px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }

        .logout-btn:hover {
            background-color: #a21717;
        }
    </style>
</head>
<body>

<div class="profil-container">
    <h2>Profilul meu</h2>

    <img src="<?= htmlspecialchars($poza) ?>" class="profil-img" alt="Poza de profil">

    <div class="profil-info">
        <p><strong>Prenume:</strong> <?= htmlspecialchars($user['prenume']) ?></p>
        <p><strong>Nume:</strong> <?= htmlspecialchars($user['nume']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Telefon:</strong> <?= htmlspecialchars($user['telefon'] ?? '-') ?></p>
        <p><strong>Oraș:</strong> <?= htmlspecialchars($user['oras'] ?? '-') ?></p>
        <p><strong>Data nașterii:</strong> <?= htmlspecialchars($user['data_nasterii'] ?? '-') ?></p>
    </div>

    <div class="btn-container">
        <a href="editare_profil.php" class="btn-profil">Editează profil</a>
        <form method="post" action="logout.php" style="margin: 0;">
            <button type="submit" class="btn-profil logout-btn">Logout</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>




