<?php
/**
 * Yii Application Config
 *
 * Edit this file at your own risk!
 *
 * The array returned by this file will get merged with
 * vendor/craftcms/cms/src/config/app.php and app.[web|console].php, when
 * Craft's bootstrap script is defining the configuration for the entire
 * application.
 *
 * You can define custom modules and system components, and even override the
 * built-in system components.
 *
 * If you want to modify the application config for *only* web requests or
 * *only* console requests, create an app.web.php or app.console.php file in
 * your config/ folder, alongside this one.
 *
 * Read more about application configuration:
 * @link https://craftcms.com/docs/5.x/reference/config/app.html
 */

use craft\helpers\App;
use craft\mail\Mailer;
use craft\mail\Message;
use Symfony\Component\Mailer\Transport;

return [
    'id' => App::env('CRAFT_APP_ID') ?: 'CraftCMS',
    'modules' => [
        'contactform' => [
            'class' => \modules\contactform\Module::class,
        ],
    ],
    'bootstrap' => ['contactform'],
    'components' => [
        'mailer' => function() {
            $username = App::env('SMTP_USERNAME') ?: 'filmsgoldenhaze@gmail.com';
            $password = App::env('SMTP_PASSWORD');
            $fromEmail = App::env('SYSTEM_EMAIL_FROM') ?: $username;
            $fromName = App::env('SYSTEM_EMAIL_FROM_NAME') ?: 'Golden Haze Films';

            if ($password) {
                $dsn = sprintf(
                    'smtp://%s:%s@smtp.gmail.com:587',
                    rawurlencode($username),
                    rawurlencode($password),
                );

                return Craft::createObject([
                    'class' => Mailer::class,
                    'messageClass' => Message::class,
                    'from' => [$fromEmail => $fromName],
                    'transport' => Transport::fromDsn($dsn),
                ]);
            }

            return Craft::createObject(App::mailerConfig());
        },
    ],
];
