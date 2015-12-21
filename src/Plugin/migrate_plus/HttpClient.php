<?php

/**
 * @file
 * Contains Drupal\migrate_plus\Plugin\migrate_plus\HttpClient.
 *
 * Uses the Guzzle HTTP Client library, which is wrapped by \Drupal::httpClient.
 *
 * @see http://docs.guzzlephp.org/
 */

namespace Drupal\migrate_plus\Plugin\migrate_plus;

use Drupal\migrate\MigrateException;
use GuzzleHttp\Exception\RequestException;

/**
 * Object to retrieve and iterate over data retrieved from an HTTP endpoint.
 */
class HttpClient implements ClientInterface {

  /**
   * The HTTP Client
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * The request headers.
   *
   * @var array
   */
  protected $headers = [];

  public function __construct() {
    $this->httpClient = \Drupal::httpClient();
  }

  /**
   * {@inheritdoc}
   */
  public function setRequestHeaders(array $headers) {
    $this->headers = $headers;
  }

  /**
   * {@inheritdoc}
   */
  public function getRequestHeaders() {
    return !empty($this->headers) ? $this->headers : array();
  }

  /**
   * {@inheritdoc}
   */
  public function getResponse($url) {
    try {
      $response = $this->httpClient->get($url, array(
        'headers' => $this->getRequestHeaders(),
        // Uncomment the following to debug the request.
        //'debug' => true,
      ));
      if (empty($response)) {
        throw new MigrateException('No response at ' . $url . '.');
      }
    }
    catch (RequestException $e) {
      throw new MigrateException('Error message: ' . $e->getMessage() . ' at ' . $url .'.');
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function getResponseContent($url) {
    $response = $this->getResponse($url);
    return $response->getBody();
  }

}
