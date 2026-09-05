#!/bin/sh
set -eu

cd /var/www/html

PLUGIN_SLUG="rigpa-de-map"
PLUGIN_DIR="/var/www/html/wp-content/plugins/${PLUGIN_SLUG}"

echo "Waiting for WordPress and database..."
for i in $(seq 1 60); do
  if wp config path --allow-root 2>/dev/null; then
    break
  fi
  sleep 2
done

if ! wp config path --allow-root 2>/dev/null; then
  echo "ERROR: wp-config.php not found after waiting."
  exit 1
fi

if ! wp core is-installed --allow-root 2>/dev/null; then
  echo "Installing WordPress..."
  wp core install \
    --url="${WP_URL}" \
    --title="${WP_SITE_TITLE}" \
    --admin_user="${WP_ADMIN_USER}" \
    --admin_password="${WP_ADMIN_PASSWORD}" \
    --admin_email="${WP_ADMIN_EMAIL}" \
    --skip-email \
    --allow-root
  echo "WordPress installed."
else
  echo "WordPress already installed."
fi

# Ensure WP can download plugins (upgrade dir lives on the volume, not bind mounts)
mkdir -p /var/www/html/wp-content/upgrade
chmod 775 /var/www/html/wp-content/upgrade 2>/dev/null || true

if ! wp plugin is-active elementor --allow-root 2>/dev/null; then
  echo "Installing and activating Elementor..."
  wp plugin install elementor --activate --allow-root
  echo "Elementor activated."
else
  echo "Elementor already active."
fi

if [ -f "${PLUGIN_DIR}/rigpa-de-map.php" ]; then
  missing_assets=0
  for asset in \
    "${PLUGIN_DIR}/assets/js/rigpa-de-map.js" \
    "${PLUGIN_DIR}/assets/css/rigpa-de-map.css" \
    "${PLUGIN_DIR}/assets/germany-vector.svg"
  do
    if [ ! -f "$asset" ]; then
      echo "WARNING: Missing plugin asset: $asset"
      missing_assets=1
    fi
  done

  if [ "$missing_assets" -eq 1 ]; then
    echo "Rigpa.de Map plugin found but assets are incomplete."
    echo "Run 'make build-map' on the host, then 'make setup' to activate the plugin."
  else
    echo "Activating Rigpa.de Map plugin..."
    wp plugin activate "${PLUGIN_SLUG}" --allow-root 2>/dev/null || true
    echo "Rigpa.de Map activated (or already active)."
  fi
else
  echo "Rigpa.de Map plugin not found in wp-content/plugins/${PLUGIN_SLUG}/."
  echo "For local dev: run 'make build-map' on the host before 'make setup'."
  echo "For production: upload dist/rigpa-de-map.zip via Plugins → Add New → Upload Plugin."
fi

MEGA_MENU_SLUG="mindful-design-mega-menu"
MEGA_MENU_DIR="/var/www/html/wp-content/plugins/${MEGA_MENU_SLUG}"

if [ -f "${MEGA_MENU_DIR}/mindful-design-mega-menu.php" ]; then
  mega_menu_missing_assets=0
  for asset in \
    "${MEGA_MENU_DIR}/assets/js/md-mega-menu.js" \
    "${MEGA_MENU_DIR}/assets/css/md-mega-menu.css"
  do
    if [ ! -f "$asset" ]; then
      echo "WARNING: Missing mega menu asset: $asset"
      mega_menu_missing_assets=1
    fi
  done

  if [ "$mega_menu_missing_assets" -eq 1 ]; then
    echo "Mindful Design Mega Menu plugin found but assets are incomplete."
    echo "Run 'make build-mega-menu' on the host, then 'make setup' to activate the plugin."
  else
    echo "Activating Mindful Design Mega Menu plugin..."
    wp plugin activate "${MEGA_MENU_SLUG}" --allow-root 2>/dev/null || true
    echo "Mindful Design Mega Menu activated (or already active)."
  fi
else
  echo "Mindful Design Mega Menu plugin not found in wp-content/plugins/${MEGA_MENU_SLUG}/."
  echo "For local dev: run 'make build-mega-menu' on the host before 'make setup'."
  echo "For production: upload dist/mindful-design-mega-menu.zip via Plugins → Add New → Upload Plugin."
fi

echo "Bootstrap complete."
