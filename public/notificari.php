<?php
require_once '../includes/init.php';
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'user') {
    header('Location: login.php');
    exit;
}

$id_user = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id, mesaj, este_citita, data_creare FROM notificare WHERE utilizator_id = ? ORDER BY data_creare DESC");
$stmt->execute([$id_user]);
$notificari = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Notificări</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            background: #f7f7f7;
            padding-top: 100px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            padding: 30px;
        }

        h2 {
            text-align: center;
            color: #00796b;
            margin-bottom: 25px;
        }

        .notificare {
            background: white;
            padding: 16px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-bottom: 15px;
            border-left: 5px solid #00796b;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: border-left-color 0.3s;
        }

        .notificare.citita {
            border-left: 5px solid #999;
        }

        .notificare span {
            color: #555;
            font-size: 14px;
        }

        .notificare p {
            margin: 0;
            font-weight: 500;
        }

        .gol {
            text-align: center;
            font-size: 16px;
            color: #888;
        }

        .btn-citeste-toate {
            display: block;
            margin: 0 auto 20px;
            padding: 10px 20px;
            background-color: #00796b;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-family: inherit;
            cursor: pointer;
        }

        .btn-citeste-toate:hover {
            background-color: #006358;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Notificările tale</h2>

    <?php if (!empty($notificari)): ?>
        <button class="btn-citeste-toate" id="marcheazaToate">Marchează toate ca citite</button>
    <?php endif; ?>

    <?php if (empty($notificari)): ?>
        <p class="gol">Nu ai notificări în acest moment.</p>
    <?php else: ?>
        <?php foreach ($notificari as $n): ?>
            <div class="notificare <?= $n['este_citita'] ? 'citita' : '' ?>" data-id="<?= $n['id'] ?>">
                <div>
                    <p><?= htmlspecialchars($n['mesaj']) ?></p>
                    <span><?= date('d.m.Y H:i', strtotime($n['data_creare'])) ?></span>
                </div>
                <input type="checkbox" class="checkbox-citire" <?= $n['este_citita'] ? 'checked' : '' ?>>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.checkbox-citire').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const notificareDiv = this.closest('.notificare');
                const notificareId = notificareDiv.dataset.id;
                const esteCitita = this.checked ? 1 : 0;

                fetch('marcare_notificare.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${notificareId}&este_citita=${esteCitita}`
                })
                    .then(response => response.text())
                    .then(data => {
                        if (data.trim() === 'OK') {
                            notificareDiv.classList.toggle('citita', esteCitita === 1);
                        } else {
                            alert("Eroare la salvarea stării notificării.");
                        }
                    });
            });
        });

        const btnToate = document.getElementById('marcheazaToate');
        if (btnToate) {
            btnToate.addEventListener('click', function () {
                fetch('marcare_notificare.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'toate=1'
                }).then(() => {
                    document.querySelectorAll('.notificare').forEach(div => {
                        div.classList.add('citita');
                        div.querySelector('.checkbox-citire').checked = true;
                    });
                });
            });
        }
    });
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html>