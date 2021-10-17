<?php

namespace Drupal\security_pack;

use Drupal;
use Drupal\config\StorageReplaceDataWrapper;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ConfigImporter;
use Drupal\Core\Config\ConfigManager;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\StorageComparer;
use Drupal\Core\Config\TypedConfigManager;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\ProxyClass\Extension\ModuleInstaller;
use Drupal\Core\Extension\ThemeHandler;
use Drupal\Core\Config\CachedStorage;
use Drupal\Core\ProxyClass\Lock\PersistentDatabaseLockBackend;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Class for helper functions.
 */
class SecurityPackOperation {

  /**
   * {@inheritdoc}
   */
  private $config_factory;
  private $config_storage;
  private $event_dispatcher;
  private $config_manager;
  private $lock_persistent;
  private $config_typed;
  private $module_handler;
  private $module_installer;
  private $theme_handler;
  private $string_translation;
  private $module_extension_list;
  private $cache_config;
  public function __construct(ConfigFactory $config_factory, CachedStorage $config_storage, ContainerAwareEventDispatcher $event_dispatcher, ConfigManager $config_manager, PersistentDatabaseLockBackend $lock_persistent, TypedConfigManager $config_typed, ModuleHandler $module_handler, ModuleInstaller $module_installer, ThemeHandler $theme_handler, TranslationManager $string_translation, ModuleExtensionList $module_extension_list, CacheBackendInterface $cache_config) {
    $this->config_factory = $config_factory;
    $this->config_storage = $config_storage;
    $this->event_dispatcher = $event_dispatcher;
    $this->config_manager = $config_manager;
    $this->lock_persistent = $lock_persistent;
    $this->config_typed = $config_typed;
    $this->module_handler = $module_handler;
    $this->module_installer = $module_installer;
    $this->theme_handler = $theme_handler;
    $this->string_translation = $string_translation;
    $this->module_extension_list = $module_extension_list;
    $this->cache_config = $cache_config;
  }

  /**
   * Install the default set of configuration.
   */
  public function importDefaultConfig() {
    $configs = [
      'antibot.settings',
      'autologout.settings',
      'login_security.settings',
      'seckit.settings',
      'password_policy.password_policy.security_pack_default',
    ];
    module_load_include('inc', 'security_pack', 'includes/helpers');
    $config_location = [drupal_get_path('module', 'security_pack') . '/config/optional'];
    foreach ($configs as $config) {
      $this->config_factory->getEditable($config)->delete();
      _security_pack_import_single_config($config, $config_location);
    }
    drupal_flush_all_caches();
  }

  public function importSingleConfig($config_name, array $locations = [], $prioritise_sync = FALSE) {
    $config_data = $this->readConfig($config_name, $locations, $prioritise_sync);

    $config_storage = $this->config_storage;
    $event_dispatcher = $this->event_dispatcher;
    $config_manager = $this->config_manager;
    $lock_persistent = $this->lock_persistent;
    $config_typed = $this->config_typed;
    $module_handler = $this->module_handler;
    $module_installer = $this->module_installer;
    $theme_handler = $this->theme_handler;
    $string_translation = $this->string_translation;
    $module_extension_list = $this->module_extension_list;

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
      $this->cache_config->delete($config_name);
    }
    catch (Exception $exception) {
      foreach ($config_importer->getErrors() as $error) {
        Drupal::logger('security_pack')->error($error);
        Drupal::messenger()->addError($error);
      }
      throw $exception;
    }
  }

  private function readConfig($id, array $locations = [], $prioritise_sync = TRUE) {
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

}
