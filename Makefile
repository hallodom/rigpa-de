.PHONY: up down logs setup wp prod-up prod-down reset build-map build-mega-menu package-plugin package-mega-menu seed-mega-menu-pages seed-mega-menu-nav

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

build-mega-menu:
	mkdir -p wp-content/plugins/rigpa-mega-menu/assets/images
	@if [ ! -f wp-content/plugins/rigpa-mega-menu/assets/images/featured-meditate.jpg ]; then \
		curl -fsSL "https://images.unsplash.com/photo-1766345080484-06f05d102458?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&w=400" \
			-o wp-content/plugins/rigpa-mega-menu/assets/images/featured-meditate.jpg; \
	fi
	@if [ ! -f wp-content/plugins/rigpa-mega-menu/assets/images/featured-retreats.jpg ]; then \
		curl -fsSL "https://images.unsplash.com/photo-1758391792869-447d30bc9035?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&w=400" \
			-o wp-content/plugins/rigpa-mega-menu/assets/images/featured-retreats.jpg; \
	fi
	@if [ ! -f wp-content/plugins/rigpa-mega-menu/assets/images/featured-welcome.jpg ]; then \
		curl -fsSL "https://images.unsplash.com/photo-1758391792889-1210746af208?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&w=400" \
			-o wp-content/plugins/rigpa-mega-menu/assets/images/featured-welcome.jpg; \
	fi
	@if [ ! -f wp-content/plugins/rigpa-mega-menu/assets/images/featured-visit.jpg ]; then \
		curl -fsSL "https://images.unsplash.com/photo-1776100882982-237106f1471d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&w=400" \
			-o wp-content/plugins/rigpa-mega-menu/assets/images/featured-visit.jpg; \
	fi
	cd wp-content/plugins/rigpa-mega-menu/src && npm install && npm run build

package-plugin:
	./scripts/package-plugin.sh

package-mega-menu:
	./scripts/package-mega-menu.sh

seed-mega-menu-pages:
	docker compose --profile tools run --rm -v "$(PWD)/scripts:/scripts:ro" wp-cli wp --allow-root eval-file /scripts/seed-mega-menu-pages.php

seed-mega-menu-nav:
	docker compose --profile tools run --rm -v "$(PWD)/scripts:/scripts:ro" wp-cli wp --allow-root eval-file /scripts/seed-mega-menu-nav.php
