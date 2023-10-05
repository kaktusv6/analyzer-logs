build:
	docker-compose build
	docker-compose run app php /var/www/composer.phar install
	docker-compose run app /var/www/docker/deploy.sh

run-analyze-example:
	cat ./resources/logs/access.test.log | docker-compose run app php /var/www/application.php analyze:logs:unavailable:stream 90 45

run-tests:
	docker-compose run app /var/www/vendor/bin/phpunit --bootstrap /var/www/bootstrap.php --configuration /var/www/phpunit.xml --testdox

commands:
	docker-compose run app php /var/www/application.php
