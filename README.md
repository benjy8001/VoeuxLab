# Vœux de Cérémonie

Application web pour aider les mariés et les officiant·e·s à rédiger leurs vœux et discours de cérémonie laïque.

**Demo :** [voeuxlab.benjamin-mabille.net](https://voeuxlab.benjamin-mabille.net)

## Fonctionnalités

- **Parcours guidé en 14 questions** pour rédiger ses vœux personnalisés
- **4 tonalités** au choix : émouvant, équilibré, léger, poétique
- **Génération automatique** des vœux depuis les réponses (côté serveur)
- **Éditeur libre** pour affiner le brouillon généré (drag-and-drop des blocs)
- **Minuteur de lecture** et conseils de relecture sur l'aperçu
- **Banque de citations** (poésie, humour, cinéma, chanson) pour s'inspirer
- **Mode officiant·e** — parcours 8 questions pour le discours de cérémonie
- **Export PDF** des vœux finalisés
- **Couple partagé** — chaque époux rédige séparément, les vœux restent privés
- Sauvegarde automatique (debounce 2 s)

## Stack

- **Backend :** Laravel 13 · PHP 8.3
- **Frontend :** Inertia.js · React · TypeScript · Tailwind CSS 4
- **Base de données :** MariaDB
- **Auth :** Laravel Breeze / Sanctum
- **Infra locale :** Docker (PHP-FPM · Nginx · MariaDB · Redis)

## Installation locale

### Prérequis

- Docker & Docker Compose
- Make

### Première installation

```bash
git clone <repo>
cd wedding
make start
```

Cette commande :
1. Copie `.env.example` → `.env`
2. Build les images Docker
3. Installe les dépendances Composer et npm
4. Génère la clé applicative
5. Lance les migrations et les seeders

L'application est ensuite disponible sur **http://localhost**.

### Commandes utiles

```bash
make run          # Démarrer les conteneurs
make stop         # Arrêter les conteneurs
make assets-watch # Mode watch (Vite)
make tests        # Lancer les tests Pest
make tinker       # Laravel Tinker
make connect      # Shell dans le conteneur PHP
```

## Déploiement (production)

Le déploiement se fait par rsync vers un hébergement mutualisé (Hostinger).

### Configuration

Copier `.env.make.example` en `.env.make` et renseigner les variables :

```bash
cp .env.make.example .env.make
```

```dotenv
DEPLOY_USER=your_ssh_user
DEPLOY_HOST=your.domain.com
DEPLOY_PORT=65002
DEPLOY_PATH=/home/your_user/domains/your.domain.com/public_html/wedding
```

Créer également un `.env.production` sur le serveur (voir `.env.production` à la racine du projet comme modèle).

### Lancer un déploiement

```bash
make deploy
```

Les étapes exécutées :

| Étape | Description |
|---|---|
| `deploy-assets` | Compile les assets pour la production (`npm run build`) |
| `deploy-sync` | Rsync vers le serveur (exclut `.git`, `node_modules`, `vendor`, `.env`, logs…) |
| `deploy-env` | Copie `.env.production` → `.env` sur le serveur |
| `deploy-composer` | `composer install --no-dev --optimize-autoloader` sur le serveur |
| `deploy-artisan` | `migrate --force`, `storage:link`, `optimize` sur le serveur |

```bash
make deploy-ssh   # Ouvrir une session SSH sur le serveur
```

## Architecture

```
app/
├── Http/Controllers/
│   ├── VowsController.php        # Parcours vœux
│   ├── OfficiantController.php   # Parcours officiant·e
│   ├── ExportController.php      # Export PDF
│   └── CoupleController.php      # Gestion du couple
├── Models/
│   ├── User, Couple, VowsDraft, VowsAnswer, Ceremony
└── Services/
    └── VowsGeneratorService.php  # Génération des vœux côté serveur

resources/js/Pages/
├── Vows/     # Parcours et éditeur de vœux
├── Officiant/ # Parcours officiant·e
└── Welcome.tsx
```

## Tests

```bash
make tests
```

Tests écrits avec **Pest PHP**.

## Licence

Projet personnel — tous droits réservés.
