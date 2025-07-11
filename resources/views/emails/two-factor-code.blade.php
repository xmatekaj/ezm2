<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kod weryfikacyjny</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #f0f0f0;
        }
        .code-container {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        .code {
            font-size: 36px;
            font-weight: bold;
            color: #007bff;
            letter-spacing: 5px;
            margin: 10px 0;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            font-size: 14px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <h2>Kod weryfikacyjny</h2>
    </div>

    <p>Witaj!</p>
    
    <p>Otrzymujesz ten email, ponieważ ktoś próbuje zalogować się na Twoje konto używając uwierzytelniania dwuskładnikowego.</p>

    <div class="code-container">
        <p><strong>Twój kod weryfikacyjny:</strong></p>
        <div class="code">{{ $code }}</div>
        <p><small>Kod jest ważny przez 15 minut</small></p>
    </div>

    <div class="warning">
        <strong>Uwaga bezpieczeństwa:</strong>
        <ul>
            <li>Nigdy nie udostępniaj tego kodu nikomu</li>
            <li>Jeśli to nie Ty próbujesz się zalogować, zignoruj ten email</li>
            <li>W razie wątpliwości, zmień hasło do swojego konta</li>
        </ul>
    </div>

    <p>Jeśli masz problemy z logowaniem, skontaktuj się z administratorem systemu.</p>

    <div class="footer">
        <p>
            Ten email został wysłany automatycznie. Prosimy nie odpowiadać na tę wiadomość.<br>
            © {{ date('Y') }} {{ config('app.name') }}. Wszystkie prawa zastrzeżone.
        </p>
    </div>
</body>
</html>