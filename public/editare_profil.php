<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'user') {
    header('Location: login.php');
    exit;
}

$id = $_SESSION['user_id'];
$erori = [];

$stmt = $pdo->prepare("SELECT * FROM utilizator WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenume = trim($_POST['prenume']);
    $nume = trim($_POST['nume']);
    $telefon = trim($_POST['telefon']);
    $oras = trim($_POST['oras']);
    $data_nasterii = $_POST['data_nasterii'];

    $poza = $user['poza'];

    if (!empty($_FILES['poza']['name']) && $_FILES['poza']['error'] === UPLOAD_ERR_OK) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['poza']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExtensions)) {
            $erori[] = 'Formatul imaginii nu este valid. Sunt permise doar JPG, JPEG, PNG și WEBP.';
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $_FILES['poza']['tmp_name']);
            finfo_close($finfo);

            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($mimeType, $allowedMimeTypes)) {
                $erori[] = 'Fișierul încărcat nu este o imagine validă.';
            }
        }

        if (empty($erori)) {
            $nume_fisier = uniqid() . '.' . $ext;
            $uploadDir = '../assets/uploads/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $cale_poza = $uploadDir . $nume_fisier;

            if (move_uploaded_file($_FILES["poza"]["tmp_name"], $cale_poza)) {
                if (!empty($user['poza']) && file_exists('../' . $user['poza'])) {
                    unlink('../' . $user['poza']);
                }
                $poza = 'assets/uploads/' . $nume_fisier; 
            } else {
                $erori[] = 'A apărut o eroare la salvarea noii imagini.';
            }
        }
    }

    if (empty($erori)) {
        $stmt = $pdo->prepare("UPDATE utilizator SET prenume = ?, nume = ?, telefon = ?, oras = ?, data_nasterii = ?, poza = ? WHERE id = ?");
        $stmt->execute([$prenume, $nume, $telefon, $oras, $data_nasterii, $poza, $id]);

        header('Location: profil.php');
        exit;
    }
}

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editare profil</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: #f7f7f7;
            font-family: 'Montserrat', sans-serif;
            margin: 0;
        }

        .form-container {
            max-width: 600px;
            margin: 100px auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #00796b;
            font-weight: 700;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
            color: #444;
        }

        input[type="text"],
        input[type="date"],
        input[type="file"],
        select {
            width: 100%;
            padding: 12px;
            margin-top: 6px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-family: inherit;
            box-sizing: border-box;
            font-size: 15px;
        }

        .btn-submit {
            margin-top: 30px;
            background-color: #00796b;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            font-family: inherit;
            width: 100%;
            transition: background-color 0.3s;
        }

        .btn-submit:hover {
            background-color: #005f56;
        }

        .back-link {
            text-align: center;
            display: block;
            margin-top: 20px;
            text-decoration: none;
            color: #00796b;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .errors {
            background: #ffebee;
            color: #c62828;
            border-radius: 8px;
            padding: 12px;
            text-align: left;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Editare profil</h2>

    <?php if (!empty($erori)): ?>
        <div class="errors">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($erori as $eroare): ?>
                    <li><?= htmlspecialchars($eroare) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="prenume">Prenume:</label>
        <input type="text" name="prenume" value="<?= htmlspecialchars($user['prenume'] ?? '') ?>" required>

        <label for="nume">Nume:</label>
        <input type="text" name="nume" value="<?= htmlspecialchars($user['nume'] ?? '') ?>" required>

        <label for="telefon">Telefon:</label>
        <input type="text" name="telefon" value="<?= htmlspecialchars($user['telefon'] ?? '') ?>">

        <label for="oras">Oraș:</label>
        <select name="oras" required>
            <option value="Cluj-Napoca" <?= (($user['oras'] ?? '') === 'Cluj-Napoca') ? 'selected' : '' ?>>Cluj-Napoca</option>
            <option value="București" <?= (($user['oras'] ?? '') === 'București') ? 'selected' : '' ?>>București</option>
            <option value="Timișoara" <?= (($user['oras'] ?? '') === 'Timișoara') ? 'selected' : '' ?>>Timișoara</option>
            <option value="Iași" <?= (($user['oras'] ?? '') === 'Iași') ? 'selected' : '' ?>>Iași</option>
        </select>

        <label for="data_nasterii">Data nașterii:</label>
        <input type="date" name="data_nasterii" value="<?= htmlspecialchars($user['data_nasterii'] ?? '') ?>">

        <label for="poza">Poză nouă (opțional):</label>
        <input type="file" name="poza" accept="image/*">

        <button type="submit" class="btn-submit">Salvează modificările</button>
    </form>

    <a href="profil.php" class="back-link">← Înapoi la profil</a>
</div>

</body>
</html>