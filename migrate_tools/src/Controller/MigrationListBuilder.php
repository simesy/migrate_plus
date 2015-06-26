<?php
/**
 * @file
 * Contains Drupal\migrate_tools\Controller\MigrationListBuilder.
 */

namespace Drupal\migrate_tools\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a listing of migration entities in a given group.
 *
 * @package Drupal\migrate_tools\Controller
 *
 * @ingroup migrate_tools
 */
class MigrationListBuilder extends ConfigEntityListBuilder implements EntityHandlerInterface {

  /**
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * Constructs a new EntityListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, CurrentRouteMatch $current_route_match) {
    parent::__construct($entity_type, $storage);
    $this->currentRouteMatch = $current_route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('current_route_match')
    );
  }

  /**
   * Builds the header row for the entity listing.
   *
   * @return array
   *   A render array structure of header strings.
   *
   * @see Drupal\Core\Entity\EntityListController::render()
   */
  public function buildHeader() {
    $header['label'] = $this->t('Migration');
    $header['machine_name'] = $this->t('Machine Name');
    $header['total'] = $this->t('Total');
    $header['imported'] = $this->t('Imported');
    $header['unprocessed'] = $this->t('Unprocessed');
    return $header + parent::buildHeader();
  }

  /**
   * Builds a row for an entity in the entity listing.
   *
   * @param EntityInterface $migration
   *   The entity for which to build the row.
   *
   * @return array
   *   A render array of the table row for displaying the entity.
   *
   * @see Drupal\Core\Entity\EntityListController::render()
   */
  public function buildRow(MigrationInterface $migration) {
    $row['label'] = $this->getLabel($migration);
    $row['machine_name'] = $migration->id();

    // Derive the stats
    $source_plugin = $migration->getSourcePlugin();
    $row['total'] = $source_plugin->count();
    $map = $migration->getIdMap();
    $row['imported'] = $map->importedCount();
    // -1 indicates uncountable sources.
    if ($row['total'] == -1) {
      $row['total'] = $this->t('N/A');
      $row['unprocessed'] = $this->t('N/A');
    }
    else {
      $row['unprocessed'] = $row['total'] - $map->processedCount();
    }

    return $row + parent::buildRow($migration);
  }

  /**
   * Retrieve the migrations belonging to the appropriate group.
   *
   * @return array
   *   An array of entity IDs.
   */
  protected function getEntityIds() {
    $query = $this->getStorage()->getQuery();
    $keys = $this->entityType->getKeys();
    $migration_group = $this->currentRouteMatch->getParameter('migration_group');
    return $query
      ->condition('migration_group', $migration_group)
      ->sort($keys['id'])
      ->pager($this->limit)
      ->execute();
  }

}