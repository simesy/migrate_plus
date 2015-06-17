<?php

/**
 * @file
 * Command-line tools to aid performing and developing migrations.
 */

use Drupal\Component\Utility\Unicode;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_plus\DrushLogMigrateMessage;
use Drupal\migrate_plus\MigrateManifest;

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
    'drupal dependencies' => array('migrate_plus'),
    'aliases' => array('ms'),
  );

  $items['migrate-import'] = array(
    'description' => 'Perform one or more migration processes.',
    'options' => array(
      'all' => 'Process all migrations.',
      'group' => 'Name of the migration group to import',
    ),
    'arguments' => array(
      'migration' => 'Name of migration(s) to import. Delimit multiple using commas.',
    ),
    'examples' => array(
      'migrate-import --all' => 'Perform all migrations',
      'migrate-import --group=beer' => 'Import all migrations in the beer group',
      'migrate-import BeerTerm,BeerNode' => 'Import new terms and nodes',
    ),
    'drupal dependencies' => array('migrate_plus'),
    'aliases' => array('mi'),
  );

  $items['migrate-manifest'] = array(
    'description' => 'Execute the migrations as specified in a manifest file.',
    'arguments' => array(
      'manifest' => 'The path to the manifest file',
    ),
    'required-arguments' => 1,
    'options' => array(
      'legacy-db-url' => array(
        'description' => 'A Drupal 6 style database URL.',
        'example-value' => 'mysql://root:pass@127.0.0.1/db',
        'required' => TRUE,
      ),
    ),
    'drupal dependencies' => array('migrate_plus'),
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
      try {
        $map = $migration->getIdMap();
        $imported = $map->importedCount();
        $source_plugin = $migration->getSourcePlugin();
      }
      catch (Exception $e) {
        continue;
      }
      try {
        if (!method_exists($source_plugin, 'count')) {
          $source_rows = dt('N/A');
          $unprocessed = dt('N/A');
        }
        else {
          $source_rows = $source_plugin->count();
          $unprocessed = $source_rows - $map->processedCount();
        }
      }
      catch (Exception $e) {
        drush_log(dt('Could not retrieve source count from !migration',
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
 * @param string $migration_names
 */
function drush_migrate_plus_migrate_import($migration_names = '') {
  $group_name = drush_get_option('group');
  $all = drush_get_option('all');
  if (!$all && !$group_name && !$migration_names) {
    drush_set_error('MIGRATE_ERROR', dt('You must specify --all, --group, or one or more migration names separated by commas'));
    return;
  }

  $log = new DrushLogMigrateMessage();

  $migrations = drush_migrate_plus_migration_list($group_name, $migration_names);

  // Take it one group at a time, importing the migrations within each group.
  foreach ($migrations as $group_id => $migration_list) {
    foreach ($migration_list as $migration_id => $migration) {
      $executable = new MigrateExecutable($migration, $log);
      // drush_op() provides --simulate support.
      drush_op(array($executable, 'import'));
    }
  }
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
    $query->condition('migration_group', $group_id);
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
      $group_id = $migration->get('migration_group');
      if (!empty($group_id)) {
        $return[$group_id][$migration_id] = $migration;
      }
      else {
        $return['default'][$migration_id] = $migration;
      }
    }
  }
  return $return;
}


/**
 * Import from a manifest file.
 *
 * This command allows you to specify a list of migrations and their config in
 * a YAML file. An example of a simple migration may look like this:
 *
 * @code
 *  - d6_action_settings
 *  - d6_aggregator_feed
 * @endcode
 *
 * You can also provide configuration to a migration for both source and the
 * destination. An example as such:
 *
 * @code
 *  - d6_file:
 *    source:
 *      conf_path: sites/assets
 *    destination:
 *      source_base_path: destination/base/path
 *      destination_path_property: uri
 *  - d6_action_settings
 * @endcode
 *
 * @param string $manifest
 *   The path to the manifest file.
 */
function drush_migrate_plus_migrate_manifest($manifest) {
  try {
    $manifest_runner = new MigrateManifest($manifest);
    return $manifest_runner->import();
  }
  catch (\Exception $e) {
    drush_set_error('MIGRATE_ERROR', $e->getMessage());
  }

  drush_invoke_process('@self', 'cache-rebuild', array(), array(), FALSE);
}
