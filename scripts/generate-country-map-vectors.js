#!/usr/bin/env node

const fs = require('fs');
const https = require('https');
const path = require('path');

const DATA_URL = 'https://raw.githubusercontent.com/nvkelso/natural-earth-vector/master/geojson/ne_50m_admin_0_countries.geojson';
const ROOT = path.resolve(__dirname, '..');
const OUT_DIR = path.join(ROOT, 'map-vectors');
const PADDING = 8;
const MAP_FILL = '#E6E6E6';
const MAP_STROKE = '#828282';
const MAP_STROKE_WIDTH = 3;

const TARGETS = {
  australia: {
    admin: 'Australia',
    width: 620,
    height: 430,
    bbox: [110, -45, 155, -9],
  },
  belgium: {
    admin: 'Belgium',
    width: 520,
    height: 400,
    padding: 64,
    bbox: [2, 49, 7, 52],
  },
  canada: {
    admin: 'Canada',
    width: 650,
    height: 420,
    bbox: [-142, 41, -52, 84],
  },
  france: {
    admin: 'France',
    width: 520,
    height: 520,
    bbox: [-6, 41, 10, 52],
  },
  ireland: {
    admin: 'Ireland',
    width: 520,
    height: 460,
    bbox: [-11, 51, -5, 56],
  },
  italy: {
    admin: 'Italy',
    width: 520,
    height: 520,
    bbox: [6, 35, 19, 48],
  },
  netherlands: {
    admin: 'Netherlands',
    width: 420,
    height: 520,
    padding: 42,
    bbox: [3, 50, 8, 54],
  },
  spain: {
    admin: 'Spain',
    width: 520,
    height: 420,
    bbox: [-19, 27, 5, 44],
  },
  switzerland: {
    admin: 'Switzerland',
    width: 460,
    height: 350,
    padding: 56,
    bbox: [5, 45, 11, 48],
  },
  uk: {
    admin: 'United Kingdom',
    width: 430,
    height: 620,
    padding: 38,
    bbox: [-9, 49, 3, 61],
  },
  usa: {
    admin: 'United States of America',
    width: 650,
    height: 420,
    bbox: [-125, 24, -66, 50],
  },
};

function download(url) {
  return new Promise((resolve, reject) => {
    https.get(url, (res) => {
      if (res.statusCode !== 200) {
        reject(new Error(`Download failed: ${res.statusCode} ${res.statusMessage}`));
        res.resume();
        return;
      }

      let body = '';
      res.setEncoding('utf8');
      res.on('data', (chunk) => {
        body += chunk;
      });
      res.on('end', () => resolve(body));
    }).on('error', reject);
  });
}

function mercatorY(lat) {
  const radians = lat * Math.PI / 180;
  return Math.log(Math.tan(Math.PI / 4 + radians / 2)) * 180 / Math.PI;
}

function polygonBbox(polygon) {
  let minLon = Infinity;
  let minLat = Infinity;
  let maxLon = -Infinity;
  let maxLat = -Infinity;

  for (const ring of polygon) {
    for (const [lon, lat] of ring) {
      minLon = Math.min(minLon, lon);
      minLat = Math.min(minLat, lat);
      maxLon = Math.max(maxLon, lon);
      maxLat = Math.max(maxLat, lat);
    }
  }

  return [minLon, minLat, maxLon, maxLat];
}

function intersects(a, b) {
  return a[0] <= b[2] && a[2] >= b[0] && a[1] <= b[3] && a[3] >= b[1];
}

function geometryToPolygons(geometry) {
  if (geometry.type === 'Polygon') {
    return [geometry.coordinates];
  }

  if (geometry.type === 'MultiPolygon') {
    return geometry.coordinates;
  }

  return [];
}

function projectedBbox(polygons) {
  let minX = Infinity;
  let minY = Infinity;
  let maxX = -Infinity;
  let maxY = -Infinity;

  for (const polygon of polygons) {
    for (const ring of polygon) {
      for (const [lon, lat] of ring) {
        const x = lon;
        const y = mercatorY(lat);
        minX = Math.min(minX, x);
        minY = Math.min(minY, y);
        maxX = Math.max(maxX, x);
        maxY = Math.max(maxY, y);
      }
    }
  }

  return { minX, minY, maxX, maxY };
}

function round(value) {
  return Number(value.toFixed(2)).toString();
}

function makeProjector(bounds, width, height) {
  const mapWidth = bounds.maxX - bounds.minX;
  const mapHeight = bounds.maxY - bounds.minY;
  const padding = bounds.padding ?? PADDING;
  const scale = Math.min((width - padding * 2) / mapWidth, (height - padding * 2) / mapHeight);
  const offsetX = (width - mapWidth * scale) / 2;
  const offsetY = (height - mapHeight * scale) / 2;

  return ([lon, lat]) => {
    const x = offsetX + (lon - bounds.minX) * scale;
    const y = offsetY + (bounds.maxY - mercatorY(lat)) * scale;

    return [round(x), round(y)];
  };
}

function ringArea(points) {
  let area = 0;
  for (let i = 0; i < points.length; i += 1) {
    const [x1, y1] = points[i];
    const [x2, y2] = points[(i + 1) % points.length];
    area += Number(x1) * Number(y2) - Number(x2) * Number(y1);
  }

  return Math.abs(area / 2);
}

function polygonPath(polygon, project) {
  return polygon.map((ring, index) => {
    const points = ring.map(project);
    if (index === 0 && ringArea(points) < 1.5) {
      return '';
    }

    return `M${points.map(([x, y]) => `${x} ${y}`).join('L')}Z`;
  }).join('');
}

function svgForCountry(feature, target) {
  const polygons = geometryToPolygons(feature.geometry)
    .filter((polygon) => intersects(polygonBbox(polygon), target.bbox));

  if (polygons.length === 0) {
    throw new Error(`No polygons left for ${target.admin}`);
  }

  const bounds = projectedBbox(polygons);
  const project = makeProjector({ ...bounds, padding: target.padding }, target.width, target.height);
  const d = polygons.map((polygon) => polygonPath(polygon, project)).join('');

  return `<?xml version="1.0" encoding="UTF-8"?>
<svg width="${target.width}" height="${target.height}" viewBox="0 0 ${target.width} ${target.height}" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="${d}" fill="${MAP_FILL}" stroke="${MAP_STROKE}" stroke-width="${MAP_STROKE_WIDTH}" stroke-linejoin="round" stroke-linecap="round" fill-rule="evenodd"/>
</svg>
`;
}

async function main() {
  const sourcePath = process.argv[2];
  const raw = sourcePath
    ? fs.readFileSync(sourcePath, 'utf8')
    : await download(DATA_URL);
  const data = JSON.parse(raw);

  fs.mkdirSync(OUT_DIR, { recursive: true });

  for (const [slug, target] of Object.entries(TARGETS)) {
    const feature = data.features.find((item) => item.properties.ADMIN === target.admin);
    if (!feature) {
      throw new Error(`Missing feature for ${target.admin}`);
    }

    const svg = svgForCountry(feature, target);
    fs.writeFileSync(path.join(OUT_DIR, `${slug}.svg`), svg);
    console.log(`Wrote ${slug}.svg`);
  }
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
