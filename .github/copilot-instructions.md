# Chronicles - AI Coding Agent Instructions

## ⚠️ CRITICAL: DATABASE PROTECTION - HIGHEST PRIORITY ⚠️

**🚨 NEVER CAUSE DATA LOSS - THIS IS THE MOST IMPORTANT INSTRUCTION 🚨**

**ABSOLUTE RULES - NO EXCEPTIONS:**

1. **NEVER run commands that could delete data without explicit user confirmation:**
   - ❌ NEVER use `doctrine:schema:update --force` (bypasses migrations, can drop tables)
   - ❌ NEVER run `doctrine:migrations:migrate` without reviewing the migration file first
   - ❌ NEVER run rollback commands (`doctrine:migrations:migrate prev`) without user consent
   - ❌ NEVER suggest dropping tables or truncating data

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

## Database Schema (Chronicles MySQL Database)

**Important:** The database has final authority. Always check this schema before making assumptions about available fields.

### Current Database Structure

**`species` table:**
- `id` (int, AUTO_INCREMENT, PRIMARY KEY)
- `name` (varchar(255), NOT NULL)
- `description` (longtext, nullable)
- `icon` (varchar(255), nullable)
- `created_at` (datetime, NOT NULL, immutable)
- `updated_at` (datetime, nullable, immutable)

**`races` table:**
- `id` (int, AUTO_INCREMENT, PRIMARY KEY)  
- `species_id` (int, NOT NULL, FOREIGN KEY to species.id)
- `name` (varchar(255), NOT NULL)
- `description` (longtext, nullable)
- `icon` (varchar(255), nullable)
- `created_at` (datetime, NOT NULL, immutable)
- `updated_at` (datetime, nullable, immutable)

**`characters` table:**
- `id` (int, AUTO_INCREMENT, PRIMARY KEY)
- `species_id` (int, NOT NULL, FOREIGN KEY to species.id)
- `race_id` (int, nullable, FOREIGN KEY to races.id)
- `name` (varchar(255), NOT NULL)
- `description` (longtext, nullable)
- `avatar` (varchar(255), nullable)
- `gender` (varchar(100), nullable)
- `age` (int, nullable)
- `birthplace` (varchar(255), nullable)
- `occupation` (varchar(255), nullable)
- `traits` (json, nullable)
- `background` (longtext, nullable)
- `created_at` (datetime, NOT NULL, immutable)
- `updated_at` (datetime, nullable, immutable)

**`users` table:**
- `id` (int, AUTO_INCREMENT, PRIMARY KEY)
- `email` (varchar(180), NOT NULL, UNIQUE)
- `username` (varchar(100), NOT NULL, UNIQUE)
- `first_name` (varchar(100), nullable)
- `last_name` (varchar(100), nullable)
- `roles` (json, NOT NULL)
- `password` (varchar(255), NOT NULL)
- `is_active` (tinyint(1), NOT NULL)
- `created_at` (datetime, NOT NULL, immutable)
- `last_login_at` (datetime, nullable, immutable)

**`ideas` table:**
- `id_idea` (int, AUTO_INCREMENT, PRIMARY KEY)
- `parent_idea_id` (int, nullable, FOREIGN KEY to ideas.id_idea, ON DELETE SET NULL)
- `title` (varchar(255), NOT NULL)
- `content` (longtext, NOT NULL)
- `category` (varchar(100), NOT NULL)
- `certainty_level` (varchar(50), NOT NULL) - Values: Idea, Not_Sure, Developing, Established, Canon
- `status` (varchar(50), nullable) - Values: Draft, Need_Correction, In_Progress, Review, Finalized, Archived
- `tags` (json, nullable)
- `comments` (longtext, nullable)
- `inspiration_source` (varchar(255), nullable)
- `priority` (int, nullable)
- `created_at` (datetime, NOT NULL, immutable)
- `updated_at` (datetime, nullable, immutable)

**`idea_categories` table:**
- `id` (int, AUTO_INCREMENT, PRIMARY KEY)
- `name` (varchar(100), NOT NULL, UNIQUE)
- `is_default` (tinyint(1), NOT NULL)
- `created_at` (datetime, NOT NULL, immutable)

