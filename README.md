# DemoPHPLaravelDocker

## Démarrer avec Docker

Prérequis : Docker Desktop (ou Docker Engine avec Docker Compose) en cours d’exécution.

À la racine du dépôt, lancez :

```sh
docker compose up --build
```

Ouvrez ensuite <http://localhost:8081>. Au premier démarrage, Docker construit l’image PHP et les ressources Vite, démarre MariaDB, génère la clé Laravel puis exécute les migrations. Le port par défaut est `8081` ; vous pouvez le modifier avec la variable `APP_PORT`.

Les données MariaDB sont conservées dans le volume `mariadb_data` après l’arrêt des conteneurs. Pour arrêter les services, utilisez `docker compose down`. Pour supprimer aussi la base de données, utilisez `docker compose down -v`.

Les identifiants par défaut sont destinés au développement local. Vous pouvez les remplacer en définissant `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` et `DB_ROOT_PASSWORD` dans l’environnement avant de lancer Compose.

## Comptes et authentification

- Compte Docker initial : `admin@example.com` / `ChangeMe123!` (à changer avant tout usage autre que local).
- Inscription : ouvrez <http://localhost:8081/register>. Les comptes créés sont automatiquement connectés.
- La page d’accueil `/` est réservée aux utilisateurs connectés. Une visite directe sans session redirige vers la connexion.
- Les mots de passe sont hashés par Laravel et ne sont jamais stockés en clair.

Pour le lancement sans Docker, configurez les variables `DEFAULT_USER_*` dans `demo_php/.env`, puis exécutez `php artisan migrate --seed` depuis `demo_php`.