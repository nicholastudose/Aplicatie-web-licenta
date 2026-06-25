<?php
if (!isset($_SESSION)) session_start();
require_once __DIR__ . '/config.php';

$poza = '../assets/img/default.png';

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT poza FROM utilizator WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($user['poza']) && file_exists('../' . $user['poza'])) {
        $poza = '../' . $user['poza'];
    }
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
        <a href="admin_dashboard.php"><img src="../assets/img/Budgi.png" alt="Budgi"></a>
    </div>

    <div class="nav-actions">
        <nav class="nav-links">
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="oferte_admin.php">Oferte</a>
            <a href="adauga_oferta.php">Adaugare oferte</a>
        </nav>

        <div class="burger" onclick="toggleMenu(this)">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <div class="profil">
            <a href="profil_admin.php">
                <img src="<?= htmlspecialchars($poza) ?>" alt="Profil">
            </a>
        </div>
    </div>

    <div class="dropdown" id="dropdownMenu">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="oferte_admin.php">Oferte</a>
        <a href="adauga_oferta.php">Adaugare oferte</a>
    </div>
</header>

<script>
    function toggleMenu(el) {
        const dropdown = document.getElementById('dropdownMenu');
        dropdown.style.display = dropdown.style.display === 'flex' ? 'none' : 'flex';
    }
</script>