### Entity Relationships
- **Species** (1:Many) **→** **Races** (Many:1) **Species**
- **Species** (1:Many) **→** **Characters** (Many:1) **Species** 
- **Race** (1:Many) **→** **Characters** (Many:1) **Race** (nullable)
- **Character** belongs to **Species** (required) and optionally to **Race**
- **Idea** (Self-referential) **→** **Parent Idea** (Many:1) - Ideas can have parent-child relationships for hierarchical organization

### Key Database Rules
1. **Characters must belong to a Species** (species_id NOT NULL)
2. **Characters can optionally belong to a Race** (race_id nullable)
3. **Races must belong to a Species** (species_id NOT NULL)
4. **All entities use DateTimeImmutable** for timestamps
5. **No lifespan, homeworld, or other extended race properties exist** - templates should not reference these

## Architecture & Data Model

### Core Entities (src/Entity/)
- **Species** (1:Many Characters, 1:Many Races) - Top-level categorization (e.g., "Humains", "Elfes", "Dragons")
- **Race** (Many:1 Species, 1:Many Characters) - Subcategorization within species (e.g., "Wyvern", "Blood Elf", "Mountain Dwarf")
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

## Template System & Twig Guidelines

**⚠️ CRITICAL**: See `docs/TWIG_TEMPLATING_GUIDE.md` for detailed templating patterns and common pitfalls.

### Common Twig Errors to Avoid
- **Duplicate Block Definitions**: Never define the same block multiple times, even in conditional branches
  - ❌ Wrong: `{% block body %}...{% endblock %}` in both IF and ELSE branches
  - ✅ Correct: Define once with `{% block body %}...{% endblock %}`, reuse with `{{ block('body') }}`
- **Template Cache Issues**: Always run `php bin/console cache:clear` after template changes

### Base Template Structure
- **Two-layout system**: Homepage (two-column with sidebar) vs. other pages (single-column)
- **Available blocks**: `title`, `stylesheets`, `leftContent` (homepage only), `body`, `javascripts`
- **Block inheritance**: `leftContent` triggers homepage layout, all other templates use standard layout

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

#### Common Issues & Solutions
- **Missing Symfony Asset Component**: If templates using `asset()` function fail with "Unknown function 'asset'" error, install with `composer require symfony/asset` and clear cache
- **Duplicate Twig Blocks**: Template inheritance errors like "block already defined" require removing duplicate block definitions while preserving functionality. **See `docs/TWIG_TEMPLATING_GUIDE.md` for detailed solutions and patterns**
- **JSON_LENGTH Function Error**: DQL error "Expected known function, got 'JSON_LENGTH'" occurs when using MySQL JSON functions in Doctrine queries. Solution: Replace with standard comparisons like `u.roles != '[]'` instead of `JSON_LENGTH(u.roles) > 0`
- **JSON_CONTAINS Function Error**: DQL error "Expected known function, got 'JSON_CONTAINS'" occurs with MySQL JSON functions in Doctrine. Solution: Use LIKE queries like `u.roles LIKE '%ROLE_ADMIN%'` instead of `JSON_CONTAINS(u.roles, 'ROLE_ADMIN')`
- **Docker Container Issues**: Ensure containers are running with `docker compose ps` and restart with `docker compose up --wait` if needed
- **Template Changes Not Visible**: Browser caching is the most common cause. Solutions: 
- cd "c:\Users\baill\Docker\Chronicles"; docker compose exec php php bin/console cache:clear
- Hard refresh (`Ctrl + F5`)
- clear browser cache
- use Developer Tools with "Disable cache" checked during development.
- **commands taking a long time**: some commands, especially those involving database migrations or data population, may take longer than expected. do not interrupt them prematurely and allow them to complete by waiting or manipulating files that should be changed later in the process during the wait. that way the terminal will be undisturbed and can complete the command.
- **Performance Issues & ERR_EMPTY_RESPONSE**: 
  - **Cause**: Cache-busting timestamp in CSS asset (`?v={{ 'now'|date('U') }}`) prevents browser caching and slows down every page load
  - **Solution**: Remove timestamp from `base.html.twig` CSS link to enable proper browser caching
  - **Doctrine Cache Issues**: APCu cache driver may not be available in PHP container - stick to default Doctrine configuration for development
  - **Container Crashes**: Invalid cache configurations can cause PHP container to fail startup with "Unknown cache type" errors
