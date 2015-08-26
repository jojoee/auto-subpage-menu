# Auto Subpage Menu

By default wordpress menu system, wordpress can only automatically add/remove **top-level page** to/from menus
- When **publish** top-level page then add it into menus
- When **move** top-level page to trash then remove it from menus
- When **restore** top-level page then add it into menus

this feature has no effect with **child page** (subpage), but **Auto Subpage Menu** can
- When **publish** child page then add it into menus (if its page parent exists in menu)
- When **update** child page then update menus
- When **move** child page to trash then remove it from menus
- When **restore** child page then add it into menus

## Compatible with

- Requires: 3.3.0 or higher, because this plugin require [wp_trash_post](https://developer.wordpress.org/reference/hooks/wp_trash_post/)
- Compatible up to: 4.3

## How to install

- Method 1, install the plugin via admin/s plguins screen
- Method 2, download and upload this plugin into `wp-content/plugins` directory

## How to use

1. Activate the plugin through the **Plugins** menu in WordPress.
2. Go to **Menus** and check [x] *Automatically add new top-level pages to this menu*
3. Let's see the magic

## Notes
- [WordPress Coding Standards](https://codex.wordpress.org/WordPress_Coding_Standards)
- [phpDocumentor](http://www.phpdoc.org/) docblock standard

## Future Updates

- [ ] Plugin option menu that contain `settings` and `usage document`
- [ ] Add more function description (DocBlockr)
