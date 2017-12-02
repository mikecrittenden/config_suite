<?php

namespace Drupal\config_suite;

use Drupal\system\SystemConfigSubscriber;
use Drupal\Core\Config\ConfigImporterEvent;
use Drupal\Core\Config\ConfigCrudEvent;

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

    $config = \Drupal::config('config_suite.settings');
    if (!$config->get('automatic_export')) {
      return;
    }

    // Get our storage settings.
    $sync_storage = \Drupal::service('config.storage.sync');
    $active_storage = \Drupal::service('config.storage');

    // Find out which config was saved.
    $config = $event->getConfig();
    $changed_config = $config->getName();

    //If the config is a config split change, we must rebuild the full config.
    if (substr($changed_config, 0, 13) !== 'config_split.') {
      $sync_storage->write($changed_config, $active_storage->read($changed_config));

      // Export configuration collections.
      foreach ($active_storage->getAllCollectionNames() as $collection) {
        $active_collection = $active_storage->createCollection($collection);
        $sync_collection = $sync_storage->createCollection($collection);
        $sync_collection->write($changed_config, $active_collection->read($changed_config));
      }
    }
    else {
      $sync_storage->deleteAll();

      // Write all .yml files.
      foreach ($active_storage->listAll() as $name) {
        $sync_storage->write($name, $active_storage->read($name));
      }

      // Export configuration collections.
      foreach ($active_storage->getAllCollectionNames() as $collection) {
        $active_collection = $active_storage->createCollection($collection);
        $sync_collection = $sync_storage->createCollection($collection);
        foreach ($active_collection->listAll() as $name) {
          $sync_collection->write($name, $active_collection->read($name));
        }
      }
    }
  }
}