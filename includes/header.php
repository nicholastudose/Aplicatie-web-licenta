<?php
if (!isset($_SESSION)) session_start();
require_once __DIR__ . '/config.php';

$poza = '../assets/img/default.png';

if (isset($_SESSION['poza']) && !empty($_SESSION['poza']) && file_exists('../' . $_SESSION['poza'])) {
    $poza_path = '../' . $_SESSION['poza'];
    $timestamp = filemtime($poza_path);
    $poza = $poza_path . '?v=' . $timestamp;
} elseif (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT poza FROM utilizator WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($user['poza']) && file_exists('../' . $user['poza'])) {
        $poza_path = '../' . $user['poza'];
        $timestamp = filemtime($poza_path);
        $poza = $poza_path . '?v=' . $timestamp;
        $_SESSION['poza'] = $user['poza'];
    }
}

// verif notificarilor necitite
$existaNotificariNecitite = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notificare WHERE utilizator_id = ? AND este_citita = 0");
    $stmt->execute([$_SESSION['user_id']]);
    $existaNotificariNecitite = $stmt->fetchColumn() > 0;
}
?>

<style>
    * {
        box-sizing: border-box;
    }
    .main-header {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        background-color: #000;
        border-bottom: 1px solid #222;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 40px;
        height: 80px;
        z-index: 1000;

    }

    .logo img {
        height: 55px;
    }

    .nav-actions {
        display: flex;
        align-items: center;
        gap: 30px;
    }

    .nav-links {
        display: flex;
        gap: 24px;
    }

    .nav-links a {
        position: relative;
        color: #fff;
        text-decoration: none;
        font-weight: 600;
        font-size: 16px;
        padding: 8px 12px;
        border-radius: 6px;
        transition: background 0.3s, color 0.3s;
    }

    .nav-links a:hover {
        background-color: #00796b;
        color: #fff;
    }

    .nav-links .notificari-link::after {
        content: '';
        position: absolute;
        top: 4px;
        right: 4px;
        width: 10px;
        height: 10px;
        background-color: red;
        border-radius: 50%;
        display: <?= $existaNotificariNecitite ? 'block' : 'none' ?>;
    }

    .profil img {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #00796b;
    }

    .burger {
        display: none;
        flex-direction: column;
        gap: 4px;
        cursor: pointer;
    }

    .burger span {
        width: 24px;
        height: 3px;
        background-color: #fff;
        border-radius: 2px;
        transition: all 0.3s ease;
    }

    .dropdown {
        display: none;
        flex-direction: column;
        position: absolute;
        top: 80px;
        right: 40px;
        background: #000;
        border: 1px solid #333;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        padding: 12px;
    }

    .dropdown a {
        position: relative;
        color: #fff;
        text-decoration: none;
        font-size: 15px;
        padding: 8px 10px;
        border-radius: 6px;
    }

    .dropdown a:hover {
        background-color: #00796b;
        color: #fff;
    }

    .dropdown .notificari-link::after {
        content: '';
        position: absolute;
        top: 6px;
        right: 6px;
        width: 10px;
        height: 10px;
        background-color: red;
        border-radius: 50%;
        display: <?= $existaNotificariNecitite ? 'block' : 'none' ?>;
    }

    @media (max-width: 768px) {
        .nav-links {
            display: none;
        }
        .burger {
            display: flex;
        }
    }
</style>

<header class="main-header">
    <div class="logo">
        <a href="dashboard_utilizator.php"><img src="../assets/img/Budgi.png" alt="Budgi"></a>
    </div>

    <div class="nav-actions">
        <nav class="nav-links">
            <a href="dashboard_utilizator.php">Dashboard</a>
            <a href="cheltuieli.php">Cheltuieli</a>
            <a href="venituri.php">Venituri</a>
            <a href="oferte.php">Oferte</a>
            <a href="notificari.php" class="notificari-link">Notificări</a>
        </nav>

        <div class="burger" onclick="toggleMenu(this)">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <div class="profil">
            <a href="profil.php">
                <img src="<?= htmlspecialchars($poza) ?>" alt="Profil">
            </a>
        </div>
    </div>

    <div class="dropdown" id="dropdownMenu">
        <a href="dashboard_utilizator.php">Dashboard</a>
        <a href="cheltuieli.php">Cheltuieli</a>
        <a href="venituri.php">Venituri</a>
        <a href="oferte.php">Oferte</a>
        <a href="notificari.php" class="notificari-link">Notificări</a>
    </div>
</header>

<script>
    function toggleMenu(el) {
        const dropdown = document.getElementById('dropdownMenu');
        dropdown.style.display = dropdown.style.display === 'flex' ? 'none' : 'flex';
    }
</script>