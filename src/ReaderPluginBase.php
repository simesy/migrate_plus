<?php

/**
 * @file
 * Contains \Drupal\migrate_plus\ReaderPluginBase.
 */

namespace Drupal\migrate_plus;

use Drupal\Core\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a base reader implementation.
 *
 * @see \Drupal\migrate_plus\Annotation\Reader
 * @see \Drupal\migrate_plus\ReaderPluginInterface
 * @see \Drupal\migrate_plus\ReaderPluginManager
 * @see plugin_api
 */
abstract class ReaderPluginBase extends PluginBase implements ReaderPluginInterface {

  /**
   * List of source urls.
   *
   * @var string[]
   */
  public $urls;

  /**
   * Index of the currently-open url.
   *
   * @var int
   */
  protected $activeUrl;

  /**
   * String indicating how to select an item's data from the source.
   *
   * @var string
   */
  public $itemSelector;

  /**
   * Current item when iterating.
   *
   * @var mixed
   */
  protected $currentItem = NULL;

  /**
   * Value of the ID for the current item when iterating.
   *
   * @var string
   */
  protected $currentId = NULL;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->urls = $configuration['urls'];
    $this->itemSelector = $configuration['item_selector'];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition);
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
    $this->currentItem = $this->currentId = NULL;
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
    if (is_null($this->currentItem)) {
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
    return $this->currentItem;
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
   *   Indicates if current item is valid
   */
  public function valid() {
    return $this->currentItem;
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    $count = 0;
    foreach ($this as $item) {
      $count++;
    }
    return $count;
  }

  /**
   * Return the selectors used to populate each configured field.
   *
   * @return string[]
   *   Array of selectors, keyed by field name.
   */
  protected function fieldSelectors() {
    $fields = [];
    foreach ($this->configuration['fields'] as $field_name => $field_info) {
      if (isset($field_info['selector'])) {
        $fields[$field_name] = $field_info['selector'];
      }
    }
    return $fields;
  }

}
