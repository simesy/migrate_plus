<?php

/**
 * @file
 * Contains Drupal\migrate_plus\Plugin\migrate\source\UrlReader.
 */

namespace Drupal\migrate_plus\Plugin\migrate\source;

abstract class UrlReader implements \Iterator, \Countable {

  /**
   * URL of the source file.
   *
   * @var string[]
   */
  public $urls;

  /**
   * @var int
   */
  protected $activeUrl;

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
   * @todo: Try to remove the direct refence to the source plugin.
   *
   * @param string[] $urls
   *   URLs of the files to be parsed.
   * @param \Drupal\migrate_plus\Plugin\migrate\source\Url $source
   *   The source plugin.
   * @param string $item_selector
   *   Query string in a restricted xpath format, for selecting elements to be.
   */
  public function __construct($urls, Url $source, $item_selector) {
    $this->urls = $urls;
    $this->urlSource = $source;
    $this->itemSelector = $item_selector;
  }

  /**
   * Implementation of Iterator::rewind().
   */
  public function rewind() {
    $this->activeUrl = NULL;
    $this->next();
  }

  /**
   * Implementation of Iterator::next().
   */
  public function next() {
    $this->currentElement = $this->currentId = NULL;
    if (is_null($this->activeUrl)) {
      if (!$this->nextSource()) {
        // No data to import.
        return;
      }
    }
    // At this point, we have a valid open source url, try to fetch a row from
    // it.
    $this->fetchNextRow();
    // If there was no valid row there, try the next url (if any).
    if (is_null($this->currentElement)) {
      if ($this->nextSource()) {
        $this->fetchNextRow();
      }
    }
  }

  abstract protected function openSourceUrl($url);

  abstract protected function fetchNextRow();

  /**
   * Advances the reader to the next source url.
   *
   * @return bool
   *   TRUE if a valid source was loaded
   */
  protected function nextSource() {
    $status = FALSE;

    while ($this->activeUrl === NULL || (count($this->urls) - 1) > $this->activeUrl) {
      if (is_null($this->activeUrl)) {
        $this->activeUrl = 0;
      }
      else {
        // Increment the activeUrl so we try to load the next source.
        $this->activeUrl = $this->activeUrl + 1;
        if ($this->activeUrl >= count($this->urls)) {
          return FALSE;
        }
      }

      if ($this->openSourceUrl($this->urls[$this->activeUrl])) {
        // We have a valid source.
        $status = TRUE;
        break;
      }
    }

    return $status;
  }

  /**
   * Implementation of Iterator::current().
   *
   * @return mixed
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

}
