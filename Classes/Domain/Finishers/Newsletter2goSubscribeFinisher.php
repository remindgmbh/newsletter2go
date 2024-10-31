<?php

declare(strict_types=1);

namespace Remind\Newsletter2go\Domain\Finishers;

use Exception;
use GuzzleHttp\RequestOptions;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;

class Newsletter2goSubscribeFinisher extends AbstractFinisher
{
    private const NL2GO_SUBMIT_URL = 'https://api.newsletter2go.com/forms/submit/';

    private RequestFactory $requestFactory;

    public function __construct()
    {
        $this->requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
    }

    protected function executeInternal(): ?string
    {
        $formId = $this->parseOption('formId');
        $formValues = $this->finisherContext->getFormValues();
        $formRuntime = $this->finisherContext->getFormRuntime();
        $formDefinition = $formRuntime->getFormDefinition();
        $successPageUid = (int) $this->parseOption('successPage');
        $errorPageUid = (int) $this->parseOption('errorPage');
        $url = self::NL2GO_SUBMIT_URL . $formId;

        $recipient = [];
        foreach ($formValues as $key => $value) {
            $element = $formDefinition->getElementByIdentifier($key);
            $properties = $element?->getProperties();
            $brevoAttribute = $properties['brevoAttribute'] ?? null;

            if ($brevoAttribute) {
                $recipient[$brevoAttribute] = $value;
            }
        }

        $additionalOptions = [
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::JSON => [
                'recipient' => $recipient,
            ],
            RequestOptions::TIMEOUT => 10,
        ];

        $response = null;
        try {
            $response = $this->requestFactory->request($url, 'POST', $additionalOptions);
        } catch (Exception $e) {
            // Nothing to do here, redirect to error page instead
        }

        $redirectPid = null;
        if ($response) {
            $statusCode = $response->getStatusCode();
            $redirectPid = $statusCode === 201 ? $successPageUid : $errorPageUid;
        }

        if (!$redirectPid) {
            $redirectPid = $errorPageUid;
        }

        $this->finisherContext->cancel();

        $redirectUrl = $this->getTypoScriptFrontendController()->cObj->createUrl([
            'parameter' => $redirectPid,
        ]);

        return json_encode(
            [
                'redirectUrl' => $redirectUrl,
                'statusCode' => 303,
            ],
            JSON_THROW_ON_ERROR
        );
    }
}
