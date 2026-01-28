<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Demande de suppression de compte confirmée - Weetoo">
    <title>Demande reçue - Weetoo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        h1 {
            color: #10b981;
            margin-bottom: 20px;
            font-size: 2em;
        }
        .info-box {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 20px;
            margin: 20px 0;
            border-radius: 6px;
            text-align: left;
        }
        .info-box h3 {
            color: #065f46;
            margin-top: 0;
            margin-bottom: 10px;
        }
        .btn {
            display: inline-block;
            padding: 14px 28px;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
            margin-top: 20px;
        }
        .btn:hover {
            background: #4f46e5;
        }
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            h1 {
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">✅</div>
        <h1>Demande reçue</h1>
        
        <div class="info-box">
            <h3>Votre demande de suppression de compte a été enregistrée</h3>
            <p>Nous avons bien reçu votre demande de suppression de compte. Notre équipe va traiter votre demande dans les plus brefs délais.</p>
            <p style="margin-top: 15px;"><strong>Délai de traitement :</strong> Votre compte sera supprimé sous 7 jours ouvrés après vérification de votre identité.</p>
            <p style="margin-top: 15px;"><strong>Prochaines étapes :</strong></p>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li>Vous recevrez un email de confirmation dans les 24 heures</li>
                <li>Nous vérifierons votre identité pour des raisons de sécurité</li>
                <li>Une fois vérifié, votre compte et toutes vos données seront définitivement supprimés</li>
            </ul>
        </div>

        <p style="color: #6b7280; margin-top: 30px;">
            Si vous avez des questions ou souhaitez annuler votre demande, contactez-nous à 
            <a href="mailto:sangolgalanga@gmail.com">sangolgalanga@gmail.com</a>
        </p>

        <a href="{{ route('privacy') }}" class="btn">
            Retour à la politique de confidentialité
        </a>
    </div>
</body>
</html>


