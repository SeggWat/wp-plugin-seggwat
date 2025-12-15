# SeggWat Feedback WordPress Plugin

A WordPress plugin that integrates the SeggWat feedback widget into your WordPress site. Collect user feedback directly from your website with a customizable, lightweight widget.

## Features

- 🎨 **Customizable Appearance** - Choose button color, position, and layout
- 🌍 **Multi-language Support** - English, German, and Swedish with auto-detection
- 🏷️ **White-Label Ready** - Option to hide "Powered by SeggWat" branding
- 📍 **Flexible Positioning** - Right side, bottom right, or compact icon-only layouts
- 🎯 **Per-Post Control** - Enable/disable widget on specific posts and pages
- 🔧 **Site-Wide Defaults** - Set default behavior for all pages

## Installation

1. Upload the `seggwat-feedback` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings → SeggWat Feedback to configure your widget
4. Enter your Project Key from the SeggWat dashboard

## Configuration

### Global Settings

Navigate to **Settings → SeggWat Feedback** in your WordPress admin to configure:

#### Project Key (Required)
Your unique project identifier from the SeggWat dashboard. Format: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`

#### Button Color
Hex color code for the button (e.g., `#10b981`). Default: `#10b981`

#### Button Position
Choose where the feedback button appears:
- **Right side** - Vertical button on the right edge of the screen
- **Bottom right** - Horizontal button with icon and text in the bottom-right corner
- **Icon only (compact)** - Round icon-only button at bottom-right for minimalist designs

#### Widget Language
Select the interface language:
- **Auto-detect from browser** - Automatically detects user's browser language (default)
- **English**
- **German (Deutsch)**
- **Swedish (Svenska)**

#### Show Branding
Control the "Powered by SeggWat" footer visibility:
- ✅ **Checked** (default) - Shows SeggWat branding in the widget footer
- ❌ **Unchecked** - Hides branding for white-label implementations

**Perfect for:**
- Agency projects where you provide feedback solutions to clients
- White-label WordPress themes or plugins
- Sites with strict brand consistency requirements
- Enterprise deployments

#### Default Behavior
Choose the default widget injection behavior:
- **Inject on all pages by default** - Widget appears site-wide (can be disabled per post/page)
- **Do not inject unless forced per post/page** - Widget only appears where explicitly enabled

### Per-Post/Page Control

Each post and page has a "SeggWat Feedback" meta box in the editor sidebar with three options:

- **Default (site setting)** - Uses the global default behavior
- **Force enable** - Always show the widget on this post/page (overrides site default)
- **Disable** - Never show the widget on this post/page (overrides site default)

This allows fine-grained control over where the feedback button appears.

## Developer Hooks & Filters

The plugin provides several filters for advanced customization:

### Filters

```php
// Change the script source URL
add_filter('seggwat/script_src', function($url) {
    return 'https://your-custom-domain.com/static/widgets/v1/seggwat-feedback.js';
});

// Modify button color dynamically
add_filter('seggwat/button_color', function($color) {
    return get_theme_mod('primary_color', '#10b981');
});

// Change button position
add_filter('seggwat/button_position', function($position) {
    return is_mobile() ? 'icon-only' : 'right-side';
});

// Override language
add_filter('seggwat/language', function($lang) {
    return 'de'; // Force German
});

// Control branding visibility programmatically
add_filter('seggwat/show_powered_by', function($show) {
    // Hide branding for logged-in users only
    return !is_user_logged_in();
});
```

### Post Type Support

By default, the plugin adds the meta box to posts and pages. To add it to custom post types:

```php
add_filter('seggwat/post_types', function($post_types) {
    $post_types[] = 'product';
    $post_types[] = 'portfolio';
    return $post_types;
});
```

## White-Label Use Cases

### Agency WordPress Solutions

Perfect for agencies building WordPress sites for clients:

```php
// functions.php - Automatically use client's brand color
add_filter('seggwat/button_color', function($color) {
    return get_theme_mod('client_primary_color', '#10b981');
});

// Hide SeggWat branding on client sites
add_filter('seggwat/show_powered_by', '__return_false');
```

### Membership Sites

Hide branding for premium members:

```php
add_filter('seggwat/show_powered_by', function($show) {
    // Show branding only for free tier users
    return !current_user_can('premium_member');
});
```

### Multi-Site Networks

Configure different settings per site in a WordPress multisite:

```php
// Per-site branding control
add_filter('seggwat/show_powered_by', function($show) {
    $site_id = get_current_blog_id();
    // Hide for site IDs 2, 5, 7 (premium sites)
    return !in_array($site_id, [2, 5, 7]);
});
```

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- A SeggWat account with a valid Project Key

## Support

For support, feature requests, or bug reports:
- Visit: [seggwat.com](https://seggwat.com)
- Documentation: [seggwat.com/docs](https://seggwat.com/docs)
- Email: info@seggwat.com

## Changelog

### 1.5.0 (2025-11-25)
- Added "Show Branding" option to hide "Powered by SeggWat" footer
- Added "Icon only (compact)" button position option
- Updated for white-label and agency use cases
- Improved settings descriptions and help text

### 1.4.0
- Added multi-language support (English, German, Swedish)
- Added language auto-detection from browser
- Added per-language UI configuration

### 1.3.0
- Added per-post/page 3-state control (Default/Force enable/Disable)
- Improved meta box UI with clearer options
- Legacy checkbox migration support

### 1.2.0
- Added button position options (right-side, bottom-right)
- Added customizable button color
- Improved settings page layout

### 1.1.0
- Added site-wide default behavior setting
- Added per-post disable checkbox

### 1.0.0
- Initial release
- Basic widget injection with project key configuration

## License

GPL-2.0+

## Credits

Developed for SeggWat - Modern feedback collection for product teams.
