<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'user') {
    header('Location: login.php');
    exit;
}

$formatter = new IntlDateFormatter('ro_RO', IntlDateFormatter::LONG, IntlDateFormatter::NONE, null, null, 'LLLL');
$months = [];
for ($i = 0; $i < 3; $i++) {
    $months[] = date('Y-m', strtotime("-{$i} month"));
}
$selectat = $_GET['luna'] ?? date('Y-m');
$lunaAfisata = ucfirst($formatter->format(new DateTime($selectat)));

function get_total($pdo, $user_id, $tip, $luna = null) {
    $query = "SELECT SUM(suma) FROM tranzactie WHERE utilizator_id = ? AND tip = ?";
    $params = [$user_id, $tip];
    if ($luna) {
        $query .= " AND DATE_FORMAT(data_tranzactie, '%Y-%m') = ?";
        $params[] = $luna;
    }
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return round($stmt->fetchColumn() ?: 0);
}
//cardurile kpi cu banii ramasi
$total_venit = get_total($pdo, $_SESSION['user_id'], 'venit', $selectat);
$total_cheltuieli = get_total($pdo, $_SESSION['user_id'], 'cheltuiala', $selectat) + get_total($pdo, $_SESSION['user_id'], 'cheltuiala_fixa', $selectat);
$bani_ramasi = $total_venit - $total_cheltuieli;

// cheltuieli pe sapt
$zile = ['Luni', 'Marti', 'Miercuri', 'Joi', 'Vineri', 'Sambata', 'Duminica'];
$cheltuieli_zilnice = array_fill_keys($zile, 0);
$stmt = $pdo->prepare("SELECT DAYOFWEEK(data_tranzactie) as zi, SUM(suma) as total FROM tranzactie WHERE utilizator_id = ? AND tip = 'cheltuiala' AND DATE_FORMAT(data_tranzactie, '%Y-%m') = ? GROUP BY zi");
$stmt->execute([$_SESSION['user_id'], $selectat]);
foreach ($stmt->fetchAll() as $row) {
    $zi_index = (int)$row['zi'];
    $map = [1=>'Duminica',2=>'Luni',3=>'Marti',4=>'Miercuri',5=>'Joi',6=>'Vineri',7=>'Sambata'];
    $nume_zi = $map[$zi_index];
    $cheltuieli_zilnice[$nume_zi] = round($row['total']);
}

$venit_total = get_total($pdo, $_SESSION['user_id'], 'venit', $selectat);
$recomandari = [];

// regula de impartire a venitului
$limita_nevoi = round($venit_total * 0.5);
$limita_dorinte = round($venit_total * 0.3);
$limita_economii = round($venit_total * 0.2);

$recomandari['Nevoi de bază'] = $limita_nevoi;
$recomandari['Dorințe personale'] = $limita_dorinte;
$recomandari['Economii'] = $limita_economii;

$categorii_nevoi = ['chirie', 'alimentatie', 'utilități', 'transport', 'market', 'educatie'];
$categorii_dorinte = ['shopping', 'restaurant', 'divertisment', 'transferuri'];

// sumele pe categorii dintr-o luna
function get_total_pe_grup($pdo, $user_id, $categorii, $luna) {
if (empty($categorii)) return 0;
    $placeholders = implode(',', array_fill(0, count($categorii), '?'));
    
    $query = "SELECT SUM(t.suma) FROM tranzactie t 
              JOIN categorie c ON c.id = t.categorie_id 
              WHERE t.utilizator_id = ? AND t.tip IN ('cheltuiala', 'cheltuiala_fixa') 
              AND DATE_FORMAT(t.data_tranzactie, '%Y-%m') = ? 
              AND c.nume IN ($placeholders)";
              
    $params = array_merge([$user_id, $luna], $categorii);
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return round($stmt->fetchColumn() ?: 0);
}

$cheltuieli_nevoi = get_total_pe_grup($pdo, $_SESSION['user_id'], $categorii_nevoi, $selectat);
$cheltuieli_dorinte = get_total_pe_grup($pdo, $_SESSION['user_id'], $categorii_dorinte, $selectat);

