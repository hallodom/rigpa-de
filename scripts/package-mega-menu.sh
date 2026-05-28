#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PLUGIN_SLUG="rigpa-mega-menu"
PLUGIN_DIR="${ROOT}/wp-content/plugins/${PLUGIN_SLUG}"
DIST_DIR="${ROOT}/dist"
ZIP_FILE="${DIST_DIR}/${PLUGIN_SLUG}.zip"

cd "$ROOT"

echo "Building plugin assets..."
make build-mega-menu

required=(
  "${PLUGIN_DIR}/rigpa-mega-menu.php"
  "${PLUGIN_DIR}/includes/class-rigpa-mega-menu.php"
  "${PLUGIN_DIR}/includes/class-rigpa-mega-menu-admin.php"
  "${PLUGIN_DIR}/includes/class-rigpa-mega-menu-seeder.php"
  "${PLUGIN_DIR}/includes/menu-descriptions.php"
  "${PLUGIN_DIR}/includes/class-rigpa-mega-menu-description-sync.php"
  "${PLUGIN_DIR}/includes/class-rigpa-mega-menu-sanitize.php"
  "${PLUGIN_DIR}/includes/class-rigpa-mega-menu-settings.php"
  "${PLUGIN_DIR}/includes/class-rigpa-mega-menu-duplicator.php"
  "${PLUGIN_DIR}/includes/menus.php"
  "${PLUGIN_DIR}/assets/js/rigpa-mega-menu.js"
  "${PLUGIN_DIR}/assets/css/rigpa-mega-menu.css"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "ERROR: Missing required file: ${file}" >&2
    exit 1
  fi
done

image_count="$(find "${PLUGIN_DIR}/assets/images" -name '*.jpg' 2>/dev/null | wc -l | tr -d ' ')"
if [ "${image_count}" -lt 1 ]; then
  echo "ERROR: No featured images found in ${PLUGIN_DIR}/assets/images/" >&2
  exit 1
fi

mkdir -p "${DIST_DIR}"
rm -f "${ZIP_FILE}"

(cd "${ROOT}/wp-content/plugins" && zip -r "${ZIP_FILE}" "${PLUGIN_SLUG}" \
  -x "*.DS_Store" \
  -x "**/.DS_Store" \
  -x "${PLUGIN_SLUG}/src/**")

echo ""
echo "Plugin package ready:"
echo "  ${ZIP_FILE}"
echo ""
echo "Install in WordPress:"
echo "  Plugins → Add New → Upload Plugin → choose ${PLUGIN_SLUG}.zip"
