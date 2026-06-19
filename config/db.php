<?php
/**
 * Database Connection Settings
 *
 * @link https://craftcms.com/docs/5.x/reference/config/db.html
 */

use craft\config\DbConfig;
use craft\helpers\App;

$trim = static fn(mixed $value): mixed => is_string($value) ? trim($value) : $value;

$dbConfig = DbConfig::create();

$url = $trim(App::env('CRAFT_DB_URL') ?: App::env('DATABASE_URL'));
if ($url) {
    $dbConfig->url($url);
}

if ($driver = $trim(App::env('CRAFT_DB_DRIVER'))) {
    $dbConfig->driver($driver);
}

if ($server = $trim(App::env('CRAFT_DB_SERVER'))) {
    $dbConfig->server($server);
}

if ($port = $trim(App::env('CRAFT_DB_PORT'))) {
    $dbConfig->port((int) $port);
}

if ($database = $trim(App::env('CRAFT_DB_DATABASE'))) {
    $dbConfig->database($database);
}

if ($user = $trim(App::env('CRAFT_DB_USER'))) {
    $dbConfig->user($user);
}

if (($password = App::env('CRAFT_DB_PASSWORD')) !== null) {
    $dbConfig->password(is_string($password) ? $password : (string) $password);
}

if (($schema = $trim(App::env('CRAFT_DB_SCHEMA'))) !== null) {
    $dbConfig->schema($schema);
}

if (($tablePrefix = $trim(App::env('CRAFT_DB_TABLE_PREFIX'))) !== null) {
    $dbConfig->tablePrefix($tablePrefix);
}

$sslCa = $trim(App::env('CRAFT_DB_SSL_CA'));
$defaultSslCa = (defined('CRAFT_BASE_PATH') ? CRAFT_BASE_PATH : dirname(__DIR__)) . '/docker/certs/ca.pem';

foreach (array_unique(array_filter([$sslCa, $defaultSslCa])) as $candidate) {
    if (is_file($candidate)) {
        $sslCa = $candidate;
        break;
    }
}

if ($sslCa && is_file($sslCa)) {
    $attributes = [
        PDO::MYSQL_ATTR_SSL_CA => $sslCa,
    ];

    if (App::parseBooleanEnv('CRAFT_DB_SSL_VERIFY')) {
        $attributes[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
    }

    $dbConfig->pdoAttributes($attributes);
}

return $dbConfig;