// top categorii la cheltuieli
$stmt = $pdo->prepare("SELECT c.nume, SUM(t.suma) AS total 
    FROM tranzactie t 
    JOIN categorie c ON c.id = t.categorie_id 
    WHERE t.utilizator_id = ? 
    AND t.tip IN ('cheltuiala', 'cheltuiala_fixa') 
    AND DATE_FORMAT(t.data_tranzactie, '%Y-%m') = ? 
    GROUP BY c.nume");
$stmt->execute([$_SESSION['user_id'], $selectat]);
$istoric = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($istoric)) {
    usort($istoric, fn($a, $b) => $b['total'] <=> $a['total']);
}

// nu uita de notificari si pt impartirea 532
foreach ($istoric as $cat) {
    $categorie_nume = $cat['nume'];
    $cheltuit = $cat['total'];
    $stmtId = $pdo->prepare("SELECT id FROM categorie WHERE nume = ? LIMIT 1");
    $stmtId->execute([$categorie_nume]);
    $categorie_id = $stmtId->fetchColumn();

    if ($categorie_id) {
        $stmtBuget = $pdo->prepare("SELECT suma FROM buget_personalizat WHERE utilizator_id = ? AND categorie_id = ?");
        $stmtBuget->execute([$_SESSION['user_id'], $categorie_id]);
        $buget = $stmtBuget->fetchColumn();

        if ($buget && $cheltuit > $buget) {
            $pdo->prepare("INSERT INTO notificare (utilizator_id, mesaj) VALUES (?, ?) ON DUPLICATE KEY UPDATE mesaj = mesaj")
                ->execute([$_SESSION['user_id'], "Ai depășit limita propusă pentru categoria $categorie_nume"]);
        }

        if (isset($recomandari[$categorie_nume]) && $cheltuit > $recomandari[$categorie_nume]) {
            $pdo->prepare("INSERT INTO notificare (utilizator_id, mesaj) VALUES (?, ?) ON DUPLICATE KEY UPDATE mesaj = mesaj")
                ->execute([$_SESSION['user_id'], "Ai depășit suma recomandată pentru categoria $categorie_nume"]);
        }
    }
}

// grafic pe 3 luni
$evolutie = [];
foreach (array_reverse($months) as $luna) {
    $evolutie[] = [
        'luna' => ucfirst($formatter->format(new DateTime($luna))),
        'venit' => get_total($pdo, $_SESSION['user_id'], 'venit', $luna),
        'cheltuiala' => get_total($pdo, $_SESSION['user_id'], 'cheltuiala', $luna) + get_total($pdo, $_SESSION['user_id'], 'cheltuiala_fixa', $luna)
    ];
}

$alarma = '';
if ($bani_ramasi < $total_venit * 0.2) {
    $alarma = '⚠️ Ai cheltuit peste 80% din veniturile lunii!';
} elseif ($bani_ramasi > $total_venit * 0.4) {
    $alarma = '✅ Ești pe drumul cel bun!';
}

