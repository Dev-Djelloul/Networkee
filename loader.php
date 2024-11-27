<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../styles/style.css">
    <title>Networkee - Chargement</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color:#445c56;
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
        }

        .loader-container {
            text-align: center;
            color: dodgerblue;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            margin-right: 50px;
        }

        .logo img {
            width: 40px;
            height: 40px;
            margin-right: 5px;
            margin-bottom: 5px;
        }

        .loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        .redirect-message {
            margin-top: 20px;
            font-size: large;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="loader-container">
        <div class="logo">
            <!-- Titre avec logo -->
        <a><img src="/networkee/icons/icons8-artificial-intelligence-48.png"
            alt="Logo loader">
            Networkee</a>
        </div>
        <div class="loader"></div>
        <p class="redirect-message">Chargement...</p>
    </div>

    <!-- Redirection vers la page principale -->
    <script>
        setTimeout(() => {
            window.location.href = "/networkee/main.php"; // Redirige vers la page principale après 3 secondes
        }, 1500);
    </script>
</body>
</html>
