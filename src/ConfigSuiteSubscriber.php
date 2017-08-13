<?php

namespace Drupal\config_suite;

use Drupal\system\SystemConfigSubscriber;
use Drupal\Core\Config\ConfigImporterEvent;


/**
 * System Config subscriber.
 */
class ConfigSuiteSubscriber extends SystemConfigSubscriber {

  /**
   * Ignores the check that the configuration synchronization is from the same site.
   *
   * This event listener blocks the check that the system.site:uuid's in the source and
   * target match to prevent the error "Site UUID in source storage does not match the target storage."
   *
   * @param ConfigImporterEvent $event
   *   The config import event.
   */
  public function onConfigImporterValidateSiteUUID(ConfigImporterEvent $event) {
    $event->stopPropagation();

    return true;
  }

  public function onConfigSave(ConfigCrudEvent $event) {
    parent::onConfigSave($event);

    /*
     *   $storage_filters = drush_config_get_storage_filters();
  if (count(glob($destination_dir . '/*')) > 0) {
    // Retrieve a list of differences between the active and target configuration (if any).
    if ($destination == CONFIG_SYNC_DIRECTORY) {
      $target_storage = \Drupal::service('config.storage.sync');
    }
    else {
      $target_storage = new FileStorage($destination_dir);
    }
    $active_storage = \Drupal::service('config.storage');
    $comparison_source = $active_storage;

    // If the output is being filtered, then write a temporary copy before doing
    // any comparison.
    if (!empty($storage_filters)) {
      $tmpdir = drush_tempdir();
      drush_copy_dir($destination_dir, $tmpdir, FILE_EXISTS_OVERWRITE);
      $comparison_source = new FileStorage($tmpdir);
      $comparison_source_filtered = new StorageWrapper($comparison_source, $storage_filters);
      foreach ($active_storage->listAll() as $name) {
        // Copy active storage to our temporary active store.
        if ($existing = $active_storage->read($name)) {
          $comparison_source_filtered->write($name, $existing);
        }
      }
    }

    $config_comparer = new StorageComparer($comparison_source, $target_storage, \Drupal::service('config.manager'));
    if (!$config_comparer->createChangelist()->hasChanges()) {
      return drush_log(dt('The active configuration is identical to the configuration in the export directory (!target).', array('!target' => $destination_dir)), LogLevel::OK);
    }

    drush_print("Differences of the active config to the export directory:\n");
    $change_list = array();
    foreach ($config_comparer->getAllCollectionNames() as $collection) {
      $change_list[$collection] = $config_comparer->getChangelist(NULL, $collection);
    }
    // Print a table with changes in color, then re-generate again without
    // color to place in the commit comment.
    _drush_print_config_changes_table($change_list);
    $tbl = _drush_format_config_changes_table($change_list);
    $output = $tbl->getTable();
    if (!stristr(PHP_OS, 'WIN')) {
      $output = str_replace("\r\n", PHP_EOL, $output);
    }
    $comment .= "\n\n$output";

    if (!$commit && !drush_confirm(dt('The .yml files in your export directory (!target) will be deleted and replaced with the active config.', array('!target' => $destination_dir)))) {
      return drush_user_abort();
    }
    // Only delete .yml files, and not .htaccess or .git.
    $target_storage->deleteAll();
  }

  // Write all .yml files.
$source_storage = \Drupal::service('config.storage');
if ($destination == CONFIG_SYNC_DIRECTORY) {
$destination_storage = \Drupal::service('config.storage.sync');
}
else {
  $destination_storage = new FileStorage($destination_dir);
}
// If there are any filters, then attach them to the destination storage
if (!empty($storage_filters)) {
  $destination_storage = new StorageWrapper($destination_storage, $storage_filters);
}
foreach ($source_storage->listAll() as $name) {
  $destination_storage->write($name, $source_storage->read($name));
}

// Export configuration collections.
foreach (\Drupal::service('config.storage')->getAllCollectionNames() as $collection) {
  $source_storage = $source_storage->createCollection($collection);
  $destination_storage = $destination_storage->createCollection($collection);
  foreach ($source_storage->listAll() as $name) {
    $destination_storage->write($name, $source_storage->read($name));
  }
}

drush_log(dt('Configuration successfully exported to !target.', array('!target' => $destination_dir)), LogLevel::SUCCESS);
drush_backend_set_result($destination_dir);

// Commit and push, or add exported configuration if requested.
$remote = drush_get_option('push', FALSE);
if ($commit || $remote) {
  // There must be changed files at the destination dir; if there are not, then
  // we will skip the commit-and-push step
  $result = drush_shell_cd_and_exec($destination_dir, 'git status --porcelain .');
  if (!$result) {
    return drush_set_error('DRUSH_CONFIG_EXPORT_FAILURE', dt("`git status` failed."));
  }
  $uncommitted_changes = drush_shell_exec_output();
  if (!empty($uncommitted_changes)) {
    $result = drush_shell_cd_and_exec($destination_dir, 'git add -A .');
    if (!$result) {
      return drush_set_error('DRUSH_CONFIG_EXPORT_FAILURE', dt("`git add -A` failed."));
    }
    $comment_file = drush_save_data_to_temp_file($comment);
    $result = drush_shell_cd_and_exec($destination_dir, 'git commit --file=%s', $comment_file);
    if (!$result) {
      return drush_set_error('DRUSH_CONFIG_EXPORT_FAILURE', dt("`git commit` failed.  Output:\n\n!output", array('!output' => implode("\n", drush_shell_exec_output()))));
    }
    if ($remote) {
      // Remote might be FALSE, if --push was not specified, or
      // it might be TRUE if --push was not given a value.
      if (!is_string($remote)) {
        $remote = 'origin';
      }
      $result = drush_shell_cd_and_exec($destination_dir, 'git push --set-upstream %s %s', $remote, $branch);
      if (!$result) {
        return drush_set_error('DRUSH_CONFIG_EXPORT_FAILURE', dt("`git push` failed."));
      }
    }
  }
}
elseif (drush_get_option('add')) {
  drush_shell_exec_interactive('git add -p %s', $destination_dir);
}

$values = array(
  'destination' => $destination_dir,
);
return $values;
     */
  }

