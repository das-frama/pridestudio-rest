# Pridestudio REST API

REST API для сайта [online.pridestudio.ru]

## Requirements
- PHP (>= 7.4)
- MongoDB (>= 1.6)

## Install
After `git clone` run:
```bash
composer create-project
```
or manually:

```bash
composer install
cp .env.example .env
php pride app:init
```

Then change DB_DATABASE in `.env` to actual database. 

## Run
```bash
composer serve
```

By default server runs at `http://api.pridestudio.local:8000`

[online.pridestudio.ru]: https://online.pridestudio.ru