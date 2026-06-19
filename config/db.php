<?php
/**
 * Database Connection Settings
 *
 * @link https://craftcms.com/docs/5.x/reference/config/db.html
 */

use craft\config\DbConfig;
use craft\helpers\App;

$dbConfig = DbConfig::create();

$sslCa = App::env('CRAFT_DB_SSL_CA');
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
