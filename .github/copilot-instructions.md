# Chronicles - AI Coding Agent Instructions

## ⚠️ CRITICAL: DATABASE PROTECTION - HIGHEST PRIORITY ⚠️

**🚨 NEVER CAUSE DATA LOSS - THIS IS THE MOST IMPORTANT INSTRUCTION 🚨**

**ABSOLUTE RULES - NO EXCEPTIONS:**

1. **NEVER run commands that could delete data without explicit user confirmation:**
   - ❌ NEVER use `doctrine:schema:update --force` (bypasses migrations, can drop tables)
   - ❌ NEVER run `doctrine:migrations:migrate` without reviewing the migration file first
   - ❌ NEVER run rollback commands (`doctrine:migrations:migrate prev`) without user consent
   - ❌ NEVER suggest dropping tables or truncating data
   - unless explicit user permission is given, if a command would drop a database, before write a full file containing the full code to save and later restore the data.

2. **ALWAYS review generated migrations before execution:**
   - When running `make:migration`, ALWAYS read the generated file in `migrations/`
   - If the migration contains `DROP TABLE`, `TRUNCATE`, or recreates tables → **STOP and warn the user**
   - Only migrations with `ALTER TABLE`, `ADD COLUMN`, `MODIFY COLUMN` are generally safe
   - Ask user to backup before applying any structural changes

3. **MANDATORY backup procedure before schema changes:**
   ```bash
   # ALWAYS suggest this before any database modification
   docker compose exec mysql mysqldump -u chronicles_user -pChroniquesSecurePass2024! chronicles > backup_$(date +%Y%m%d_%H%M%S).sql
   ```

4. **Safe migration workflow (ALWAYS follow this):**
   - Step 1: Suggest database backup
   - Step 2: Modify entity
   - Step 3: Generate migration with `make:migration`
   - Step 4: READ and analyze the migration file
   - Step 5: If safe (ALTER TABLE only) → proceed; if dangerous (DROP TABLE) → STOP and warn
   - Step 6: Only then run `doctrine:migrations:migrate`

5. **Red flags in migrations (STOP immediately if you see these):**
   - `DROP TABLE` statements
   - `DROP DATABASE` statements
   - Creating tables that already exist (means tracking is out of sync)
   - Any SQL that removes columns without explicit user permission

**If in doubt about data safety: STOP, EXPLAIN THE RISK, and ASK THE USER.**

## Project Overview

Chronicles is a Symfony 7.3 web application for managing fictional characters, species, and races in a fantasy universe. Built with Docker, FrankenPHP, and MySQL, it follows a hierarchical data model where Characters belong to Species, and optionally to Races within those Species.

**Important:** 
- This project (`Chronicles/`) is a complete Symfony rebuild of an older manually-built project located in the sibling `Docker/` folder. 
- The old project serves as a reference and **should not be modified**. 
- All development work should be done in the Chronicles Symfony project and using symfony best practices.
- When copying code or patterns from the old project, ensure it is adapted to Symfony conventions and the new architecture.
  - For example, replace raw SQL queries with Doctrine ORM methods, and convert PHP templates to Twig.
- When modifying/adding/deleting features, always update this instruction file to document the changes for future reference.
- When modifying/adding/deleting features, always finish by running the command `docker compose exec php php bin/console cache:clear` to ensure the cache is up to date.
- if a change is expected for a later date, add a TODO comment to find it again later

## Database Schema

**📄 See [docs/DATABASE_SCHEMA.md](../docs/DATABASE_SCHEMA.md) for complete database structure.**

The database schema document contains:
- All table structures with field types and constraints
- Entity relationships and foreign keys
- Key database rules and conventions
- Enumeration values for status fields
- Migration history

**Quick Reference:**
- Hierarchical model: Species → Races → Characters
- All entities use `DateTimeImmutable` for timestamps
- User roles via many-to-many relationship (no JSON column)
- World events use custom calendar (year/month/day integers)

## Architecture & Entity Conventions

- All entities use `DateTimeImmutable` for timestamps with auto-initialization in constructors
- JSON fields for complex data (e.g., Character `$traits`, Idea `$tags`)
- Nullable fields extensively used for optional attributes
- Repository classes follow naming: `EntityRepository` (e.g., `CharacterRepository`)
- Hierarchical model: Species → Races → Characters (see [DATABASE_SCHEMA.md](../docs/DATABASE_SCHEMA.md))

## Development Environment

