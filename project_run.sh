docker-compose up -d --build site
cd src
docker-compose run --rm composer install
docker-compose run artisan migrate:fresh --seed
