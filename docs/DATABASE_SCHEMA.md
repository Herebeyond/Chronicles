# Chronicles Database Schema

**Last Updated:** December 21, 2025  
**Database:** MySQL 8.0 (`chronicles`)

> **Note:** This document is the authoritative reference for the database structure. Always check this schema before making assumptions about available fields.

## Database Tables

### `species` table
- `id` (int, AUTO_INCREMENT, PRIMARY KEY)
- `name` (varchar(255), NOT NULL)
- `description` (longtext, nullable)
- `icon` (varchar(255), nullable)
- `created_at` (datetime, NOT NULL, immutable)
- `updated_at` (datetime, nullable, immutable)

### `races` table
- `id` (int, AUTO_INCREMENT, PRIMARY KEY)  
- `species_id` (int, NOT NULL, FOREIGN KEY to species.id)
- `name` (varchar(255), NOT NULL)
- `description` (longtext, nullable)
- `icon` (varchar(255), nullable)
- `created_at` (datetime, NOT NULL, immutable)
- `updated_at` (datetime, nullable, immutable)

### `characters` table
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

### `users` table
- `id` (int, AUTO_INCREMENT, PRIMARY KEY)
- `email` (varchar(180), NOT NULL, UNIQUE)
- `username` (varchar(100), NOT NULL, UNIQUE)
- `first_name` (varchar(100), nullable)
- `last_name` (varchar(100), nullable)
- `password` (varchar(255), NOT NULL)
- `is_active` (tinyint(1), NOT NULL)
- `avatar_filename` (varchar(255), nullable)
- `created_at` (datetime, NOT NULL, immutable)
- `last_login_at` (datetime, nullable, immutable)

### `roles` table
- `id` (int, AUTO_INCREMENT, PRIMARY KEY)
- `name` (varchar(50), NOT NULL, UNIQUE)
- `description` (varchar(255), nullable)
- `created_at` (datetime, NOT NULL, immutable)

### `user_roles` table (junction table)
- `user_id` (int, NOT NULL, FOREIGN KEY to users.id, ON DELETE CASCADE)
- `role_id` (int, NOT NULL, FOREIGN KEY to roles.id, ON DELETE CASCADE)
- PRIMARY KEY (user_id, role_id)

### `ideas` table
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

### `idea_categories` table
- `id` (int, AUTO_INCREMENT, PRIMARY KEY)
- `name` (varchar(100), NOT NULL, UNIQUE)
- `is_default` (tinyint(1), NOT NULL)
- `created_at` (datetime, NOT NULL, immutable)

### `world_events` table
- `id` (int, AUTO_INCREMENT, PRIMARY KEY)
- `title` (varchar(255), NOT NULL)
- `description` (longtext, nullable)
- `start_year` (int, NOT NULL)
- `start_month` (int, NOT NULL)
- `start_day` (int, NOT NULL)
- `end_year` (int, nullable) - NULL if event is ongoing
- `end_month` (int, nullable)
- `end_day` (int, nullable)
- `color` (varchar(7), NOT NULL, default '#3498db')
- `significance` (longtext, nullable)
- `created_at` (datetime, NOT NULL, immutable)
- `updated_at` (datetime, nullable, immutable)

### `calendar_months` table
- `id` (int, AUTO_INCREMENT, PRIMARY KEY)
- `month_number` (int, NOT NULL)
- `name` (varchar(100), NOT NULL)
- `days_count` (int, NOT NULL)
- `description` (longtext, nullable)
- `created_at` (datetime, NOT NULL, immutable)

### `maps` table
- `id` (int, AUTO_INCREMENT, PRIMARY KEY)
- `name` (varchar(255), NOT NULL)
- `image_file` (varchar(255), nullable)
- `description` (longtext, nullable)
- `created_at` (datetime, NOT NULL, immutable)
- `updated_at` (datetime, nullable, immutable)

### `interest_point_types` table
- `id` (int, AUTO_INCREMENT, PRIMARY KEY)
- `name` (varchar(100), NOT NULL)
- `color` (varchar(7), NOT NULL, default '#ff4444')
- `icon` (varchar(50), nullable) - Emoji or icon identifier
- `created_at` (datetime, NOT NULL, immutable)

