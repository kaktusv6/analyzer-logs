build:
	docker-compose build

run-analyze-example:
	cat ./resources/logs/access.log | docker-compose run app php /var/www/application.php analyze:unavailable:logs 90 45

run-tests:
	docker-compose run app /var/www/vendor/bin/phpunit --bootstrap /var/www/bootstrap.php --configuration /var/www/phpunit.xml --testdox

commands:
	docker-compose run app php /var/www/application.php
