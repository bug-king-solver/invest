<?php

namespace App\Misc\EmailValidation;

use App\Models\BurnerEmail;
use GuzzleHttp\Client;

class EmailHippo extends AbstractEmailValidator implements EmailValidatorInterface
{
    const API_URL = 'https://api.disposable-email-detector.com/api/dea/v1/check/{email_address}';

    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;

        parent::__construct();
    }

    /**
     * @param $email
     * @return bool
     */
    public function isValid($email)
    {
        $url = str_replace('{email_address}', $email, self::API_URL);
        try {
            $response = $this->client->request('GET', $url);

            if ($response->getStatusCode() !== 200) {
                $this->log('status code ' . $response->getStatusCode());
                return true;
            }

            $json_response = json_decode($response->getBody()->getContents(), true);
            if ( ! isset($json_response['result']['isDisposable'])) {
                $this->log('result key is missing');
                return true;
            }


            if ($json_response['result']['isDisposable'] === true) {
                $this->log('invalid email: ' . $email . ' is disposable');
                return false;
            } else {
                return true;
            }
        } catch (\Throwable $e) {
            $this->log($e->getMessage());
            return true;
        }
    }
}