### `interest_points` table
- `id` (int, AUTO_INCREMENT, PRIMARY KEY)
- `map_id` (int, NOT NULL, FOREIGN KEY to maps.id, ON DELETE CASCADE)
- `type_id` (int, nullable, FOREIGN KEY to interest_point_types.id, ON DELETE SET NULL)
- `name` (varchar(255), NOT NULL)
- `description` (longtext, nullable)
- `x_coordinate` (decimal(10,6), NOT NULL) - Percentage (0-100)
- `y_coordinate` (decimal(10,6), NOT NULL) - Percentage (0-100)
- `other_names` (longtext, nullable)
- `main_image` (varchar(255), nullable) - Stored in `public/images/places/`
- `gallery` (json, nullable) - Array of `{filename, name}` objects, stored in `public/images/places/gallery/`
- `created_at` (datetime, NOT NULL, immutable)
- `updated_at` (datetime, nullable, immutable)

## Entity Relationships

- **Species** (1:Many) **→** **Races** (Many:1) **Species**
- **Species** (1:Many) **→** **Characters** (Many:1) **Species** 
- **Race** (1:Many) **→** **Characters** (Many:1) **Race** (nullable)
- **Character** belongs to **Species** (required) and optionally to **Race**
- **User** (Many:Many) **←→** **Role** - Users can have multiple roles, roles can be assigned to multiple users
- **Idea** (Self-referential) **→** **Parent Idea** (Many:1) - Ideas can have parent-child relationships for hierarchical organization
- **WorldEvent** - Standalone entity for tracking historical events with custom calendar dates
- **Map** (1:Many) **→** **InterestPoint** (Many:1) **Map** - Maps contain multiple points of interest
- **InterestPointType** (1:Many) **→** **InterestPoint** (Many:1) **Type** (nullable) - Points can optionally have a type

## Key Database Rules

1. **Characters must belong to a Species** (species_id NOT NULL)
2. **Characters can optionally belong to a Race** (race_id nullable)
3. **Races must belong to a Species** (species_id NOT NULL)
4. **All entities use DateTimeImmutable** for timestamps
5. **User roles are managed via many-to-many relationship** - No JSON column, proper relational design
6. **Default roles are created by migrations** - ROLE_USER, ROLE_MODERATOR, ROLE_ADMIN, ROLE_SUPER_ADMIN
7. **No lifespan, homeworld, or other extended race properties exist** - templates should not reference these
8. **World events use custom calendar system** - Dates are stored as year/month/day integers, not DateTime objects
9. **Interest point coordinates are percentages** - Values from 0-100 representing position on map image

## Enumeration Values

### Role Names
- `ROLE_USER` - Basic user
- `ROLE_MODERATOR` - Content moderator
- `ROLE_ADMIN` - Administrator
- `ROLE_SUPER_ADMIN` - Super administrator

### Certainty Levels (ideas)
- `Idea` - Initial concept
- `Not_Sure` - Uncertain
- `Developing` - Being developed
- `Established` - Well-defined
- `Canon` - Canonical/official

### Status Values (ideas)
- `Draft` - Work in progress
- `Need_Correction` - Requires fixes
- `In_Progress` - Active development
- `Review` - Under review
- `Finalized` - Completed
- `Archived` - Archived/inactive

## Migration History

- `Version20250916132323` - Initial species/races/characters tables
- `Version20250916132410` - User authentication tables
- `Version20250919113508` - Default admin user creation
- `Version20250923083053` - Ideas and categories tables
- `Version20251027112439` - World events and calendar tables
- `Version20251215150253` - User roles many-to-many relationship
- `Version20251215154313` - User avatar support
- `Version20251215160251` - Role descriptions
- `Version20260118225509` - Maps and interest points tables with default types

## Notes

- **Charset**: All tables use `utf8mb4` for full Unicode support
- **MySQL Version**: 8.0
- **Timestamps**: All entities use `DateTimeImmutable` with auto-initialization
- **Foreign Keys**: CASCADE delete on junction tables, SET NULL on optional relationships