// buget personalizat, adauga cat a cheltuit
$stmt = $pdo->prepare("
    SELECT 
        c.id, c.nume, b.suma AS suma_alocata,
        (
            SELECT SUM(t.suma)
            FROM tranzactie t
            WHERE t.utilizator_id = b.utilizator_id
              AND t.categorie_id = b.categorie_id
              AND t.tip IN ('cheltuiala', 'cheltuiala_fixa')
              AND DATE_FORMAT(t.data_tranzactie, '%Y-%m') = ?
        ) AS suma_cheltuita
    FROM buget_personalizat b
    JOIN categorie c ON c.id = b.categorie_id
    WHERE b.utilizator_id = ?
");
$stmt->execute([$selectat, $_SESSION['user_id']]);
$buget_personal = $stmt->fetchAll(PDO::FETCH_ASSOC);
include '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Financiar</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding-top: 100px;
            font-family: 'Montserrat', sans-serif;
            background-color: #f7f7f7;
        }
        .page-container {
            width: 100%;
            max-width: 1400px;
            margin: auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
        .card {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
        }
        h2, h3 {
            color: #00796b;
        }
        select {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid #00796b;
            font-size: 16px;
        }
        .chart-container {
            width: 100%;
            margin: auto;
        }
        .kpi {
            display: flex;
            gap: 20px;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .kpi .item {
            flex: 1;
            background: #b2dfdb;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            font-weight: bold;
            color: #004d40;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 14px;
            border-bottom: 1px solid #eee;
            text-align: center;
            font-size: 15px;
        }
        th {
            background-color: #00796b;
            color: white;
        }
        ul {
            list-style: none;
            padding: 0;
            font-size: 16px;
        }
        ul li {
            padding: 6px 0;
            border-bottom: 1px solid #eee;
            color: #000000;
        }
        .btn {
            display: inline-block;
            background-color: #00796b;
            color: white;
            padding: 10px 20px;
            margin-top: 15px;
            border-radius: 8px;
            text-decoration: none;
        }
        @media (max-width: 1024px) {
            .page-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="page-container">
    <div>
        <div class="card">
            <h2>Dashboard Financiar - <?= htmlspecialchars($lunaAfisata) ?></h2>
            <form method="get">
                <select name="luna" onchange="this.form.submit()">
                    <?php foreach ($months as $luna): ?>
                        <option value="<?= $luna ?>" <?= $luna == $selectat ? 'selected' : '' ?>>
                            <?= ucfirst($formatter->format(new DateTime($luna))) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <?php if ($alarma): ?><p><strong><?= $alarma ?></strong></p><?php endif; ?>
        </div>

        <div class="card kpi">
            <div class="item">Venituri: <?= $total_venit ?> lei</div>
            <div class="item">Cheltuieli: <?= $total_cheltuieli ?> lei</div>
            <div class="item">Disponibil: <?= $bani_ramasi ?> lei</div>
        </div>

        <div class="card">
            <h3>Cheltuieli pe zilele săptămânii</h3>
            <div class="chart-container">
                <canvas id="ziChart"></canvas>
            </div>
        </div>

        <div class="card">
            <h3>Evoluție venituri și cheltuieli (3 luni)</h3>
            <div class="chart-container">
                <canvas id="evolutieChart"></canvas>
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <h3>Recomandări automate</h3>
            <table>
                <thead><tr><th>Categorie</th><th>Sumă recomandată</th></tr></thead>
                <tbody>
                <?php foreach ($recomandari as $cat => $suma): ?>
                    <tr><td><?= htmlspecialchars($cat) ?></td><td><?= $suma ?> lei</td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h3>Top categorii cheltuieli</h3>
            <ul>
                <?php foreach ($istoric as $c): ?>
                    <li><?= htmlspecialchars($c['nume']) ?>: <?= round($c['total']) ?> lei</li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="card">
            <h3>Buget personalizat</h3>
            <table>
                <thead>
                <tr>
                    <th>Categorie</th>
                    <th>Sumă alocată</th>
                    <th>Cât ai cheltuit</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($buget_personal as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['nume']) ?></td>
                        <td><?= $b['suma_alocata'] ?> lei</td>
                        <td><?= $b['suma_cheltuita'] ? round($b['suma_cheltuita']) : 0 ?> lei</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <a href="editare_buget.php" class="btn">Editează bugetul</a>
        </div>
    </div>
</div>

<!-- scripturi Chart.js -->
<script>
    const ziCtx = document.getElementById('ziChart').getContext('2d');
    new Chart(ziCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($cheltuieli_zilnice)) ?>,
            datasets: [{
                label: 'Cheltuieli zilnice',
                data: <?= json_encode(array_values($cheltuieli_zilnice)) ?>,
                backgroundColor: '#00796b'
            }]
        },
        options: {
            plugins: {
                legend: { display: false },
                title: { display: true, text: 'Distribuție cheltuieli pe săptămână' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    const evolutieCtx = document.getElementById('evolutieChart').getContext('2d');
    new Chart(evolutieCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($evolutie, 'luna')) ?>,
            datasets: [
                {
                    label: 'Venituri',
                    data: <?= json_encode(array_column($evolutie, 'venit')) ?>,
                    borderColor: 'green',
                    fill: false
                },
                {
                    label: 'Cheltuieli',
                    data: <?= json_encode(array_column($evolutie, 'cheltuiala')) ?>,
                    borderColor: 'red',
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                title: { display: true, text: 'Evoluție financiară pe 3 luni' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
<?php include '../includes/footer.php'; ?>
</body>
</html>

