<?php

/**
 * @file
 * Contains \Drupal\migrate_plus\Annotation\Reader.
 */

namespace Drupal\migrate_plus\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a migration reader annotation object.
 *
 * Plugin Namespace: Plugin\migrate_plus\tip
 *
 * For a working example, see \Drupal\migrate_plus\Plugin\migrate_plus\reader\urlReader
 *
 * @see \Drupal\migrate_plus\ReaderPluginBase
 * @see \Drupal\migrate_plus\ReaderPluginInterface
 * @see \Drupal\migrate_plus\ReaderPluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class Reader extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The title of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $title;

}
