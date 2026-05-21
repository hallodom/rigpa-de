.PHONY: up down logs setup wp prod-up prod-down reset build-map

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
