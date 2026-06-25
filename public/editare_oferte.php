<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID ofertă invalid!');
}

$id = intval($_GET['id']);
$erori = [];

$stmt = $pdo->prepare("SELECT * FROM oferta WHERE id = ?");
$stmt->execute([$id]);
$oferta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$oferta) {
    die('Ofertă inexistentă!');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titlu = trim($_POST['titlu'] ?? '');
    $descriere = trim($_POST['descriere'] ?? '');
    $data_inceput = $_POST['data_inceput'] ?? null;
    $data_sfarsit = $_POST['data_sfarsit'] ?? null;
    $poza = $oferta['link'];

    if (empty($titlu) || empty($descriere)) {
        $erori[] = "Titlul și descrierea sunt obligatorii.";
    }

    if (!empty($_FILES['poza']['name']) && $_FILES['poza']['error'] === UPLOAD_ERR_OK) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['poza']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExtensions)) {
            $erori[] = "Formatul imaginii nu este valid. Sunt permise doar JPG, JPEG, PNG și WEBP.";
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $_FILES['poza']['tmp_name']);
            finfo_close($finfo);

            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($mimeType, $allowedMimeTypes)) {
                $erori[] = "Fișierul încărcat nu este o imagine validă.";
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
                if (!empty($oferta['link']) && file_exists('../' . $oferta['link'])) {
                    unlink('../' . $oferta['link']);
                }
                $poza = 'assets/oferte/' . $filename;
            } else {
                $erori[] = "Eroare la salvarea noii imagini pe server.";
            }
        }
    }

    if (empty($erori)) {
        $stmt = $pdo->prepare("UPDATE oferta SET titlu = ?, descriere = ?, link = ?, data_inceput = ?, data_sfarsit = ? WHERE id = ?");
        $stmt->execute([$titlu, $descriere, $poza, $data_inceput, $data_sfarsit, $id]);

        header('Location: oferte_admin.php');
        exit;
    }
}

include '../includes/header_admin.php';
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Editare ofertă</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f7f7f7;
        }

        .container-form {
            max-width: 600px;
            margin: 100px auto 40px;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #00796b;
        }

        label {
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        input[type="date"],
        textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0 20px;
            border: 1px solid #ccc;
            border-radius: 12px;
            font-size: 15px;
            box-sizing: border-box;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .btn-save {
            background-color: #00796b;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            width: 100%;
            transition: background 0.3s;
        }

        .btn-save:hover {
            background-color: #005f56;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: #555;
        }

        .back-link:hover {
            color: #000;
        }
    </style>
</head>
<body>

<div class="container-form">
    <h2>Editare ofertă</h2>
    <form method="POST">
        <label for="titlu">Titlu:</label>
        <input type="text" id="titlu" name="titlu" value="<?= htmlspecialchars($oferta['titlu']) ?>" required>

        <label for="descriere">Descriere:</label>
        <textarea id="descriere" name="descriere" required><?= htmlspecialchars($oferta['descriere']) ?></textarea>

        <label for="poza">Schimbă poza de fundal (opțional):</label>
        <input type="file" name="poza" id="poza" accept="image/*">

        <label for="data_inceput">Data început:</label>
        <input type="date" id="data_inceput" name="data_inceput" value="<?= $oferta['data_inceput'] ?>">

        <label for="data_sfarsit">Data sfârșit:</label>
        <input type="date" id="data_sfarsit" name="data_sfarsit" value="<?= $oferta['data_sfarsit'] ?>">

        <button type="submit" class="btn-save">Salvează modificările</button>
    </form>

    <a href="oferte_admin.php" class="back-link">← Înapoi la lista de oferte</a>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