  public function onConfigRead() {
    /*
     *   $config_importer = new ConfigImporter(
    $storage_comparer,
    \Drupal::service('event_dispatcher'),
    \Drupal::service('config.manager'),
    \Drupal::lock(),
    \Drupal::service('config.typed'),
    \Drupal::moduleHandler(),
    \Drupal::service('module_installer'),
    \Drupal::service('theme_handler'),
    \Drupal::service('string_translation')
  );
  if ($config_importer->alreadyImporting()) {
    drush_log('Another request may be synchronizing configuration already.', LogLevel::WARNING);
  }
  else{
    try {
      // This is the contents of \Drupal\Core\Config\ConfigImporter::import.
      // Copied here so we can log progress.
      if ($config_importer->hasUnprocessedConfigurationChanges()) {
        $sync_steps = $config_importer->initialize();
        foreach ($sync_steps as $step) {
          $context = array();
          do {
            $config_importer->doSyncStep($step, $context);
            if (isset($context['message'])) {
              drush_log(str_replace('Synchronizing', 'Synchronized', (string)$context['message']), LogLevel::OK);
            }
          } while ($context['finished'] < 1);
        }
      }
      drush_log('The configuration was imported successfully.', LogLevel::SUCCESS);
    }
    catch (ConfigException $e) {
      // Return a negative result for UI purposes. We do not differentiate
      // between an actual synchronization error and a failed lock, because
      // concurrent synchronizations are an edge-case happening only when
      // multiple developers or site builders attempt to do it without
      // coordinating.
      $message = 'The import failed due for the following reasons:' . "\n";
      $message .= implode("\n", $config_importer->getErrors());

      watchdog_exception('config_import', $e);
      return drush_set_error('config_import_fail', $message);
    }
  }

     */
  }
}