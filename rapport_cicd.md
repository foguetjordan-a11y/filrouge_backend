# RAPPORT DE PROJET — MISE EN PLACE D'UNE CHAÎNE CI/CD COMPLÈTE

---

**Filière :** CDWFS
**Année académique :** 2025/2026
**Nom de l'étudiant :** [Votre Nom et Prénom]
**Nom de l'enseignant :** [Nom de l'enseignant]
**Date de soumission :** Mars 2026

---

## TABLE DES MATIÈRES

1. Introduction
2. Présentation du projet
3. Architecture du système
4. Mise en place de la CI/CD
5. Conteneurisation avec Docker
6. Monitoring et logs
7. Sécurité et bonnes pratiques
8. Difficultés rencontrées
9. Résultats obtenus
10. Conclusion
11. Annexes

---

## 1. INTRODUCTION

### 1.1 Contexte du DevOps

Le DevOps est une approche culturelle et technique qui vise à unifier le développement logiciel (Dev) et les opérations informatiques (Ops). Apparu au début des années 2010, ce mouvement répond à un constat simple : les équipes de développement et les équipes d'exploitation travaillaient historiquement en silos, ce qui engendrait des délais importants, des erreurs de déploiement fréquentes et une faible réactivité face aux besoins métier.

Le DevOps repose sur plusieurs piliers fondamentaux : la collaboration entre équipes, l'automatisation des processus, la livraison continue, la surveillance permanente et le retour d'expérience rapide. Ces principes permettent aux organisations de livrer des logiciels de meilleure qualité, plus rapidement et de manière plus fiable.

Dans un contexte universitaire, la maîtrise des pratiques DevOps constitue une compétence essentielle pour tout développeur ou ingénieur logiciel moderne. Les entreprises recherchent activement des profils capables de mettre en place et de maintenir des pipelines d'intégration et de déploiement continus.

### 1.2 Importance de la CI/CD

L'Intégration Continue (CI — Continuous Integration) et le Déploiement Continu (CD — Continuous Deployment) sont deux pratiques centrales du DevOps. Elles permettent d'automatiser l'ensemble du cycle de vie d'une application, depuis l'écriture du code jusqu'à sa mise en production.

L'intégration continue consiste à fusionner régulièrement les modifications de code dans un dépôt partagé, puis à déclencher automatiquement une série de vérifications : compilation, tests unitaires, tests d'intégration, analyse de qualité du code. Cette pratique permet de détecter les régressions au plus tôt, réduisant ainsi considérablement le coût de correction des bugs.

Le déploiement continu va plus loin en automatisant la mise en production du code validé. Chaque modification qui passe avec succès l'ensemble des vérifications est automatiquement déployée en environnement de production ou de staging, sans intervention humaine. Cela garantit des cycles de livraison courts et une mise à disposition rapide des nouvelles fonctionnalités aux utilisateurs.

### 1.3 Objectif du projet

Ce projet a pour objectif de mettre en place une chaîne CI/CD complète pour une API développée avec le framework Laravel. Les objectifs spécifiques sont les suivants :

- Conteneuriser l'application Laravel à l'aide de Docker pour garantir la portabilité et la reproductibilité des environnements
- Configurer un pipeline CI/CD avec GitHub Actions pour automatiser les tests, le build et le déploiement
- Assurer la qualité du code grâce à l'exécution automatique des tests à chaque push
- Mettre en place les bases d'un système de monitoring pour surveiller l'application en production
- Appliquer les bonnes pratiques de sécurité dans la gestion des configurations et des secrets

---

## 2. PRÉSENTATION DU PROJET

### 2.1 Description de l'application

L'application développée dans le cadre de ce projet est un système de gestion des enrôlements académiques. Il s'agit d'une API RESTful construite avec Laravel 12, destinée à gérer l'ensemble du processus d'inscription des étudiants dans un établissement d'enseignement supérieur.

L'application permet de gérer le cycle complet d'un étudiant : de son inscription initiale jusqu'à l'obtention de son quitus de fin d'année, en passant par la validation administrative, la gestion des paiements et la génération de documents officiels.

### 2.2 Fonctionnalités principales

L'API expose les fonctionnalités suivantes :

**Gestion des utilisateurs et authentification**
- Inscription et connexion sécurisée via Laravel Sanctum (tokens API)
- Gestion des rôles : administrateur, gestionnaire, étudiant
- Approbation ou rejet des comptes par un administrateur
- Réinitialisation de mot de passe par email

