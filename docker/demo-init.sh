#!/bin/sh
set -eu

echo "Preparing the seeded YP Exam Portal demo database..."

INITIALIZED=$(php -r '
try {
    $pdo = new PDO(
        "mysql:host=" . getenv("DB_HOST") . ";port=" . getenv("DB_PORT") . ";dbname=" . getenv("DB_DATABASE"),
        getenv("DB_USERNAME"),
        getenv("DB_PASSWORD"),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $count = (int) $pdo->query("SELECT COUNT(*) FROM migrations")->fetchColumn();
    echo $count > 0 ? "1" : "0";
} catch (Throwable $e) {
    echo "0";
}
')

if [ "$INITIALIZED" = "1" ]; then
    echo "Database already initialized; applying any new migrations."
    php artisan migrate --force
else
    echo "Fresh database; running migrations and seeders."
    php artisan migrate:fresh --seed --force
fi

php artisan storage:link 2>/dev/null || true
php artisan optimize:clear

echo "Demo is ready."
echo "URL: http://localhost:8000"
echo "Admin: admin@gmail.com / qwertyuiop"
