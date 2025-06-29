up:
	docker-compose up -d

down:
	docker-compose down

bash:
	docker exec -it laravel-app bash

composer-install:
	docker exec laravel-app composer install

migrate:
	docker exec laravel-app php artisan migrate

seed:
	docker exec laravel-app php artisan db:seed

logs:
	docker exec -it laravel-app tail -f storage/logs/laravel.log
