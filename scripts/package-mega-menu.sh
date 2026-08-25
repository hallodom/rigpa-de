#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PLUGIN_SLUG="mindful-design-mega-menu"
PLUGIN_DIR="${ROOT}/wp-content/plugins/${PLUGIN_SLUG}"
DIST_DIR="${ROOT}/dist"
ZIP_FILE="${DIST_DIR}/${PLUGIN_SLUG}.zip"

cd "$ROOT"

echo "Building plugin assets..."
make build-mega-menu

required=(
  "${PLUGIN_DIR}/mindful-design-mega-menu.php"
  "${PLUGIN_DIR}/includes/class-md-mega-menu.php"
  "${PLUGIN_DIR}/includes/class-md-mega-menu-admin.php"
  "${PLUGIN_DIR}/includes/class-md-mega-menu-page-settings.php"
  "${PLUGIN_DIR}/includes/class-md-mega-menu-sanitize.php"
  "${PLUGIN_DIR}/includes/class-md-mega-menu-settings.php"
  "${PLUGIN_DIR}/includes/menus.php"
  "${PLUGIN_DIR}/assets/js/md-mega-menu.js"
  "${PLUGIN_DIR}/assets/css/md-mega-menu.css"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "ERROR: Missing required file: ${file}" >&2
    exit 1
  fi
done

mkdir -p "${DIST_DIR}"
rm -f "${ZIP_FILE}"

(cd "${ROOT}/wp-content/plugins" && zip -r "${ZIP_FILE}" "${PLUGIN_SLUG}" \
  -x "*.DS_Store" \
  -x "**/.DS_Store" \
  -x "${PLUGIN_SLUG}/src" \
  -x "${PLUGIN_SLUG}/src/" \
  -x "${PLUGIN_SLUG}/src/**")

echo ""
echo "Plugin package ready:"
echo "  ${ZIP_FILE}"
echo ""
echo "Install in WordPress:"
echo "  Plugins → Add New → Upload Plugin → choose ${PLUGIN_SLUG}.zip"
