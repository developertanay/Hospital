<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · {{ config('branding.name') }}</title>
    @include('branding.meta')
    <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/login.css') }}" rel="stylesheet">
</head>
<body class="hospital-login">
    <main class="login-shell">
        <section class="login-intro" aria-labelledby="intro-title">
            <div class="login-brand"><img src="{{ asset(config('branding.logo')) }}" width="44" height="44" alt=""><span>{{ config('branding.name') }}<small>Care management portal</small></span></div>
            <div class="intro-content">
                <div class="care-symbol" aria-hidden="true"><i class="bx bx-plus-medical"></i></div>
                <p class="intro-eyebrow">Connected care, simpler management</p>
                <h1 id="intro-title">Your care.<br> Your workspace.</h1>
                <p class="intro-description">Manage hospital resources and stay connected to the care that matters.</p>
                <div class="intro-features">
                    <div><span class="feature-icon"><i class="bx bx-buildings" aria-hidden="true"></i></span><span><strong>Hospital resources</strong><small>Keep departments and inventory organised.</small></span></div>
                    <div><span class="feature-icon"><i class="bx bx-car" aria-hidden="true"></i></span><span><strong>Care coordination</strong><small>Manage transport and follow request progress.</small></span></div>
                </div>
            </div>
            <p class="intro-footnote">One portal. A workspace assigned to your role.</p>
        </section>
        <section class="login-form-panel" aria-labelledby="login-title">
            <div class="login-form-content">
                <span class="welcome-label">WELCOME BACK</span>
                <h2 id="login-title">Sign in to your account</h2>
                <p class="login-description">Enter your details to access your workspace.</p>
                @if(session('status'))<div class="login-message" role="status">{{ session('status') }}</div>@endif
                @if($errors->any())
                    <div class="login-error" role="alert"><i class="bx bx-error-circle" aria-hidden="true"></i><div>@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div></div>
                @endif
                <form method="POST" action="{{ route('login') }}" id="login-form">
                    @csrf
                    <div class="login-field">
                        <label for="email">Email / User ID</label>
                        <div class="input-wrap"><i class="bx bx-user" aria-hidden="true"></i><input id="email" name="email" type="text" value="{{ old('email') }}" placeholder="Enter your email or user ID" required autocomplete="username" autocapitalize="none" spellcheck="false" @error('email') aria-invalid="true" @enderror></div>
                    </div>
                    <div class="login-field">
                        <div class="password-label"><label for="password">Password</label><a href="{{ url('forgot_password') }}">Forgot password?</a></div>
                        <div class="input-wrap"><i class="bx bx-lock-alt" aria-hidden="true"></i><input id="password" name="password" type="password" placeholder="Enter your password" required autocomplete="current-password" @error('password') aria-invalid="true" @enderror><button class="password-toggle" id="password-toggle" type="button" aria-label="Show password" aria-pressed="false" aria-controls="password"><i class="bx bx-show" aria-hidden="true"></i></button></div>
                    </div>
                    <button class="login-submit" id="login-submit" type="submit"><span id="submit-label">Sign in</span><i class="bx bx-right-arrow-alt" aria-hidden="true"></i></button>
                </form>
                <div class="login-help"><span class="help-icon"><i class="bx bx-help-circle" aria-hidden="true"></i></span><p>Need access?<br><span>Contact your administrator for an account or hospital assignment.</span></p></div>
            </div>
            <footer>© {{ now()->year }} {{ config('branding.name') }}. All rights reserved.</footer>
        </section>
    </main>
    <script src="{{ asset('assets/js/login.js') }}"></script>
</body>
</html>
