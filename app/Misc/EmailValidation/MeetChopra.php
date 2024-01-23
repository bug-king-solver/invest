<?php

namespace App\Misc\EmailValidation;

use App\Models\BurnerEmail;
use GuzzleHttp\Client;

class MeetChopra extends AbstractEmailValidator implements EmailValidatorInterface
{
    const API_URL = 'https://verifier.meetchopra.com/verify/{email_address}';

    const API_TOKEN = 'ad74bdc3878685a063c79311e48aed0ab03a04a8308b4f1b3eb1253aedf21781';

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
        $url .= '?' . http_build_query([
                'token' => self::API_TOKEN
            ]);
        $response = $this->client->request('GET', $url);

        if ($response->getStatusCode() !== 200) {
            $this->log('status code ' . $response->getStatusCode());
            return true;
        }

        $json_response = json_decode($response->getBody()->getContents(), true);
        if ( ! array_key_exists('status', $json_response)) {
            $this->log('status key is missing');
            return true;
        }

        if ($json_response['status'] === true) {
            return true;
        } else {
            $this->log('invalid email: ' . $email . ' ' . $json_response['error']['message'] ?? '');
            return false;
        }
    }
}
