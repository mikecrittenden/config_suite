<?php

namespace Drupal\config_suite;

// This is the interface we are going to implement.
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
// This class contains the event we want to subscribe to.
use Symfony\Component\HttpKernel\KernelEvents;
// Our event listener method will receive one of these.
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
// We'll use this to perform a redirect if necessary.
use Symfony\Component\HttpFoundation\RedirectResponse;

use \Drupal\Core\Config\StorageComparerInterface;
use Drupal\Core\Config\StorageComparer;
use Drupal\Core\Config\ConfigImporter;



/**
 * Subscribe to KernelEvents::REQUEST events and redirect if site is currently
 * in maintenance mode.
 */
class ConfigSuiteImportSubscriber implements EventSubscriberInterface {
  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('checkForRedirection');

    return $events;
  }

  /**
   * This method is called whenever the KernelEvents::REQUEST event is
   * dispatched.
   *
   * @param GetResponseEvent $event
   */
  public function checkForRedirection(GetResponseEvent $event) {

    // Set up the ConfigImporter object for testing.
    $storage_comparer = new StorageComparer(
      \Drupal::service('config.storage.sync'),
      \Drupal::service('config.storage'),
      \Drupal::service('config.manager')
    );

    $config_importer = new ConfigImporter(
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
    if (!$config_importer->alreadyImporting()) {
      try {
        if ($storage_comparer->createChangelist()->hasChanges()) {
          $sync_steps = $config_importer->initialize();
          foreach ($sync_steps as $step) {
            $context = array();
            do {
              $config_importer->doSyncStep($step, $context);
            } while ($context['finished'] < 1);
          }
        }
      } catch (ConfigException $e) {
        // Return a negative result for UI purposes. We do not differentiate
        // between an actual synchronization error and a failed lock, because
        // concurrent synchronizations are an edge-case happening only when
        // multiple developers or site builders attempt to do it without
        // coordinating.
        $message = 'The import failed due for the following reasons:' . "\n";
        $message .= implode("\n", $config_importer->getErrors());

        watchdog_exception('config_import', $e);
      }
    }
  }
}