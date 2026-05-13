<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | WellMeadows</title>

    <link rel="stylesheet" href="/css/register.css?v=10">
</head>
<body>

    <div class="auth-wrapper">

        <div class="auth-card">

            <div class="logo">
                <span class="logo-icon">⌁</span>
                <span class="logo-text">WellMeadows</span>
            </div>

            <h2>Create Account</h2>
            <p class="subtitle">
                Register to access the hospital management system
            </p>

            <form action="{{ route('register') }}" method="POST">
                @csrf

                <div class="name-row">

                    <div class="form-group">
                        <label for="first_name">First Name</label>

                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            placeholder="John"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>

                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            placeholder="Doe"
                            required
                        >
                    </div>

                </div>

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
                    <label for="role_id">Role</label>

                    <select name="role_id" id="role_id" required>
                        <option value="">Select your role</option>

                        <option value="1">Administrator</option>
                        <option value="2">Receptionist</option>
                        <option value="3">ChargeNurse</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Create a strong password"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password_confirmation">
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="Re-enter your password"
                        required
                    >
                </div>

                <div class="terms">
                    <input type="checkbox" required>

                    <span>
                        I agree to the Terms of Service and Privacy Policy
                    </span>
                </div>

                <button type="submit" class="btn-primary">
                    Create Account
                </button>

            </form>

            <p class="auth-link">
                Already have an account?
                <a href="{{ route('login') }}">Sign in here</a>
            </p>

            <a href="{{ url('/') }}" class="back-link">
                ← Back to home
            </a>

        </div>

        <footer>
            <p>All data encrypted</p>
        </footer>

    </div>

</body>
</html>