<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f6f6f8; color: #333; padding: 20px; text-align: center; }
        .container { background-color: #ffffff; max-width: 500px; margin: 0 auto; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h2 { color: #1e40af; }
        .btn { display: inline-block; padding: 12px 24px; color: #ffffff !important; background-color: #1e40af; border-radius: 8px; text-decoration: none; font-weight: bold; margin-top: 20px; }
        .footer { margin-top: 30px; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Volei.Club - Resetare Parolă</h2>
        <p>Salut!</p>
        <p>Ai primit acest e-mail deoarece a fost solicitată o resetare de parolă pentru contul tău asociat acestei adrese ({{ $email }}).</p>
        
        <a href="{{ $url }}" class="btn">Resetează Parola</a>

        <p style="margin-top: 25px;">Acest link de resetare va expira în 60 de minute.</p>
        <p>Dacă nu ai solicitat tu această resetare, poți ignora liniștit mesajul.</p>
        
        <div class="footer">
            Echipa Volei.Club
        </div>
    </div>
</body>
</html>
