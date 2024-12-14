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
        <h1>Passwort zurücksetzen</h1>
        <p>Hallo {{ $name }},</p>
        <p>Sie haben angefragt, Ihr Passwort zurückzusetzen. Klicken Sie auf den untenstehenden Button, um ein neues Passwort festzulegen:</p>
        <a href="{{ $reset_url }}" class="btn">Passwort zurücksetzen</a>
        <p>Falls der Button nicht funktioniert, können Sie auch den folgenden Link verwenden:</p>
        <p><a href="{{ $reset_url }}">{{ $reset_url }}</a></p>
    </div>
</body>
</html>
