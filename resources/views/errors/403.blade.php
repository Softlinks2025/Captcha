<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden</title>
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <style>
        body { background: #f8fafc; color: #1e293b; font-family: 'Inter', sans-serif; }
        .centered { min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .error-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(30,41,59,0.09); padding: 48px 32px; max-width: 400px; text-align: center; }
        .error-code { font-size: 4rem; font-weight: 700; color: #ef4444; margin-bottom: 12px; }
        .error-message { font-size: 1.3rem; font-weight: 500; margin-bottom: 18px; }
        .btn-home { background: #3b82f6; color: #fff; border-radius: 8px; padding: 12px 28px; font-weight: 600; text-decoration: none; transition: background 0.2s; }
        .btn-home:hover { background: #2563eb; }
    </style>
</head>
<body>
    <div class="centered">
        <div class="error-card">
            <div class="error-code">403</div>
            <div class="error-message">You do not have permission to access this page.</div>
            <a href="{{ route('admin.dashboard') }}" class="btn-home">Go to Dashboard</a>
        </div>
    </div>
</body>
</html> 