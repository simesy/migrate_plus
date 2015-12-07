<?php

/**
 * @file
 * Contains \Drupal\migrate_plus\Plugin\migrate_plus\reader\ReaderPlugin.
 */

namespace Drupal\migrate_plus\Plugin\migrate_plus\reader;

use Drupal\Component\Utility\Html;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Utility\Token;
use Drupal\migrate_plus\ReaderPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Displays some text as a tip.
 *
 * @Tip(
 *   id = "xml",
 *   title = @Translation("Text")
 * )
 */
class TipPluginText extends ReaderPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The body text which is used for render of this Text Tip.
   *
   * @var string
   */
  protected $body;

  /**
   * Token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * The forced position of where the tip will be located.
   *
   * @var string
   */
  protected $location;

  /**
   * Constructs a \Drupal\tour\Plugin\tour\tip\TipPluginText object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Token $token) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('token'));
  }

}
