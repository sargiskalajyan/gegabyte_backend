<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('emails.verify_email_subject') }}</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; }
        .container { background: white; padding: 20px; border-radius: 8px; }
        .code { font-size: 32px; font-weight: bold; text-align: center; margin: 20px 0; }
    </style>
</head>
<body>
<div class="container">
    <h2>{{ __('emails.verify_email_subject') }}</h2>

    <p>{{ __('emails.verify_email_code_is') }}</p>

    <div class="code">
        {{ $code }}
    </div>

    <p>{{ __('emails.verify_email_footer') }}</p>
</div>
</body>
</html>
