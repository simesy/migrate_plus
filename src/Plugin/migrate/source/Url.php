<?php

/**
 * @file
 * Contains Drupal\migrate_plus\Plugin\migrate\source\Url.
 */

namespace Drupal\migrate_plus\Plugin\migrate\source;

use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate_plus\ReaderPluginInterface;

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
   * The reader plugin.
   *
   * @var \Drupal\migrate_plus\ReaderPluginInterface
   */
  protected $readerPlugin;

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
   * {@inheritdoc}
   */
  public function getReaderPlugin() {
    if (!isset($this->readerPlugin)) {
      $this->readerPlugin = \Drupal::service('plugin.manager.migrate_plus.reader')->createInstance($this->configuration['reader_plugin'], $this->configuration, $this);
    }
    return $this->readerPlugin;
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
    return $this->createReader();
  }

  protected function createReader() {
    $reader_class = $this->getReaderClass();
    return new $reader_class(
            $this->sourceUrls,
                  $this,
                  $this->itemSelector());
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
