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
		<form method="POST" action="{{ route('logout') }}">
			@csrf
			<button class="logout-button" type="submit">Se déconnecter</button>
		</form>
	</header>
	<main class="welcome-content">
		<p class="eyebrow">ESPACE PERSONNEL</p>
		<h1>Bienvenue, {{ auth()->user()->name }} <span aria-hidden="true">✦</span></h1>
		<p>Vous êtes connecté avec <strong>{{ auth()->user()->email }}</strong>.</p>
		<div class="welcome-badge"><span></span> Session sécurisée</div>
	</main>
</body>
</html>