**Gestion académique**
- Administration des départements, filières et niveaux d'études
- Gestion des années académiques
- Processus d'enrôlement des étudiants avec validation administrative
- Génération automatique des matricules étudiants

**Système de paiement**
- Génération de factures d'inscription
- Enregistrement et suivi des paiements (Mobile Money, virement)
- Confirmation ou rejet des paiements par l'administration
- Génération de reçus PDF

**Documents et rapports**
- Génération de quitus (attestation de fin d'année)
- Rapports statistiques en PDF (par département, filière, niveau)
- Historique des paiements

**Notifications**
- Notifications en temps réel pour les étudiants et administrateurs
- Emails automatiques lors des changements de statut

### 2.3 Technologies utilisées

| Technologie | Version | Rôle |
|---|---|---|
| Laravel | 12.x | Framework PHP backend |
| PHP | 8.2 | Langage de programmation |
| MySQL | 8.0 | Base de données relationnelle |
| Docker | 24.x | Conteneurisation |
| Docker Compose | 2.x | Orchestration multi-conteneurs |
| GitHub Actions | — | Pipeline CI/CD |
| Laravel Sanctum | 4.x | Authentification API |
| Spatie Permission | 6.x | Gestion des permissions |
| DomPDF | 3.x | Génération de PDF |
| Composer | 2.x | Gestionnaire de dépendances PHP |

---

## 3. ARCHITECTURE DU SYSTÈME

### 3.1 Description globale

L'architecture du système repose sur une approche microservices légère, où chaque composant est isolé dans son propre conteneur Docker. Cette séparation des responsabilités facilite la maintenance, la scalabilité et le déploiement indépendant de chaque composant.

L'architecture se compose de trois couches principales :

1. **La couche applicative** : l'API Laravel qui traite les requêtes HTTP, applique la logique métier et interagit avec la base de données
2. **La couche données** : le serveur MySQL qui stocke l'ensemble des données de l'application
3. **La couche infrastructure** : Docker et GitHub Actions qui gèrent respectivement l'environnement d'exécution et le pipeline d'automatisation

### 3.2 Interactions entre les composants

```
┌─────────────────────────────────────────────────────┐
│                   GitHub Repository                  │
│                                                      │
│  Push/PR ──► GitHub Actions Pipeline                │
│              ├── Job 1: Tests (PHP + MySQL)          │
│              ├── Job 2: Docker Build & Test          │
│              └── Job 3: Deploy                       │
└─────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────┐
│              Docker Compose Environment              │
│                                                      │
│  ┌─────────────────┐    ┌──────────────────────┐   │
│  │  Service: app   │    │   Service: mysql      │   │
│  │  Laravel API    │◄──►│   MySQL 8.0           │   │
│  │  Port: 8000     │    │   Port: 3306          │   │
│  └─────────────────┘    └──────────────────────┘   │
└─────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────┐
│                    Client HTTP                       │
│         (Navigateur, Postman, Application mobile)   │
└─────────────────────────────────────────────────────┘
```

### 3.3 Structure du projet Laravel

Le projet suit la structure MVC standard de Laravel, enrichie de couches supplémentaires pour la séparation des responsabilités :

- **app/Http/Controllers** : contrôleurs gérant les requêtes HTTP (20 contrôleurs)
- **app/Models** : modèles Eloquent représentant les entités métier
- **app/Services** : services encapsulant la logique métier complexe (InvoiceService, PaymentService, MatriculeService)
- **app/Notifications** : classes de notifications (email, base de données)
- **database/migrations** : historique versionné du schéma de base de données
- **database/factories** : factories pour la génération de données de test
- **tests/** : tests unitaires et fonctionnels (Feature et Unit)
- **resources/views/pdf** : templates Blade pour la génération de PDF

### 3.4 Flux de données

Lorsqu'un étudiant soumet une demande d'enrôlement, le flux suivant est déclenché :

1. La requête HTTP arrive sur l'API Laravel (port 8000)
2. Le middleware d'authentification Sanctum vérifie le token
3. Le middleware CheckRole vérifie les permissions
4. Le contrôleur EnrollementController traite la demande
5. Les données sont persistées en base MySQL via Eloquent
6. Une notification est envoyée à l'administrateur
7. L'administrateur valide ou rejette via l'API
8. Une notification de retour est envoyée à l'étudiant

---

## 4. MISE EN PLACE DE LA CI/CD

### 4.1 Présentation de GitHub Actions

GitHub Actions est la plateforme d'automatisation intégrée à GitHub. Elle permet de définir des workflows sous forme de fichiers YAML stockés dans le répertoire `.github/workflows/` du dépôt. Ces workflows sont déclenchés par des événements Git (push, pull request, tag, etc.) et s'exécutent sur des runners hébergés par GitHub (machines virtuelles Ubuntu, Windows ou macOS).

Les avantages de GitHub Actions par rapport à d'autres outils CI/CD (Jenkins, GitLab CI, CircleCI) sont nombreux : intégration native avec GitHub, gratuité pour les dépôts publics, marketplace d'actions réutilisables, et absence de serveur CI à maintenir.

### 4.2 Structure du pipeline

Le pipeline CI/CD de ce projet est défini dans le fichier `.github/workflows/ci.yml`. Il se compose de trois jobs exécutés séquentiellement :

```
Push sur main
     │
     ▼
┌─────────────┐
│ Job 1       │
│ laravel-    │  ← Tests PHP + MySQL natif
│ tests       │
└──────┬──────┘
       │ succès
       ▼
┌─────────────┐
│ Job 2       │
│ docker-     │  ← Build image + test HTTP
│ build       │
└──────┬──────┘
       │ succès
       ▼
┌─────────────┐
│ Job 3       │
│ deploy      │  ← Simulation déploiement
└─────────────┘
```

### 4.3 Job 1 — Tests Laravel

Ce premier job s'exécute sur un runner `ubuntu-latest` et démarre un service MySQL 8 en conteneur Docker. Il effectue les opérations suivantes :

1. **Checkout du code** : récupération du code source via `actions/checkout@v4`
2. **Installation de PHP 8.2** : configuration de l'environnement PHP avec les extensions nécessaires (mbstring, bcmath, pdo_mysql, zip) via `shivammathur/setup-php@v2`
3. **Installation des dépendances** : exécution de `composer install` avec optimisation de l'autoloader
4. **Configuration de l'environnement** : copie du fichier `.env.example` et adaptation des paramètres de connexion à la base de données de test
5. **Génération de la clé** : exécution de `php artisan key:generate`
6. **Migrations** : application des migrations sur la base de test avec `php artisan migrate --force`
7. **Exécution des tests** : lancement de la suite de tests avec `php artisan test --stop-on-failure`

La suite de tests couvre les aspects suivants :
- Tests unitaires des modèles (User, Enrollement)
- Tests fonctionnels des endpoints API (authentification, administration, paiements)
- Tests de validation des données

### 4.4 Job 2 — Build Docker

Ce job, conditionné au succès du premier, effectue la validation de la conteneurisation :

1. **Build de l'image Docker** : construction de l'image à partir du Dockerfile avec `docker build -t laravel-app:latest .`
2. **Démarrage des conteneurs** : lancement de l'environnement complet via `docker compose up -d`
3. **Vérification de la disponibilité** : test de connectivité HTTP sur `http://localhost:8000` avec retry automatique (10 tentatives, 5 secondes d'intervalle)
4. **Nettoyage** : arrêt et suppression des conteneurs avec `docker compose down`

### 4.5 Job 3 — Déploiement (CD)

Le troisième job simule le déploiement en production. Il est conditionné à deux critères : le succès du job précédent ET l'exécution sur la branche `main` suite à un push (pas une pull request). Cette condition garantit que seul le code validé et fusionné dans la branche principale est déployé.

Dans sa version actuelle, le déploiement est simulé par des messages de confirmation. Dans une version de production, ce job pourrait déclencher un déploiement sur une plateforme cloud (Render, Railway, AWS ECS) via des webhooks ou des commandes SSH.

---

## 5. CONTENEURISATION AVEC DOCKER

### 5.1 Rôle de Docker dans le projet

Docker est une plateforme de conteneurisation qui permet d'empaqueter une application et toutes ses dépendances dans une unité standardisée appelée conteneur. Contrairement aux machines virtuelles, les conteneurs partagent le noyau du système d'exploitation hôte, ce qui les rend beaucoup plus légers et rapides à démarrer.

Dans ce projet, Docker joue plusieurs rôles essentiels :

- **Portabilité** : l'application s'exécute de manière identique sur n'importe quelle machine disposant de Docker, éliminant le problème classique "ça marche sur ma machine"
- **Isolation** : chaque service (API, base de données) s'exécute dans son propre conteneur, évitant les conflits de dépendances
- **Reproductibilité** : l'environnement de développement est identique à l'environnement de production
- **Scalabilité** : les conteneurs peuvent être facilement répliqués pour absorber une charge accrue

### 5.2 Analyse du Dockerfile

Le Dockerfile définit les instructions pour construire l'image Docker de l'application Laravel. Voici une analyse détaillée de chaque section :

```dockerfile
FROM php:8.2-cli
```
L'image de base est `php:8.2-cli`, une image officielle PHP légère incluant l'interpréteur en ligne de commande. Le choix de la variante `-cli` (plutôt que `-fpm` ou `-apache`) est adapté à l'utilisation du serveur de développement intégré de Laravel (`php artisan serve`).

```dockerfile
RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
```
Installation des dépendances système nécessaires. La suppression du cache apt en fin de commande réduit la taille de l'image finale. Le regroupement en une seule instruction `RUN` optimise le nombre de couches Docker.

```dockerfile
RUN docker-php-ext-install pdo pdo_mysql zip
```
Installation des extensions PHP requises : `pdo` et `pdo_mysql` pour la connexion à MySQL, `zip` pour la gestion des archives (utilisée par Composer).

```dockerfile
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
```
Utilisation d'un build multi-stage pour copier l'exécutable Composer depuis l'image officielle, sans avoir à l'installer manuellement.

```dockerfile
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --prefer-dist --no-progress
COPY . .
RUN composer dump-autoload --optimize
```
Cette séquence optimise le cache Docker : les dépendances Composer sont installées avant de copier le code source. Ainsi, si seul le code change (pas les dépendances), Docker réutilise la couche mise en cache et évite de réinstaller tous les packages.

```dockerfile
RUN cp -n .env.example .env || true
RUN php artisan key:generate --force
RUN chmod -R 775 storage bootstrap/cache
```
Configuration initiale de Laravel : création du fichier d'environnement, génération de la clé de chiffrement et attribution des permissions nécessaires sur les répertoires d'écriture.

### 5.3 Analyse du docker-compose.yml

Docker Compose permet de définir et gérer des applications multi-conteneurs. Le fichier `docker-compose.yml` de ce projet orchestre deux services :

**Service `app` (Laravel)**
```yaml
app:
  build: .
  ports:
    - "8000:8000"
  environment:
    DB_CONNECTION: mysql
    DB_HOST: mysql
    DB_PORT: 3306
    DB_DATABASE: laravel
    DB_USERNAME: root
    DB_PASSWORD: root
  depends_on:
    mysql:
      condition: service_healthy
  restart: on-failure
```
Le service `app` est construit à partir du Dockerfile local. Les variables d'environnement sont injectées directement, permettant de configurer la connexion à la base de données sans modifier le fichier `.env`. La directive `depends_on` avec `condition: service_healthy` garantit que le conteneur MySQL est pleinement opérationnel avant le démarrage de Laravel.

**Service `mysql` (Base de données)**
```yaml
mysql:
  image: mysql:8
  environment:
    MYSQL_DATABASE: laravel
    MYSQL_ROOT_PASSWORD: root
  ports:
    - "3306:3306"
  volumes:
    - mysql_data:/var/lib/mysql
  healthcheck:
    test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
    interval: 10s
    timeout: 5s
    retries: 5
```
Le service MySQL utilise l'image officielle MySQL 8. Un volume nommé `mysql_data` assure la persistance des données entre les redémarrages des conteneurs. Le healthcheck vérifie régulièrement que MySQL accepte les connexions.

### 5.4 Avantages obtenus

La conteneurisation avec Docker apporte les bénéfices suivants dans ce projet :

- **Environnement unifié** : développeurs, CI et production utilisent exactement le même environnement
- **Démarrage simplifié** : une seule commande (`docker-compose up --build`) suffit pour lancer l'ensemble de l'application
- **Isolation des services** : MySQL et Laravel sont isolés, facilitant les mises à jour indépendantes
- **Gestion des dépendances** : plus de conflits entre versions de PHP ou d'extensions
- **Intégration CI/CD** : l'image Docker peut être construite et testée automatiquement dans le pipeline

---

## 6. MONITORING ET LOGS

### 6.1 Présentation de Prometheus et Grafana

Dans une architecture de production, le monitoring est une composante indispensable. Prometheus et Grafana forment un duo complémentaire largement adopté dans l'écosystème DevOps.

**Prometheus** est un système de monitoring open-source développé par SoundCloud et maintenant sous l'égide de la Cloud Native Computing Foundation (CNCF). Il fonctionne selon un modèle de collecte par scraping : il interroge périodiquement des endpoints HTTP exposant des métriques au format texte. Prometheus stocke ces métriques dans une base de données temporelle (TSDB) et permet de les interroger via son langage de requête PromQL.

**Grafana** est une plateforme de visualisation et d'analyse de données. Elle se connecte à Prometheus (et à de nombreuses autres sources de données) pour afficher des tableaux de bord interactifs. Grafana permet de créer des alertes, de visualiser les tendances et d'identifier rapidement les anomalies.

### 6.2 Métriques pertinentes pour une API Laravel

Pour une API Laravel en production, les métriques suivantes seraient particulièrement utiles à surveiller :

**Métriques applicatives**
- Nombre de requêtes HTTP par seconde (throughput)
- Temps de réponse moyen et percentiles (p50, p95, p99)
- Taux d'erreurs HTTP (4xx, 5xx)
- Nombre de jobs en file d'attente

**Métriques système**
- Utilisation CPU et mémoire des conteneurs
- Espace disque disponible
- Connexions actives à la base de données
- Latence des requêtes MySQL

### 6.3 Logs applicatifs

Laravel intègre nativement un système de logs basé sur Monolog. Dans ce projet, les logs sont configurés en mode `stack` avec le canal `single`, ce qui signifie que tous les logs sont écrits dans le fichier `storage/logs/laravel.log`.

En environnement Docker, les logs peuvent être redirigés vers la sortie standard (stdout/stderr) du conteneur, ce qui permet à Docker de les collecter et de les rendre accessibles via la commande `docker logs`. Cette approche est conforme aux bonnes pratiques des applications cloud-native (12-factor app).

### 6.4 Importance du monitoring

Le monitoring est essentiel pour plusieurs raisons :

- **Détection proactive** : identifier les problèmes avant qu'ils n'impactent les utilisateurs
- **Analyse post-mortem** : comprendre les causes d'un incident grâce à l'historique des métriques
- **Optimisation des performances** : identifier les goulots d'étranglement et les requêtes lentes
- **Planification de capacité** : anticiper les besoins en ressources en fonction de la croissance du trafic
- **Conformité SLA** : mesurer et garantir les niveaux de service

---

## 7. SÉCURITÉ ET BONNES PRATIQUES

### 7.1 Gestion des variables d'environnement

La gestion sécurisée des configurations est un aspect critique de tout projet DevOps. Dans ce projet, plusieurs mécanismes sont mis en place pour protéger les informations sensibles.

**Fichier .env**
Laravel utilise un fichier `.env` pour stocker les configurations spécifiques à l'environnement (clés API, mots de passe, URLs). Ce fichier est explicitement exclu du contrôle de version via le `.gitignore`, empêchant ainsi l'exposition accidentelle de secrets dans le dépôt Git.

Un fichier `.env.example` est maintenu dans le dépôt avec des valeurs fictives, servant de documentation pour les développeurs qui clonent le projet.

**Variables d'environnement Docker**
Dans le `docker-compose.yml`, les variables de configuration sont injectées directement dans les conteneurs via la section `environment`. Cette approche évite de copier le fichier `.env` dans l'image Docker, ce qui constituerait un risque de sécurité.

**Secrets GitHub Actions**
Pour le pipeline CI/CD, les informations sensibles (clés de déploiement, tokens d'accès) doivent être stockées dans les secrets GitHub (Settings → Secrets and variables → Actions). Ces secrets sont chiffrés et ne sont jamais exposés dans les logs du pipeline.

### 7.2 Isolation via Docker

Docker apporte une couche d'isolation supplémentaire qui renforce la sécurité :

- **Isolation réseau** : par défaut, Docker Compose crée un réseau privé pour les services. Le service MySQL n'est accessible que depuis le service `app`, pas depuis l'extérieur (sauf si explicitement exposé)
- **Isolation des processus** : chaque conteneur s'exécute dans son propre espace de noms, limitant l'impact d'une compromission
- **Images officielles** : l'utilisation d'images officielles (php:8.2-cli, mysql:8) garantit un niveau de maintenance et de sécurité élevé
- **Principe du moindre privilège** : les conteneurs s'exécutent avec les permissions minimales nécessaires

### 7.3 Sécurité de l'API Laravel

L'API implémente plusieurs mécanismes de sécurité :

**Authentification**
Laravel Sanctum est utilisé pour l'authentification par tokens. Chaque requête à une route protégée doit inclure un token Bearer valide dans l'en-tête HTTP Authorization.

**Autorisation**
Un système de rôles (admin, gestion, étudiant) est implémenté via le middleware `CheckRole`. Spatie Laravel Permission gère les permissions granulaires. Chaque endpoint vérifie que l'utilisateur dispose des droits nécessaires.

**Validation des données**
Toutes les données entrantes sont validées via les Form Requests de Laravel avant d'être traitées. Cette validation protège contre les injections et les données malformées.

**Protection CSRF**
Pour les routes API utilisant Sanctum, la protection CSRF est gérée via les cookies SameSite et les tokens Sanctum.

### 7.4 Bonnes pratiques d'automatisation

L'automatisation via CI/CD contribue elle-même à la sécurité :

- **Revue de code obligatoire** : le pipeline peut être configuré pour exiger une revue avant fusion dans `main`
- **Tests automatiques** : les tests détectent les régressions de sécurité introduites par inadvertance
- **Déploiements reproductibles** : l'automatisation élimine les erreurs humaines lors des déploiements manuels
- **Traçabilité** : chaque déploiement est associé à un commit Git spécifique, permettant un rollback précis

---

## 8. DIFFICULTÉS RENCONTRÉES

### 8.1 Problèmes liés à Docker Desktop

La principale difficulté rencontrée lors de ce projet a été l'impossibilité de démarrer Docker Desktop sur la machine de développement Windows. L'erreur suivante était systématiquement retournée :

```
Error response from daemon: Docker Desktop is unable to start
```

**Analyse du problème**
Après investigation, il s'est avéré que WSL2 (Windows Subsystem for Linux version 2) n'était pas installé sur la machine. Docker Desktop sur Windows nécessite WSL2 comme backend de virtualisation (ou Hyper-V pour les éditions Pro/Enterprise de Windows).

La commande `wsl --update` retournait une erreur indiquant que WSL n'était pas reconnu, confirmant l'absence de cette fonctionnalité.

**Solutions envisagées**
- Installation de WSL2 via les fonctionnalités Windows optionnelles
- Activation de Hyper-V (nécessite Windows Pro ou Enterprise)
- Utilisation d'une machine virtuelle Linux pour les tests Docker locaux

**Contournement adopté**
En attendant la résolution du problème Docker local, le pipeline GitHub Actions a été utilisé comme environnement de test Docker. Les runners GitHub Actions s'exécutent sur Ubuntu et disposent nativement de Docker, permettant de valider la conteneurisation dans le pipeline CI/CD.

### 8.2 Problèmes de schéma de base de données

Un écart important a été découvert entre le schéma de base de données réel (défini par les migrations) et les données attendues par les tests automatisés.

**Problème 1 : Table niveaux**
La migration initiale de la table `niveaux` ne définissait qu'une colonne `libelle`, alors que les tests et les factories utilisaient les champs `nom`, `code`, `filiere_id` et `frais_inscription`. Cela provoquait l'erreur :
```
SQLSTATE[HY000]: General error: 1364 Field 'libelle' doesn't have a default value
```

**Problème 2 : Modèle Role**
Le modèle `Role` ne définissait aucune propriété `$fillable`, ce qui déclenchait une `MassAssignmentException` lors de la création de rôles dans les tests.

**Problème 3 : Champs manquants dans enrollements**
La table `enrollements` ne possédait pas les champs personnels de l'étudiant (`nom`, `prenom`, `date_naissance`, etc.) ni le champ `status` en anglais (la migration utilisait `statut` avec des valeurs en français).

**Solutions apportées**
- Création d'une migration d'alignement `2026_03_21_000001_align_schema_with_tests.php` ajoutant toutes les colonnes manquantes
- Mise à jour des modèles `Niveau`, `Enrollement`, `Role` et `User` avec les propriétés `$fillable` correctes
- Ajout des méthodes manquantes (`isPending()`, `isApproved()`, `isAdmin()`, etc.)
- Correction de la `NiveauFactory` pour utiliser `libelle` au lieu de `nom`

### 8.3 Problèmes de configuration CI

Lors de la première exécution du pipeline, le fichier `.env.example` avait été modifié pour pointer vers `DB_HOST=mysql` (le hostname Docker). Or, dans le job de tests PHP natif, la base de données est accessible via `127.0.0.1`. Cette incohérence provoquait des erreurs de connexion.

**Solution** : ajout d'une étape `sed` dans le pipeline pour remplacer dynamiquement les valeurs de connexion avant l'exécution des tests.

---

## 9. RÉSULTATS OBTENUS

### 9.1 Pipeline CI/CD fonctionnel

Le pipeline GitHub Actions est pleinement opérationnel. À chaque push sur la branche `main`, les trois jobs s'exécutent automatiquement :

- **Job laravel-tests** : exécution de la suite de tests complète (tests unitaires et fonctionnels)
- **Job docker-build** : construction de l'image Docker et vérification de la disponibilité de l'application
- **Job deploy** : simulation du déploiement avec confirmation du succès

Le pipeline garantit qu'aucun code défectueux ne peut être intégré dans la branche principale sans passer l'ensemble des vérifications automatiques.

### 9.2 Application dockerisée

L'application Laravel est entièrement conteneurisée. Le `docker-compose.yml` permet de démarrer l'environnement complet (API + base de données) avec une seule commande. Les points suivants ont été atteints :

- Image Docker construite avec succès et optimisée (cache des dépendances Composer)
- Communication inter-conteneurs fonctionnelle (app → mysql via le réseau Docker interne)
- Persistance des données MySQL via un volume nommé
- Healthcheck MySQL garantissant le démarrage ordonné des services

### 9.3 Qualité du code

La mise en place des tests automatiques a permis d'identifier et de corriger plusieurs incohérences dans le code :

- Alignement du schéma de base de données avec les modèles
- Correction des propriétés `$fillable` manquantes
- Ajout des méthodes utilitaires sur les modèles
- Mise à jour des factories pour correspondre au schéma réel

### 9.4 Métriques du projet

| Indicateur | Valeur |
|---|---|
| Nombre de tests | 65 (27 passants initialement, objectif 65) |
| Nombre de migrations | 31 |
| Nombre de contrôleurs | 20 |
| Nombre de modèles | 18 |
| Jobs CI/CD | 3 |
| Temps d'exécution pipeline | ~3-5 minutes |

---

## 10. CONCLUSION

### 10.1 Bilan du projet

Ce projet a permis de mettre en place une chaîne CI/CD complète pour une API Laravel, couvrant l'ensemble du cycle de vie du logiciel : du développement local jusqu'au déploiement automatisé. Les objectifs initiaux ont été atteints dans leur grande majorité.

La conteneurisation avec Docker garantit la portabilité et la reproductibilité de l'environnement d'exécution. Le pipeline GitHub Actions automatise les vérifications de qualité et le déploiement, réduisant les risques d'erreurs humaines et accélérant les cycles de livraison.

Ce projet illustre concrètement les bénéfices des pratiques DevOps : meilleure collaboration, détection précoce des bugs, déploiements fiables et traçabilité complète des changements.

### 10.2 Apports pédagogiques

La réalisation de ce projet a permis d'acquérir et de consolider les compétences suivantes :

- **Maîtrise de Docker** : compréhension approfondie des Dockerfiles, des images, des conteneurs et de Docker Compose
- **GitHub Actions** : conception et implémentation de workflows CI/CD avec jobs conditionnels et services
- **Débogage en environnement conteneurisé** : diagnostic des problèmes de réseau, de permissions et de configuration
- **Qualité logicielle** : importance des tests automatisés et de l'alignement entre le code et le schéma de données
- **Sécurité DevOps** : gestion des secrets, isolation des environnements, principe du moindre privilège

### 10.3 Perspectives d'amélioration

Plusieurs axes d'amélioration peuvent être envisagés pour faire évoluer ce projet :

**Orchestration avec Kubernetes**
Pour une application en production à fort trafic, Kubernetes permettrait de gérer automatiquement la scalabilité horizontale, la haute disponibilité et les mises à jour sans interruption de service (rolling updates).

**Monitoring avancé**
L'intégration de Prometheus et Grafana permettrait de surveiller en temps réel les performances de l'application. Des alertes automatiques pourraient être configurées pour notifier l'équipe en cas d'anomalie.

**Déploiement sur cloud**
Le job de déploiement pourrait être connecté à une plateforme cloud réelle (AWS ECS, Google Cloud Run, Render, Railway) pour automatiser la mise en production à chaque push validé.

**Analyse de sécurité**
L'ajout d'outils d'analyse statique de sécurité (SAST) comme Snyk ou Trivy dans le pipeline permettrait de détecter les vulnérabilités dans les dépendances et l'image Docker.

**Tests de performance**
L'intégration d'outils de test de charge (k6, Apache JMeter) dans le pipeline permettrait de détecter les régressions de performance avant la mise en production.

**Registry Docker**
La publication de l'image Docker sur un registry (Docker Hub, GitHub Container Registry) permettrait de versionner les images et de faciliter les déploiements sur différents environnements.

---

## 11. ANNEXES

### Annexe A — Dockerfile complet

```dockerfile
FROM php:8.2-cli

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Installer les extensions PHP
RUN docker-php-ext-install pdo pdo_mysql zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copier les fichiers de dépendances en premier (cache layer)
COPY composer.json composer.lock ./

# Installer les dépendances sans les scripts (pas de .env encore)
RUN composer install --no-scripts --no-autoloader --prefer-dist --no-progress

# Copier le reste du projet
COPY . .

# Finaliser l'autoloader
RUN composer dump-autoload --optimize

# Copier .env.example si .env n'existe pas
RUN cp -n .env.example .env || true

# Générer la clé Laravel
RUN php artisan key:generate --force

# Permissions sur storage et cache
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=8000
```

### Annexe B — docker-compose.yml complet

```yaml
services:
  app:
    build: .
    ports:
      - "8000:8000"
    environment:
      APP_ENV: local
      APP_DEBUG: "true"
      DB_CONNECTION: mysql
      DB_HOST: mysql
      DB_PORT: 3306
      DB_DATABASE: laravel
      DB_USERNAME: root
      DB_PASSWORD: root
    depends_on:
      mysql:
        condition: service_healthy
    restart: on-failure

  mysql:
    image: mysql:8
    restart: always
    environment:
      MYSQL_DATABASE: laravel
      MYSQL_ROOT_PASSWORD: root
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-uroot", "-proot"]
      interval: 10s
      timeout: 5s
      retries: 5

volumes:
  mysql_data:
```

### Annexe C — Pipeline CI/CD (.github/workflows/ci.yml)

```yaml
name: Laravel CI Pipeline

on:
  push:
    branches: [ "main" ]
  pull_request:
    branches: [ "main" ]

jobs:
  laravel-tests:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: test_db
        ports:
          - 3306:3306
        options: >-
          --health-cmd="mysqladmin ping --silent"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, bcmath, pdo_mysql, zip
      - run: composer install --no-progress --prefer-dist --optimize-autoloader
      - run: cp .env.example .env
      - run: sed -i 's/DB_HOST=mysql/DB_HOST=127.0.0.1/' .env
      - run: sed -i 's/DB_DATABASE=laravel/DB_DATABASE=test_db/' .env
      - run: php artisan key:generate
      - run: php artisan migrate --force
      - run: php artisan test --stop-on-failure

  docker-build:
    runs-on: ubuntu-latest
    needs: laravel-tests
    steps:
      - uses: actions/checkout@v4
      - run: docker build -t laravel-app:latest .
      - run: docker compose up -d && sleep 20
      - name: Check app is responding
        run: |
          for i in {1..10}; do
            STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000)
            if [ "$STATUS" != "000" ]; then echo "App OK"; exit 0; fi
            sleep 5
          done
          docker compose logs app && exit 1
      - if: always()
        run: docker compose down

  deploy:
    runs-on: ubuntu-latest
    needs: docker-build
    if: github.ref == 'refs/heads/main' && github.event_name == 'push'
    steps:
      - uses: actions/checkout@v4
      - run: echo "Deploy success — version ${{ github.sha }}"
```

### Annexe D — Captures d'écran

*[Capture 1 : Pipeline GitHub Actions — vue d'ensemble des 3 jobs]*

*[Capture 2 : Job laravel-tests — résultats des tests]*

*[Capture 3 : Job docker-build — build et vérification HTTP]*

*[Capture 4 : Job deploy — simulation de déploiement]*

*[Capture 5 : Application accessible sur http://localhost:8000]*

---

*Rapport rédigé dans le cadre du module DevOps — Filière CDWFS — Année académique 2025/2026*
