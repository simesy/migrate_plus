<?php

/**
 * @file
 * Contains \Drupal\migrate_plus\Plugin\migrate\source\UrlIterator.
 */

namespace Drupal\migrate_plus\Plugin\migrate\source;

/**
 * Uses one or more XmlReaders to produce a single iterator.
 *
 * This class is independent from XmlReader primarily to support multiple
 * input XML documents in a single migration.
 */
class UrlIterator implements \Iterator, \Countable {

  /**
   * The XmlReader currently in use.
   *
   * @var \Drupal\migrate_source_xml\Plugin\migrate\source\XmlReader
   */
  protected $reader = NULL;

  /**
   * Reference to the Xml source plugin over which we are iterating.
   *
   * @var \Drupal\migrate_source_xml\Plugin\migrate\source\Xml
   */
  protected $urlSource;

  /**
   * Copy of the source URLs listed in the urlSource.
   *
   * @var array
   */
  protected $sourceUrls;

  /**
   * Holds our current position within the $sourceUrls array.
   *
   * @var int
   */
  protected $activeUrl = NULL;

  /**
   * At all times, contains the value that should be returned by current().
   *
   * @var \SimpleXmlElement
   */
  protected $currentElement = NULL;

  /**
   * At all times, contains the key that should be returned by key().
   *
   * @var string
   */
  protected $currentKey = NULL;

  /**
   * Constructs a new UrlIterator.
   */
  public function __construct(Url $url_source) {
    $this->urlSource = $url_source;
    $this->sourceUrls = $this->urlSource->sourceUrls();
  }

  protected function createReader() {
    $reader_class = $this->urlSource->getReaderClass();
    return new $reader_class(
            $this->sourceUrls[$this->activeUrl],
                  $this->urlSource,
                  $this->urlSource->itemSelector());
  }

  /**
   * {@inheritdoc}
   */
  public function next() {
    $this->currentElement = NULL;
    $this->currentKey = NULL;

    if (isset($this->reader)) {
      // Attempt to load the next row.
      $this->reader->next();
    }

    // Test the reader for a valid row.
    if (isset($this->reader) && $this->reader->valid()) {
      $this->currentElement = $this->reader->current();
      $this->currentKey = $this->reader->key();
    }
    else {
      // The current source is at the end, try to load the next source.
      if ($this->nextSource()) {
        if ($this->reader->valid()) {
          $this->currentElement = $this->reader->current();
          $this->currentKey = $this->reader->key();
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function valid() {
    return $this->currentElement !== NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function rewind() {
    $this->reader = NULL;
    $this->activeUrl = NULL;
    $this->next();
  }

  /**
   * Generates a migration source row (associative array) at this position.
   *
   * @return array
   *   The migration source row.
   */
  public function current() {
    if ($this->valid()) {
      return $this->currentElement;
    }
    else {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function key() {
    return $this->currentKey;
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    $count = 0;
    foreach ($this as $element) {
      $count++;
    }
    return $count;
  }

  /**
   * Advances the reader to the next source from sourceUrls.
   *
   * @return bool
   *   TRUE if a valid source was loaded
   */
  public function nextSource() {
    // Return value.
    $status = FALSE;

    while ($this->activeUrl === NULL || (count($this->sourceUrls) - 1) > $this->activeUrl) {
      if (is_null($this->activeUrl)) {
        $this->activeUrl = 0;
      }
      else {
        // Increment the activeUrl so we try to load the next source.
        $this->activeUrl = $this->activeUrl + 1;
        if ($this->activeUrl >= count($this->sourceUrls)) {
          return FALSE;
          // Avoid below invalid index into $this->sourceUrls
        }
      }

      $this->reader = $this->createReader();
      $this->reader->rewind();

      if ($this->reader->valid()) {
        // We have a valid source.
        $status = TRUE;
        break;
      }
    }

    return $status;
  }

}
