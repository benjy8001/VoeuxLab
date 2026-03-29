<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Erreur serveur — Vœux de cérémonie</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            background-color: #fafaf8;
            color: #44403c;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .container {
            text-align: center;
            max-width: 480px;
        }

        .ornament {
            font-size: 2rem;
            color: #b45309;
            margin-bottom: 1.5rem;
            letter-spacing: 0.5em;
        }

        .code {
            font-size: 6rem;
            font-weight: 300;
            color: #d6ccc0;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 400;
            color: #44403c;
            margin-bottom: 1rem;
            font-style: italic;
        }

        p {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #78716c;
            font-size: 0.9375rem;
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        a {
            display: inline-block;
            padding: 0.75rem 2rem;
            background-color: #b45309;
            color: #fff;
            text-decoration: none;
            border-radius: 0.5rem;
            font-family: 'Helvetica Neue', Arial, sans-serif;
            font-size: 0.875rem;
            transition: background-color 0.2s;
        }

        a:hover {
            background-color: #92400e;
        }

        footer {
            margin-top: 4rem;
            font-family: 'Helvetica Neue', Arial, sans-serif;
            font-size: 0.75rem;
            color: #a8a29e;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="ornament">✦ ✦ ✦</div>
        <div class="code">500</div>
        <h1>Quelque chose s'est mal passé</h1>
        <p>
            Une erreur inattendue s'est produite de notre côté.<br>
            Vos vœux sont en sécurité — veuillez réessayer dans quelques instants.
        </p>
        <a href="/">Retour à l'accueil</a>
    </div>
    <footer>Vœux de cérémonie laïque ✦ {{ date('Y') }}</footer>
</body>
</html>
