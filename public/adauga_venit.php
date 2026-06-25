<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'user') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$erori = [];
$mesaj = '';

$stmt = $pdo->prepare("SELECT id, nume FROM categorie WHERE LOWER(nume) IN ('salar', 'bonus', 'venit suplimentar', 'cadou')");
$stmt->execute();
$categorii = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categorie_id = $_POST['categorie_id'] ?? '';
    $suma = $_POST['suma'] ?? '';
    $data = $_POST['data_tranzactie'] ?? '';
    $descriere = trim($_POST['descriere'] ?? '');

    if (!$categorie_id || !$suma || !$data) {
        $erori[] = 'Toate câmpurile marcate cu * sunt obligatorii.';
    } elseif ($suma <= 0) {
        $erori[] = 'Suma trebuie să fie mai mare decât 0.';
    }

    if (empty($erori)) {
        $stmt = $pdo->prepare("INSERT INTO tranzactie (utilizator_id, categorie_id, suma, data_tranzactie, tip, descriere)
                               VALUES (?, ?, ?, ?, 'venit', ?)");
        $stmt->execute([
            $user_id,
            $categorie_id,
            $suma,
            $data,
            $descriere
        ]);

        $mesaj = "Venitul a fost salvat cu succes!";

        adaugaNotificare($pdo, $user_id, "Ai adăugat un venit în valoare de " . round($suma) . " lei.");
    }
}

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adaugă venit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            background: #f7f7f7;
            padding-top: 20px;
        }

        .form-container {
            max-width: 500px;
            margin: 120px auto;
            background: white;
            padding: 35px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #00796b;
            font-weight: 700;
        }

        label {
            font-weight: 600;
            display: block;
            margin: 16px 0 6px;
            color: #444;
            font-size: 14px;
        }

        select, input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            box-sizing: border-box;
        }

        textarea {
            resize: vertical;
        }

        .btn {
            margin-top: 25px;
            background-color: #00796b;
            color: white;
            font-weight: 600;
            padding: 14px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            width: 100%;
            font-family: inherit;
            font-size: 16px;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background-color: #004d40;
        }

        .msg {
            text-align: center;
            font-size: 14px;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
        }

        .msg.error {
            background: #ffebee;
            color: #c62828;
        }

        .msg.success {
            background: #e0f2f1;
            color: #004d40;
        }

        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            font-weight: 600;
            color: #00796b;
            font-size: 14px;
        }
        .btn-back:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="form-container">
    <a href="venituri.php" class="btn-back">← Înapoi la venituri</a>
    <h2>Adaugă un venit</h2>

    <?php if (!empty($erori)): ?>
        <div class="msg error"><?= implode('<br>', $erori) ?></div>
    <?php elseif ($mesaj): ?>
        <div class="msg success"><?= $mesaj ?></div>
    <?php endif; ?>

    <form method="post">
        <label for="categorie_id">Categorie *</label>
        <select name="categorie_id" id="categorie_id" required>
            <option value="">Selectează categoria</option>
            <?php foreach ($categorii as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nume']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="suma">Sumă (lei) *</label>
        <input type="number" name="suma" step="0.01" min="0.01" placeholder="0.00" required>

        <label for="data_tranzactie">Data *</label>
        <input type="date" name="data_tranzactie" value="<?= date('Y-m-d') ?>" required>

        <label for="descriere">Descriere (opțional)</label>
        <textarea name="descriere" id="descriere" rows="3" maxlength="20" placeholder="Ex: Bonus muncă, cadou părinți..."></textarea>

        <button type="submit" class="btn">Salvează venitul</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>