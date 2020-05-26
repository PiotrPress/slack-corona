<?php

namespace PiotrPress\Slack\Corona;

use Symfony\Component\HttpClient\HttpClient;

class Api {
    const URL = 'https://api.covid19api.com/';

    protected $client = null;

    public function __construct() {
        $this->client = HttpClient::create();
    }

    public function summary() {
        $response = $this->client->request( 'GET', self::URL . 'summary' );
        return 200 === $response->getStatusCode() ? $response->toArray( false ) : false;
    }
}