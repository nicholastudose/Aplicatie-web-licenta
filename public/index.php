<?php include '../includes/header_guest.php'; ?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Acasă</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding-top: 100px;
        }
        .container {
            max-width: 1100px;
            margin: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        .card {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
        }
        .card h1 {
            font-size: 30px;
            color: #004d40;
            margin-bottom: 20px;
        }
        .card p {
            font-size: 18px;
            color: #555;
            margin-bottom: 30px;
        }
        .card .btn {
            padding: 14px 28px;
            background-color: #00796b;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .card .btn:hover {
            background-color: #004d40;
            transform: translateY(-2px);
        }
        .feature-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .feature-card h2 {
            font-size: 24px;
            color: #00796b;
            margin-bottom: 20px;
        }
        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
            font-size: 16px;
        }
        .feature-item {
            background: #e0f2f1;
            padding: 12px 18px;
            border-radius: 12px;
            color: #004d40;
        }
        .preview-card {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            text-align: center;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
        }
        .preview-img {
            width: 100%;
            max-width: 900px;
            border: 2px solid #ddd;
            border-radius: 12px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <h1>Studenție cu stil și cu bani în cont.<br>Administrează-ți bugetul ca un pro!</h1>
        <p>Noi te ajutăm să scoți maximul din portofelul tău</p>
        <a href="register1.php" class="btn">Înregistrează-te</a>
    </div>

    <div class="feature-card">
        <h2>Ce îți oferă aplicația noastră?</h2>
        <div class="feature-list">
            <div class="feature-item">Grafice intuitive pentru cheltuieli și venituri</div>
            <div class="feature-item">Istoric organizat pe luni și categorii</div>
            <div class="feature-item">Notificări și recomandări pentru un buget echilibrat</div>
        </div>
    </div>

    <div class="preview-card">
        <img src="../assets/img/dashboard.png" alt="Preview aplicație" class="preview-img">
        <img src="../assets/img/dashboard2.png" alt="Preview aplicație" class="preview-img">
    </div>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>