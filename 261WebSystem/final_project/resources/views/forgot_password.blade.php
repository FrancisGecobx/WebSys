<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Prefect of Discipline</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/pod-styles.css') }}">
</head>
<body class="auth-page">

    <div class="auth-card">
        <div class="brand-header">
            <img src="{{ asset('ac_logo.png') }}" alt="Aldersgate College Logo">
            <h1>Reset Your Password</h1>
            <p>Enter the Gmail address linked to your student or staff account and we will send you a temporary password.</p>
        </div>

        @if($errors->any())
            <div class="alert-error">⚠️ {{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="form-group">
                <label for="email">Registered Gmail / Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="e.g. juan.delacruz@gmail.com" required autofocus>
            </div>

            <button type="submit" class="btn-submit auth-btn-full">Send Temporary Password</button>
        </form>

        <a href="/login" class="back-link">← Back to Login</a>
    </div>

</body>
</html>