# Chronicles - AI Coding Agent Instructions

## ⚠️ CRITICAL: DATABASE BACKUP PROCEDURE ⚠️

**MANDATORY backup before any database schema changes:**

```powershell
# Use the automated backup script before migrations or schema modifications
.\scripts\backup.ps1
```

The backup script creates timestamped backups in `backups/database/` and maintains a backup history.

## Project Overview

Chronicles is a Symfony web application for managing fictional characters, species, and races in a fantasy universe. Built with Docker, FrankenPHP, and MySQL.

**Core Conventions:**
- English language UI and content
- Fantasy/medieval theme with rich character lore
- Hierarchical data model: Species → Races → Characters
- Nullable-first approach for optional attributes

**Important:** 
- All development work should be done in the Chronicles Symfony project and using symfony best practices.
- When modifying/adding/deleting features, always update this instruction file to document the changes for future reference.
- When modifying/adding/deleting features, always finish by running the command `docker compose exec php php bin/console cache:clear` to ensure the cache is up to date.
- if a change is expected for a later date, add a TODO comment to find it again later, or if it doesn't fit in a specific file, add it in the file TODO.txt in the project root.

**🚨 CRITICAL: JavaScript & Template Rules 🚨**

1. **NO inline scripts in templates** - Never add `<script>` tags in Twig templates
2. **Server-side first** - If functionality can be achieved by modifying routes, controllers, entities, or forms → DO THAT instead of JavaScript
3. **Minimize client-side calls** - Goal is to reduce HTTP requests and keep logic server-side
4. **External JS files only** - If JavaScript is absolutely necessary with no server-side alternative:
   - Create separate `.js` files in `public/js/` directory
   - Organize by feature/module (e.g., `public/js/ideas-quick-add.js`)
   - Include via `<script src="{{ asset('js/filename.js') }}">` in template
5. **Prefer Symfony solutions**: Use forms, validation, Doctrine events, custom commands, etc. before resorting to client-side scripts

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
- **Base**: French language, `templates/base.html.twig`, color scheme #2c3e50/#34495e
- **Two layouts**: Homepage (sidebar) vs. single-column via `leftContent` block
- **CSS architecture** (modular):
  - `public/css/theme.css` — design tokens (color palette, spacing, fonts, radii, shadows). Loaded globally **before** other stylesheets. Always reference these CSS variables (`var(--color-primary)`, `var(--space-4)`, etc.) instead of hard-coded values when adding new styles.
  - `public/css/style.css` — global/legacy site styles (header, nav, layouts, all public pages). Loaded on every page.
  - `public/css/admin.css` — admin-only UI (dashboard, modules, stat cards). Loaded **only** by admin templates via `{% block stylesheets %}<link rel="stylesheet" href="{{ asset('css/admin.css') }}">{% endblock %}`.
  - **No inline styles** in templates. Add new selectors to the appropriate file. For new feature areas, create a dedicated `public/css/<feature>.css` and opt-in via the `stylesheets` block of the relevant templates.
- **JavaScript**: External files only in `public/js/` - see JavaScript & Template Rules
- **Security**: `is_granted('ROLE_ADMIN')` for admin features
- **Details**: See `docs/TWIG_TEMPLATING_GUIDE.md` for template patterns

## Data Population

### PopulateDataCommand (`php bin/console app:populate-data`)
Seeds database with realistic sample data:
- Clears existing data (Characters → Races → Species)
- Creates hierarchical fantasy data: "Humains", "Elfes", "Nains", "Orcs" with associated Races and Characters
- Rich French-language descriptions and varied attributes

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
- **Empty Controller Files**: Do not leave empty files in `src/Controller/` (or subfolders). Symfony attribute route loading can fail with autoloader errors if the expected class is missing.
- **Ideas inspiration_source**: Keep `ideas.inspiration_source` as TEXT-compatible mapping. Reducing it to `VARCHAR(255)` can fail migrations when existing rows exceed 255 chars.
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

## Interactive Map System

World map viewer with clickable points of interest and admin editing functionality.

### Core Features
- **Multiple Maps**: Support for different map layers (overworld, underground, aerial, regional)
- **Interest Points**: Clickable locations with types, colors, descriptions, and optional detail pages
- **Point Types**: Categorized locations (City, Temple, Dungeon, Forest, etc.) with customizable colors/icons
- **Admin Editor**: Interactive point placement with add/move modes, drag-and-drop positioning
- **Place Details**: Wikipedia-style info pages with descriptions, other names, and image galleries

### Entities & Relationships
- **Map** → **InterestPoint** (OneToMany, orphanRemoval)
- **InterestPointType** → **InterestPoint** (OneToMany, nullable)
- Coordinates stored as DECIMAL(10,6) percentages (0-100 range)

### Main Routes
**Public:**
- `/map` - Map index/selection page
- `/map/{id}` - View specific map with points
- `/map/place/{id}` - Place detail page

