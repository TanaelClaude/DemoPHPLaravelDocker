<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créer un compte · Demo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <main class="auth-card">
        <a class="brand" href="{{ route('register') }}">Demo<span>.</span></a>
        <p class="eyebrow">VOTRE ESPACE COMMENCE ICI</p>
        <h1>Créer un compte</h1>
        <p class="auth-subtitle">Quelques informations suffisent pour vous inscrire.</p>

        @if ($errors->any())
            <div class="form-alert" role="alert">
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ url('/register') }}" class="auth-form">
            @csrf
            <label for="name">Nom</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Votre nom">

            <label for="email">Adresse e-mail</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" placeholder="vous@exemple.fr">

            <label for="password">Mot de passe</label>
            <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" placeholder="8 caractères minimum">

            <label for="password_confirmation">Confirmer le mot de passe</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" placeholder="Répétez le mot de passe">

            <button class="primary-button" type="submit">Créer mon compte <span aria-hidden="true">→</span></button>
        </form>

        <p class="auth-footer">Vous avez déjà un compte ? <a href="{{ route('login') }}">Se connecter</a></p>
    </main>
</body>
</html>
