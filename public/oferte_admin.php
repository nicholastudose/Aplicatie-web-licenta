<?php 
require_once '../includes/init.php';
include '../includes/header_admin.php';

$stmt = $pdo->query("SELECT * FROM oferta ORDER BY data_inceput DESC, id DESC");
$oferte = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Oferte pentru studenți</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f7f7f7;
            padding-top:0px;
        }

        .oferte-container {
            max-width: 1100px;
            margin: 100px auto 40px;
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .oferta-card {
            position: relative;
            background: #f7f7f7;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }

        .oferta-card:hover {
            transform: translateY(-4px);
        }

        .oferta-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .oferta-content {
            padding: 18px;
        }

        .oferta-titlu {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #00796b;
        }

        .oferta-descriere {
            font-size: 15px;
            color: #555;
            margin-bottom: 10px;
        }

        .oferta-perioada {
            font-size: 13px;
            color: #888;
        }

        .oferta-actiuni {
            margin-top: 15px;
        }

        .oferta-actiuni a {
            text-decoration: none;
            padding: 6px 12px;
            margin-right: 10px;
            border-radius: 12px;
            font-size: 13px;
            transition: background 0.2s;
        }

        .btn-edit {
            background-color: #00796b;
            color: #fff;
        }

        .btn-edit:hover {
            background-color: #005f56;
        }

        .btn-delete {
            background-color: #e53935;
            color: #fff;
        }

        .btn-delete:hover {
            background-color: #b71c1c;
        }

    </style>
</head>
<body>


<div class="oferte-container">
    <?php foreach ($oferte as $oferta): ?>
        <div class="oferta-card">
            <?php if (!empty($oferta['link'])): ?>
                <img src="../<?= htmlspecialchars($oferta['link']) ?>" alt="Ofertă" class="oferta-img">
            <?php else: ?>
                <img src="../assets/img/imagine_banner.jpg" alt="Default" class="oferta-img">
            <?php endif; ?>

            <div class="oferta-content">
                <div class="oferta-titlu"><?= htmlspecialchars($oferta['titlu']) ?></div>
                <div class="oferta-descriere"><?= nl2br(htmlspecialchars($oferta['descriere'])) ?></div>
                <?php if (!empty($oferta['data_inceput']) || !empty($oferta['data_sfarsit'])): ?>
                    <div class="oferta-perioada">
                        Valabil:
                        <?= $oferta['data_inceput'] ? date('d.m.Y', strtotime($oferta['data_inceput'])) : '...' ?>
                        -
                        <?= $oferta['data_sfarsit'] ? date('d.m.Y', strtotime($oferta['data_sfarsit'])) : '...' ?>
                    </div>
                <?php endif; ?>

                <div class="oferta-actiuni">
                    <a href="editare_oferte.php?id=<?= $oferta['id'] ?>" class="btn-edit">Editează</a>
                    <a href="stergere_oferte.php?id=<?= $oferta['id'] ?>" class="btn-delete" onclick="return confirm('Ești sigur că vrei să ștergi această ofertă?');">Șterge</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
