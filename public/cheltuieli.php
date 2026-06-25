<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'user') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['sterge']) && is_numeric($_GET['sterge'])) {
    $stmt = $pdo->prepare("DELETE FROM tranzactie WHERE id = ? AND utilizator_id = ?");
    $stmt->execute([$_GET['sterge'], $_SESSION['user_id']]);

    header("Location: cheltuieli.php");
    exit;
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

// Pie chart
$stmt = $pdo->prepare("
    SELECT c.nume AS categorie, SUM(t.suma) AS total
    FROM tranzactie t
    JOIN categorie c ON c.id = t.categorie_id
    WHERE t.utilizator_id = ?
      AND t.tip = 'cheltuiala'
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

// tabel istoric detaliat
$stmtDetalii = $pdo->prepare("
    SELECT t.id, t.data_tranzactie, c.nume AS categorie, t.suma, t.descriere
    FROM tranzactie t
    JOIN categorie c ON c.id = t.categorie_id
    WHERE t.utilizator_id = ?
      AND t.tip = 'cheltuiala'
      AND DATE_FORMAT(t.data_tranzactie, '%Y-%m') = ?
    ORDER BY t.data_tranzactie DESC
");
$stmtDetalii->execute([$user_id, $selectat]);
$cheltuieliDetaliate = $stmtDetalii->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Cheltuieli</title>
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
        }

        .right-section {
            flex: 2;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
        }

        .card h2 {
            margin-bottom: 15px;
            color: #333;
        }

        select {
            padding: 10px 14px;
            border-radius: 12px;
            border: 1px solid #ccc;
            font-size: 15px;
            width: 100%;
        }

        .chart-container {
            position: relative;
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
        }

        #cheltuieliChart {
            width: 100% !important;
            height: auto !important;
        }

        .chart-total {
    position: absolute;
    top: 43%; /* Îl centrează perfect în golul graficului */
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 18px;
    font-weight: 700;
    color: #333;
    text-align: center;
    width: 80%;
    pointer-events: none;
        }

        .btn {
            background-color: #e65100;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 12px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            width: 100%;
        }

        .btn:hover {
            background-color: #bf360c;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }

        th {
            background-color: #ffe0b2;
        }

        @media (max-width: 1024px) {
            .page-container {
                flex-direction: column;
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<div class="page-container">
    <!-- sectiunea stanga -->
    <div class="left-section">
<div class="card">
            <h2>Cheltuieli luna <?= htmlspecialchars($lunaAfisata) ?></h2>
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
                <canvas id="cheltuieliChart"></canvas>
                <div class="chart-total"><?= number_format($total, 2) ?> lei</div>
            </div>
        </div>

        <div class="card">
            <a href="adauga_cheltuiala.php" class="btn">Adaugă o cheltuială</a>
        </div>
    </div>

    <!-- sectiunea dreapta -->
    <div class="right-section">
        <h3>Istoric Cheltuieli</h3>

        <?php if ($cheltuieliDetaliate): ?>
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
            <?php foreach ($cheltuieliDetaliate as $c): ?>
                <tr>
                    <td><?= date('d.m.Y', strtotime($c['data_tranzactie'])) ?></td>
                    <td><?= htmlspecialchars($c['categorie']) ?></td>
                    <td><?= number_format($c['suma'], 2) ?></td>
                    <td><?= htmlspecialchars($c['descriere']) ?></td>
                    <td>
                        <a href="?sterge=<?= $c['id'] ?>" onclick="return confirm('Ești sigur că vrei să ștergi această cheltuială?')"
                           style="color: red; font-weight: bold; text-decoration: none;">Șterge</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Nu există cheltuieli înregistrate pentru luna selectată.</p>
<?php endif; ?>
    </div>
</div>

<script>
    const ctx = document.getElementById('cheltuieliChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                data: <?= json_encode($values) ?>,
                backgroundColor: ['#f44336', '#ff7043', '#ffca28', '#ff8a65', '#ff5722']
            }]
        },
        options: {
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 20,
                        padding: 15
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.formattedValue + ' lei';
                        }
                    }
                },
                cutout: '70%'
            }
        }
    });
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html>

