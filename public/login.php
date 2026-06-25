<?php
require_once '../includes/init.php';

$eroare = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $parola = $_POST['parola'];

    $stmt = $pdo->prepare("SELECT * FROM utilizator WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($parola, $user['parola'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['rol'] = $user['rol']; // salvare rol in sesiune

        if ($user['rol'] === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: dashboard_utilizator.php");
        }
        exit;
    } else {
        $eroare = "Email sau parolă incorecte.";
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
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
            padding-top: 0px;
        }

        .page-wrapper {
            min-height: calc(100vh - 80px);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            background: #fff;
            padding: 35px;
            border-radius: 12px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 0 12px rgba(0,0,0,0.08);
            text-align: center;
        }

        .login-container h2 {
            margin-bottom: 20px;
            color: #333;
        }

        input[type="email"],
        input[type="password"],
        input[type="submit"] {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #00796b;
            color: white;
            font-weight: bold;
            margin-top: 20px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #00675b;
        }

        .eroare {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<?php include '../includes/header_guest.php'; ?>

<div class="page-wrapper">
    <div class="login-container">
        <h2>Autentificare</h2>

        <?php if ($eroare): ?>
            <p class="eroare"><?= htmlspecialchars($eroare) ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            <input type="password" name="parola" placeholder="Parolă" required>
            <input type="submit" value="Conectează-te">
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>