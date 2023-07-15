### Инструкция
```bash
cp .env.example .env
composer install
docker-compose up -d
symfony console doctrine:migrations:migrate
symfony server:start
```
