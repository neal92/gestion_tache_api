# Gestion Tâches API

API de gestion de tâches basée sur Symfony 7 + API Platform + Doctrine (PostgreSQL).

## Prérequis

- PHP >= 8.2
- Composer
- PostgreSQL (ou un autre SGBD compatible Doctrine)
- (Optionnel) Symfony CLI pour le serveur local

## Installation rapide

1. Installer les dépendances PHP.
   - Optionnel (documentation):
     ```powershell
     composer install
     ```

2. Configurer les variables d'environnement en local (ne pas committer vos secrets):
   - Créez un fichier `.env.local` à la racine avec, par exemple:
     ```dotenv
     APP_ENV=dev
     # Générez une valeur aléatoire (32 hex)
     APP_SECRET=remplacez_par_un_secret
     # Exemple PostgreSQL
     DATABASE_URL="pgsql://USER:PASSWORD@127.0.0.1:5432/gestion_taches"
     # CORS par défaut pour localhost
     CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'
     ```
   - Pour générer un secret rapidement (exécuter dans PowerShell si PHP est installé):
     ```powershell
     php -r "echo bin2hex(random_bytes(16));"
     ```

3. Préparer la base de données.
   - Optionnel (documentation):
     ```powershell
     php bin/console doctrine:database:create
     php bin/console doctrine:migrations:migrate -n
     # Si vous souhaitez des données d'exemple
     php bin/console doctrine:fixtures:load -n
     ```

4. Lancer l'API en local.
   - Avec Symfony CLI (recommandé):
     ```powershell
     symfony server:start -d
     ```
   - Ou avec PHP intégré:
     ```powershell
     php -S 127.0.0.1:8000 -t public
     ```

5. Documentation et test de l'API.
   - Interface API Platform (OpenAPI/Swagger): http://127.0.0.1:8000/api

## À propos des environnements

- `.env` contient des valeurs par défaut de développement et reste committé mais sans secrets.
- `.env.local` (non committé) doit contenir vos vraies valeurs sensibles en local.
- Évitez de committer des fichiers `.env.*` contenant des secrets. Les patterns sont ignorés dans `.gitignore`.

## Sécurité: que faire si des secrets ont été poussés

Si des secrets (.env) ont été poussés sur Git par erreur (ce qui a été corrigé sur la branche):

1. Rotation des secrets (recommandé):
   - Changez les mots de passe de BDD (DATABASE_URL), régénérez `APP_SECRET`, et toute autre clé/API token.

2. Purge de l'historique Git (optionnel mais conseillé si des secrets sensibles ont fuité):
   - Utilisez un outil comme `git filter-repo` ou `BFG Repo-Cleaner` pour supprimer les fuites de toutes les révisions.
   - Après la réécriture, force-pushez la branche et informez les collaborateurs de re-cloner.

Exemple avec `git filter-repo` (documentation uniquement):
```powershell
# Installer git-filter-repo selon votre OS, puis:
# Supprimer les fichiers .env de tout l'historique
git filter-repo --path .env --path .env.dev --invert-paths
# Force push après vérification
git push --force
```

Note: Même après purge, considérez les secrets comme compromis et procédez à la rotation.

## Déploiement (prod)

- Compilez les variables d'environnement pour la prod (avec Symfony Flex):
  ```powershell
  composer dump-env prod
  ```
- Configurez vos variables sur le serveur (APP_ENV=prod, DATABASE_URL, etc.).
- Migrations en prod:
  ```powershell
  php bin/console doctrine:migrations:migrate -n
  ```
- Cache warmup (optionnel):
  ```powershell
  php bin/console cache:clear --env=prod --no-warmup
  php bin/console cache:warmup --env=prod
  ```

## Points d'entrée utiles

- Console Symfony: `php bin/console`
- Interface API Platform: `/api`

## Licence

Propriétaire (voir `composer.json`).
