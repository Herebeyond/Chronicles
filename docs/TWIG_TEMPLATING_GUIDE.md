# Twig Templating Guide - Chronicles Project

## Common Pitfalls and Solutions

### 1. Duplicate Block Definitions ⚠️

**PROBLEM**: Twig doesn't allow multiple block definitions with the same name, even in conditional branches.

```twig
<!-- ❌ WRONG - This causes syntax error -->
{% if condition %}
    {% block body %}Content A{% endblock %}
{% else %}
    {% block body %}Content B{% endblock %}  <!-- ERROR: Block already defined -->
{% endif %}
```

**SOLUTION**: Define the block once, then call it multiple times using `{{ block('name') }}`.

```twig
<!-- ✅ CORRECT -->
{% if condition %}
    <div class="layout-a">
        {% block body %}{% endblock %}  <!-- Block definition -->
    </div>
{% else %}
    <div class="layout-b">
        {{ block('body') }}  <!-- Block call/render -->
    </div>
{% endif %}
```

### 2. Block vs Block Call Syntax

- **Block Definition**: `{% block name %}{% endblock %}` - Creates/defines the block
- **Block Call**: `{{ block('name') }}` - Renders the block content
- **Block Check**: `block('name') is defined` - Tests if block exists

### 3. Conditional Layout Patterns

When creating multiple layout structures that need the same content block:

```twig
<!-- Pattern for dual layouts -->
{% if special_condition %}
    <!-- Layout A: Two columns -->
    <div class="two-column">
        <div class="sidebar">{% block sidebar %}{% endblock %}</div>
        <div class="main">{% block body %}{% endblock %}</div>
    </div>
{% else %}
    <!-- Layout B: Single column -->
    <div class="single-column">
        {{ block('body') }}  <!-- Render the same block -->
    </div>
{% endif %}
```

### 4. Block Inheritance Best Practices

- **One definition per block**: Define each block only once in the template
- **Use block calls for reuse**: Use `{{ block('name') }}` to render blocks multiple times
- **Meaningful block names**: Use descriptive names like `main_content`, `sidebar`, `page_title`
- **Document complex inheritance**: Comment complex block structures

## Template Structure Guidelines

### Base Template Structure
```twig
<!DOCTYPE html>
<html>
<head>
    <title>{% block title %}Default Title{% endblock %}</title>
    {% block stylesheets %}{% endblock %}
</head>
<body>
    <header><!-- Fixed header content --></header>
    
    <main>
        {% block body %}{% endblock %}  <!-- Main content area -->
    </main>
    
    <footer><!-- Fixed footer content --></footer>
    
    {% block javascripts %}{% endblock %}
</body>
</html>
```

### Child Template Pattern
```twig
{% extends 'base.html.twig' %}

{% block title %}Specific Page Title{% endblock %}

{% block body %}
    <!-- Page-specific content -->
{% endblock %}
```

## Error Messages and Solutions

### "Block 'name' has already been defined"
- **Cause**: Multiple `{% block name %}` definitions in same template
- **Solution**: Use `{{ block('name') }}` for additional renderings

### "Unknown block 'name'"
- **Cause**: Trying to call a block that wasn't defined
- **Solution**: Define the block first with `{% block name %}{% endblock %}`

### "Cannot override block that does not exist"
- **Cause**: Child template tries to override non-existent block
- **Solution**: Add the block definition to parent template

## Chronicles Project Specifics

### Current Base Template Structure
```twig
<!-- Homepage: Two-column layout -->
{% if app.request.get('_route') == 'homepage' and block('leftContent') is defined %}
    <div id="englobe">
        <div class="leftText">{% block leftContent %}{% endblock %}</div>
        <div id="mainText">{% block body %}{% endblock %}</div>
    </div>
{% else %}
    <!-- Other pages: Single-column layout -->
    <div id="mainText">{{ block('body') }}</div>
{% endif %}
```

### Available Blocks in Base Template
- `title`: Page title in HTML head
- `stylesheets`: Additional CSS files
- `leftContent`: Homepage sidebar content
- `body`: Main page content
- `javascripts`: Additional JavaScript

### Template Inheritance Hierarchy
```
base.html.twig
├── home/index.html.twig (uses leftContent + body)
├── characters/index.html.twig (uses body)
├── security/profile.html.twig (uses body)
└── admin/dashboard.html.twig (uses body)
```

## Debugging Tips

1. **Clear cache after template changes**: `php bin/console cache:clear`
2. **Check Twig syntax**: Look for mismatched brackets and incorrect block syntax
3. **Use Symfony debug toolbar**: Shows which templates are being rendered
4. **Test with simple content**: Start with basic text before adding complex HTML

## Route Verification & Management

### Common Route Issues
- **Invalid route names**: Always verify route exists before using in templates
- **Typos in route names**: `'home'` vs `'homepage'`, check exact route name
- **Missing routes**: Ensure controller routes are properly defined and registered

### Route Verification Commands
```bash
# List all available routes
docker compose exec php php bin/console debug:router

# Test specific route
docker compose exec php php bin/console router:match /path/to/test

# Search for route references in templates
grep -r "path('" templates/
```

### Route Naming Conventions in Chronicles
- Homepage: `homepage` (not `home`)
- Admin routes: `admin_*` prefix
- User management: `admin_users_*` prefix  
- API routes: `api_*` prefix

### Future Prevention Guidelines

1. **Always use unique block names** when extending templates
2. **Check Twig syntax carefully** - blocks cannot be redefined in conditionals
3. **Clear cache after template changes**: `php bin/console cache:clear`
4. **Use template inheritance properly** - one block definition, reuse with `{{ block('name') }}`
5. **Test templates immediately** after making changes
6. **Use consistent naming conventions** for blocks and templates
7. **Verify route names** before using in templates - check `debug:router` output

## Version History

- **2025-09-23**: Initial documentation created after resolving duplicate block definition error
- **2025-09-23**: Added route verification section after comprehensive route audit
- Future updates should be added here to track template structure evolution