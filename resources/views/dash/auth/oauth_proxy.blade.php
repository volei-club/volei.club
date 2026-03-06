<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Sincronizare Login...</title>
    <style>
        body {
            background-color: #f6f6f8;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: sans-serif;
            color: #1e3fae;
        }
        .loader {
            border: 4px solid #e2e8f0;
            border-top: 4px solid #1e3fae;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="loader"></div>
        <p>Finalizare securizare sesiune...</p>
    </div>

    <script>
        // Preia token-ul primit via Blade
        const apiToken = "{{ $token }}";
        
        if (apiToken) {
            // Sincronizează în memoria locală (ca un SPA complet)
            localStorage.setItem('auth_token', apiToken);
            // Șterge eventale rămășițe temporare de 2FA
            sessionStorage.removeItem('2fa_user_id');
            // Redirecționează cu Javascript spre Dashboard pentru a declanșa logica API-Driven
            window.location.replace('/{{ app()->getLocale() }}/dash');
        } else {
            window.location.replace('/{{ app()->getLocale() }}/dash/login');
        }
    </script>
</body>
</html>
