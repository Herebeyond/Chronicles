# Chronicles

<div align="center">

![Chronicles Logo](public/images/logos/chronicles_logo.png)

**A Symfony 7.3 web application for managing fictional characters, species, and races in a fantasy universe**

[![Symfony](https://img.shields.io/badge/Symfony-7.3-black.svg)](https://symfony.com/)
[![PHP](https://img.shields.io/badge/PHP-8.4.12-777BB4.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1.svg)](https://www.mysql.com/)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED.svg)](https://www.docker.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

</div>

---

## 📖 Table of Contents

- [Overview](#-overview)
- [Features](#-features)
- [Technology Stack](#-technology-stack)
- [Project Structure](#-project-structure)
- [Getting Started](#-getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
  - [Database Setup](#database-setup)
- [Usage](#-usage)
  - [Creating an Admin User](#creating-an-admin-user)
  - [Populating Sample Data](#populating-sample-data)
  - [Accessing the Application](#accessing-the-application)
- [Database Schema](#-database-schema)
- [Key Entities](#-key-entities)
- [Admin Features](#-admin-features)
- [Development](#-development)
  - [Useful Commands](#useful-commands)
  - [Template Development](#template-development)
  - [Cache Management](#cache-management)
- [Security](#-security)
- [Documentation](#-documentation)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🌟 Overview

**Chronicles** is a comprehensive web application built with Symfony 7.3 for managing a rich fantasy universe. It allows users to create and organize fictional **characters**, categorize them by **species**, and further refine them into **races**. The application features a hierarchical data model with extensive character attributes, user authentication, role-based access control, and a full-featured admin panel.

This project uses Docker with FrankenPHP (a modern PHP application server) and MySQL for a complete, production-ready development environment.

This project is still in developpement, a lot of features will be hadded in the futur.

---

## ✨ Features

### Core Features
- **Character Management**: Create, view, edit, and delete characters with rich attributes (age, gender, occupation, birthplace, traits, background)
- **Species & Race System**: Hierarchical organization where characters belong to species and optionally to races
- **Search & Filtering**: Search characters by name and filter by species or race
- **User Authentication**: Secure registration and login system with email/username support
- **Role-Based Access Control**: Multiple user roles (USER, MODERATOR, ADMIN, SUPER_ADMIN) with hierarchical permissions
- **Profile Management**: Users can edit their profiles, upload avatars, and change passwords
- **Responsive Design**: Modern, mobile-friendly interface with custom CSS

### Admin Features
- **Species & Race Management**: Complete CRUD operations with image upload support
- **User Management**: Create, edit, activate/deactivate users, assign roles
- **Statistics Dashboard**: Overview of characters, species, races, and users
- **File Upload System**: Image management for species icons, race icons, and user avatars
- **Data Population**: Sample data generator for development and testing

### UI/UX Features
- **French Language Interface**: All UI text and sample data in French
- **Fantasy Theme**: Medieval/fantasy-inspired design and content
- **Icon System**: Visual representation with custom icons for species and races
- **Interactive Navigation**: Expandable sections, dropdown menus, and smooth transitions
- **Flash Messaging**: User feedback for all operations

---

## 🛠 Technology Stack

| Technology | Version | Purpose |
|------------|---------|---------|
| **Symfony** | 7.3.* | PHP framework for web application |
| **PHP** | 8.4.12+ | Programming language |
| **MySQL** | 8.0 | Database management system |
| **Doctrine ORM** | 3.5+ | Object-relational mapping |
| **Twig** | 3.* | Template engine |
| **FrankenPHP** | Latest | Modern PHP application server with Caddy |
| **Docker** | Latest | Containerization platform |
| **Docker Compose** | Latest | Multi-container orchestration |

---

## 📁 Project Structure

```
Chronicles/
├── bin/                          # Symfony console and scripts
├── config/                       # Application configuration
│   ├── packages/                 # Package-specific configs
│   └── routes/                   # Routing configuration
├── docs/                         # Documentation files
├── frankenphp/                   # FrankenPHP configuration
│   ├── Caddyfile                 # Caddy web server config
│   └── conf.d/                   # PHP configuration
├── migrations/                   # Database migrations
├── public/                       # Web root directory
│   ├── css/                      # Stylesheets
│   ├── images/                   # Image assets
│   │   ├── characters/           # Character avatars
│   │   ├── species/              # Species icons
│   │   ├── races/                # Race icons
│   │   └── user_icon/            # User profile pictures
│   └── index.php                 # Front controller
├── src/                          # Application source code
│   ├── Command/                  # Console commands
│   ├── Controller/               # Controllers
│   ├── Entity/                   # Doctrine entities
│   ├── Form/                     # Form types
│   ├── Repository/               # Doctrine repositories
│   ├── Security/                 # Security components
│   └── Validator/                # Custom validators
├── templates/                    # Twig templates
│   ├── admin/                    # Admin panel templates
│   ├── beings/                   # Species/Race views
│   ├── characters/               # Character views
│   ├── home/                     # Homepage
│   ├── registration/             # User registration
│   └── security/                 # Login/logout
├── var/                          # Cache, logs, sessions
├── vendor/                       # Composer dependencies
├── compose.yaml                  # Docker Compose configuration
├── Dockerfile                    # Docker image definition
└── README.md                     # This file
```

---

## 🚀 Getting Started

### Prerequisites

- **Docker** (version 20.10+)
- **Docker Compose** (version 2.0+)
- **Git**

### Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/Herebeyond/Chronicles.git
   cd Chronicles
   ```

2. **Build and start Docker containers**:
   ```bash
   docker compose build --pull --no-cache
   docker compose up --wait
   ```

   This will:
   - Build the FrankenPHP image with PHP 8.4.12
   - Start MySQL 8.0 database
   - Install Composer dependencies
   - Configure the Symfony application

3. **Verify containers are running**:
   ```bash
   docker compose ps
   ```

### Database Setup

1. **Run database migrations**:
   ```bash
   docker compose exec php php bin/console doctrine:migrations:migrate
   ```

2. **Verify database schema**:
   ```bash
   docker compose exec php php bin/console doctrine:schema:validate
   ```

---

## 💻 Usage

### Creating an Admin User

To create an administrator account:

```bash
docker compose exec php php bin/console app:create-admin
```

Follow the interactive prompts to set up your admin credentials.

### Populating Sample Data

To generate sample species, races, and characters for development:

```bash
docker compose exec php php bin/console app:populate-data
```

This creates:
- Multiple species (Humains, Elfes, Nains, Orcs, Dragons, etc.)
- Races for each species
- Detailed characters with French-language descriptions

### Accessing the Application

- **HTTPS**: https://localhost:9443
- **HTTP**: http://localhost:9080
- **MySQL**: `localhost:3307` (credentials in `compose.yaml`)

**Default Database Credentials**:
- User: `chronicles_user`
- Password: `ChroniquesSecurePass2024!`
- Database: `chronicles`

---

## 🗄 Database Schema

### Entities & Relationships

```
Species (1) ──→ (Many) Races
   │
   └──→ (Many) Characters ←──(Many) Races (optional)
```

### Tables

#### `species`
- `id` (Primary Key)
- `name` (varchar 255, NOT NULL)
- `description` (longtext)
- `icon` (varchar 255)
- `created_at`, `updated_at` (datetime)

#### `races`
- `id` (Primary Key)
- `species_id` (Foreign Key → species.id, NOT NULL)
- `name` (varchar 255, NOT NULL)
- `description` (longtext)
- `icon` (varchar 255)
- `created_at`, `updated_at` (datetime)

#### `characters`
- `id` (Primary Key)
- `species_id` (Foreign Key → species.id, NOT NULL)
- `race_id` (Foreign Key → races.id, nullable)
- `name` (varchar 255, NOT NULL)
- `description` (longtext)
- `avatar` (varchar 255)
- `gender` (varchar 100)
- `age` (int)
- `birthplace` (varchar 255)
- `occupation` (varchar 255)
- `traits` (JSON)
- `background` (longtext)
- `created_at`, `updated_at` (datetime)

#### `users`
- `id` (Primary Key)
- `email` (varchar 180, UNIQUE, NOT NULL)
- `username` (varchar 100, UNIQUE, NOT NULL)
- `first_name`, `last_name` (varchar 100)
- `roles` (JSON, NOT NULL)
- `password` (varchar 255, NOT NULL)
- `is_active` (boolean)
- `avatar_filename` (varchar 255)
- `created_at`, `last_login_at` (datetime)

---

## 🎯 Key Entities

### Character Entity
Rich character model with:
- **Required**: name, species
- **Optional**: race, avatar, gender, age, birthplace, occupation, background
- **Traits**: JSON array for flexible character attributes
- Relationships with Species and Race

### Species Entity
Top-level categorization:
- Examples: Humains, Elfes, Nains, Orcs, Dragons
- Can have multiple races
- Can have multiple characters
- Icon support

### Race Entity
Sub-categorization within species:
- Examples: High Elf, Wood Elf, Dark Elf (for Elfes species)
- Belongs to one species
- Can have multiple characters
- Icon support

### User Entity
Authentication and authorization:
- Email/username login
- Role hierarchy: USER → MODERATOR → ADMIN → SUPER_ADMIN
- Profile picture support
- Account activation/deactivation

---

## 🔧 Admin Features

### Species & Race Management (`/admin/species-management`)
- Create, edit, delete species and races
- Upload icons (JPG, PNG, GIF, WebP, max 2MB)
- View statistics and relationships
- Cascade delete warnings
- Interactive sidebar navigation

### User Management (`/admin/users`)
- View all registered users
- Create admin users with role assignment
- Edit user profiles and roles
- Activate/deactivate accounts
- Search and filter users
- Statistics dashboard

### File Upload System
- **Supported Formats**: JPG, PNG, GIF, WebP
- **Size Limit**: 2MB per file
- **Automatic Processing**: 
  - Unique filename generation
  - Old file cleanup on updates
  - Image preview in forms
  - Fallback handling for missing images

---

## 👨‍💻 Development

### Useful Commands

```bash
# Clear Symfony cache (run after config/template changes)
docker compose exec php php bin/console cache:clear

# Access PHP container shell
docker compose exec php sh

# View logs
docker compose logs -f php
docker compose logs -f database

# Run migrations
docker compose exec php php bin/console doctrine:migrations:migrate

# Create a new migration
docker compose exec php php bin/console make:migration

# Install dependencies
docker compose exec php composer install

# Debug routes
docker compose exec php php bin/console debug:router

# Database status
docker compose exec php php bin/console doctrine:schema:validate
```

### Template Development

**⚠️ Important**: Always consult `docs/TWIG_TEMPLATING_GUIDE.md` before working with templates to avoid common pitfalls.

**Common Issues**:
- **Duplicate Blocks**: Never define the same Twig block twice
- **Cache Issues**: Run `cache:clear` after template changes
- **Browser Cache**: Use `Ctrl + F5` for hard refresh during development

### Cache Management

After making changes to:
- Configuration files
- Templates
- Entities
- Forms

Always run:
```bash
docker compose exec php php bin/console cache:clear
```

---

## 🔒 Security

### Authentication
- Email or username-based login
- Secure password hashing with Symfony's built-in system
- Remember-me functionality
- Account activation system

### Authorization
- Role-based access control (RBAC)
- Hierarchical permissions
- Protected admin routes
- CSRF protection on all forms

### User Roles
1. **ROLE_USER**: Basic authenticated user
2. **ROLE_MODERATOR**: Extended permissions
3. **ROLE_ADMIN**: Full admin access (except user deletion)
4. **ROLE_SUPER_ADMIN**: Complete system access

### Security Features
- Strong password validation
- User switching for super admins
- Account deactivation capability
- Session management
- Protected file upload directories

---

## 📚 Documentation

Detailed documentation is available in the `docs/` directory:

- **TWIG_TEMPLATING_GUIDE.md**: Template development best practices
- **PASSWORD_VALIDATION_GUIDE.md**: Password security implementation
- **authentication.md**: Authentication system details
- **mysql.md**: Database configuration
- **troubleshooting.md**: Common issues and solutions
- **production.md**: Production deployment guide

---

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. **Fork the repository**
2. **Create a feature branch**: `git checkout -b feature/amazing-feature`
3. **Follow Symfony best practices**
4. **Update documentation**: Modify `.github/copilot-instructions.md` for significant changes
5. **Clear cache before testing**: `docker compose exec php php bin/console cache:clear`
6. **Commit your changes**: `git commit -m 'Add amazing feature'`
7. **Push to branch**: `git push origin feature/amazing-feature`
8. **Open a Pull Request**

### Development Guidelines

- Use Doctrine ORM instead of raw SQL queries
- Follow Twig templating conventions (see `TWIG_TEMPLATING_GUIDE.md`)
- Maintain French language for UI text
- Test all changes in Docker environment
- Document new features in README and copilot instructions

---

## 📄 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

- Built with [Symfony](https://symfony.com/)
- Powered by [FrankenPHP](https://frankenphp.dev/)
- Database management with [Doctrine ORM](https://www.doctrine-project.org/)
- Template engine: [Twig](https://twig.symfony.com/)

---

<div align="center">

**Chronicles** - *Managing Fantasy Universes with Symfony*

Made with ❤️ using Symfony 7.3

</div>

---
---

# Chronicles (Français)

<div align="center">

![Logo Chronicles](public/images/logos/chronicles_logo.png)

**Une application web Symfony 7.3 pour gérer des personnages, espèces et races fictifs dans un univers fantastique**

[![Symfony](https://img.shields.io/badge/Symfony-7.3-black.svg)](https://symfony.com/)
[![PHP](https://img.shields.io/badge/PHP-8.4.12-777BB4.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1.svg)](https://www.mysql.com/)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED.svg)](https://www.docker.com/)
[![Licence](https://img.shields.io/badge/Licence-MIT-green.svg)](LICENSE)

</div>

---

## 📖 Table des matières

- [Présentation](#-présentation)
- [Fonctionnalités](#-fonctionnalités)
- [Stack technologique](#-stack-technologique)
- [Structure du projet](#-structure-du-projet)
- [Démarrage](#-démarrage)
  - [Prérequis](#prérequis)
  - [Installation](#installation)
  - [Configuration de la base de données](#configuration-de-la-base-de-données)
- [Utilisation](#-utilisation)
  - [Créer un utilisateur administrateur](#créer-un-utilisateur-administrateur)
  - [Peupler avec des données d'exemple](#peupler-avec-des-données-dexemple)
  - [Accéder à l'application](#accéder-à-lapplication)
- [Schéma de la base de données](#-schéma-de-la-base-de-données)
- [Entités principales](#-entités-principales)
- [Fonctionnalités admin](#-fonctionnalités-admin)
- [Développement](#-développement)
  - [Commandes utiles](#commandes-utiles)
  - [Développement de templates](#développement-de-templates)
  - [Gestion du cache](#gestion-du-cache)
- [Sécurité](#-sécurité)
- [Documentation](#-documentation)
- [Contribuer](#-contribuer)
- [Licence](#-licence)

---

## 🌟 Présentation

**Chronicles** est une application web complète construite avec Symfony 7.3 pour gérer un univers fantastique riche. Elle permet aux utilisateurs de créer et d'organiser des **personnages** fictifs, de les catégoriser par **espèce**, et de les affiner davantage en **races**. L'application propose un modèle de données hiérarchique avec des attributs de personnage étendus, l'authentification des utilisateurs, un contrôle d'accès basé sur les rôles, et un panneau d'administration complet.

Ce projet utilise Docker avec FrankenPHP (un serveur d'applications PHP moderne) et MySQL pour un environnement de développement complet et prêt pour la production.

Ce projet est toujours en développement ; de nombreuses fonctionnalités seront ajoutées à l'avenir.

---

## ✨ Fonctionnalités

### Fonctionnalités principales
- **Gestion des personnages** : Créer, consulter, modifier et supprimer des personnages avec des attributs riches (âge, genre, métier, lieu de naissance, traits, histoire)
- **Système d'espèces et de races** : Organisation hiérarchique où les personnages appartiennent à des espèces et optionnellement à des races
- **Recherche et filtrage** : Rechercher des personnages par nom et filtrer par espèce ou race
- **Authentification des utilisateurs** : Système d'inscription et de connexion sécurisé avec prise en charge email/nom d'utilisateur
- **Contrôle d'accès par rôles** : Rôles utilisateur multiples (USER, MODERATOR, ADMIN, SUPER_ADMIN) avec permissions hiérarchiques
- **Gestion du profil** : Les utilisateurs peuvent modifier leur profil, télécharger des avatars et changer de mot de passe
- **Design responsive** : Interface moderne et mobile avec CSS personnalisé

### Fonctionnalités admin
- **Gestion des espèces et races** : Opérations CRUD complètes avec support de téléchargement d'images
- **Gestion des utilisateurs** : Créer, modifier, activer/désactiver des utilisateurs, attribuer des rôles
- **Tableau de bord statistiques** : Vue d'ensemble des personnages, espèces, races et utilisateurs
- **Système de téléchargement de fichiers** : Gestion d'images pour les icônes d'espèces, de races et les avatars d'utilisateurs
- **Peuplement de données** : Générateur de données d'exemple pour le développement et les tests

### Fonctionnalités UI/UX
- **Interface en français** : Tout le texte de l'interface et les données d'exemple en français
- **Thème fantastique** : Design et contenu inspirés du médiéval/fantastique
- **Système d'icônes** : Représentation visuelle avec des icônes personnalisées pour les espèces et races
- **Navigation interactive** : Sections extensibles, menus déroulants et transitions fluides
- **Messages flash** : Retour utilisateur pour toutes les opérations

---

## 🛠 Stack technologique

| Technologie | Version | Usage |
|-------------|---------|-------|
| **Symfony** | 7.3.* | Framework PHP pour l'application web |
| **PHP** | 8.4.12+ | Langage de programmation |
| **MySQL** | 8.0 | Système de gestion de base de données |
| **Doctrine ORM** | 3.5+ | Mapping objet-relationnel |
| **Twig** | 3.* | Moteur de templates |
| **FrankenPHP** | Latest | Serveur d'applications PHP moderne avec Caddy |
| **Docker** | Latest | Plateforme de conteneurisation |
| **Docker Compose** | Latest | Orchestration multi-conteneurs |

---

## 📁 Structure du projet

```
Chronicles/
├── bin/                          # Console Symfony et scripts
├── config/                       # Configuration de l'application
│   ├── packages/                 # Configs spécifiques aux packages
│   └── routes/                   # Configuration du routage
├── docs/                         # Fichiers de documentation
├── frankenphp/                   # Configuration FrankenPHP
│   ├── Caddyfile                 # Config du serveur web Caddy
│   └── conf.d/                   # Configuration PHP
├── migrations/                   # Migrations de base de données
├── public/                       # Répertoire racine web
│   ├── css/                      # Feuilles de style
│   ├── images/                   # Ressources images
│   │   ├── characters/           # Avatars des personnages
│   │   ├── species/              # Icônes des espèces
│   │   ├── races/                # Icônes des races
│   │   └── user_icon/            # Photos de profil utilisateur
│   └── index.php                 # Contrôleur frontal
├── src/                          # Code source de l'application
│   ├── Command/                  # Commandes console
│   ├── Controller/               # Contrôleurs
│   ├── Entity/                   # Entités Doctrine
│   ├── Form/                     # Types de formulaires
│   ├── Repository/               # Dépôts Doctrine
│   ├── Security/                 # Composants de sécurité
│   └── Validator/                # Validateurs personnalisés
├── templates/                    # Templates Twig
│   ├── admin/                    # Templates du panneau admin
│   ├── beings/                   # Vues Espèces/Races
│   ├── characters/               # Vues Personnages
│   ├── home/                     # Page d'accueil
│   ├── registration/             # Inscription utilisateur
│   └── security/                 # Connexion/déconnexion
├── var/                          # Cache, logs, sessions
├── vendor/                       # Dépendances Composer
├── compose.yaml                  # Configuration Docker Compose
├── Dockerfile                    # Définition de l'image Docker
└── README.md                     # Ce fichier
```

---

## 🚀 Démarrage

### Prérequis

- **Docker** (version 20.10+)
- **Docker Compose** (version 2.0+)
- **Git**

### Installation

1. **Cloner le dépôt** :
   ```bash
   git clone https://github.com/Herebeyond/Chronicles.git
   cd Chronicles
   ```

2. **Construire et démarrer les conteneurs Docker** :
   ```bash
   docker compose build --pull --no-cache
   docker compose up --wait
   ```

   Cela va :
   - Construire l'image FrankenPHP avec PHP 8.4.12
   - Démarrer la base de données MySQL 8.0
   - Installer les dépendances Composer
   - Configurer l'application Symfony

3. **Vérifier que les conteneurs fonctionnent** :
   ```bash
   docker compose ps
   ```

### Configuration de la base de données

1. **Exécuter les migrations de base de données** :
   ```bash
   docker compose exec php php bin/console doctrine:migrations:migrate
   ```

2. **Vérifier le schéma de la base de données** :
   ```bash
   docker compose exec php php bin/console doctrine:schema:validate
   ```

---

## 💻 Utilisation

### Créer un utilisateur administrateur

Pour créer un compte administrateur :

```bash
docker compose exec php php bin/console app:create-admin
```

Suivez les instructions interactives pour configurer vos identifiants admin.

### Peupler avec des données d'exemple

Pour générer des espèces, races et personnages d'exemple pour le développement :

```bash
docker compose exec php php bin/console app:populate-data
```

Cela crée :
- Plusieurs espèces (Humains, Elfes, Nains, Orcs, Dragons, etc.)
- Des races pour chaque espèce
- Des personnages détaillés avec descriptions en français

### Accéder à l'application

- **HTTPS** : https://localhost:9443
- **HTTP** : http://localhost:9080
- **MySQL** : `localhost:3307` (identifiants dans `compose.yaml`)

**Identifiants de base de données par défaut** :
- Utilisateur : `chronicles_user`
- Mot de passe : `ChroniquesSecurePass2024!`
- Base de données : `chronicles`

---

## 🗄 Schéma de la base de données

### Entités et relations

```
Species (1) ──→ (Many) Races
   │
   └──→ (Many) Characters ←──(Many) Races (optionnel)
```

### Tables

#### `species` (espèces)
- `id` (Clé primaire)
- `name` (varchar 255, NOT NULL)
- `description` (longtext)
- `icon` (varchar 255)
- `created_at`, `updated_at` (datetime)

#### `races`
- `id` (Clé primaire)
- `species_id` (Clé étrangère → species.id, NOT NULL)
- `name` (varchar 255, NOT NULL)
- `description` (longtext)
- `icon` (varchar 255)
- `created_at`, `updated_at` (datetime)

#### `characters` (personnages)
- `id` (Clé primaire)
- `species_id` (Clé étrangère → species.id, NOT NULL)
- `race_id` (Clé étrangère → races.id, nullable)
- `name` (varchar 255, NOT NULL)
- `description` (longtext)
- `avatar` (varchar 255)
- `gender` (varchar 100)
- `age` (int)
- `birthplace` (varchar 255)
- `occupation` (varchar 255)
- `traits` (JSON)
- `background` (longtext)
- `created_at`, `updated_at` (datetime)

#### `users` (utilisateurs)
- `id` (Clé primaire)
- `email` (varchar 180, UNIQUE, NOT NULL)
- `username` (varchar 100, UNIQUE, NOT NULL)
- `first_name`, `last_name` (varchar 100)
- `roles` (JSON, NOT NULL)
- `password` (varchar 255, NOT NULL)
- `is_active` (boolean)
- `avatar_filename` (varchar 255)
- `created_at`, `last_login_at` (datetime)

---

## 🎯 Entités principales

### Entité Character (Personnage)
Modèle de personnage riche avec :
- **Requis** : nom, espèce
- **Optionnel** : race, avatar, genre, âge, lieu de naissance, métier, histoire
- **Traits** : Tableau JSON pour des attributs de personnage flexibles
- Relations avec Species et Race

### Entité Species (Espèce)
Catégorisation de premier niveau :
- Exemples : Humains, Elfes, Nains, Orcs, Dragons
- Peut avoir plusieurs races
- Peut avoir plusieurs personnages
- Support d'icônes

### Entité Race
Sous-catégorisation au sein des espèces :
- Exemples : Haut-Elfe, Elfe des bois, Elfe noir (pour l'espèce Elfes)
- Appartient à une espèce
- Peut avoir plusieurs personnages
- Support d'icônes

### Entité User (Utilisateur)
Authentification et autorisation :
- Connexion par email/nom d'utilisateur
- Hiérarchie des rôles : USER → MODERATOR → ADMIN → SUPER_ADMIN
- Support de photo de profil
- Activation/désactivation de compte

---

## 🔧 Fonctionnalités admin

### Gestion des espèces et races (`/admin/species-management`)
- Créer, modifier, supprimer des espèces et races
- Télécharger des icônes (JPG, PNG, GIF, WebP, max 2 Mo)
- Voir les statistiques et relations
- Alertes de suppression en cascade
- Navigation par barre latérale interactive

### Gestion des utilisateurs (`/admin/users`)
- Voir tous les utilisateurs inscrits
- Créer des utilisateurs admin avec attribution de rôles
- Modifier les profils et rôles utilisateurs
- Activer/désactiver des comptes
- Rechercher et filtrer des utilisateurs
- Tableau de bord statistiques

### Système de téléchargement de fichiers
- **Formats supportés** : JPG, PNG, GIF, WebP
- **Limite de taille** : 2 Mo par fichier
- **Traitement automatique** :
  - Génération de nom de fichier unique
  - Nettoyage des anciens fichiers lors des mises à jour
  - Aperçu d'image dans les formulaires
  - Gestion de repli pour les images manquantes

---

## 👨‍💻 Développement

### Commandes utiles

```bash
# Vider le cache Symfony (à exécuter après modification de config/templates)
docker compose exec php php bin/console cache:clear

# Accéder au shell du conteneur PHP
docker compose exec php sh

# Voir les logs
docker compose logs -f php
docker compose logs -f database

# Exécuter les migrations
docker compose exec php php bin/console doctrine:migrations:migrate

# Créer une nouvelle migration
docker compose exec php php bin/console make:migration

# Installer les dépendances
docker compose exec php composer install

# Déboguer les routes
docker compose exec php php bin/console debug:router

# Statut de la base de données
docker compose exec php php bin/console doctrine:schema:validate
```

### Développement de templates

**⚠️ Important** : Consultez toujours `docs/TWIG_TEMPLATING_GUIDE.md` avant de travailler avec les templates pour éviter les pièges courants.

**Problèmes courants** :
- **Blocs dupliqués** : Ne jamais définir le même bloc Twig deux fois
- **Problèmes de cache** : Exécutez `cache:clear` après modifications de templates
- **Cache du navigateur** : Utilisez `Ctrl + F5` pour rafraîchissement forcé pendant le développement

### Gestion du cache

Après avoir modifié :
- Fichiers de configuration
- Templates
- Entités
- Formulaires

Toujours exécuter :
```bash
docker compose exec php php bin/console cache:clear
```

---

## 🔒 Sécurité

### Authentification
- Connexion par email ou nom d'utilisateur
- Hachage sécurisé des mots de passe avec le système intégré de Symfony
- Fonctionnalité "Se souvenir de moi"
- Système d'activation de compte

### Autorisation
- Contrôle d'accès basé sur les rôles (RBAC)
- Permissions hiérarchiques
- Routes admin protégées
- Protection CSRF sur tous les formulaires

### Rôles utilisateur
1. **ROLE_USER** : Utilisateur authentifié de base
2. **ROLE_MODERATOR** : Permissions étendues
3. **ROLE_ADMIN** : Accès admin complet (sauf suppression d'utilisateurs)
4. **ROLE_SUPER_ADMIN** : Accès système complet

### Fonctionnalités de sécurité
- Validation de mots de passe forts
- Changement d'utilisateur pour les super admins
- Capacité de désactivation de compte
- Gestion de session
- Répertoires de téléchargement de fichiers protégés

---

## 📚 Documentation

Une documentation détaillée est disponible dans le répertoire `docs/` :

- **TWIG_TEMPLATING_GUIDE.md** : Bonnes pratiques de développement de templates
- **PASSWORD_VALIDATION_GUIDE.md** : Implémentation de la sécurité des mots de passe
- **authentication.md** : Détails du système d'authentification
- **mysql.md** : Configuration de la base de données
- **troubleshooting.md** : Problèmes courants et solutions
- **production.md** : Guide de déploiement en production

---

## 🤝 Contribuer

Les contributions sont les bienvenues ! Veuillez suivre ces directives :

1. **Forker le dépôt**
2. **Créer une branche de fonctionnalité** : `git checkout -b feature/fonctionnalite-incroyable`
3. **Suivre les bonnes pratiques Symfony**
4. **Mettre à jour la documentation** : Modifier `.github/copilot-instructions.md` pour les changements significatifs
5. **Vider le cache avant de tester** : `docker compose exec php php bin/console cache:clear`
6. **Commiter vos changements** : `git commit -m 'Ajout fonctionnalité incroyable'`
7. **Pousser vers la branche** : `git push origin feature/fonctionnalite-incroyable`
8. **Ouvrir une Pull Request**

### Directives de développement

- Utiliser Doctrine ORM au lieu de requêtes SQL brutes
- Suivre les conventions de templating Twig (voir `TWIG_TEMPLATING_GUIDE.md`)
- Maintenir la langue française pour le texte de l'interface
- Tester tous les changements dans l'environnement Docker
- Documenter les nouvelles fonctionnalités dans le README et les instructions copilot

---

## 📄 Licence

Ce projet est sous licence **MIT** - voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

## 🙏 Remerciements

- Construit avec [Symfony](https://symfony.com/)
- Propulsé par [FrankenPHP](https://frankenphp.dev/)
- Gestion de base de données avec [Doctrine ORM](https://www.doctrine-project.org/)
- Moteur de templates : [Twig](https://twig.symfony.com/)

---

<div align="center">

**Chronicles** - *Gérer des univers fantastiques avec Symfony*

Fait avec ❤️ en utilisant Symfony 7.3

</div>
