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
| `make package-plugin` | Build assets and create `dist/rigpa-de-map.zip` for WP upload |

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

Committed: compose files, scripts, `.env.example`, `wp-content/themes`, `wp-content/plugins/rigpa-de-map/`, `Replicate Design/` (excluding `node_modules`), `germany-vector.svg`.

Not committed: `.env`, uploads, other `wp-content/plugins/*` runtime installs (see `.gitignore`).
