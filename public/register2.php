<?php
require_once '../includes/init.php';

if (!isset($_SESSION['reg_prenume'], $_SESSION['reg_nume'], $_SESSION['reg_email'], $_SESSION['reg_parola'])) {
    echo "<p style='color:red;'>Lipsesc datele din pasul 1.</p>";
    exit;
}
$erori = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telefon = trim($_POST['telefon'] ?? '');
    $oras = trim($_POST['oras'] ?? '');
    $data_nasterii = $_POST['data_nasterii'] ?? '';
    $poza = null;

    // procesare+validare poza
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
            $filename = uniqid() . '.' . $ext;
            $uploadDir = '../assets/uploads/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $destination = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['poza']['tmp_name'], $destination)) {
                $poza = 'assets/uploads/' . $filename;
            } else {
                $erori[] = 'A apărut o eroare la salvarea imaginii de profil.';
            }
        }
    }

    // verificăm și introducem în baza de date
    if (empty($erori)) {
        $stmt = $pdo->prepare("INSERT INTO utilizator (prenume, nume, email, parola, telefon, oras, data_nasterii, poza) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['reg_prenume'],
            $_SESSION['reg_nume'],
            $_SESSION['reg_email'],
            $_SESSION['reg_parola'],
            $telefon,
            $oras,
            $data_nasterii,
            $poza
        ]);

        // curățăm sesiunea
        unset($_SESSION['reg_prenume'], $_SESSION['reg_nume'], $_SESSION['reg_email'], $_SESSION['reg_parola']);

        echo "<!DOCTYPE html><html lang='ro'><head>
            <meta charset='UTF-8'>
            <meta http-equiv='refresh' content='3;url=login.php'>
            <title>Înregistrare finalizată</title>
            <link href='https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap' rel='stylesheet'>
            <style>
                body { font-family: 'Montserrat', sans-serif; background: #f7f7f7; text-align: center; padding-top: 100px; }
                .card { background: white; padding: 40px; border-radius: 12px; display: inline-block; box-shadow: 0 4px 12px rgba(0,0,0,0.08); max-width: 400px; }
                h2 { color: #004d40; font-weight: 700; margin-bottom: 15px; }
                p { color: #555; font-size: 16px; }
            </style>
            </head><body>
            <div class='card'>
                <h2>Cont creat cu succes! 🎉</h2>
                <p>Bun venit! Veți fi redirecționat către pagina de autentificare în câteva secunde...</p>
            </div>
        </body></html>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Înregistrare - Pasul 2</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f7f7f7;
            font-family: 'Montserrat', sans-serif;
            padding-top: 95px;
        }

        .page-wrapper {
            min-height: calc(100vh - 80px);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .register-form {
            background: white;
            padding: 35px;
            border-radius: 12px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 0 12px rgba(0,0,0,0.08);
            text-align: center;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 12px;
            text-align: left;
        }

        input[type="text"], input[type="date"], input[type="file"], select {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-family: inherit;
            box-sizing: border-box;
            font-size: 15px;
        }

        .errors {
            background: #ffebee;
            color: #c62828;
            border-radius: 8px;
            padding: 10px;
            text-align: left;
            margin-bottom: 20px;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 14px;
            background-color: #00796b;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background-color: #00675b;
        }
    </style>
</head>
<body>

<?php include '../includes/header_guest.php'; ?>

<div class="page-wrapper">
    <div class="register-form">
        <h2>Înregistrare - Pasul 2</h2>

        <?php if (!empty($erori)): ?>
            <div class="errors">
            <ul style="margin: 0; padding-left: 20px;">
            <?php foreach ($erori as $eroare): ?>
                <li><?= htmlspecialchars($eroare) ?></li>
            <?php endforeach; ?>
            </ul>
            </div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <label for="telefon">Telefon</label>
            <input type="text" name="telefon" id="telefon" placeholder="07..." required>

            <label for="oras">Oraș</label>
            <select name="oras" id="oras" required>
                <option value="">Selectează oraș</option>
                <option>Cluj-Napoca</option>
                <option>București</option>
                <option>Timișoara</option>
                <option>Iași</option>
            </select>

            <label for="data_nasterii">Data nașterii</label>
            <input type="date" name="data_nasterii" id="data_nasterii" required>

            <label for="poza">Poza de profil (opțional)</label>
            <input type="file" name="poza" id="poza" accept="image/*">

            <button type="submit">Finalizează</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
