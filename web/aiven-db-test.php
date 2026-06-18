<?php
/**
 * Standalone Aiven MySQL connection test.
 * Visit /aiven-db-test.php or run: php web/aiven-db-test.php
 *
 * Download ca.pem from Aiven Console → MySQL service → Overview → CA certificate
 * and save it to storage/certs/ca.pem
 */

require dirname(__DIR__) . '/bootstrap.php';

use craft\helpers\App;

$host = App::env('CRAFT_DB_SERVER');
$port = App::env('CRAFT_DB_PORT') ?: '3306';
$database = App::env('CRAFT_DB_DATABASE');
$user = App::env('CRAFT_DB_USER');
$password = App::env('CRAFT_DB_PASSWORD');
$sslCa = App::env('CRAFT_DB_SSL_CA') ?: dirname(__DIR__) . '/storage/certs/ca.pem';

if (!$host || !$user || $password === null || $password === '') {
    exit("Missing database credentials in .env (CRAFT_DB_PASSWORD is required)\n");
}

$conn = "mysql:host={$host};port={$port};dbname={$database}";
if (is_file($sslCa)) {
    $conn .= ';sslmode=verify-ca;sslrootcert=' . $sslCa;
}

try {
    $db = new PDO($conn, $user, $password);
    $stmt = $db->query('SELECT VERSION()');
    echo 'Connected. MySQL version: ' . $stmt->fetchColumn() . PHP_EOL;
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}
