<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'user') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['sterge']) && is_numeric($_GET['sterge'])) {
    $stmt = $pdo->prepare("DELETE FROM tranzactie WHERE id = ? AND utilizator_id = ? AND tip = 'venit'");
    $stmt->execute([$_GET['sterge'], $user_id]);

    header("Location: venituri.php");
    exit();
}

$luni_raw = [];
for ($i = 0; $i < 4; $i++) {
    $luni_raw[] = date('Y-m', strtotime("-{$i} month"));
}

$selectat = $_GET['luna'] ?? date('Y-m');

if (!in_array($selectat, $luni_raw)) {
    $selectat = date('Y-m');
}

$formatter = new IntlDateFormatter('ro_RO', IntlDateFormatter::LONG, IntlDateFormatter::NONE, null, null, 'LLLL yyyy');
$lunaAfisata = ucfirst($formatter->format(new DateTime($selectat . "-01")));

$stmt = $pdo->prepare("
    SELECT c.nume AS categorie, SUM(t.suma) AS total
    FROM tranzactie t
    JOIN categorie c ON c.id = t.categorie_id
    WHERE t.utilizator_id = ?
      AND t.tip = 'venit'
      AND DATE_FORMAT(t.data_tranzactie, '%Y-%m') = ?
    GROUP BY c.nume
");
$stmt->execute([$user_id, $selectat]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$values = [];
$total = 0;

foreach ($data as $row) {
    $labels[] = $row['categorie'];
    $values[] = (float)$row['total'];
    $total += $row['total'];
}

//istoric detaliat venituri
$stmtDetalii = $pdo->prepare("
    SELECT t.id, t.data_tranzactie, c.nume AS categorie, t.suma, t.descriere
    FROM tranzactie t
    JOIN categorie c ON c.id = t.categorie_id
    WHERE t.utilizator_id = ?
      AND t.tip = 'venit'
      AND DATE_FORMAT(t.data_tranzactie, '%Y-%m') = ?
    ORDER BY t.data_tranzactie DESC
");
$stmtDetalii->execute([$user_id, $selectat]);
$venituriDetaliate = $stmtDetalii->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venituri</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            background-color: #f7f7f7;
            padding-top: 0px;
        }

        .page-container {
            display: flex;
            flex-wrap: wrap;
            max-width: 1400px;
            margin: 100px auto 0;
            padding: 20px;
            gap: 40px;
        }

        .left-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 30px;
            min-width: 320px;
        }

        .right-section {
            flex: 2;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            overflow-x: auto;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            text-align: center;
            box-sizing: border-box;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.08);
        }

        .card h2 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
            font-weight: 700;
        }

        select {
            padding: 12px 14px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 15px;
            font-family: inherit;
            width: 100%;
        }

        .chart-container {
            position: relative;
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
        }

        .chart-total {
            position: absolute;
            top: 45%; 
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 18px;
            font-weight: 700;
            color: #004d40;
            text-align: center;
            width: 80%;
            pointer-events: none;
        }

        .btn {
            display: block;
            background-color: #00796b;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            box-sizing: border-box;
            width: 100%;
            transition: background 0.3s;
        }

        .btn:hover {
            background-color: #004d40;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 14px;
            border-bottom: 1px solid #eee;
            text-align: center;
            font-size: 15px;
        }

        th {
            background-color: #e0f2f1;
            color: #00796b;
            font-weight: 600;
        }

        .sterge-link {
            color: #c62828;
            font-weight: 600;
            text-decoration: none;
        }
        .sterge-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 1024px) {
            .page-container {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>
<body>

<div class="page-container">
    <div class="left-section">
        <div class="card">
            <h2>Venituri luna <?= htmlspecialchars($lunaAfisata) ?></h2>
            <form method="get">
                <select name="luna" onchange="this.form.submit()">
                    <?php foreach ($luni_raw as $luna): ?>
                        <option value="<?= $luna ?>" <?= $luna === $selectat ? 'selected' : '' ?>>
                            <?= ucfirst($formatter->format(new DateTime($luna . "-01"))) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <div class="card">
            <div class="chart-container">
                <canvas id="venituriChart"></canvas>
                <div class="chart-total"><?= round($total) ?> lei</div>
            </div>
        </div>

        <div class="card">
            <a href="adauga_venit.php" class="btn">Adaugă un venit</a>
        </div>
    </div>

    <div class="right-section">
        <h3 style="color:#00796b; font-weight:700; margin-top:0;">Istoric Venituri</h3>

        <?php if (!empty($venituriDetaliate)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Categorie</th>
                        <th>Suma (lei)</th>
                        <th>Descriere</th>
                        <th>Acțiune</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($venituriDetaliate as $v): ?>
                        <tr>
                            <td><?= date('d.m.Y', strtotime($v['data_tranzactie'])) ?></td>
                            <td><?= htmlspecialchars($v['categorie']) ?></td>
                            <td><strong><?= round($v['suma']) ?> lei</strong></td>
                            <td><?= htmlspecialchars($v['descriere'] ?: '-') ?></td>
                            <td>
                                <a class="sterge-link" href="?sterge=<?= $v['id'] ?>" onclick="return confirm('Ești sigur că vrei să ștergi acest venit?')">Șterge</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color:#666; margin-top:20px;">Nu există venituri înregistrate pentru luna selectată.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    const ctx = document.getElementById('venituriChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                data: <?= json_encode($values) ?>,
                backgroundColor: ['#66bb6a', '#26a69a', '#42a5f5', '#ffca28']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, font: { family: 'Montserrat' }, padding: 15 }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.label + ': ' + context.raw + ' lei';
                        }
                    }
                }
            },
            cutout: '70%'
        }
    });
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html>