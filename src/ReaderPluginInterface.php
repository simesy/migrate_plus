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

}
