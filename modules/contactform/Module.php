<?php

namespace modules\contactform;

use Craft;

class Module extends \yii\base\Module
{
    public function init(): void
    {
        Craft::setAlias('@modules/contactform', __DIR__);

        if (Craft::$app->request->isConsoleRequest) {
            $this->controllerNamespace = 'modules\\contactform\\console\\controllers';
        } else {
            $this->controllerNamespace = 'modules\\contactform\\controllers';
        }

        parent::init();

        $this->setComponents([
            'email' => \modules\contactform\services\EmailService::class,
        ]);
    }
}
