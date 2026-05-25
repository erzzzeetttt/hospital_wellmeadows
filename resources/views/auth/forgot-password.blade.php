<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | WellMeadows</title>

    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>

    <div class="auth-wrapper">
        <div class="auth-card">

            <div class="logo">
                <span class="logo-icon">⌁</span>
                <span class="logo-text">WellMeadows</span>
            </div>

            <h2>Reset Password</h2>
            <p class="subtitle">Enter your email address and we'll send you a reset link</p>

            @if (session('status'))
                <div class="alert-success" style="background:#d1fae5; border:1px solid #6ee7b7; color:#065f46; padding:12px 16px; border-radius:6px; margin-bottom:20px; font-size:14px;">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert-error" style="background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; padding:12px 16px; border-radius:6px; margin-bottom:20px; font-size:14px;">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="your.email@hospital.com"
                        required
                        autofocus
                    >
                </div>

                <button type="submit" class="btn-primary">Send Reset Link</button>
            </form>

            <p class="auth-link">
                Remember your password?
                <a href="{{ route('login') }}">Back to Login</a>
            </p>

        </div>

        <footer>
            <p>
                By signing in, you agree to our
                <a href="#">Terms of Service</a>
                and
                <a href="#">Privacy Policy</a>
            </p>
            <p>All data encrypted</p>
        </footer>
    </div>

</body>
</html>
