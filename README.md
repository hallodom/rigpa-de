# rigpa-de — WordPress + MySQL (Docker)

Docker Compose stack for WordPress (latest official image) with MySQL 8.4, automated first-run setup (admin user + Elementor), and separate local/production overrides.

## Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (or Docker Engine + Compose v2)

## Quick start

```bash
cp .env.example .env
docker compose up -d
```

Wait until `wordpress-setup` finishes (`docker compose logs wordpress-setup`). Then open:

- **Site:** http://localhost:8080
- **Admin:** http://localhost:8080/wp-admin

### Default login (local dev)

Values come from `.env` (defaults in `.env.example`):

| Setting  | Default              |
|----------|----------------------|
| Username | `admin`              |
| Password | `admin`              |
| Email    | `admin@rigpa-de.local` |

**Change these before any production deploy.**

Elementor (free) is installed and activated automatically.

### Optional: German locale

```bash
docker compose --profile tools run --rm wp-cli wp language core install de_DE --activate
```

## Makefile shortcuts

| Command      | Description                          |
|--------------|--------------------------------------|
| `make up`    | Start all services                   |
| `make down`  | Stop services (keeps volumes)        |
| `make logs`  | Follow logs                          |
| `make setup` | Re-run bootstrap (idempotent)        |
| `make wp ARGS="plugin list"` | Run WP-CLI |
| `make reset` | `down -v` then `up` (factory reset)  |
| `make prod-up` | Start with production overrides    |
| `make build-map` | Build Rigpa.de Map plugin assets |
| `make build-mega-menu` | Build Rigpa Mega Menu plugin assets |
| `make package-plugin` | Build assets and create `dist/rigpa-de-map.zip` for WP upload |
| `make package-mega-menu` | Build assets and create `dist/rigpa-mega-menu.zip` for WP upload |
| `make seed-mega-menu-pages` | Create demo pages, seed nav menus, and assign plugin locations |
| `make seed-mega-menu-nav` | Seed nav menus only and assign plugin locations |

## Local development

- WordPress: http://localhost:8080 (`WP_PORT` in `.env`)
- phpMyAdmin (optional): `docker compose --profile dev up -d` → http://localhost:8081
- Debug mode is enabled via `docker-compose.override.yml`

## Production

1. Copy `.env.example` to `.env` and set **strong** passwords, unique salts, and real `WP_URL`.
2. Start:

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

Production overrides: no MySQL port on the host, `restart: unless-stopped`, debug off. Put TLS/reverse proxy in front and set `WP_URL` to your HTTPS domain. Optional in `.env`:

```env
WORDPRESS_CONFIG_EXTRA=define('FORCE_SSL_ADMIN', true);
```

## Reuse this stack for another WordPress site

This repo is intended as a **reusable template** for future projects.

### What is reusable vs per-site

| Reusable (copy as-is) | Per-site (change every time) |
|-----------------------|------------------------------|
| `docker-compose.yml`, overrides, prod file | `.env` (passwords, URLs, admin) |
| `scripts/bootstrap-wordpress.sh` | `COMPOSE_PROJECT_NAME` |
| `Makefile`, `.env.example` | `WP_SITE_TITLE`, `WP_URL`, ports |
| Official images (`wordpress:latest`, `mysql:8.4`) | `wp-content/` themes & plugins |

Each site gets its own Docker volumes (`<COMPOSE_PROJECT_NAME>_db_data`, etc.). **Never use the same `COMPOSE_PROJECT_NAME` in two project folders.**

### Option A — New project (recommended)

