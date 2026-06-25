<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include '../includes/header_admin.php';

$mesaj = '';
$erori = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titlu = $_POST['titlu'] ?? '';
    $descriere = $_POST['descriere'] ?? '';
    $data_inceput = $_POST['data_inceput'] ?? '';
    $data_sfarsit = $_POST['data_sfarsit'] ?? '';
    $poza = null;

    if (empty($titlu) || empty($descriere)) {
        $erori[] = "Titlul și descrierea sunt obligatorii.";
    }

if (!empty($_FILES['poza']['name']) && $_FILES['poza']['error'] === UPLOAD_ERR_OK) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['poza']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExtensions)) {
            $erori[] = "Formatul imaginii nu este valid. Sunt permise doar JPG, JPEG, PNG și WEBP.";
        } else {
            // Verificare tip MIME real pe server
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $_FILES['poza']['tmp_name']);
            finfo_close($finfo);

            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($mimeType, $allowedMimeTypes)) {
                $erori[] = "Fișierul încărcat nu este o imagine reală validă.";
            }
        }

        if (empty($erori)) {
            $filename = uniqid() . '.' . $ext;
            $uploadDir = '../assets/oferte/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $destination = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['poza']['tmp_name'], $destination)) {
                $poza = 'assets/oferte/' . $filename;
            } else {
                $erori[] = "A apărut o eroare la salvarea fizică a imaginii.";
            }
        }
    }

    if (empty($erori)) {
        $stmt = $pdo->prepare("INSERT INTO oferta (titlu, descriere, link, data_inceput, data_sfarsit) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$titlu, $descriere, $poza, $data_inceput, $data_sfarsit]);
        $mesaj = "Oferta a fost adăugată cu succes!";
    }
}
include '../includes/header_admin.php';
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Adaugă ofertă</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding-top:19px;
        }

        .form-wrapper {
            max-width: 600px;
            margin: 100px auto;
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.08);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        label {
            font-weight: bold;
            display: block;
            margin: 12px 0 6px;
        }

        input, textarea {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid #ccc;
        }

        textarea {
            resize: vertical;
        }

        .btn {
            margin-top: 25px;
            background-color: #00796b;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: bold;
            width: 100%;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #00695c;
        }

        .msg {
            margin-top: 15px;
            font-weight: bold;
            text-align: center;
        }

        .msg.success {
            color: #2e7d32;
        }

        .msg.error {
            color: #c62828;
        }
        .btn-back {
            display: block;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
            color: #00796b;
        }
    </style>
</head>
<body>

<div class="form-wrapper">
<a href="oferte_admin.php" class="btn-back">← Înapoi la oferte</a>
    <h2>Adaugă o ofertă</h2>

    <?php if ($mesaj): ?>
        <div class="msg success"><?= $mesaj ?></div>
    <?php endif; ?>

    <?php if (!empty($erori)): ?>
        <div class="msg error"><?= implode('<br>', $erori) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label for="titlu">Nume locație / Titlu ofertă *</label>
        <input type="text" name="titlu" id="titlu" required>

        <label for="descriere">Descriere ofertă *</label>
        <textarea name="descriere" id="descriere" rows="4" required></textarea>

        <label for="poza">Poză fundal (card ofertă)</label>
        <input type="file" name="poza" accept="image/*">

        <label for="data_inceput">Dată început (opțional)</label>
        <input type="date" name="data_inceput">

        <label for="data_sfarsit">Dată sfârșit (opțional)</label>
        <input type="date" name="data_sfarsit">

        <button type="submit" class="btn">Salvează oferta</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
