<?php

namespace Core;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\RequestException;
use Composer\CaBundle\CaBundle;

use Core\ErrorResponse as Error;

/**
 * A service class to handle Payroll API requests
 * 
 * @category Core
 */
class PayrollApiService{

  /**
   * A Guzzle Http client object.
   * @var Client
   */
  private Client $client;

  public function __construct()
  {
      $this->client = new Client([
          'base_uri' => \Core\Config::read('staffology.apiurl'),
          RequestOptions::TIMEOUT  => 10.0,
          RequestOptions::VERIFY => CaBundle::getSystemCaRootBundlePath()
      ]);
  }

  /**
   * Generic GET request
   */
  public function get(string $endpoint, array $params = []): array
  {
      try {
          $response = $this->client->request('GET', $endpoint, [
              'headers' => $this->getHeaders(),
              'query'   => $params,
          ]);

          return $this->parseResponse($response->getBody()->getContents());
      } catch (RequestException $e) {
          Error::response("Unable to GET data from Payroll api.", $e);
      }
  }

  /**
   * Generic POST request
   */
  public function post(string $endpoint, array $data = []): array
  {
      try {
          $response = $this->client->request('POST', $endpoint, [
              'headers' => $this->getHeaders(),
              'json'    => $data,
          ]);

          return $this->parseResponse($response->getBody()->getContents());
      } catch (RequestException $e) {
          Error::response("Unable to POST to Payroll api.", $e);
      }
  }

  private function getHeaders(): array
  {
      return [
          'Accept'        => 'application/json',
          'Authorization' => 'Basic ' . getenv(\Core\Config::read('staffology.apikey')),
      ];
  }

  private function parseResponse(string $body): array
  {
      return json_decode($body, true) ?? [];
  }

}