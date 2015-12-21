<?php

/**
 * @file
 * Contains Drupal\migrate_plus\Plugin\migrate_plus\ClientInterface.
 *
 */

namespace Drupal\migrate_plus\Plugin\migrate_plus;

use Psr\Http\Message\ResponseInterface;

/**
 * Provides an interface for data retrieval clients.
 */
interface ClientInterface {

  /**
   * Set the client headers.
   *
   * @param $headers
   *   An array of the headers to set on the HTTP request.
   */
  public function setRequestHeaders(array $headers);

  /**
   * Get the currently set request headers.
   */
  public function getRequestHeaders();

  /**
   * Return content.
   *
   * @param $url
   *   URL to retrieve from.
   *
   * @return string
   *   Content at the given url.
   */
  public function getResponseContent($url);

  /**
   * Return Http Response object for a given url.
   *
   * @param $url
   *   URL to retrieve from.
   *
   * @return \Psr\Http\Message\ResponseInterface
   */
  public function getResponse($url);

}
