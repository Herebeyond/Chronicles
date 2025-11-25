# Chronicles Images Directory Structure

This directory contains all image assets for The Rift Chronicles project.

## Directory Structure

```
public/images/
├── icons/           # UI icons and interface elements
│   ├── dropdown-arrow.svg  # Arrow icon for dropdown menus
│   ├── eye-icon.svg        # Original placeholder eye icon (SVG)
│   ├── Eye.jpg            # Main eye icon used in header (from old project)
│   ├── favicon.svg        # Website favicon
│   ├── default_user_icon.png  # Default user profile icon
│   └── settings.png       # Settings/configuration icon (from old project)
├── decorative/      # Decorative and background elements
│   ├── Icon.png           # Main decorative icon (from old project)
│   ├── star-icon.svg      # Original placeholder star decorations (SVG)
│   ├── starry-background.svg  # Original placeholder background (SVG)
│   └── wallpaper.jpg      # Main background wallpaper (from old project)
├── user_icon/       # User profile icons
│   ├── default_user_icon.png  # Default user icon
│   └── [hash-named files]  # User-specific profile icons
├── logos/           # Brand logos and large graphics
├── characters/      # Character portraits and related images
├── species/         # Species-specific imagery
│   ├── Angels.png         # Angelic beings
│   ├── Dwarfs.png         # Dwarven species
│   ├── Gods.png           # Divine beings
│   ├── Undead.jpg         # Undead species
│   ├── Draugr.jpg         # Draugr type undead
│   ├── Dullahan.jpg       # Dullahan creatures
│   ├── Revenant.jpg       # Revenant undead
│   ├── Zombie.jpg         # Zombie creatures
│   ├── Necrolyte.webp     # Necrolyte beings
│   └── [various hash-named files]  # Other species images from old project
├── races/           # Race-specific imagery (subdivisions of species)
│   └── [hash-named files]  # Race images from old project
└── maps/           # World and area maps
    ├── default-map.jpg    # Default map view
    ├── dimensions.png     # Dimensional maps
    ├── map_aerial.png     # Aerial view map
    ├── map_monde.png      # World map
    ├── map_underground.png # Underground map
    └── online_maps_saves/ # Saved online maps
```

## Image Migration Status

### ✅ **Completed Migrations:**
- **Header Eye Icon**: Now uses `Eye.jpg` from old project instead of SVG placeholder
- **Left Sidebar Icons**: Now uses `Icon.png` from old project instead of star SVG
- **Background**: Now uses `wallpaper.jpg` from old project instead of SVG pattern
- **Species Images**: All named species images copied from old project
- **Maps Collection**: Complete map directory migrated for future features
- **Race Images**: Hash-named race images copied for future organization

### 🔄 **Naming Convention:**
- **Clear Names**: Angels.png, Dwarfs.png, Gods.png, etc. for main species
- **Hash Names**: Files like `4788f7aed675174ece498762a06ce035_964992015.jpg` need to be mapped to database entries
- **Future Task**: Map hash-named files to meaningful species/race names

## Usage Guidelines

### Icons
- All icons should be optimized for web use
- SVG versions kept as fallbacks for some UI elements
- Real artwork from old project takes priority

### Decorative Elements
- Background wallpaper provides authentic fantasy atmosphere
- Icon.png provides consistent left sidebar decoration matching old project
- Maintains visual continuity between old and new project

### Species and Races
- Rich collection of fantasy creature artwork available
- Hash-named files need database correlation for proper display
- Supports diverse fantasy world building

### Maps
- Complete collection ready for future map features
- Multiple view types (aerial, underground, world)
- Save system preserved from old project

## Asset Integration

All images are loaded using Symfony's `asset()` function:
```twig
<img src="{{ asset('images/species/Angels.png') }}" alt="Angels">
```

This ensures proper cache busting and CDN integration.

## Performance Considerations

- Original artwork preserved in full quality
- Consider creating thumbnail versions for galleries
- WebP format supported (Necrolyte.webp example)
- JPG and PNG formats for broad compatibility

## Fantasy Theme Authenticity

All imagery maintains the authentic Chronicles fantasy theme:
- Medieval and mystical atmosphere
- Rich creature designs from original project
- Atmospheric backgrounds and textures
- Consistent visual identity preserved