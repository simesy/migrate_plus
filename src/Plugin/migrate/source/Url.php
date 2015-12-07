<?php

/**
 * @file
 * Contains Drupal\migrate_plus\Plugin\migrate\source\Url.
 */

namespace Drupal\migrate_plus\Plugin\migrate\source;

use Drupal\migrate\Entity\MigrationInterface;

/**
 * Source plugin for retrieving data via URLs.
 */
abstract class Url extends SourcePluginExtension {

  /**
   * The source URLs to retrieve.
   *
   * @var array
   */
  protected $sourceUrls = [];

  /**
   * The iterator class used to traverse the XML.
   *
   * @var string
   */
  protected $iteratorClass = '';

  /**
   * The reader class used to traverse the XML.
   *
   * @var string
   */
  protected $readerClass = '';

  /**
   * The reader class serving as a cursor over the XML source.
   *
   * @return string
   *   XMLReader
   */
  public function getReaderClass() {
    return $this->readerClass;
  }

  /**
   * The query string used to recognize elements being iterated.
   *
   * This is an xpath-like expression.
   *
   * @var string
   */
  protected $itemSelector = '';

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);

    if (!is_array($configuration['urls'])) {
      $configuration['urls'] = [$configuration['urls']];
    }

    if (!empty($configuration['iterator_class'])) {
      $this->iteratorClass = $configuration['iterator_class'];
    }

    if (!empty($configuration['reader_class'])) {
      $this->readerClass = $configuration['reader_class'];
    }

    $this->itemSelector = $configuration['item_selector'];
    $this->sourceUrls = $configuration['urls'];
  }

  /**
   * Return a string representing the source URLs.
   *
   * @return string
   *   Comma-separated list of URLs being imported.
   */
  public function __toString() {
    // This could cause a problem when using a lot of urls, may need to hash.
    $urls = implode(', ', $this->sourceUrls);
    return $urls;
  }

  /**
   * Gets the source URLs where the data is located.
   *
   * @return array
   *   Array of URLs
   */
  public function sourceUrls() {
    return $this->sourceUrls;
  }

  /**
   * Gets the iterator class used to traverse the XML.
   *
   * @return string
   *   The name of the class to be used for low-level XML processing.
   */
  public function iteratorClass() {
    return $this->iteratorClass;
  }

  /**
   * Gets the xpath-like query controlling the iterated elements.
   *
   * Matching elements will be presented by the iterator. Most xpath syntax
   * is supported (it is evaluated by \SimpleXMLElement::xpath), however the
   * SimpleXMLElement object is rooted at the context node and has no ancestors
   * available.
   *
   * @return string
   *   An xpath-like expression.
   */
  public function itemSelector() {
    return $this->itemSelector;
  }

  /**
   * Creates and returns a filtered Iterator over the documents.
   *
   * @return \Iterator
   *   An iterator over the documents providing source rows that match the
   *   configured itemSelector.
   */
  protected function initializeIterator() {
    $iterator_class = $this->iteratorClass();
    $iterator = new $iterator_class($this);

    return $iterator;
  }

  /**
   * Return a count of all available source records.
   *
   * @return int
   *   The number of available source records.
   */
  public function computeCount() {
    $count = 0;
    foreach ($this->sourceUrls as $url) {
      $iterator = new $this->iteratorClass($this);
      $count += $iterator->count();
    }

    return $count;
  }

  /**
   * Return the selectors used to populate each configured field.
   *
   * @return string[]
   *   Array of selectors, keyed by field name.
   */
  public function fieldSelectors() {
    $fields = [];
    foreach ($this->configuration['fields'] as $field_name => $field_info) {
      if (isset($field_info['selector'])) {
        $fields[$field_name] = $field_info['selector'];
      }
    }
    return $fields;
  }

}
