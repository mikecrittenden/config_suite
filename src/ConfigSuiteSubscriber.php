<?php

namespace Drupal\config_suite;

use Drupal\system\SystemConfigSubscriber;
use Drupal\Core\Config\ConfigImporterEvent;
use Drupal\Core\Config\ConfigCrudEvent;

use Drupal\config\StorageReplaceDataWrapper;
use Drush\Log\LogLevel;
use Drupal\Core\Config\StorageComparer;
use Drupal\Core\Config\ConfigImporter;
use Drupal\Core\Config\ConfigException;
use Drupal\Core\Config\FileStorage;
use Drupal\Component\Utility\NestedArray;
use Drush\Config\StorageWrapper;
use Drush\Config\CoreExtensionFilter;
use Symfony\Component\Yaml\Parser;


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
    // Get our storage settings.
    $sync_storage = \Drupal::service('config.storage.sync');
    $active_storage = \Drupal::service('config.storage');

    // Find out which config was saved.
    $config = $event->getConfig();
    $changed_config = $config->getName();
    $sync_storage->write($changed_config, $active_storage->read($changed_config));

    // Export configuration collections.
    foreach ($active_storage->getAllCollectionNames() as $collection) {
      $active_collection = $active_storage->createCollection($collection);
      $sync_collection = $sync_storage->createCollection($collection);
      $sync_collection->write($changed_config, $active_collection->read($changed_config));
    }
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