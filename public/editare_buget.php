<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$mesaj = '';
$erori = [];

$stmt = $pdo->query("SELECT id, nume FROM categorie WHERE nume NOT IN ('salar', 'bonus', 'venit suplimentar','cadou')");
$categorii = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['sterge']) && is_numeric($_GET['sterge'])) {
    $stmt = $pdo->prepare("DELETE FROM buget_personalizat WHERE utilizator_id = ? AND categorie_id = ?");
    $stmt->execute([$_SESSION['user_id'], $_GET['sterge']]);
    $mesaj = "Categoria a fost ștearsă din buget.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categorie_id = (int)($_POST['nume'] ?? 0);
    $suma = (int)($_POST['suma'] ?? 0);

    if ($categorie_id <= 0 || $suma <= 0) {
        $erori[] = "Selectează o categorie validă și introdu o sumă mai mare ca 0.";
    } else {
        $stmt = $pdo->prepare("REPLACE INTO buget_personalizat (utilizator_id, categorie_id, suma) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $categorie_id, $suma]);
        $mesaj = "Categoria și suma au fost adăugate cu succes.";
    }
}

$stmt = $pdo->prepare("SELECT c.id, c.nume, b.suma FROM buget_personalizat b JOIN categorie c ON c.id = b.categorie_id WHERE b.utilizator_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$buget = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Editare buget</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: #f7f7f7;
            margin: 0;
        }
        .form-container {
            max-width: 700px;
            margin: 100px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
        }
        h2 {
            text-align: center;
            color: #00796b;
        }
        label {
            display: block;
            margin-top: 16px;
            font-weight: bold;
        }
        input[type="number"], select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        button {
            margin-top: 24px;
            width: 100%;
            background: #00796b;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: bold;
            font-family: inherit;
            cursor: pointer;
        }
        .msg {
            margin-top: 10px;
            font-weight: bold;
            text-align: center;
        }
        .msg.error { color: #c62828; }
        .msg.success { color: #00796b; }
        table {
            margin-top: 30px;
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        th { background: #00796b; color: white; }
        .sterge-link {
            color: #c62828;
            font-weight: bold;
            text-decoration: none;
            margin-left: 10px;
        }
        .sterge-link:hover {
            text-decoration: underline;
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
<div class="form-container">
    <a href="dashboard_utilizator.php" class="back-link">← Înapoi la dashboard</a>
    <h2>Editează bugetul tău</h2>
    <?php if (!empty($erori)): ?><div class="msg error"><?= implode('<br>', $erori) ?></div><?php endif; ?>
    <?php if ($mesaj): ?><div class="msg success"><?= $mesaj ?></div><?php endif; ?>

    <form method="post">
        <label for="nume">Selectează categoria</label>
        <select id="nume" name="nume" required>
            <option value="">Alege o categorie</option>
            <?php foreach ($categorii as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nume']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="suma">Sumă alocată (lei)</label>
        <input type="number" id="suma" name="suma" min="1" required>

        <button type="submit">Salvează în buget</button>
    </form>

    <?php if ($buget): ?>
        <table>
            <thead><tr><th>Categorie</th><th>Sumă</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($buget as $b): ?>
                <tr>
                    <td><?= htmlspecialchars($b['nume']) ?></td>
                    <td><?= $b['suma'] ?> lei</td>
                    <td><a class="sterge-link" href="?sterge=<?= $b['id'] ?>" onclick="return confirm('Ești sigur că vrei să ștergi această categorie?')">Șterge</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>
