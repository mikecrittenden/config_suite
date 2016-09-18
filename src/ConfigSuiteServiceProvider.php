<?php

namespace Drupal\config_suite;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;

/**
*  TODO: Override the default provider.
 */
class ConfigSuiteServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {

    // Class to alter the service interface
    $break = 1 + 1;
  }

}
