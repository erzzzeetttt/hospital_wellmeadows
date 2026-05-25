<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | WellMeadows</title>

    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>

    <div class="auth-wrapper">
        <div class="auth-card">

            <div class="logo">
                <span class="logo-icon">⌁</span>
                <span class="logo-text">WellMeadows</span>
            </div>

            <h2>Set New Password</h2>
            <p class="subtitle">Enter and confirm your new password</p>

            @if ($errors->any())
                <div class="alert-error" style="background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; padding:12px 16px; border-radius:6px; margin-bottom:20px; font-size:14px;">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('password.store') }}" method="POST">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $request->email) }}"
                        placeholder="your.email@hospital.com"
                        required
                        autofocus
                        autocomplete="username"
                    >
                </div>

                <div class="form-group">
                    <label for="password">New Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter new password"
                        required
                        autocomplete="new-password"
                    >
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="Confirm new password"
                        required
                        autocomplete="new-password"
                    >
                </div>

                <button type="submit" class="btn-primary">Reset Password</button>
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