### Docker Setup
- **Main service:** `php` (FrankenPHP with Caddy)
- **Database:** MySQL 8.0 on port 3307 (externally)
- **Database Management:** phpMyAdmin on port 8081 (http://localhost:8081)
- **Default credentials:** `chronicles_user` / `ChroniquesSecurePass2024!`
- **Database:** `chronicles`

**Key Commands:**
```bash
# Build and start
docker compose build --pull --no-cache
docker compose up --wait

# Access via https://localhost:9443 or http://localhost:9080
# phpMyAdmin available at http://localhost:8081
```

### Default Admin User
**IMPORTANT:** A default admin user is automatically created by migrations:
- **Username:** `Nox`
- **Email:** `baillard.bjm2@orange.fr`
- **Role:** `ROLE_SUPER_ADMIN`, `ROLE_ADMIN`, `ROLE_USER`

This user is created in migration `Version20250919113508` to ensure there's always an admin account available after database initialization or reset. **This is your personal admin account - keep the credentials secure.**

### Symfony Console
- Located at `bin/console` 
- Custom command: `app:populate-data` - seeds database with sample Species, Races, and Characters
- Standard Symfony commands available for migrations, cache, etc.

## Controller & Template Patterns

### Controllers
- Repository injection via constructor or method parameters
- Consistent search/filtering logic using repository methods
- Standard route patterns: `#[Route('/entity', name: 'entity_index')]`

### Templates
- **Base**: French language, `templates/base.html.twig`, color scheme #2c3e50 / #34495e
- **Two layouts**: Homepage (two-column with sidebar via `leftContent` block) vs. single-column
- **Blocks**: `title`, `stylesheets`, `leftContent`, `body`, `javascripts`
- **CSS**: Centralized in `public/css/style.css` - no inline styles or `<style>` tags in templates
- **Security**: `is_granted('ROLE_ADMIN')` for admin features

## Data Population

### PopulateDataCommand (`php bin/console app:populate-data`)
Seeds database with realistic sample data:
- Clears existing data (Characters → Races → Species)
- Creates hierarchical fantasy data: "Humains", "Elfes", "Nains", "Orcs" with associated Races and Characters
- Rich French-language descriptions and varied attributes

## Database Considerations

- **Migrations**: Located in `migrations/`, uses Doctrine with proper foreign key relationships
- **Connection**: Configured via `DATABASE_URL` environment variable
- **Charset**: `utf8mb4` for full Unicode support, MySQL 8.0 specific settings

## Project-Specific Conventions

1. **French Language:** All UI text, descriptions, and sample data in French
2. **Fantasy Theme:** Medieval/fantasy character attributes (occupation, birthplace, background)
3. **Nullable-First:** Most character attributes are optional with extensive null handling
4. **Hierarchical Display:** Characters always shown in Species → Race → Character groupings
5. **Rich Metadata:** Characters include detailed backgrounds, traits (JSON), and descriptions

## Key Files for Context

- `src/Entity/Character.php` - Main domain model with all relationships
- `src/Controller/CharactersController.php` - Primary business logic patterns
- `src/Command/PopulateDataCommand.php` - Sample data structure
- `compose.yaml` - Docker configuration
- `templates/base.html.twig` - UI framework
- `public/css/style.css` - Centralized styling

## Development Guidelines

### Problem Documentation & Maintenance
**Critical:** When a problem is found and dealt with, modify this instruction file to add the problem nature and solution. This ensures future AI agents and developers can benefit from resolved issues and prevents recurring problems.

**Template Development**: Always consult `docs/TWIG_TEMPLATING_GUIDE.md` before making template changes to avoid common pitfalls, especially duplicate block definitions.

### Admin Management System

#### Species & Race Management
- **Admin Panel** (`/admin/species-management`) - Complete CRUD interface for Species and Races:
  - Interactive sidebar navigation between Species, Races, and Statistics sections
  - File upload support for Species and Race icons with image preview
  - Entity relationship management (Races belong to Species)
  - Statistics dashboard showing counts and relationships
  - Cascade delete warnings for data integrity
- **File Upload System**: 
  - Images stored in `public/images/species/` and `public/images/races/` directories
  - Supports JPG, PNG, GIF, WebP formats (max 2MB)
  - Automatic unique filename generation with timestamp
  - Old file cleanup when uploading new images
  - Image preview in edit forms with fallback handling
- **Forms**: 
  - **SpeciesType** (`src/Form/SpeciesType.php`) - Species creation/editing with file upload
  - **RaceType** (`src/Form/RaceType.php`) - Race creation/editing with Species selection and file upload
  - All forms use English labels and help text
  - Proper validation constraints for file uploads
- **Controller**: **SpeciesManagementController** (`src/Controller/Admin/SpeciesManagementController.php`)
  - Complete CRUD operations with file handling
  - SluggerInterface integration for safe filename generation
  - Proper error handling and flash messages
  - Image cleanup on updates and deletions

- At the end of every chat request, check if any information here needs updating such as, but not limited to:
  - New added pages or features
  - Modified existing pages or features
  - Structural changes
  - Changes in dependencies or versions
  - Changes in architecture or patterns
  - Important commands or workflows

## Common Issues & Solutions

**⚠️ See `docs/TWIG_TEMPLATING_GUIDE.md` for template-specific issues and patterns**

### Critical Issues
- **Duplicate Twig Blocks**: Never define same block multiple times - see TWIG_TEMPLATING_GUIDE.md
- **Template Cache**: Run `docker compose exec php php bin/console cache:clear` after changes
- **Browser Cache**: Use `Ctrl + F5` or disable cache in DevTools during development

### Database & Doctrine
- **JSON Functions**: DQL doesn't support MySQL JSON functions - use LIKE queries instead
  - `u.roles LIKE '%ROLE_ADMIN%'` instead of `JSON_CONTAINS(u.roles, 'ROLE_ADMIN')`
- **Entity Properties**: Always verify entity fields exist before using in templates
- **Docker Containers**: Check with `docker compose ps`, restart with `docker compose up --wait`

### Performance
- **Cache-busting timestamps**: Remove `?v={{ 'now'|date('U') }}` from assets to enable browser caching
- **APCu Cache**: Stick to default Doctrine configuration for development

## User Management System

### Core Components
- **User Entity**: Many-to-many with Role via `user_roles` junction table, email/username auth, avatar support
- **Role Entity**: ROLE_USER → ROLE_MODERATOR → ROLE_ADMIN → ROLE_SUPER_ADMIN (created by migrations)
- **Admin Panel** (`/admin/users`): Full CRUD, role management, requires ROLE_ADMIN
- **User Profile** (`/profile`): View/edit info, avatar upload, password change
- **Avatar System**: `public/images/user_icon/`, JPG/PNG/GIF/WebP (max 2MB)

### Key Routes
- `/register` - Public registration (role-less by default)
- `/login` - Email or username authentication
- `/admin/users` - Admin user management (ROLE_ADMIN)
- `/profile` - User profile and settings

## Ideas Management System

World-building idea tracker with hierarchical organization, categorization, and bulk operations.

### Key Features
- CRUD operations, parent-child hierarchies, advanced filtering, bulk import/export
- **Certainty Levels**: Idea → Not_Sure → Developing → Established → Canon
- **Status Values**: Draft → Need_Correction → In_Progress → Review → Finalized → Archived
- Dynamic categories (Magic_Systems, Creatures, Gods_Demons, etc.) + JSON tags

### Main Routes & Commands
- `/ideas` - Main index with filtering, quick-add modal, bulk import
- `/ideas/create`, `/ideas/{id}/edit` - CRUD operations
- `/ideas/export` - Export all ideas to text file
- `php bin/console app:init-ideas` - Initialize default categories
- `php bin/console app:init-ideas --with-samples` - With sample data

### Bulk Import Format
```
Title: Idea Title Here
Content: Your idea content...
Tags: tag1, tag2

---

Title: Another Idea
Content: More content...
```

## World Events & Timeline System

Chronological event tracker with horizontal Gantt-style timeline and custom 360-day calendar (12 months × 30 days).

### Key Features
- **Custom Calendar**: Primis through Duodecimus (12 months, 30 days each)
- **Timeline Visualization**: Colored bars showing event duration, animated gradient for ongoing events (NULL end date)
- **Admin Management** (`/admin/events`): Full CRUD, color customization per event
- **Chronological Sorting**: Auto-sorted by start_year/month/day, then by id

### Main Routes
**Public**: `/events` - Timeline visualization
**Admin**: `/admin/events`, `/admin/calendar` - Event and month management

### Timeline Calculation
- **Year Span**: `latestYear - earliestYear + 1`
- **Start Offset**: `((startYear - earliestYear) * 12 + startMonth) / (yearSpan * 12) * 100%`
- **Width**: Ongoing events use `100% - startOffset`, completed events calculated from duration

### Best Practices
- **Ongoing events**: Use NULL end dates, not far-future dates
- **Start dates**: Must be before end dates (validation enforced)
- **Color coding**: Use distinct hex colors for different event types

When working with this codebase, prioritize maintaining the hierarchical Species→Race→Character model and the rich French-language fantasy theme throughout all additions.