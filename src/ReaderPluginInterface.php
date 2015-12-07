<?php

/**
 * @file
 * Contains \Drupal\migrate_plus\ReaderPluginInterface.
 */

namespace Drupal\migrate_plus;

/**
 * Defines an interface for readers.
 *
 * @see \Drupal\migrate_plus\Annotation\Reader
 * @see \Drupal\migrate_plus\ReaderPluginBase
 * @see \Drupal\migrate_plus\ReaderPluginManager
 * @see plugin_api
 */
interface ReaderPluginInterface {

  /**
   * Returns id of the reader.
   *
   * @return string
   *   The id of the reader.
   */
  public function id();

  /**
   * Used for returning values by key.
   *
   * @var string
   *   Key of the value.
   *
   * @return string
   *   Value of the key.
   */
  public function get($key);

  /**
   * Used for returning values by key.
   *
   * @var string
   *   Key of the value.
   *
   * @var string
   *   Value of the key.
   */
  public function set($key, $value);

}
