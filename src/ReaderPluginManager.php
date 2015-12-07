<?php

/**
 * @file
 * Contains \Drupal\migrate_plus\ReaderPluginManager.
 */

namespace Drupal\migrate_plus;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides a plugin manager for readers.
 *
 * @see \Drupal\migrate_plus\Annotation\Reader
 * @see \Drupal\migrate_plus\ReaderPluginBase
 * @see \Drupal\migrate_plus\ReaderPluginInterface
 * @see plugin_api
 */
class ReaderPluginManager extends DefaultPluginManager {

  /**
   * Constructs a new ReaderPluginManager.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations,
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/migrate_plus/reader', $namespaces, $module_handler, 'Drupal\migrate_plus\ReaderPluginInterface', 'Drupal\migrate_plus\Annotation\Reader');

    $this->alterInfo('reader_info');
    $this->setCacheBackend($cache_backend, 'migrate_plus_plugins');
  }

}
