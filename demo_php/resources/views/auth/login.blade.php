<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion · Demo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <main class="auth-card">
        <a class="brand" href="{{ route('login') }}">Demo<span>.</span></a>
        <p class="eyebrow">RAVI DE VOUS REVOIR</p>
        <h1>Connexion</h1>
        <p class="auth-subtitle">Connectez-vous pour accéder à votre espace.</p>

        @if ($errors->any())
            <div class="form-alert" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ url('/login') }}" class="auth-form">
            @csrf
            <label for="email">Adresse e-mail</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="vous@exemple.fr">

            <label for="password">Mot de passe</label>
            <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Votre mot de passe">

            <label class="remember-row"><input type="checkbox" name="remember" value="1"> <span>Se souvenir de moi</span></label>
            <button class="primary-button" type="submit">Se connecter <span aria-hidden="true">→</span></button>
        </form>

        <p class="auth-footer">Pas encore de compte ? <a href="{{ route('register') }}">Créer un compte</a></p>
    </main>
</body>
</html>
