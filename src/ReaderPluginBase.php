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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->configuration['id'];
  }

}
