<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}
include '../includes/header_admin.php';
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f7f7f7;
            padding-top:19px;
        }

        .admin-wrapper {
            max-width: 700px;
            margin: 100px auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
            text-align: center;
        }

        h2 {
            margin-bottom: 30px;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #00796b;
            color: white;
            border: none;
            border-radius: 12px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 20px;
        }

        .btn:hover {
            background-color: #006055;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <h2>Bine ai venit în zona de administrare!</h2>
        <p>Poți gestiona ofertele pentru studenți.</p>
        <a href="adauga_oferta.php" class="btn">Adaugă ofertă</a>
    </div>
<?php include '../includes/footer.php'; ?>
</body>
</html>
