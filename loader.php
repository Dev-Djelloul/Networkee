<?php $baseUrl = './'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <title>Networkee — Chargement</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: #f8fafc;
        }
        .loader-container {
            text-align: center;
        }
        .loader-logo {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 1.5rem;
        }
        .loader-logo .logo-mark {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
            font-size: 1.25rem;
        }
        .loader {
            width: 48px;
            height: 48px;
            border: 4px solid #e2e8f0;
            border-top-color: #0d9488;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .redirect-message {
            margin-top: 1.25rem;
            font-size: 0.9375rem;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="loader-container">
        <div class="loader-logo">
            <div class="logo-mark">N</div>
            <span>Networkee</span>
        </div>
        <div class="loader"></div>
        <p class="redirect-message">Chargement...</p>
    </div>

    <script>
        setTimeout(() => {
            window.location.href = "main.php";
        }, 1500);
    </script>
</body>
</html>
