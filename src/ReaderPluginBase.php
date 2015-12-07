<?php

/**
 * @file
 * Contains \Drupal\migrate_plus\ReaderPluginBase.
 */

namespace Drupal\migrate_plus;

use Drupal\Core\Plugin\PluginBase;

/**
 * Defines a base tour item implementation.
 *
 * @see \Drupal\migrate_plus\Annotation\Reader
 * @see \Drupal\migrate_plus\ReaderPluginInterface
 * @see \Drupal\migrate_plus\ReaderPluginManager
 * @see plugin_api
 */
abstract class ReaderPluginBase extends PluginBase implements ReaderPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->get('id');
  }

  /**
   * {@inheritdoc}
   */
  public function get($key) {
    if (!empty($this->configuration[$key])) {
      return $this->configuration[$key];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function set($key, $value) {
    $this->configuration[$key] = $value;
  }
}
