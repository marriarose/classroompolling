#!/usr/bin/env bash
# Creates a fresh Laravel 11 app and overlays this project onto it.
# Usage: ./setup.sh [target-dir]   (default: classroom-quiz)
set -euo pipefail
HERE="$(cd "$(dirname "$0")" && pwd)"
TARGET="${1:-classroom-quiz}"

composer create-project laravel/laravel "$TARGET" "^11.0" --prefer-dist
cp -R "$HERE/overlay/." "$TARGET/"
cd "$TARGET"
rm -f tests/Feature/ExampleTest.php   # default test expects 200 on "/"

# Point the default env at MySQL
sed -i -e 's/^DB_CONNECTION=.*/DB_CONNECTION=mysql/' \
       -e 's/^# *DB_\(HOST\|PORT\|DATABASE\|USERNAME\|PASSWORD\)=/DB_\1=/' \
       -e 's/^DB_DATABASE=.*/DB_DATABASE=classroom_quiz/' \
       -e 's/^DB_USERNAME=.*/DB_USERNAME=root/' .env.example
cp .env.example .env
php artisan key:generate
echo
echo "Done. Create the MySQL database 'classroom_quiz', adjust DB_* in .env, then run:"
echo "  php artisan migrate --seed && php artisan serve"
