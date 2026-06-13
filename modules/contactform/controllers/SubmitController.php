<?php

namespace modules\contactform\controllers;

use Craft;
use craft\elements\Entry;
use craft\web\Controller;
use modules\contactform\services\EmailService;
use yii\web\Response;

class SubmitController extends Controller
{
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_LIVE | self::ALLOW_ANONYMOUS_OFFLINE;

    public function actionSend(): ?Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $firstName = trim((string)$request->getBodyParam('firstName', ''));
        $lastName = trim((string)$request->getBodyParam('lastName', ''));
        $phoneNumber = trim((string)$request->getBodyParam('phoneNumber', ''));
        $emailAddress = trim((string)$request->getBodyParam('emailAddress', ''));
        $serviceInterestedIn = trim((string)$request->getBodyParam('serviceInterestedIn', ''));
        $yourMessage = trim((string)$request->getBodyParam('yourMessage', ''));

        if (
            $firstName === '' ||
            $lastName === '' ||
            $phoneNumber === '' ||
            $emailAddress === '' ||
            !filter_var($emailAddress, FILTER_VALIDATE_EMAIL)
        ) {
            Craft::$app->getSession()->setFlash('contactError', 'Please fill in all required fields with a valid email address.');
            return $this->redirectToPostedUrl();
        }

        $section = Craft::$app->entries->getSectionByHandle('submissions');
        $entryType = Craft::$app->entries->getEntryTypeByHandle('submission');

        if (!$section || !$entryType) {
            Craft::$app->getSession()->setFlash('contactError', 'The submissions section is not configured yet.');
            return $this->redirectToPostedUrl();
        }

        $entry = new Entry();
        $entry->sectionId = $section->id;
        $entry->typeId = $entryType->id;
        $entry->title = sprintf('%s %s — %s', $firstName, $lastName, date('Y-m-d H:i'));
        $entry->setFieldValues([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'phoneNumber' => $phoneNumber,
            'emailAddress' => $emailAddress,
            'serviceInterestedIn' => $serviceInterestedIn,
            'yourMessage' => $yourMessage,
        ]);

        if (!Craft::$app->getElements()->saveElement($entry)) {
            Craft::$app->getSession()->setFlash('contactError', 'We could not save your message. Please try again.');
            return $this->redirectToPostedUrl();
        }

        /** @var EmailService $emailService */
        $emailService = Craft::$app->getModule('contactform')->get('email');
        $emailSent = $emailService->sendContactNotification(
            $firstName,
            $lastName,
            $phoneNumber,
            $emailAddress,
            $serviceInterestedIn,
            $yourMessage,
        );

        if (!$emailSent) {
            Craft::$app->getSession()->setFlash(
                'contactError',
                'Your message was saved, but we could not send the notification email right now. Please contact us directly at filmsgoldenhaze@gmail.com.'
            );
            return $this->redirectToPostedUrl();
        }

        Craft::$app->getSession()->setFlash('contactSuccess', true);
        return $this->redirectToPostedUrl();
    }
}
