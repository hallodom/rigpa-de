#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

const ROOT = path.resolve(__dirname, '..');
const COUNTRIES_FILE = path.join(ROOT, 'wp-content/plugins/rigpa-de-map/includes/countries.php');
const GEOJSON_FILE = '/tmp/rigpa-map-vectors/ne_50m_admin_0_countries.geojson';
const CACHE_FILE = '/tmp/rigpa-de-map-geocodes.json';
const PADDING = 8;

const TARGETS = {
  australia: { admin: 'Australia', width: 620, height: 430, bbox: [110, -45, 155, -9] },
  belgium: { admin: 'Belgium', width: 520, height: 400, bbox: [2, 49, 7, 52] },
  canada: { admin: 'Canada', width: 650, height: 420, bbox: [-142, 41, -52, 84] },
  france: { admin: 'France', width: 520, height: 520, bbox: [-6, 41, 10, 52] },
  ireland: { admin: 'Ireland', width: 520, height: 460, bbox: [-11, 51, -5, 56] },
  italy: { admin: 'Italy', width: 520, height: 520, bbox: [6, 35, 19, 48] },
  netherlands: { admin: 'Netherlands', width: 420, height: 520, bbox: [3, 50, 8, 54] },
  spain: { admin: 'Spain', width: 520, height: 420, bbox: [-19, 27, 5, 44] },
  switzerland: { admin: 'Switzerland', width: 460, height: 350, bbox: [5, 45, 11, 48] },
  uk: { admin: 'United Kingdom', width: 430, height: 620, bbox: [-9, 49, 3, 61] },
  usa: { admin: 'United States of America', width: 650, height: 420, bbox: [-125, 24, -66, 50] },
};

const COUNTRY_NAMES = {
  australia: 'Australia',
  belgium: 'Belgium',
  canada: 'Canada',
  france: 'France',
  ireland: 'Ireland',
  italy: 'Italy',
  netherlands: 'Netherlands',
  spain: 'Spain',
  switzerland: 'Switzerland',
  uk: 'United Kingdom',
  usa: 'United States',
};

const QUERY_OVERRIDES = {
  australia: 'Australia',
  'bush-telegraph-distance-learning': 'Blueys Beach, New South Wales, Australia',
  'garab-ling-blueys': 'Blueys Beach, New South Wales, Australia',
  ireland: 'Ireland',
  'london-cornwall': 'Cornwall, United Kingdom',
  'london-norwich': 'Norwich, United Kingdom',
  'london-south-west': 'South West London, United Kingdom',
  'rigpa-australia-bush-telegraph': 'Blueys Beach, New South Wales, Australia',
  'rigpa-usa': 'United States',
  'usa-distance-sangha-online-group': 'United States',
};

// Natural Earth's 50m coastline is intentionally generalized. These preserve
// the place while keeping coastal markers visibly inside the rendered landmass.
const COORDINATE_OVERRIDES = {
  'bush-telegraph-distance-learning': { lon: 152.511667, lat: -32.339167 },
  'garab-ling-blueys': { lon: 152.511667, lat: -32.339167 },
  'rigpa-australia-bush-telegraph': { lon: 152.511667, lat: -32.339167 },
  'dzogchen-beara': { lon: -9.985707, lat: 51.619593 },
  'new-york-ny': { lon: -73.991015, lat: 40.692728 },
  'san-francisco-bay-area-ca': { lon: -122.385847, lat: 37.788497 },
  alicante: { lon: -0.493171, lat: 38.343637 },
};

function mercatorY(lat) {
  const radians = lat * Math.PI / 180;
  return Math.log(Math.tan(Math.PI / 4 + radians / 2)) * 180 / Math.PI;
}

function polygonBbox(polygon) {
  const bounds = [Infinity, Infinity, -Infinity, -Infinity];
  for (const ring of polygon) {
    for (const [lon, lat] of ring) {
      bounds[0] = Math.min(bounds[0], lon);
      bounds[1] = Math.min(bounds[1], lat);
      bounds[2] = Math.max(bounds[2], lon);
      bounds[3] = Math.max(bounds[3], lat);
    }
  }
  return bounds;
}

function intersects(a, b) {
  return a[0] <= b[2] && a[2] >= b[0] && a[1] <= b[3] && a[3] >= b[1];
}

function polygonsFor(feature, bbox) {
  const polygons = feature.geometry.type === 'Polygon'
    ? [feature.geometry.coordinates]
    : feature.geometry.coordinates;
  return polygons.filter((polygon) => intersects(polygonBbox(polygon), bbox));
}

