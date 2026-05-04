#!/bin/sh
set -eu

php -r '
$pdo = new PDO("mysql:host=mysql;port=3306;charset=utf8mb4", "root", "root", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$sql = file_get_contents("docker/mysql/create-test-db.sql");

foreach (array_filter(array_map("trim", explode(";", $sql))) as $statement) {
    $pdo->exec($statement);
}
'

php artisan test
