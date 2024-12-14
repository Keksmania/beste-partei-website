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
        <h1>Email Verification</h1>
        <p>Hello {{ $name }},</p>
        <p>Thank you for registering. Please click the button below to verify your email address:</p>
        <a href="{{ $verificationUrl }}" class="btn">Verify Email</a>
        <p>If the button doesn't work, you can also click the following link:</p>
        <p><a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a></p>
    </div>
</body>
</html>