function projectFor(feature, target) {
  const points = polygonsFor(feature, target.bbox).flat(2);
  const minX = Math.min(...points.map(([lon]) => lon));
  const maxX = Math.max(...points.map(([lon]) => lon));
  const minY = Math.min(...points.map(([, lat]) => mercatorY(lat)));
  const maxY = Math.max(...points.map(([, lat]) => mercatorY(lat)));
  const scale = Math.min(
    (target.width - PADDING * 2) / (maxX - minX),
    (target.height - PADDING * 2) / (maxY - minY),
  );
  const offsetX = (target.width - (maxX - minX) * scale) / 2;
  const offsetY = (target.height - (maxY - minY) * scale) / 2;

  return (lon, lat) => ({
    x: Math.round(offsetX + (lon - minX) * scale),
    y: Math.round(offsetY + (maxY - mercatorY(lat)) * scale),
  });
}

function parseLocations(source) {
  let country = null;
  const locations = [];

  for (const line of source.split('\n')) {
    const countryMatch = line.match(/^        '([^']+)' => array\($/);
    if (countryMatch && TARGETS[countryMatch[1]]) {
      country = countryMatch[1];
      continue;
    }

    const locationMatch = line.match(/rigpa_de_map_location\('([^']+)', '([^']+)', '[^']+', '([^']+)', (\d+), (\d+)\)/);
    if (country && locationMatch && country !== 'germany') {
      const [, id, name, url, x, y] = locationMatch;
      locations.push({ country, id, name, url, current: { x: Number(x), y: Number(y) } });
    }
  }

  return locations;
}

async function geocode(query) {
  const url = new URL('https://nominatim.openstreetmap.org/search');
  url.searchParams.set('format', 'jsonv2');
  url.searchParams.set('limit', '1');
  url.searchParams.set('q', query);
  const response = await fetch(url, { headers: { 'User-Agent': 'RigpaCountryMapAudit/1.0' } });
  if (!response.ok) {
    throw new Error(`Nominatim request failed (${response.status}) for ${query}`);
  }
  const results = await response.json();
  return results[0] || null;
}

async function main() {
  const requestedCountry = process.argv.find((arg) => arg.startsWith('--country='))?.split('=')[1];
  const write = process.argv.includes('--write');
  const source = fs.readFileSync(COUNTRIES_FILE, 'utf8');
  const cache = fs.existsSync(CACHE_FILE) ? JSON.parse(fs.readFileSync(CACHE_FILE, 'utf8')) : {};
  const locations = parseLocations(source).filter((location) => !requestedCountry || location.country === requestedCountry);
  const geojson = JSON.parse(fs.readFileSync(GEOJSON_FILE, 'utf8'));
  const projectors = Object.fromEntries(Object.entries(TARGETS).map(([slug, target]) => {
    const feature = geojson.features.find((item) => item.properties.ADMIN === target.admin);
    return [slug, projectFor(feature, target)];
  }));
  const report = [];

  for (const location of locations) {
    const query = QUERY_OVERRIDES[location.id] || `${location.name}, ${COUNTRY_NAMES[location.country]}`;
    if (!cache[location.id]) {
      const result = await geocode(query);
      if (!result) {
        throw new Error(`No geocoding result for ${query}`);
      }
      cache[location.id] = {
        displayName: result.display_name,
        lat: Number(result.lat),
        lon: Number(result.lon),
        query,
      };
      fs.writeFileSync(CACHE_FILE, JSON.stringify(cache, null, 2) + '\n');
      await new Promise((resolve) => setTimeout(resolve, 1000));
    }

    const geo = { ...cache[location.id], ...COORDINATE_OVERRIDES[location.id] };
    const projected = projectors[location.country](geo.lon, geo.lat);
    report.push({ ...location, geo, projected, delta: { x: projected.x - location.current.x, y: projected.y - location.current.y } });
  }

  if (write) {
    const coordinates = new Map(report.map((item) => [item.id, item.projected]));
    let country = null;
    const updated = source.split('\n').map((line) => {
      const countryMatch = line.match(/^        '([^']+)' => array\($/);
      if (countryMatch && TARGETS[countryMatch[1]]) {
        country = countryMatch[1];
      }
      const locationMatch = line.match(/rigpa_de_map_location\('([^']+)',/);
      const projected = country !== 'germany' && locationMatch ? coordinates.get(locationMatch[1]) : null;
      return projected ? line.replace(/, \d+, \d+\)(,?)$/, `, ${projected.x}, ${projected.y})$1`) : line;
    }).join('\n');
    fs.writeFileSync(COUNTRIES_FILE, updated);
  }

  console.log(JSON.stringify(report, null, 2));
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
