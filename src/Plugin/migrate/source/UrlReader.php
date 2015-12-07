<?php

/**
 * @file
 * Contains Drupal\migrate_plus\Plugin\migrate\source\UrlReader.
 */

namespace Drupal\migrate_plus\Plugin\migrate\source;

abstract class UrlReader implements \Iterator {

  /**
   * URL of the source file.
   *
   * @var string
   */
  public $url;

  /**
   * Query string used to retrieve the elements from the XML file.
   *
   * @var string
   */
  public $itemSelector;

  /**
   * Current element object when iterating.
   *
   * @var mixed
   */
  protected $currentElement = NULL;

  /**
   * Value of the ID for the current element when iterating.
   *
   * @var string
   */
  protected $currentId = NULL;

  /**
   * Reference to the Url source plugin we are serving as iterator for.
   *
   * @var \Drupal\migrate_plus\Plugin\migrate\source\Url
   */
  protected $urlSource;

  /**
   * Prepares our extensions to the XMLReader object.
   *
   * @param string $xml_url
   *   URL of the XML file to be parsed.
   * @param \Drupal\migrate_source_xml\Plugin\migrate\source\Xml $xml_source
   *   The xml source plugin.
   * @param string $element_query
   *   Query string in a restricted xpath format, for selecting elements to be.
   */
  public function __construct($url, Url $source, $item_selector) {
    $this->url = $url;
    $this->itemSelector = $item_selector;
    $this->urlSource = $source;
  }

  /**
   * Implementation of Iterator::current().
   *
   * @return \SimpleXMLElement|null
   *   Current item
   */
  public function current() {
    return $this->currentElement;
  }

  /**
   * Implementation of Iterator::key().
   *
   * @return null|string
   *   Current key
   */
  public function key() {
    return $this->currentId;
  }

  /**
   * Implementation of Iterator::valid().
   *
   * @return bool
   *   Indicates if current element is valid
   */
  public function valid() {
    return $this->currentElement;
  }

}
