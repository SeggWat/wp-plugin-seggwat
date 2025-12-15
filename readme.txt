=== SeggWat Feedback ===
Contributors: haukejung
Tags: feedback, widget, user feedback, bug report, feature request
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.5.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Collect user feedback directly from your WordPress site with a beautiful, customizable widget.

== Description ==

SeggWat Feedback adds a lightweight feedback widget to your WordPress site, allowing visitors to submit bug reports, feature requests, praise, and questions directly from any page.

**Features:**

* Customizable button color and position
* Multi-language support (English, German, Swedish) with auto-detection
* Per-page control to enable/disable the widget on specific posts and pages
* White-label option to hide "Powered by SeggWat" branding
* Three button positions: right side, bottom right, or compact icon-only
* Developer-friendly with filters for advanced customization

**How It Works:**

1. Create a free account at [seggwat.com](https://seggwat.com)
2. Get your Project Key from the SeggWat dashboard
3. Configure the plugin in WordPress under Settings > SeggWat Feedback
4. The feedback widget appears on your site, collecting submissions to your SeggWat dashboard

== Installation ==

1. Upload the `seggwat-feedback` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > SeggWat Feedback
4. Enter your Project Key from [seggwat.com](https://seggwat.com)
5. Customize button color, position, and language as needed

== Frequently Asked Questions ==

= Do I need a SeggWat account? =

Yes, a free SeggWat account is required to receive and manage feedback submissions. Sign up at [seggwat.com](https://seggwat.com).

= Where do I find my Project Key? =

Log in to your SeggWat dashboard, select your project, and copy the Project Key from the project settings.

= Can I disable the widget on specific pages? =

Yes. Each post and page has a "SeggWat Feedback" meta box in the editor where you can choose: Default (site setting), Force enable, or Disable.

= Can I hide the SeggWat branding? =

Yes. Uncheck "Show Branding" in Settings > SeggWat Feedback to hide the "Powered by SeggWat" footer in the widget.

= What languages are supported? =

The widget supports English, German (Deutsch), and Swedish (Svenska). You can set a fixed language or let it auto-detect from the visitor's browser.

= Can I customize the widget programmatically? =

Yes. The plugin provides several filters:

* `seggwat/button_color` - Modify button color dynamically
* `seggwat/button_position` - Change position based on conditions
* `seggwat/language` - Override language setting
* `seggwat/show_powered_by` - Control branding visibility
* `seggwat/post_types` - Add widget control to custom post types

== Screenshots ==

1. Plugin settings page in WordPress admin
2. Feedback widget on the frontend (right side position)
3. Feedback widget (bottom right position)
4. Per-page widget control in the post editor

== Changelog ==

= 1.5.0 =
* Added "Show Branding" option to hide "Powered by SeggWat" footer
* Added "Icon only (compact)" button position option
* Updated for white-label and agency use cases
* Improved settings descriptions and help text

= 1.4.0 =
* Added multi-language support (English, German, Swedish)
* Added language auto-detection from browser
* Added per-language UI configuration

= 1.3.0 =
* Added per-post/page 3-state control (Default/Force enable/Disable)
* Improved meta box UI with clearer options
* Legacy checkbox migration support

= 1.2.0 =
* Added button position options (right-side, bottom-right)
* Added customizable button color
* Improved settings page layout

= 1.1.0 =
* Added site-wide default behavior setting
* Added per-post disable checkbox

= 1.0.0 =
* Initial release
* Basic widget injection with project key configuration

== Upgrade Notice ==

= 1.5.0 =
New white-label options: hide SeggWat branding and use compact icon-only button position.

= 1.3.0 =
Improved per-page control with 3-state options. Existing disable settings are automatically migrated.
