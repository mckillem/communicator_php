docker up:
	docker compose -p communicator_php up -d --build --force-recreate

docker down:
	docker compose -p communicator_php down

phpstan:
	docker exec communicator_php vendor/bin/phpstan analyse app

test:
	docker exec communicator_php vendor/bin/codecept run --steps

composer-install:
	docker exec communicator_php composer install

composer-update:
	docker exec communicator_php composer update