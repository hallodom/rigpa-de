#!/bin/sh
set -eu

cd /var/www/html

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

echo "Bootstrap complete."
