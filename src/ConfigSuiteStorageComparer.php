<?php

namespace Drupal\config_suite;

use Drupal\Core\Config\StorageComparer;

/**
 * Overrides storage comparer to remove validation.
 */
class ConfigSuiteStorageComparer extends StorageComparer {

  /**
   * {@inheritdoc}
   */
  public function validateSiteUuid() {
    echo 'Validate Works';

    return true;
  }
}