**Admin (ROLE_ADMIN required):**
- `/admin/maps` - Map management dashboard
- `/admin/maps/new`, `/admin/maps/{id}/edit` - Map CRUD
- `/admin/maps/{id}/editor` - Interactive point editor
- `/admin/maps/types/new`, `/admin/maps/types/{id}/edit` - Type CRUD
- `/admin/maps/points/new`, `/admin/maps/points/{id}/edit` - Point form CRUD

**API Endpoints:**
- `/map/api/maps` - List all maps (JSON)
- `/map/api/points/{mapId}` - Points for a map (JSON)
- `/map/api/types` - All point types (JSON)
- `/admin/maps/api/save-points` - Save editor points (POST)
- `/admin/maps/api/delete-point` - Delete point (POST)
- `/admin/maps/api/clear-points` - Clear all map points (POST)

### File Storage
- **Map images**: `public/images/maps/` (max 10MB, JPG/PNG/GIF/WebP)
- **Place main images**: `public/images/places/` (max 5MB, JPG/PNG/GIF/WebP)
- **Place gallery images**: `public/images/places/gallery/` (max 5MB each)

### Gallery System
- **Upload Modal**: Admins can add gallery images directly from the place detail page via a modal popup (no need to go through the edit form)
- **Drag & Drop**: Upload modal supports drag-and-drop for easy multi-file selection
- **Multiple upload**: Multiple images can be selected and uploaded at once
- **Error reporting**: If an upload fails, the error message includes the specific filename that caused the issue
- **Image names**: Each gallery image has a display name (defaults to filename, editable by admins)
- **Lightbox viewer**: Clicking a gallery image opens fullscreen view with navigation
- **Admin controls**: In lightbox, admins can rename images (✏️ button) or delete them (🗑️ button)
- **Keyboard navigation**: Arrow keys navigate between images, Escape closes lightbox
- **JSON storage**: Gallery stored as JSON array of `{filename, name}` objects in `interest_points.gallery`

**Gallery API Endpoints:**
- `/admin/maps/api/gallery/upload` - Upload gallery images via modal (POST, requires point_id, files[])
- `/admin/maps/api/gallery/rename` - Rename gallery image (POST, requires point_id, filename, new_name)
- `/admin/maps/api/gallery/delete` - Delete gallery image (POST, requires point_id, filename)

### JavaScript Files
- `public/js/map-view.js` - Public map viewing (point rendering, tooltips, navigation)
- `public/js/map-editor.js` - Admin editor (add/move modes, drag-drop, save/delete)

### Default Data (Migration)
- 15 default French point types with colors: Ville, Temple, Donjon, Forêt, etc.
- One default "Carte du Monde" map

### Editor Usage
1. Upload map image in Maps management
2. Open Editor for the map
3. Enable "Mode Ajout" to click and place points
4. Enable "Mode Déplacement" to drag points
5. Click existing points to edit details
6. Save all points with the save button

### Map Navigation Access (May 2026)
- Restored direct access to all map options from admin and global navigation:
  - Admin dashboard now includes a dedicated **Cartes & Lieux** module with shortcuts to map dashboard, create map, create type, create point, and public map view.
  - Global navigation and connected-user dropdown include **Gestion Cartes** linking to `/admin/maps`.
- If map options seem missing, first check access through `/admin/maps` and then clear cache (`docker compose exec php php bin/console cache:clear`).

## Search Hub System (June 2026)

### New Page
- **Search Hub** (`/hub`) provides two entry points:
  - Page-type redirect search (e.g., `beings`, `characters`)
  - Direct suggestion search over names/titles

### Current Search Behavior
- Suggestions are limited to **10** items.
- Matching supports:
  - contiguous input including hyphen (`-`)
  - tokenized space-separated input (e.g. `el da`)
  - priority bonus when tokens are matched in user-entered order
- Suggestions display:
  - main icon (species/race/character image when available)
  - label + type suffix in parentheses
  - hierarchy-first ordering (Species before Race before Character)
  - depth-based indentation and type-based color variation
  - highlighted matched tokens

### Architecture
- Implemented with a **provider-based search system** for future extension:
  - `App\Search\HubSearchProviderInterface`
  - Providers currently available:
    - `BeingsHubSearchProvider` (species + races)
    - `CharactersHubSearchProvider` (characters)
  - Aggregation/scoring service:
    - `App\Service\HubSearchService`
- Providers are tagged via `app.hub_search_provider` and collected with `AutowireIterator`.

### Future Extension Rule
- New pages/entities must integrate by creating a new provider implementing `HubSearchProviderInterface` rather than duplicating search logic.
- Keep server-side-first behavior and external JS only (`public/js/hub-search.js`).

When working with this codebase, prioritize maintaining the hierarchical Species→Race→Character model and the rich French-language fantasy theme throughout all additions.