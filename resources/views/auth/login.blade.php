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
            <div class="login-brand"><img src="{{ asset(config('branding.logo')) }}" width="44" height="44" alt=""><span>{{ config('branding.name') }}<small>Care portal</small></span></div>
            <div class="intro-content">
                <div class="care-symbol" aria-hidden="true"><i class="bx bx-plus-medical"></i></div>
                <h1 id="intro-title">Care, together.</h1>
            </div>
        </section>
        <section class="login-form-panel" aria-labelledby="login-title">
            <div class="login-form-content">
                <h2 id="login-title">Sign in</h2>
                <p class="login-description">Welcome back.</p>
                @if(session('status'))<div class="login-message" role="status">{{ session('status') }}</div>@endif
                @if($errors->any())
                    <div class="login-error" role="alert"><i class="bx bx-error-circle" aria-hidden="true"></i><div>@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div></div>
                @endif
                <form method="POST" action="{{ route('login') }}" id="login-form">
                    @csrf
                    <div class="login-field">
                        <label for="email">Email / User ID</label>
                        <div class="input-wrap"><i class="bx bx-user" aria-hidden="true"></i><input id="email" name="email" type="text" value="{{ old('email') }}" placeholder="Email or user ID" required autocomplete="username" autocapitalize="none" spellcheck="false" @error('email') aria-invalid="true" @enderror></div>
                    </div>
                    <div class="login-field">
                        <div class="password-label"><label for="password">Password</label><a href="{{ url('forgot_password') }}">Forgot password?</a></div>
                        <div class="input-wrap"><i class="bx bx-lock-alt" aria-hidden="true"></i><input id="password" name="password" type="password" placeholder="Password" required autocomplete="current-password" @error('password') aria-invalid="true" @enderror><button class="password-toggle" id="password-toggle" type="button" aria-label="Show password" aria-pressed="false" aria-controls="password"><i class="bx bx-show" aria-hidden="true"></i></button></div>
                    </div>
                    <button class="login-submit" id="login-submit" type="submit"><span id="submit-label">Sign in</span><i class="bx bx-right-arrow-alt" aria-hidden="true"></i></button>
                </form>
                <details class="login-help"><summary>Need an account?</summary><p>Contact your administrator.</p></details>
            </div>
            <footer>© {{ now()->year }} {{ config('branding.name') }}</footer>
        </section>
    </main>
    <script src="{{ asset('assets/js/login.js') }}"></script>
</body>
</html>
