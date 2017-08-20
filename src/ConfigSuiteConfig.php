<?php

namespace Drupal\config_suite;

use Drupal\Core\Config;


/**
 * Config override
 */
class ConfigSuiteConfig extends Config {

  public function __construct($name, StorageInterface $storage, EventDispatcherInterface $event_dispatcher, TypedConfigManagerInterface $typed_config) {
    $this->name = $name;
    $this->storage = $storage;
    $this->eventDispatcher = $event_dispatcher;
    $this->typedConfigManager = $typed_config;
  }

  /**
   * {@inheritdoc}
   */
  public function initWithData(array $data) {
    parent::initWithData($data);
    $this->resetOverriddenData();
    return $this;
  }
}