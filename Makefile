.PHONY: up down logs setup wp prod-up prod-down reset build-map build-mega-menu package-plugin package-mega-menu

up:
	docker compose up -d

down:
	docker compose down

logs:
	docker compose logs -f

setup:
	docker compose run --rm wordpress-setup

wp:
	docker compose --profile tools run --rm wp-cli wp --allow-root $(ARGS)

prod-up:
	docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d

prod-down:
	docker compose -f docker-compose.yml -f docker-compose.prod.yml down

reset:
	docker compose down -v
	docker compose up -d

build-map:
	cp germany-vector.svg wp-content/plugins/rigpa-de-map/assets/germany-vector.svg
	cd "Replicate Design" && npm install && npm run build:wp
	mkdir -p wp-content/plugins/rigpa-de-map/assets/images
	cp "Replicate Design/src/assets/images/"*.jpg wp-content/plugins/rigpa-de-map/assets/images/
	cp wp-content/plugins/rigpa-de-map/includes/admin-media.js wp-content/plugins/rigpa-de-map/assets/js/admin-media.js

build-mega-menu:
	cd wp-content/plugins/rigpa-mega-menu/src && npm install && npm run build

package-plugin:
	./scripts/package-plugin.sh

package-mega-menu:
	./scripts/package-mega-menu.sh
