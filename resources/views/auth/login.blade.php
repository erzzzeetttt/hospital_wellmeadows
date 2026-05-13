<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | WellMeadows</title>

    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>

    <div class="auth-wrapper">
        <div class="auth-card">

            <div class="logo">
                <span class="logo-icon">⌁</span>
                <span class="logo-text">WellMeadows</span>
            </div>

            <h2>Welcome Back</h2>
            <p class="subtitle">Sign in to access your account</p>

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="your.email@hospital.com"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <div class="form-options">
                    <label class="remember">
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>

                    <a href="#">Forgot password?</a>
                </div>

                <button type="submit" class="btn-primary">Sign In</button>
            </form>

            <p class="auth-link">
                Don’t have an account?
                <a href="{{ route('register') }}">Register here</a>
            </p>

            <a href="{{ url('/') }}" class="back-link">← Back to home</a>
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