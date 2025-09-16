# Chronicles - AI Coding Agent Instructions

## Project Overview

Chronicles is a Symfony 7.3 web application for managing fictional characters, species, and races in a fantasy universe. Built with Docker, FrankenPHP, and MySQL, it follows a hierarchical data model where Characters belong to Species, and optionally to Races within those Species.

**Important:** This project (`Chronicles/`) is a complete Symfony rebuild of an older manually-built project located in the sibling `Docker/` folder. The old project serves as a reference and **should not be modified**. All development work should be done in the Chronicles Symfony project.

## Architecture & Data Model

### Core Entities (src/Entity/)
- **Species** (1:Many Characters, 1:Many Races) - Top-level categorization (e.g., "Humains", "Elfes")
- **Race** (Many:1 Species, 1:Many Characters) - Subcategorization within species (e.g., "Nobles", "Artisans")
- **Character** (Many:1 Species, Many:1 Race) - Individual characters with rich attributes

**Key Relationships:**
```php
// Character -> Species (required)
#[ORM\ManyToOne(inversedBy: 'characters')]
#[ORM\JoinColumn(nullable: false)]
private ?Species $species = null;

// Character -> Race (optional)
#[ORM\ManyToOne(inversedBy: 'characters')]
private ?Race $race = null;
```

### Entity Conventions
- All entities use `DateTimeImmutable` for timestamps with auto-initialization in constructors
- JSON fields for complex data (e.g., Character `$traits`)
- Nullable fields are extensively used for optional character attributes
- Repository classes follow naming: `EntityRepository` (e.g., `CharacterRepository`)

## Development Environment

### Docker Setup
- **Main service:** `php` (FrankenPHP with Caddy)
- **Database:** MySQL 8.0 on port 3307 (externally)
- **Default credentials:** `chronicles_user` / `ChroniquesSecurePass2024!`
- **Database:** `chronicles`

**Key Commands:**
```bash
# Build and start
docker compose build --pull --no-cache
docker compose up --wait

# Access via https://localhost:9443 or http://localhost:9080
```

### Symfony Console
- Located at `bin/console` 
- Custom command: `app:populate-data` - seeds database with sample Species, Races, and Characters
- Standard Symfony commands available for migrations, cache, etc.

## Controller Patterns

### Repository Injection
Controllers consistently inject repositories as constructor or method parameters:
```php
public function index(
    CharacterRepository $characterRepository, 
    SpeciesRepository $speciesRepository,
    Request $request
): Response
```

### Search & Filtering Logic
- **CharactersController** implements search by name and species filtering
- Uses repository methods like `findByNameSearch()`, `findBySpecies()`, `findGroupedBySpecies()`
- Results are grouped/organized for display (e.g., characters grouped by race within species)

### Route Patterns
```php
#[Route('/characters', name: 'characters_index')]
#[Route('/characters/species/{speciesId}', name: 'characters_by_species', requirements: ['speciesId' => '\d+'])]
#[Route('/characters/{characterId}', name: 'characters_show', requirements: ['characterId' => '\d+'])]
```

## Template Structure

### Base Template (`templates/base.html.twig`)
- French language (`lang="fr"`)
- Built-in CSS styling (no external framework)
- Navigation structure with responsive design
- Color scheme: #2c3e50 (primary), #34495e (secondary)

### Template Inheritance
- Extends `base.html.twig` consistently
- Uses blocks: `title`, `page_title`, `body`
- Admin functionality controlled with `is_granted('ROLE_ADMIN')` checks

### Content Organization
- Templates mirror controller structure: `characters/`, `admin/`, `beings/`, `home/`
- Heavy use of inline styling for component-specific CSS
- Form elements follow consistent styling patterns

## Data Population

### PopulateDataCommand
Critical for development - creates realistic sample data:
- Clears existing data (Characters → Races → Species)
- Creates hierarchical data: Species with associated Races and Characters
- Characters have rich French-language descriptions and varied attributes
- Fantasy theme with species like "Humains", "Elfes", "Nains", "Orcs"

## Database Considerations

### Migrations
- Located in `migrations/` directory
- Uses Doctrine migrations with proper foreign key relationships
- Schema designed with `utf8mb4` charset for full Unicode support

### Connection
- Configured via `DATABASE_URL` environment variable
- MySQL-specific settings: `serverVersion=8.0&charset=utf8mb4`

## Project-Specific Conventions

1. **French Language:** All UI text, descriptions, and sample data in French
2. **Fantasy Theme:** Medieval/fantasy character attributes (occupation, birthplace, background)
3. **Nullable-First:** Most character attributes are optional with extensive null handling
4. **Hierarchical Display:** Characters always shown in Species → Race → Character groupings
5. **Rich Metadata:** Characters include detailed backgrounds, traits (JSON), and descriptions

## Key Files for Context

- `src/Entity/Character.php` - Main domain model with all relationships
- `src/Controller/CharactersController.php` - Primary business logic patterns
- `src/Command/PopulateDataCommand.php` - Sample data structure and relationships
- `compose.yaml` - Docker configuration and environment variables
- `templates/base.html.twig` - UI framework and styling approach

## Development Guidelines

### Problem Documentation & Maintenance
**Critical:** When a problem is found and dealt with, modify this instruction file to add the problem nature and solution. This ensures future AI agents and developers can benefit from resolved issues and prevents recurring problems.
- At the end of every chat request, check if any information here needs updating such as, but not limited to:
  - New added pages or features
  - Modified existing pages or features
  - Structural changes
  - Changes in dependencies or versions
  - Changes in architecture or patterns
  - Important commands or workflows

#### Common Issues & Solutions
- **Missing Symfony Asset Component**: If templates using `asset()` function fail with "Unknown function 'asset'" error, install with `composer require symfony/asset` and clear cache
- **Duplicate Twig Blocks**: Template inheritance errors like "block already defined" require removing duplicate block definitions while preserving functionality
- **Docker Container Issues**: Ensure containers are running with `docker compose ps` and restart with `docker compose up --wait` if needed

When working with this codebase, prioritize maintaining the hierarchical Species→Race→Character model and the rich French-language fantasy theme throughout all additions.