- **CSS and Styling**: 
  - **Centralized CSS**: All styling is consolidated in `public/css/style.css` - no templates should contain `<style>` tags or extensive inline styles
  - **CSS Classes**: Templates use semantic CSS classes like `.profile-container`, `.form-group`, `.alert`, `.btn-profile`, etc.
  - **Form Styling**: Consistent form classes (`.form-input`, `.form-label`, `.form-button`) are used across all forms
  - **Template Maintenance**: When adding new templates, use existing CSS classes or add new styles to the main CSS file, never inline styles
  - **Responsive Design**: Ensure new templates and components are responsive and maintain the overall look and feel of the application
  - **Duplicate Styles**: Avoid duplicating styles in multiple templates; always refer to the main CSS file for consistency
- **Entity Property Errors**: Templates trying to access non-existent entity properties (e.g., `race.lifespan`, `race.homeworld`) will cause runtime errors
  - **Cause**: Templates created before database schema finalization or copied from older versions
  - **Solution**: Always check entity classes and database schema before accessing properties in templates
  - **Fixed**: `beings/index.html.twig` and `beings/display.html.twig` - removed references to non-existent Race properties (`lifespan`, `homeworld`, `country`, `habitat`)
  - **Prevention**: Use `php bin/console debug:container --parameters` and examine entity classes before template development
- **Beings Page Layout Issues**: Expandable sections (races/characters) displaying incorrectly with overflow and fixed heights
  - **Original Cause**: Missing initial `display: none` on expandable sections, fixed container heights, and poor CSS grid handling
  - **Initial Solution**: Added `style="display: none;"` to sections and improved CSS
  - **Improved Solution (Dropdown Overlay)**: Changed from expanding containers to dropdown overlays
    - **Races/Characters as Overlays**: Changed from inline expansion to absolute positioned dropdowns with high z-index (1000+)
    - **Better UX**: Content below no longer gets pushed down when sections are opened
    - **Auto-close Behavior**: Only one dropdown can be open at a time, automatically closes when clicking outside
    - **CSS Implementation**: `position: absolute`, dark backgrounds with shadows, proper z-index stacking
    - **JavaScript Enhancement**: Enhanced toggle logic with outside-click detection and mutual exclusion
  - **Fixed**: `beings/index.html.twig` template and corresponding CSS classes now use dropdown overlay pattern
  - **Prevention**: Always consider overlay patterns for expandable content to avoid layout displacement

## User Management System

### User Entity & Authentication
- **User Entity** (`src/Entity/User.php`) - Implements Symfony UserInterface with:
  - Multiple roles support via JSON array storage
  - Email-based authentication
  - Optional first/last names
  - Account status (active/inactive) management
  - Created/last login timestamps
  - Avatar/profile picture support via `avatarFilename` field
  - `getAvatarUrl()` method returns user's avatar or default icon
- **Roles Hierarchy**: `ROLE_USER` → `ROLE_MODERATOR` → `ROLE_ADMIN` → `ROLE_SUPER_ADMIN`
- **Registration**: Public users can create accounts (role-less by default)
- **Authentication**: Login/logout via email or username

### Admin User Management
- **Admin Panel** (`/admin/users`) - Complete user management interface:
  - User creation with role assignment
  - Profile editing and role management
  - Account activation/deactivation
  - User search and filtering
  - Statistics dashboard
- **Security**: Admin functions require `ROLE_ADMIN`, user deletion requires `ROLE_SUPER_ADMIN`

### User Profile Management
- **Profile Pages** (`/profile`) - Users can:
  - View their role assignments and permissions
  - Edit personal information
  - Upload and change avatar/profile pictures
  - Change passwords with current password verification
  - See account creation and last login dates
- **Avatar System**: 
  - Files stored in `public/images/user_icon/` directory
  - Supports JPG, PNG, GIF, WebP formats (max 2MB)
  - Automatic filename generation and old file cleanup
  - Falls back to default icon if no avatar set

### Key Commands
- `php bin/console app:create-admin` - Create admin users
- `php bin/console doctrine:migrations:migrate` - Apply user table migrations

### Security Configuration
- Form-based authentication with remember-me functionality
- Role-based access control throughout application
- User switching for super admins (`_su` parameter)
- Proper password hashing and validation

### Templates & Navigation
- Base template includes user dropdown menu
- Role-based navigation (admin links only visible to admins)
- Responsive user interface with proper authentication flows
- Flash messaging for user feedback

## Ideas Management System

### Overview
Complete universe ideas management system for tracking creative concepts, lore, and world-building elements. Supports hierarchical organization, categorization, and bulk operations.

