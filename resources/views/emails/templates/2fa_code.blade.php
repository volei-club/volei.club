<!DOCTYPE html>
<html>
<head>
    <title>Cod de verificare 2FA</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <div style="max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #eaeaea; border-radius: 10px;">
        <h2 style="color: #1e40af; text-align: center;">Autentificare Volei.Club</h2>
        <p>Salut,</p>
        <p>Pentru a finaliza procesul de autentificare, te rugăm să introduci următorul cod de verificare în browser-ul tău:</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <span style="font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #1e40af; background: #f8fafc; padding: 10px 20px; border-radius: 8px; border: 1px solid #e2e8f0;">{{ $code }}</span>
        </div>
        
        <p style="font-size: 14px; color: #64748b;">Acest cod este valabil timp de 10 minute. Dacă nu ai solicitat acest cod, poți ignora acest email.</p>
        <hr style="border: none; border-top: 1px solid #eaeaea; margin-top: 30px; margin-bottom: 20px;">
        <p style="font-size: 12px; color: #94a3b8; text-align: center;">Echipa Volei.Club</p>
    </div>
</body>
</html>