1. Copy or clone this repo into a new directory, e.g. `~/src/my-client-site`.
2. `cd` into it and run `cp .env.example .env`.
3. Set a **unique** `COMPOSE_PROJECT_NAME` in `.env` (e.g. `my-client-site`).
4. Customize:
   - `WP_URL` (and `WP_PORT` if not using 8080)
   - `WP_SITE_TITLE`, `WP_ADMIN_*`, `MYSQL_*` passwords
   - All `WORDPRESS_*` salts — [generate new keys](https://api.wordpress.org/secret-key/1.1/salt/) per site
5. Clear `wp-content/plugins` and `wp-content/themes` if you do not want this site’s assets (empty dirs are fine).
6. Run `docker compose up -d` — bootstrap creates a fresh WordPress install, admin user, and Elementor.
7. Log in at `WP_URL` with your new admin credentials.

### Option B — Two sites on the same machine

Use **separate directories** and different env values:

| Project   | `COMPOSE_PROJECT_NAME` | `WP_PORT` |
|-----------|------------------------|-----------|
| rigpa-de  | `rigpa-de`             | `8080`    |
| client-foo| `client-foo`           | `8082`    |

```bash
cd ~/src/client-foo
cp .env.example .env
# edit COMPOSE_PROJECT_NAME, WP_PORT, WP_URL=http://localhost:8082, etc.
docker compose up -d
```

### Option C — Git template

Mark this repository as a GitHub template, or maintain a `wordpress-docker-starter` repo and `git clone` it per client.

### Factory reset (one site)

Removes database and WordPress core volume for **this** `COMPOSE_PROJECT_NAME` only:

```bash
docker compose down -v
docker compose up -d
```

Warning: deletes all posts and DB data. Files under `wp-content/uploads/` on the bind mount may remain until you delete them manually.

### Moving a site to another machine

1. Export DB: `docker compose --profile tools run --rm wp-cli wp db export -` or `mysqldump` via the `db` service.
2. Copy `wp-content/` and your compose files + `.env`.
3. On the new machine: `docker compose up -d`, restore the dump, then update URLs:

```bash
docker compose --profile tools run --rm wp-cli wp option update home 'https://new-domain.example'
docker compose --profile tools run --rm wp-cli wp option update siteurl 'https://new-domain.example'
```

## Rigpa.de Map plugin

Interactive Germany locations map from the [Replicate Design](Replicate%20Design/) Figma export.

### Build assets (required before first use)

```bash
make build-map
```

This compiles React/Tailwind into `wp-content/plugins/rigpa-de-map/assets/` and copies [`germany-vector.svg`](germany-vector.svg).

### Shortcode

Add to any page or post:

```
[rigpa_de_map]
```

Alias: `[rigpa-de-map]`. In Elementor, use the **Shortcode** widget.

Bootstrap activates the plugin when the plugin directory exists (after `make build-map`). It does **not** change your homepage automatically.

### Testing the map on your homepage (manual)

1. Build assets: `make build-map`
2. Ensure the plugin is active: **Plugins → Rigpa.de Map**, or `make wp ARGS="plugin activate rigpa-de-map"`
3. Edit the page you use as the site front page
4. Add `[rigpa_de_map]` in the block editor, or an Elementor **Shortcode** widget
5. Update the page and view the site URL

One-off WP-CLI example (replace `123` with your home page ID):

```bash
make wp ARGS="post update 123 --post_content='[rigpa_de_map]'"
```

**Full-width test page (page ID 8):** Map Test at http://localhost:8080/?page_id=8 uses template `page-no-title` and post meta `_rigpa_de_map_full_width=1` so the map spans the content area. To enable on another page:

```bash
make wp ARGS="post meta update PAGE_ID _wp_page_template page-no-title"
make wp ARGS="post meta update PAGE_ID _rigpa_de_map_full_width 1"
```

### Rebuild after design changes

```bash
make build-map
```

Then hard-refresh the browser (assets use `filemtime` cache busting).

### Install on another WordPress site (zip)

Build a upload-ready plugin archive:

```bash
make package-plugin
```

This runs `make build-map`, verifies required assets, and writes **`dist/rigpa-de-map.zip`**.

Install in any WordPress admin:

1. **Plugins → Add New → Upload Plugin**
2. Choose `dist/rigpa-de-map.zip`
3. **Install Now**, then **Activate**

The zip contains the `rigpa-de-map/` folder at the archive root, as WordPress expects.

For local Docker dev, the plugin is bind-mounted from `wp-content/plugins/rigpa-de-map/` — run `make build-map` before `make setup` so bootstrap can activate it with complete assets.

## Rigpa Mega Menu plugin

Interactive header mega menu with English and German menu structures. Use in an Elementor Theme Builder header via shortcode.

### Build assets (required before first use)

```bash
make build-mega-menu
```

This compiles React/Tailwind from `wp-content/plugins/rigpa-mega-menu/src/` into `assets/` and downloads featured card images.

### Shortcode

```
[rigpa_mega_menu]
```

Alias: `[rigpa-mega-menu]`. Language override: `[rigpa_mega_menu lang="german"]` or `lang="english"`. Default `lang="auto"` follows the WordPress locale (`de_DE` → German).

Per-page appearance overrides (useful for interior pages without a dark hero, where the default white-on-transparent header would be invisible): `[rigpa_mega_menu transparent="false" color="#171717"]`. Both attributes are optional and fall back to the global settings on **Tools → Mega Menu** when omitted.

### Per-page header overrides (no shortcode required)

When the mega menu is mounted from a theme template part / nav-menu location (i.e. no shortcode is called on the page), use the **Mega Menu Header** metabox in the Page editor sidebar to override transparency and text colour for that page only. Options:

- **Header style** — Inherit / Solid (white background) / Transparent (over hero).
- **Menu text colour** — leave blank to auto-derive (white when transparent, dark when solid), or enter a hex like `#171717` to override.

**Defaults**: the built-in default is **solid + dark text** (`#171717`) so interior pages work out of the box. The homepage (or any page over a hero image / video) sets Header style = Transparent in its metabox; text colour then auto-derives to white without needing a separate override.

Both the shortcode path and the slot-renderer path resolve appearance through the `rigpa_mega_menu_is_transparent` and `rigpa_mega_menu_text_color` filters, so the metabox values apply regardless of mount method. Plugin/theme code can hook the same filters for programmatic overrides:

```php
add_filter('rigpa_mega_menu_is_transparent', function ($transparent, $context) {
    return is_page('about') ? false : $transparent;
}, 10, 2);
```

### Languages

The plugin supports **two menu languages**, each backed by its own nav menu and location:

| Shortcode `lang` | Menu loaded |
|------------------|-------------|
| `auto` (default) | **Mega Menu (German)** if WordPress locale starts with `de` (e.g. `de_DE`); otherwise **Mega Menu (English)** |
| `english` or `en` | Always **Mega Menu (English)** |
| `german` or `de` | Always **Mega Menu (German)** |

- **Menu links and section titles** come from the assigned WordPress nav menu for that language.
- **Header UI labels** (e.g. “Menu”, “Open menu”, “Learn more →”) follow the resolved language in the React frontend.
- **Other locales** (French, Spanish, etc.) use the **English** menu when `lang="auto"`. Set the site language to Deutsch, or pin the shortcode with `lang="german"` / `lang="english"`, to control which menu appears.
- **WPML / Polylang** — not auto-detected. Use separate Elementor headers per language with the matching `lang` attribute, or rely on `lang="auto"` where WordPress locale matches the menu you want.

Adding a third language would require a new menu location and code changes; only English and German are supported out of the box.

### Elementor header setup

1. **Templates → Theme Builder → Header** — create or edit a header template (display: Entire Site)
2. Add an Elementor **Shortcode** widget with `[rigpa_mega_menu]`
3. Disable the theme’s default header if it conflicts

Bootstrap activates the plugin when built assets exist (after `make build-mega-menu`).

### How menu content works

The mega menu reads its structure from **WordPress navigation menus** stored in the database (`Appearance → Menus`), not from the theme’s header menu.

1. **Plugin locations** — The plugin registers two slots: **Mega Menu (English)** (`rigpa-mega-menu-en`) and **Mega Menu (German)** (`rigpa-mega-menu-de`).
2. **Assignment** — You assign a nav menu to each slot under **Appearance → Menus → Menu Settings** (checkboxes at the bottom). WordPress stores this as a mapping from location slug → menu ID in the active theme.
3. **Structure** — Top-level menu items become section labels (e.g. Meditate, About). Nested items become the links inside each dropdown panel.
4. **Frontend** — The `[rigpa_mega_menu]` shortcode loads the assigned menu for the current language, passes it to the React app as JSON, and renders the interactive mega menu.
5. **Fallback** — If no menu is assigned to a location, the plugin uses built-in defaults from `includes/menus.php`.

**Editing:** Go to **Appearance → Menus**, select **Mega Menu (English)** or **Mega Menu (German)**, and edit items there. Enable **Description** under **Screen Options** to edit the subtitle text under each link.

**Status:** **Tools → Mega Menu** in wp-admin shows whether each location is assigned.

### Seed demo content (local dev)

Menus are **not** created automatically when the plugin is activated. Run a seed command from the project root:

```bash
# Demo pages + nav menus + location assignment
make seed-mega-menu-pages

# Nav menus + location assignment only
make seed-mega-menu-nav
```

The seed script creates both nav menus (6 top-level sections with nested links), links them to existing pages where possible, assigns them to the plugin locations, and sets featured-card meta on section items. Re-running is safe for pages (existing are skipped); re-running the nav seed **replaces** menu items with the default structure from `includes/menus.php`.

Then visit **http://localhost:8080/mega-menu-demo/** — menu links point at the seeded pages (e.g. `/introduction-to-meditation/`, `/de-berlin-dharma-mali/`).

### Install on another WordPress site (zip)

```bash
make package-mega-menu
```

Produces **`dist/rigpa-mega-menu.zip`** for **Plugins → Add New → Upload Plugin**.

After installing on another site, create menus under **Appearance → Menus**, assign them to **Mega Menu (English)** / **Mega Menu (German)**, or copy the structure from `includes/menus.php` manually.

## Troubleshooting

| Issue | What to try |
|-------|-------------|
| Port already in use | Change `WP_PORT` in `.env` |
| Setup failed | `docker compose logs wordpress-setup` |
| Stuck on install screen | Run `make setup` or check setup logs |
| Elementor / plugin install failed (`Could not create directory …/upgrade`) | `wordpress-setup` must run as uid `33:33` to match the WordPress container; run `make setup` again after pulling the fix |
| Permission errors in wp-cli | WP-CLI services use uid `33:33` to match `wordpress:latest` |
| Start fresh | `make reset` |

## Architecture

- **db** — MySQL 8.4, data in volume `db_data`
- **wordpress** — Apache + PHP, core in `wordpress_data`; `./wp-content/plugins`, `themes`, and `uploads` are bind-mounted (default core themes stay in the volume)
- **wordpress-setup** — one-shot WP-CLI install + Elementor
- **wp-cli** — optional (`--profile tools`) for maintenance commands

## Git

Committed: compose files, scripts, `.env.example`, `wp-content/themes`, `wp-content/plugins/rigpa-de-map/`, `wp-content/plugins/rigpa-mega-menu/`, `Replicate Design/` (excluding `node_modules`), `germany-vector.svg`.

Not committed: `.env`, uploads, other `wp-content/plugins/*` runtime installs (see `.gitignore`).