### Key Features
- **CRUD Operations**: Full create, read, update, delete functionality for ideas
- **Hierarchical Organization**: Parent-child relationships between ideas for structured world-building
- **Advanced Filtering**: Search by title/content/tags, filter by category/certainty level/status
- **Bulk Import**: Import multiple ideas from formatted text (Word/text files)
- **Quick Add**: Simplified modal for rapid idea capture
- **Category Management**: Dynamic categories with default set (Magic_Systems, Creatures, Gods_Demons, etc.)
- **Tags System**: JSON-based tagging for flexible categorization
- **Export**: Export all ideas to text file with full metadata
- **Statistics Dashboard**: Real-time counts of total ideas, canon ideas, developing ideas, and categories

### Entities & Structure
- **Idea Entity** (`src/Entity/Idea.php`):
  - Self-referential relationship for parent-child hierarchies
  - JSON tags field for flexible tagging
  - Five certainty levels: Idea, Not_Sure, Developing, Established, Canon
  - Six status values: Draft, Need_Correction, In_Progress, Review, Finalized, Archived
  - Optional fields: inspiration_source, comments, priority
  - Automatic timestamp management (created_at, updated_at)
  
- **IdeaCategory Entity** (`src/Entity/IdeaCategory.php`):
  - Dynamic category management with default categories
  - Default categories include: Other, Magic_Systems, Creatures, Gods_Demons, Dimensions_Realms, Physics_Reality, Races_Beings, Items_Artifacts, Lore_History, Geography, Politics, Technology, Culture

### Routes & Endpoints
- `GET /ideas` - Main ideas index with filtering and pagination
- `GET /ideas/create` - Create new idea form
- `POST /ideas/create` - Save new idea
- `GET /ideas/{id}/edit` - Edit idea form
- `POST /ideas/{id}/edit` - Update idea
- `POST /ideas/{id}/delete` - Delete idea
- `POST /ideas/{id}/duplicate` - Duplicate idea with "(Copy)" suffix
- `POST /ideas/quick-add` - AJAX endpoint for quick add modal
- `POST /ideas/bulk-import` - AJAX endpoint for bulk import
- `GET /ideas/export` - Export all ideas to text file
- `GET /ideas/categories` - Get all categories (JSON)
- `POST /ideas/categories/add` - Add new category (JSON)
- `POST /ideas/categories/{id}/delete` - Delete category (JSON)
- `GET /ideas/tags` - Get all existing tags (JSON)

### Repository Methods
- `findWithFilters()` - Search and filter ideas with pagination
- `countWithFilters()` - Count filtered results
- `getStatistics()` - Get dashboard statistics
- `getAllTags()` - Extract all unique tags from ideas
- `findAllForExport()` - Get all ideas for export
- `findParentOptions()` - Get potential parent ideas for dropdown

### Templates
- `templates/ideas/index.html.twig` - Main ideas page with filters, modals, and grid display
- `templates/ideas/form.html.twig` - Create/edit form with all fields
- `templates/ideas/_idea_card.html.twig` - Reusable idea card component with hierarchical display

### Console Commands
- `php bin/console app:init-ideas` - Initialize default categories
- `php bin/console app:init-ideas --with-samples` - Initialize with sample ideas

### Bulk Import Format
```
Title: Idea Title Here
Content: Your idea content goes here...
Tags: tag1, tag2, tag3

---

Title: Another Idea
Content: More content...
Tags: other, tags
```

### CSS Styling
All ideas-specific styles are in `public/css/style.css` under "IDEAS MANAGEMENT STYLES" section:
- Responsive grid layout for idea cards
- Color-coded badges for certainty levels and categories
- Parent/child visual hierarchy with indentation and borders
- Modal overlays for quick actions
- Statistics dashboard with gradient backgrounds

### Best Practices
1. **Always initialize categories** before first use: `app:init-ideas`
2. **Use hierarchical organization** for related ideas (parent-child relationships)
3. **Tags are flexible** - no predefined list, creates organically
4. **Certainty levels** guide maturity: Idea → Not_Sure → Developing → Established → Canon
5. **Status tracking** for workflow: Draft → In_Progress → Review → Finalized
6. **Bulk import** efficient for transferring existing notes/docs

When working with this codebase, prioritize maintaining the hierarchical Species→Race→Character model and the rich French-language fantasy theme throughout all additions.