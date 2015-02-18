<?php

/**
 * @file
 * Command-line tools to aid performing and developing migrations.
 */

use Drupal\Component\Utility\Unicode;
use Drupal\migrate\Entity\MigrationInterface;

/**
 * Implements hook_drush_command().
 */
function migrate_plus_drush_command() {
  $items['migrate-status'] = array(
    'description' => 'List all migrations with current status.',
    'options' => array(
      'group' => 'Name of the migration group to list',
      'names-only' => 'Only return names, not all the details (faster)',
    ),
    'arguments' => array(
      'migration' => 'Restrict to a comma-separated list of migrations. Optional',
    ),
    'examples' => array(
      'migrate-status' => 'Retrieve status for all migrations',
      'migrate-status --group=beer' => 'Retrieve status for all migrations in a given group',
      'migrate-status BeerTerm,BeerNode' => 'Retrieve status for specific migrations',
    ),
    'drupal dependencies' => array('migrate'),
    'aliases' => array('ms'),
  );

  return $items;
}

/**
 * @param string $migration_names
 */
function drush_migrate_plus_migrate_status($migration_names = '') {
  $group_name = drush_get_option('group');
  $names_only = drush_get_option('names-only');

  $migrations = drush_migrate_plus_migration_list($group_name, $migration_names);

  $table = array();
  // Take it one group at a time, listing the migrations within each group.
  foreach ($migrations as $group_id => $migration_list) {
    if ($names_only) {
      $table[] = array(
        dt('Group: !name', array('!name' => $group_id))
      );
    }
    else {
      $table[] = array(
        dt('Group: !name', array('!name' => $group_id)),
        dt('Total'),
        dt('Imported'),
        dt('Unprocessed'),
      );
    }
    foreach ($migration_list as $migration_id => $migration) {
      $map = $migration->getIdMap();
      $imported = $map->importedCount();
      $source_plugin = $migration->getSourcePlugin();
      // We can't get counts from an EmptySource plugin, or one without a query.
      // @todo: Is there a more general approach? Are there other edge cases?
      try {
        if (is_a($source_plugin, 'Drupal\migrate\Plugin\migrate\source\EmptySource')) {
          $source_rows = dt('N/A');
          $unprocessed = dt('N/A');
        }
        elseif (!$source_plugin->query()) {
          $source_rows = dt('N/A');
          $unprocessed = dt('N/A');
        }
        else {
          $source_rows = $source_plugin->count();
          $unprocessed = $source_rows - $map->processedCount();
        }
      }
      catch (Exception $e) {
        drush_print(dt('Could not retrieve source count from !migration',
                      array('!migration' => $migration_id)));
        $source_rows = dt('N/A');
        $unprocessed = dt('N/A');
      }

      if ($names_only) {
        $table[] = array($migration_id);
      }
      else {
        $table[] = array($migration_id, $source_rows, $imported, $unprocessed);
      }
    }
  }
  drush_print_table($table);
}

/**
 * Retrieve a list of active migrations.
 *
 * @param string $group_id
 *  Group machine name - if present, return only migrations in this group.
 * @param string $migration_ids
 *  Comma-separated list of migrations - if present, return only these migrations.
 *
 * @return MigrationInterface[][]
 *   An array keyed by migration group, each value containing an array of migrations.
 */
function drush_migrate_plus_migration_list($group_id = '', $migration_ids = '') {
  $query = \Drupal::entityQuery('migration');
  if ($group_id) {
    $query->condition('migration_groups.*', $group_id);
  }
  $names = $query->execute();

  // Order the migrations according to their dependencies.
  /** @var MigrationInterface[] $migrations */
  $migrations = \Drupal::entityManager()
     ->getStorage('migration')
     ->loadMultiple($names);

  if (!empty($migration_ids)) {
    $migration_ids = explode(',', Unicode::strtolower($migration_ids));
  }
  else {
    $migration_ids = array();
  }

  $return = array();
  foreach ($migrations as $migration_id => $migration) {
    if (empty($migration_ids) || in_array(Unicode::strtolower($migration_id), $migration_ids)) {
      $group_id = reset($migration->migration_groups);
      $return[$group_id][$migration_id] = $migration;
    }
  }
  return $return;
}
