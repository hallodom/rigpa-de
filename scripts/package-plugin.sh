#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PLUGIN_SLUG="rigpa-de-map"
PLUGIN_DIR="${ROOT}/wp-content/plugins/${PLUGIN_SLUG}"
DIST_DIR="${ROOT}/dist"
ZIP_FILE="${DIST_DIR}/${PLUGIN_SLUG}.zip"

cd "$ROOT"

echo "Building plugin assets..."
make build-map

required=(
  "${PLUGIN_DIR}/rigpa-de-map.php"
  "${PLUGIN_DIR}/includes/class-rigpa-de-map.php"
  "${PLUGIN_DIR}/includes/class-rigpa-de-map-admin.php"
  "${PLUGIN_DIR}/includes/countries.php"
  "${PLUGIN_DIR}/includes/locations.php"
  "${PLUGIN_DIR}/includes/admin-media.js"
  "${PLUGIN_DIR}/assets/js/rigpa-de-map.js"
  "${PLUGIN_DIR}/assets/js/admin-media.js"
  "${PLUGIN_DIR}/assets/css/rigpa-de-map.css"
  "${PLUGIN_DIR}/assets/germany-vector.svg"
  "${PLUGIN_DIR}/assets/maps/australia.svg"
  "${PLUGIN_DIR}/assets/maps/belgium.svg"
  "${PLUGIN_DIR}/assets/maps/canada.svg"
  "${PLUGIN_DIR}/assets/maps/france.svg"
  "${PLUGIN_DIR}/assets/maps/germany.svg"
  "${PLUGIN_DIR}/assets/maps/ireland.svg"
  "${PLUGIN_DIR}/assets/maps/italy.svg"
  "${PLUGIN_DIR}/assets/maps/netherlands.svg"
  "${PLUGIN_DIR}/assets/maps/spain.svg"
  "${PLUGIN_DIR}/assets/maps/switzerland.svg"
  "${PLUGIN_DIR}/assets/maps/uk.svg"
  "${PLUGIN_DIR}/assets/maps/usa.svg"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "ERROR: Missing required file: ${file}" >&2
    exit 1
  fi
done

image_count="$(find "${PLUGIN_DIR}/assets/images" -name '*.jpg' 2>/dev/null | wc -l | tr -d ' ')"
if [ "${image_count}" -lt 1 ]; then
  echo "ERROR: No city images found in ${PLUGIN_DIR}/assets/images/" >&2
  exit 1
fi

mkdir -p "${DIST_DIR}"
rm -f "${ZIP_FILE}"

# WordPress expects the plugin folder at the root of the zip archive.
(cd "${ROOT}/wp-content/plugins" && zip -r "${ZIP_FILE}" "${PLUGIN_SLUG}" -x "*.DS_Store" -x "**/.DS_Store")

echo ""
echo "Plugin package ready:"
echo "  ${ZIP_FILE}"
echo ""
echo "Install in WordPress:"
echo "  Plugins → Add New → Upload Plugin → choose ${PLUGIN_SLUG}.zip"
