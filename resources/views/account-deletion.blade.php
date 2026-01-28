<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Demande de suppression de compte - Weetoo">
    <title>Demande de suppression de compte - Weetoo</title>
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
        }
        h1 {
            color: #6366f1;
            margin-bottom: 10px;
            font-size: 2em;
        }
        .warning-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            margin: 20px 0;
            border-radius: 6px;
        }
        .warning-box h3 {
            color: #92400e;
            margin-top: 0;
            margin-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }
        input[type="email"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 16px;
            font-family: inherit;
        }
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        input[type="email"]:focus,
        textarea:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 20px 0;
        }
        .checkbox-group input[type="checkbox"] {
            margin-top: 4px;
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        .checkbox-group label {
            margin: 0;
            font-weight: normal;
            cursor: pointer;
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
        }
        .btn:hover {
            background: #4f46e5;
        }
        .btn-danger {
            background: #ef4444;
        }
        .btn-danger:hover {
            background: #dc2626;
        }
        .btn-secondary {
            background: #6b7280;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
        .error {
            color: #ef4444;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 15px;
            margin: 20px 0;
            border-radius: 6px;
            color: #065f46;
        }
        .back-link {
            margin-top: 20px;
            display: inline-block;
            color: #6366f1;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
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
        <h1>🗑️ Suppression de compte</h1>
        
        @if(session('error'))
            <div class="error" style="background: #fee2e2; border-left: 4px solid #ef4444; padding: 15px; margin: 20px 0; border-radius: 6px; color: #991b1b;">
                {{ session('error') }}
            </div>
        @endif

        <div class="warning-box">
            <h3>⚠️ Attention</h3>
            <p>La suppression de votre compte est <strong>irréversible</strong>. Toutes vos données seront définitivement supprimées, notamment :</p>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li>Votre profil et informations personnelles</li>
                <li>Vos photos de profil</li>
                <li>Votre historique de recherche</li>
                <li>Vos demandes de couple</li>
                <li>Vos notifications</li>
                <li>Toutes autres données associées à votre compte</li>
            </ul>
            <p style="margin-top: 15px; margin-bottom: 0;"><strong>Si vous êtes en couple, votre partenaire sera automatiquement délié et son statut passera à "Célibataire".</strong></p>
        </div>

        <form method="POST" action="{{ route('account-deletion.submit') }}">
            @csrf

            <div class="form-group">
                <label for="email">Adresse email de votre compte *</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required
                    placeholder="votre.email@exemple.com"
                >
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="reason">Raison de la suppression (optionnel)</label>
                <textarea 
                    id="reason" 
                    name="reason" 
                    placeholder="Dites-nous pourquoi vous souhaitez supprimer votre compte. Cela nous aide à améliorer notre service."
                >{{ old('reason') }}</textarea>
                @error('reason')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="checkbox-group">
                <input 
                    type="checkbox" 
                    id="confirmation" 
                    name="confirmation" 
                    value="1"
                    required
                >
                <label for="confirmation">
                    Je comprends que la suppression de mon compte est <strong>irréversible</strong> et que toutes mes données seront définitivement supprimées. Je confirme ma demande de suppression. *
                </label>
            </div>
            @error('confirmation')
                <div class="error">{{ $message }}</div>
            @enderror

            <div style="margin-top: 30px; display: flex; gap: 15px; flex-wrap: wrap;">
                <button type="submit" class="btn btn-danger">
                    Confirmer la suppression
                </button>
                <a href="{{ route('privacy') }}" class="btn btn-secondary">
                    Annuler
                </a>
            </div>
        </form>

        <p style="margin-top: 30px; font-size: 0.9em; color: #6b7280;">
            Vous pouvez également nous contacter directement par email à 
            <a href="mailto:sangolgalanga@gmail.com?subject=Demande de suppression de compte">sangolgalanga@gmail.com</a>
        </p>
    </div>
</body>
</html>


