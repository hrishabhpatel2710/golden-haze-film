<?php

namespace modules\contactform\services;

use Craft;
use craft\helpers\App;
use craft\mail\Message;
use yii\base\Component;

class EmailService extends Component
{
    private const NOTIFY_EMAIL = 'filmsgoldenhaze@gmail.com';

    public function sendContactNotification(
        string $firstName,
        string $lastName,
        string $phoneNumber,
        string $emailAddress,
        string $serviceInterestedIn,
        string $yourMessage,
    ): bool {
        $settings = Craft::$app->getSystemSettings()->getSettings()->email;
        $fromEmail = App::parseEnv($settings->fromEmail) ?: self::NOTIFY_EMAIL;
        $fromName = App::parseEnv($settings->fromName) ?: 'Golden Haze Films';

        $message = new Message();
        $message->setFrom([$fromEmail => $fromName]);
        $message->setTo(self::NOTIFY_EMAIL);
        $message->setReplyTo([$emailAddress => trim($firstName . ' ' . $lastName)]);
        $message->setSubject(sprintf('New contact form submission — %s %s', $firstName, $lastName));
        $message->setHtmlBody($this->buildHtmlBody(
            $firstName,
            $lastName,
            $phoneNumber,
            $emailAddress,
            $serviceInterestedIn,
            $yourMessage,
        ));
        $message->setTextBody($this->buildTextBody(
            $firstName,
            $lastName,
            $phoneNumber,
            $emailAddress,
            $serviceInterestedIn,
            $yourMessage,
        ));

        $sent = Craft::$app->getMailer()->send($message);

        if (!$sent) {
            Craft::error('Contact form notification email was not sent to ' . self::NOTIFY_EMAIL, __METHOD__);
        }

        return $sent;
    }

    private function buildHtmlBody(
        string $firstName,
        string $lastName,
        string $phoneNumber,
        string $emailAddress,
        string $serviceInterestedIn,
        string $yourMessage,
    ): string {
        $rows = [
            'First Name' => htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8'),
            'Last Name' => htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8'),
            'Phone Number' => htmlspecialchars($phoneNumber, ENT_QUOTES, 'UTF-8'),
            'Email Address' => htmlspecialchars($emailAddress, ENT_QUOTES, 'UTF-8'),
            'Service Interested In' => htmlspecialchars($serviceInterestedIn !== '' ? $serviceInterestedIn : '—', ENT_QUOTES, 'UTF-8'),
            'Message' => $yourMessage !== '' ? nl2br(htmlspecialchars($yourMessage, ENT_QUOTES, 'UTF-8')) : '—',
        ];

        $html = '<h2>New contact form submission</h2><table cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;">';

        foreach ($rows as $label => $value) {
            $html .= sprintf(
                '<tr><td><strong>%s</strong></td><td>%s</td></tr>',
                htmlspecialchars($label, ENT_QUOTES, 'UTF-8'),
                $value,
            );
        }

        return $html . '</table>';
    }

    private function buildTextBody(
        string $firstName,
        string $lastName,
        string $phoneNumber,
        string $emailAddress,
        string $serviceInterestedIn,
        string $yourMessage,
    ): string {
        return implode("\n", [
            'New contact form submission',
            '',
            'First Name: ' . $firstName,
            'Last Name: ' . $lastName,
            'Phone Number: ' . $phoneNumber,
            'Email Address: ' . $emailAddress,
            'Service Interested In: ' . ($serviceInterestedIn !== '' ? $serviceInterestedIn : '—'),
            'Message:',
            $yourMessage !== '' ? $yourMessage : '—',
        ]);
    }
}
