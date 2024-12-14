<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #ffffff;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 20px 0;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>E-Mail-Verifizierung</h1>
        <p>Hallo {{ $name }},</p>
        <p>Vielen Dank für die Registrierung. Bitte klicken Sie auf den untenstehenden Button, um Ihre E-Mail-Adresse zu verifizieren:</p>
        <a href="{{ $verificationUrl }}" class="btn">E-Mail verifizieren</a>
        <p>Falls der Button nicht funktioniert, können Sie auch den folgenden Link verwenden:</p>
        <p><a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a></p>
    </div>
</body>
</html>
