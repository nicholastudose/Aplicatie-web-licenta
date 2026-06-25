<?php
session_start();
require_once '../includes/init.php';

$erori = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenume = trim($_POST['prenume']);
    $nume = trim($_POST['nume']);
    $email = trim($_POST['email']);
    $parola = $_POST['parola'];

    // Validare email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erori[] = 'Emailul introdus nu este valid.';
    } else {
        //unicitate email
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilizator WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $erori[] = 'Acest email este deja folosit.';
        }
    }

    if (strlen($parola) < 7 ||
        !preg_match('/[A-Z]/', $parola) ||
        !preg_match('/[0-9]/', $parola) ||
        !preg_match('/[^a-zA-Z0-9]/', $parola)
    ) {
        $erori[] = 'Parola trebuie să aibă minim 7 caractere, cel puțin o majusculă, o cifră și un caracter special.';
    }

    if (empty($erori)) {
        $_SESSION['reg_prenume'] = $prenume;
        $_SESSION['reg_nume'] = $nume;
        $_SESSION['reg_email'] = $email;
        $_SESSION['reg_parola'] = password_hash($parola, PASSWORD_DEFAULT);
        header('Location: register2.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Înregistrare - Pasul 1</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Montserrat', sans-serif;
            background: #f7f7f7;
            padding-top: 35px;
        }

        .page-wrapper {
            min-height: calc(100vh - 80px);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .form-container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 0 12px rgba(0,0,0,0.08);
            text-align: center;
        }

        .form-container h2 {
            margin-bottom: 25px;
            color: #333;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        button {
            width: 100%;
            padding: 14px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit; 
            box-sizing: border-box;
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
            background-color: #00796b;
            color: white;
            font-weight: bold;
            margin-top: 20px;
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
    <div class="form-container">
        <h2>Înregistrare</h2>

        <?php if (!empty($erori)): ?>
            <div class="errors">
                <ul>
                    <?php foreach ($erori as $eroare): ?>
                        <li><?= htmlspecialchars($eroare) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="text" name="prenume" placeholder="Prenume" value="<?= $_POST['prenume'] ?? '' ?>" required>
            <input type="text" name="nume" placeholder="Nume" value="<?= $_POST['nume'] ?? '' ?>" required>
            <input type="email" name="email" placeholder="Email" value="<?= $_POST['email'] ?? '' ?>" required>
            <input type="password" name="parola" placeholder="Parolă" required>
            <button type="submit">Continuă</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
