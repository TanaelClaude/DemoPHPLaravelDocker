<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Bienvenue · Demo</title>
	@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="welcome-page">
	<header class="welcome-nav">
		<a class="brand" href="{{ route('home') }}">Demo<span>.</span></a>
		<div class="account-actions">
			<button class="delete-account-button" type="button" onclick="document.getElementById('delete-account-dialog').showModal()">Supprimer mon compte</button>
			<form method="POST" action="{{ route('logout') }}">
				@csrf
				<button class="logout-button" type="submit">Se déconnecter</button>
			</form>
		</div>
	</header>
	<main class="welcome-content">
		<p class="eyebrow">ESPACE PERSONNEL</p>
		<h1>Bienvenue, {{ auth()->user()->name }} <span aria-hidden="true">✦</span></h1>
		<p>Vous êtes connecté avec <strong>{{ auth()->user()->email }}</strong>.</p>
		<div class="welcome-badge"><span></span> Session sécurisée</div>
	</main>
	<dialog class="delete-account-dialog" id="delete-account-dialog" aria-labelledby="delete-account-title">
		<form method="POST" action="{{ route('account.destroy') }}" class="delete-account-form">
			@csrf
			@method('DELETE')
			<p class="eyebrow">ACTION DÉFINITIVE</p>
			<h2 id="delete-account-title">Supprimer votre compte ?</h2>
			<p>Cette action est irréversible. Confirmez votre mot de passe pour continuer.</p>
			<label for="delete-account-password">Mot de passe</label>
			<input id="delete-account-password" name="password" type="password" required autocomplete="current-password" autofocus>
			@if ($errors->has('password'))
				<p class="delete-account-error" role="alert">{{ $errors->first('password') }}</p>
			@endif
			<div class="dialog-actions">
				<button class="dialog-cancel-button" type="button" onclick="document.getElementById('delete-account-dialog').close()">Annuler</button>
				<button class="delete-account-button" type="submit">Supprimer définitivement</button>
			</div>
		</form>
	</dialog>
	@if ($errors->has('password'))
		<script>document.getElementById('delete-account-dialog').showModal();</script>
	@endif
</body>
</html>