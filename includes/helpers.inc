<?php

/**
 * Helper functions for importing configuration.
 * Attribution: https://git.drupalcode.org/project/tide_core/-/blob/master/includes/helpers.inc
 */

use Drupal\config\StorageReplaceDataWrapper;
use Drupal\Core\Config\ConfigImporter;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\StorageComparer;

function _security_pack_read_config($id, array $locations = [], $prioritise_sync = TRUE) {
  static $storages;

  global $config_directories;

  if (!$prioritise_sync) {
    // CONFIG_SYNC has lower priority.
    array_push($locations, $config_directories[CONFIG_SYNC_DIRECTORY]);
  }
  else {
    // CONFIG_SYNC has top priority.
    array_unshift($locations, $config_directories[CONFIG_SYNC_DIRECTORY]);
  }

  foreach ($locations as $path) {
    if (file_exists($path . DIRECTORY_SEPARATOR . $id . '.yml')) {
      $storages[$path] = new FileStorage($path);
      break;
    }
  }

  if (!isset($storages[$path])) {
    throw new Exception('Configuration does not exist in any provided locations');
  }

  return $storages[$path]->read($id);
}

function _security_pack_import_single_config($config_name, array $locations = [], $prioritise_sync = TRUE) {
  $config_data = _security_pack_read_config($config_name, $locations, $prioritise_sync);

  $config_storage = Drupal::service('config.storage');
  $event_dispatcher = Drupal::service('event_dispatcher');
  $config_manager = Drupal::service('config.manager');
  $lock_persistent = Drupal::service('lock.persistent');
  $config_typed = Drupal::service('config.typed');
  $module_handler = Drupal::service('module_handler');
  $module_installer = Drupal::service('module_installer');
  $theme_handler = Drupal::service('theme_handler');
  $string_translation = Drupal::service('string_translation');
  $module_extension_list = Drupal::service('extension.list.module');

  Drupal::config($config_name);

  $source_storage = new StorageReplaceDataWrapper($config_storage);
  $source_storage->replaceData($config_name, $config_data);

  $storage_comparer = new StorageComparer(
    $source_storage,
    $config_storage,
    $config_manager
  );

  $storage_comparer->createChangelist();

  $config_importer = new ConfigImporter(
    $storage_comparer,
    $event_dispatcher,
    $config_manager,
    $lock_persistent,
    $config_typed,
    $module_handler,
    $module_installer,
    $theme_handler,
    $string_translation,
    $module_extension_list
  );

  try {
    $config_importer->import();
    Drupal::cache('config')->delete($config_name);
  }
  catch (Exception $exception) {
    foreach ($config_importer->getErrors() as $error) {
      Drupal::logger('security_pack')->error($error);
      Drupal::messenger()->addError($error);
    }
    throw $exception;
  }
}
