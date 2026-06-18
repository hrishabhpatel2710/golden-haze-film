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
