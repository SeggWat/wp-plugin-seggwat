# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

SeggWat Feedback WordPress Plugin - integrates the SeggWat feedback widget into WordPress sites. Single-file PHP plugin that injects a configurable JavaScript widget on the frontend.

**Part of the SeggWat monorepo** - see `../CLAUDE.md` for cross-project context.

## Architecture

### Single Plugin File (`seggwat-feedback.php`)

The entire plugin is contained in one PHP class `SeggWat_Feedback_Plugin`:

- **Settings Registration** (`register_settings`) - WordPress Settings API for admin configuration
- **Settings Page** (`render_settings_page`) - Admin UI under Settings → SeggWat Feedback
- **Post Meta** (`register_post_meta`, `add_meta_box`, `save_meta_box`) - Per-post/page widget control
- **Frontend Injection** (`enqueue_widget`) - Conditional script loading with data attributes

### Configuration Options

| Option Key | Purpose | Default |
|------------|---------|---------|
| `seggwat_project_key` | Widget authentication | (required) |
| `seggwat_button_color` | Hex color | `#10b981` |
| `seggwat_button_position` | `right-side`, `bottom-right`, `icon-only` | `right-side` |
| `seggwat_default_behavior` | `inject` (all pages) or `skip` | `inject` |
| `seggwat_language` | `auto`, `en`, `de`, `sv` | `auto` |
| `seggwat_show_powered_by` | Boolean branding toggle | `true` |

### Per-Post Meta

- `_seggwat_widget_behavior` - `inherit`, `enable`, or `disable` (overrides site default)
- `_seggwat_disable_widget` - Legacy boolean (pre-v1.3.0, auto-migrated)

### WordPress Filters for Customization

```php
seggwat/script_src        // Modify widget script URL
seggwat/button_color      // Dynamic color
seggwat/button_position   // Dynamic position
seggwat/language          // Override language
seggwat/show_powered_by   // Control branding
seggwat/post_types        // Extend beyond posts/pages (default: ['post', 'page'])
```

## Development

### Local Testing

1. Symlink or copy plugin folder to WordPress installation: `/wp-content/plugins/seggwat-feedback/`
2. Activate via WordPress admin Plugins menu
3. Configure at Settings → SeggWat Feedback

### File Structure

```
wp-plugin-seggwat/
├── seggwat-feedback.php   # Main plugin (all functionality)
├── uninstall.php          # Cleanup on plugin deletion
└── README.md              # User-facing documentation
```

### Uninstall Cleanup

`uninstall.php` removes all `seggwat_*` options and `_seggwat_*` post meta on plugin deletion.

## Widget Integration

The plugin injects the widget script from `https://seggwat.com/static/widgets/v1/seggwat-feedback.js` (configurable via `seggwat/script_src` filter).

Output script tag format:
```html
<script id="widget-script" src="..." defer
        data-project-key="..."
        data-button-color="..."
        data-button-position="..."
        data-language="..."
        data-show-powered-by="..."></script>
```

## CI/CD

GitHub Actions workflow (`.github/workflows/wp-plugin.yml`) creates a deployable artifact on push to main.
