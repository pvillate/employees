<?php

namespace Drupal\employees;

use GuzzleHttp\Client;
use Drupal\Component\Serialization;

class EmployeesManager {
    
    protected $httpClient;
    protected $serialize;

    public function __construct(Client $http_client, Serialization $serialize) {
        $this->httpClient = $http_client;
        $this->serialize = $serialize;
    }

    public function wsClient(string $url) {
        $employees = NULL;

        try {
            $response = $this->httpClient->get($url);

            if ($response->getStatusCode() === 200) {
                $employees = $this->serialize->Json::decode($response);
            } else {
                throw New \RuntimeException(
                    'Error webservice: ' . $response->getStatusCode()
                );
            }
        } catch (\RuntimeException $e) {
            watchdog_exception(__METHOD__, $e);
        }

        return $employees;
    }
}