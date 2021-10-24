<?php

namespace Drupal\security_pack;

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
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class for helper functions.
 */
class SecurityPackOperation {
  private $configFactory;
  private $configStorage;
  private $eventDispatcher;
  private $configManager;
  private $lockPersistent;
  private $typedConfigManager;
  private $moduleHandler;
  private $moduleInstaller;
  private $themeHandler;
  private $stringTranslation;
  private $moduleExtensionList;
  private $cacheConfig;
  private $logger;
  private $messenger;

  /**
   * Instantiates a new security pack importer service.
   */
  public function __construct(ConfigFactory $config_factory, CachedStorage $config_storage, ContainerAwareEventDispatcher $event_dispatcher, ConfigManager $config_manager, PersistentDatabaseLockBackend $lock_persistent, TypedConfigManager $config_typed, ModuleHandler $module_handler, ModuleInstaller $module_installer, ThemeHandler $theme_handler, TranslationManager $string_translation, ModuleExtensionList $module_extension_list, CacheBackendInterface $cache_config, LoggerChannelFactoryInterface $logger, MessengerInterface $messenger) {
    $this->configFactory = $config_factory;
    $this->configStorage = $config_storage;
    $this->eventDispatcher = $event_dispatcher;
    $this->configManager = $config_manager;
    $this->lockPersistent = $lock_persistent;
    $this->typedConfigManager = $config_typed;
    $this->moduleHandler = $module_handler;
    $this->moduleInstaller = $module_installer;
    $this->themeHandler = $theme_handler;
    $this->stringTranslation = $string_translation;
    $this->moduleExtensionList = $module_extension_list;
    $this->cacheConfig = $cache_config;
    $this->logger = $logger;
    $this->messenger = $messenger;
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
      $this->configFactory->getEditable($config)->delete();
      $this->importSingleConfig($config, $config_location);
    }
  }

  /**
   * Import a single config file.
   */
  public function importSingleConfig($config_name, array $locations = [], $prioritise_sync = FALSE) {
    $config_data = $this->readConfig($config_name, $locations, $prioritise_sync);

    $config_storage = $this->configStorage;
    $event_dispatcher = $this->eventDispatcher;
    $config_manager = $this->configManager;
    $lock_persistent = $this->lockPersistent;
    $config_typed = $this->typedConfigManager;
    $module_handler = $this->moduleHandler;
    $module_installer = $this->moduleInstaller;
    $theme_handler = $this->themeHandler;
    $string_translation = $this->stringTranslation;
    $module_extension_list = $this->moduleExtensionList;

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
      $this->cacheConfig->delete($config_name);
    }
    catch (Exception $exception) {
      foreach ($config_importer->getErrors() as $error) {
        $this->logger->get('security_pack')->error($error);
        $this->messenger->addError($error);
      }
      throw $exception;
    }
  }

  /**
   * Reads in a single config yml file.
   */
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
