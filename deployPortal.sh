#!/usr/bin/env bash
git pull
php composer.phar install -o
yarn encore production
php bin/console doctrine:migrations:migrate